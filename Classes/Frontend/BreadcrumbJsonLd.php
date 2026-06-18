<?php

declare(strict_types=1);

namespace VMS\Sitepackage\Frontend;

use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Erzeugt eine schema.org BreadcrumbList als JSON-LD aus der Rootline.
 * Aufruf als TypoScript USER-Funktion (cached, pro Seite).
 */
final class BreadcrumbJsonLd
{
    public ?ContentObjectRenderer $cObj = null;

    public function setContentObjectRenderer(ContentObjectRenderer $cObj): void
    {
        $this->cObj = $cObj;
    }

    public function render(string $content, array $conf): string
    {
        $request = $this->cObj?->getRequest();
        $pageInformation = $request?->getAttribute('frontend.page.information');
        if ($pageInformation === null) {
            return '';
        }
        $rootLine = $pageInformation->getRootLine();
        if (!is_array($rootLine) || $rootLine === []) {
            return '';
        }

        // Rootline kommt von der aktuellen Seite aufwärts -> für Breadcrumb umdrehen (Wurzel zuerst)
        $crumbs = [];
        foreach (array_reverse($rootLine) as $page) {
            $doktype = (int)($page['doktype'] ?? 1);
            if (in_array($doktype, [199, 254, 255], true)) { // Spacer/Ordner/Papierkorb überspringen
                continue;
            }
            $uid = (int)($page['uid'] ?? 0);
            if ($uid <= 0) {
                continue;
            }
            $title = trim((string)($page['nav_title'] ?? ''));
            if ($title === '') {
                $title = trim((string)($page['title'] ?? ''));
            }
            if ($title === '') {
                continue;
            }
            $url = $this->cObj->createUrl([
                'parameter' => 't3://page?uid=' . $uid,
                'forceAbsoluteUrl' => '1',
            ]);
            // Aufeinanderfolgende URL-Duplikate überspringen (z.B. Wurzel-Shortcut == Startseite)
            $prev = end($crumbs);
            if ($prev !== false && $prev['url'] === $url) {
                continue;
            }
            $crumbs[] = ['name' => $title, 'url' => $url];
        }

        // Nur ausgeben, wenn es echte Tiefe gibt (mehr als nur die Startseite)
        if (count($crumbs) < 2) {
            return '';
        }

        $items = [];
        $position = 1;
        foreach ($crumbs as $crumb) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $crumb['name'],
                'item' => $crumb['url'],
            ];
        }

        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
        $json = json_encode(
            $data,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP
        );

        return '<script type="application/ld+json">' . $json . '</script>';
    }
}
