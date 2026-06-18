<?php

declare(strict_types=1);

namespace VMS\Sitepackage\Solr;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Lizenzfreier Ersatz für die alten DPX-Solr-Index-userFuncs
 * (DPX\DpxTemplate\Solr\UserFunc\IndexUserFunc).
 *
 * Liefert pro Publikations-Datensatz die Werte der FAL-Datei (Feld "file"):
 * absolute URL, relativer Pfad, Dateiendung und (lesbare) Größe.
 *
 * Aufruf als USER-cObject im Solr-IndexQueue (apache-solr-for-typo3): der
 * Indexer ruft cObjGetSingle() mit dem Datensatz als cObj->data auf, der
 * ContentObjectRenderer wird per setContentObjectRenderer() injiziert.
 *
 * WICHTIG: Für die Tabelle tx_dpxtemplate_domain_model_publication existiert
 * im Rebuild KEINE TCA (DPX-Extension entfernt). Daher wird die Datei-Referenz
 * direkt aus sys_file_reference gelesen (Kern-Tabelle mit TCA) statt über
 * FileRepository::findByRelation(), das eine TCA-Definition voraussetzt.
 */
final class PublicationIndexer
{
    private const TABLE = 'tx_dpxtemplate_domain_model_publication';
    private const FIELD = 'file';

    public ?ContentObjectRenderer $cObj = null;

    public function setContentObjectRenderer(ContentObjectRenderer $cObj): void
    {
        $this->cObj = $cObj;
    }

    /** Absolute URL der Datei (Site-Base + öffentlicher Pfad). */
    public function getFileLink(string $content, array $conf): string
    {
        $file = $this->resolveFile();
        if ($file === null) {
            return '';
        }
        $relative = $this->buildPublicPath($file);
        if ($relative === '') {
            return '';
        }
        $base = $this->getSiteBase();
        return $base !== '' ? $base . $relative : $relative;
    }

    /** Relativer Pfad ab Web-Root, z.B. /fileadmin/.../datei.pdf */
    public function getRelativeFileLink(string $content, array $conf): string
    {
        $file = $this->resolveFile();
        return $file === null ? '' : $this->buildPublicPath($file);
    }

    /** Dateiendung in Kleinbuchstaben, z.B. pdf */
    public function getFileExtensionForFileUid(string $content, array $conf): string
    {
        $file = $this->resolveFile();
        return $file === null ? '' : strtolower($file->getExtension());
    }

    /** Lesbare Dateigröße, z.B. "1.23 MB" */
    public function getFileSizeForFileUid(string $content, array $conf): string
    {
        $file = $this->resolveFile();
        if ($file === null) {
            return '';
        }
        return trim(GeneralUtility::formatSize((int)$file->getSize(), ' | KB| MB| GB| TB'));
    }

    /**
     * Erste FAL-Datei des Felds "file" für den aktuellen Datensatz.
     * Liest sys_file_reference direkt (TCA-frei) und lädt die sys_file.
     */
    private function resolveFile(): ?File
    {
        $uid = (int)($this->cObj?->data['uid'] ?? 0);
        if ($uid <= 0) {
            return null;
        }
        try {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable('sys_file_reference');
            $fileUid = $queryBuilder
                ->select('uid_local')
                ->from('sys_file_reference')
                ->where(
                    $queryBuilder->expr()->eq('tablenames', $queryBuilder->createNamedParameter(self::TABLE)),
                    $queryBuilder->expr()->eq('fieldname', $queryBuilder->createNamedParameter(self::FIELD)),
                    $queryBuilder->expr()->eq('uid_foreign', $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT))
                )
                ->orderBy('sorting_foreign', 'ASC')
                ->setMaxResults(1)
                ->executeQuery()
                ->fetchOne();
            if (!$fileUid) {
                return null;
            }
            $file = GeneralUtility::makeInstance(ResourceFactory::class)->getFileObject((int)$fileUid);
            return $file instanceof File ? $file : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Öffentlicher Pfad ab Web-Root (mit führendem /). Robust auch im CLI-/
     * Index-Kontext: bevorzugt getPublicUrl(), fällt sonst auf
     * Storage-Basis + Identifier zurück.
     */
    private function buildPublicPath(File $file): string
    {
        try {
            $url = (string)$file->getPublicUrl();
        } catch (\Throwable) {
            $url = '';
        }
        if ($url !== '') {
            return '/' . ltrim($url, '/');
        }
        try {
            $identifier = ltrim($file->getIdentifier(), '/');
            $base = trim((string)($file->getStorage()->getConfiguration()['basePath'] ?? ''), '/');
            $path = trim($base . '/' . $identifier, '/');
            return $path === '' ? '' : '/' . $path;
        } catch (\Throwable) {
            return '';
        }
    }

    /**
     * Site-Base ohne abschließenden Slash (z.B. https://www.example.de).
     */
    private function getSiteBase(): string
    {
        $pid = (int)($this->cObj?->data['pid'] ?? 0);
        if ($pid <= 0) {
            return '';
        }
        try {
            $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($pid);
            return rtrim((string)$site->getBase(), '/');
        } catch (\Throwable) {
            return '';
        }
    }
}
