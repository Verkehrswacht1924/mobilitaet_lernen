<?php

declare(strict_types=1);

namespace VMS\Sitepackage\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\Stream;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Lizenzfreier Ersatz für den alten DPX-„ZipResults"-Controller.
 *
 * Fängt das POST der Publikations-Auswahl (Feld tx_vmszip_download) ab,
 * validiert die ausgewählten Publikations-UIDs gegen die DB-Tabelle und
 * liefert die zugehörigen FAL-Dateien gebündelt als ZIP aus.
 *
 * Sicherheit: Es werden ausschließlich Dateien sichtbarer, nicht gelöschter
 * Publikationen aus dem Publikations-Ordner ausgeliefert; die Dateipfade
 * werden serverseitig aus der DB aufgelöst (keine Pfad-/Datei-Eingabe durch
 * den Client).
 */
final class ZipDownloadMiddleware implements MiddlewareInterface
{
    private const TABLE = 'tx_dpxtemplate_domain_model_publication';
    private const STORAGE_PID = 77;
    private const MAX_FILES = 100;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Legacy: alte "ZipResults"-Links (aus Seiten-Cache, Bookmarks, Suchmaschinen,
        // Crawlern) würden sonst eine Extbase-Exception werfen und das Log fluten.
        // Sauber per 301 auf die Seite selbst umleiten (Crawler verlernen die toten URLs).
        if ($this->isLegacyZipResultsRequest($request)) {
            return new RedirectResponse($request->getUri()->getPath(), 301);
        }

        $body = $request->getParsedBody();
        if (
            $request->getMethod() !== 'POST'
            || !is_array($body)
            || empty($body['tx_vmszip_download'])
        ) {
            return $handler->handle($request);
        }

        $uids = $this->sanitizeUids($body['tx_vmszip_pub'] ?? []);
        if ($uids === []) {
            // Nichts ausgewählt -> normale Seite weiter ausliefern.
            return $handler->handle($request);
        }

        $files = $this->collectFiles($uids);
        if ($files === []) {
            return $handler->handle($request);
        }

        return $this->streamZip($files);
    }

    /**
     * Erkennt Aufrufe der alten DPX-ZipResults-Plugin-Action (Extbase-Routing
     * über tx_solr[controller]/[action] bzw. den rohen Query-String).
     */
    private function isLegacyZipResultsRequest(ServerRequestInterface $request): bool
    {
        $solr = $request->getQueryParams()['tx_solr'] ?? null;
        if (is_array($solr)) {
            if (($solr['controller'] ?? '') === 'ZipResults' || ($solr['action'] ?? '') === 'downloadeZip') {
                return true;
            }
        }
        $rawQuery = $request->getUri()->getQuery();
        return $rawQuery !== '' && (str_contains($rawQuery, 'ZipResults') || str_contains($rawQuery, 'downloadeZip'));
    }

    /**
     * @param mixed $raw
     * @return int[]
     */
    private function sanitizeUids(mixed $raw): array
    {
        if (!is_array($raw)) {
            return [];
        }
        $uids = [];
        foreach ($raw as $value) {
            $uid = (int)$value;
            if ($uid > 0) {
                $uids[$uid] = $uid;
            }
        }
        return array_slice(array_values($uids), 0, self::MAX_FILES);
    }

    /**
     * Liefert [['name' => Zielname, 'file' => File], ...] für gültige Publikationen.
     *
     * @param int[] $uids
     * @return array<int, array{name: string, file: File}>
     */
    private function collectFiles(array $uids): array
    {
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable(self::TABLE);
        // Für die Publikations-Tabelle existiert keine TCA -> Restriktionen manuell.
        $queryBuilder->getRestrictions()->removeAll();

        $rows = $queryBuilder
            ->select('p.uid', 'p.name', 'r.uid_local')
            ->from(self::TABLE, 'p')
            ->join(
                'p',
                'sys_file_reference',
                'r',
                (string)$queryBuilder->expr()->and(
                    $queryBuilder->expr()->eq('r.uid_foreign', $queryBuilder->quoteIdentifier('p.uid')),
                    $queryBuilder->expr()->eq('r.tablenames', $queryBuilder->createNamedParameter(self::TABLE)),
                    $queryBuilder->expr()->eq('r.fieldname', $queryBuilder->createNamedParameter('file')),
                    $queryBuilder->expr()->eq('r.deleted', 0)
                )
            )
            ->where(
                $queryBuilder->expr()->in('p.uid', $queryBuilder->createNamedParameter($uids, Connection::PARAM_INT_ARRAY)),
                $queryBuilder->expr()->eq('p.pid', $queryBuilder->createNamedParameter(self::STORAGE_PID, Connection::PARAM_INT)),
                $queryBuilder->expr()->eq('p.hidden', 0),
                $queryBuilder->expr()->eq('p.deleted', 0)
            )
            ->orderBy('r.sorting_foreign', 'ASC')
            ->executeQuery()
            ->fetchAllAssociative();

        $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
        $files = [];
        $usedNames = [];
        foreach ($rows as $row) {
            try {
                $file = $resourceFactory->getFileObject((int)$row['uid_local']);
            } catch (\Throwable) {
                continue;
            }
            if (!$file instanceof File) {
                continue;
            }
            $name = $this->buildEntryName((string)$row['name'], $file, $usedNames);
            $files[] = ['name' => $name, 'file' => $file];
        }
        return $files;
    }

    /**
     * Erzeugt einen sauberen, eindeutigen Dateinamen für das ZIP.
     *
     * @param array<string, bool> $usedNames
     */
    private function buildEntryName(string $title, File $file, array &$usedNames): string
    {
        $extension = strtolower($file->getExtension());
        $base = trim($title) !== '' ? $title : $file->getNameWithoutExtension();
        // Auf dateisystem-/ZIP-sichere Zeichen reduzieren.
        $base = preg_replace('/[^\p{L}\p{N}\-_. ]+/u', '', $base) ?? '';
        $base = trim(preg_replace('/\s+/', '_', $base) ?? '');
        if ($base === '') {
            $base = 'datei';
        }
        $name = $base . ($extension !== '' ? '.' . $extension : '');
        $counter = 2;
        while (isset($usedNames[strtolower($name)])) {
            $name = $base . '_' . $counter . ($extension !== '' ? '.' . $extension : '');
            $counter++;
        }
        $usedNames[strtolower($name)] = true;
        return $name;
    }

    /**
     * @param array<int, array{name: string, file: File}> $files
     */
    private function streamZip(array $files): ResponseInterface
    {
        $zipPath = GeneralUtility::tempnam('vms_zip_', '.zip');
        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::OVERWRITE) !== true) {
            GeneralUtility::unlink_tempfile($zipPath);
            return (new Response())->withStatus(500);
        }
        foreach ($files as $entry) {
            try {
                $zip->addFromString($entry['name'], $entry['file']->getContents());
            } catch (\Throwable) {
                // Einzelne unlesbare Datei überspringen, Rest trotzdem ausliefern.
                continue;
            }
        }
        $zip->close();

        // Temp-Datei nach Auslieferung wieder entfernen.
        register_shutdown_function(static function () use ($zipPath): void {
            if (is_file($zipPath)) {
                @unlink($zipPath);
            }
        });

        $filename = 'publikationen_' . date('Y-m-d') . '.zip';
        $stream = new Stream($zipPath, 'r');

        return (new Response())
            ->withHeader('Content-Type', 'application/zip')
            ->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->withHeader('Content-Length', (string)(filesize($zipPath) ?: 0))
            ->withHeader('Cache-Control', 'no-store, must-revalidate')
            ->withBody($stream);
    }
}
