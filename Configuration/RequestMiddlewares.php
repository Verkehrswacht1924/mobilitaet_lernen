<?php

declare(strict_types=1);

use VMS\Sitepackage\Middleware\ZipDownloadMiddleware;

/**
 * Registriert die ZIP-Sammeldownload-Middleware im Frontend-Stack.
 * Läuft nach der Site-Auflösung und vor dem Seiten-Rendering, damit sie das
 * Auswahl-POST früh abfangen und das ZIP direkt ausliefern kann.
 */
return [
    'frontend' => [
        'vms/sitepackage/zip-download' => [
            'target' => ZipDownloadMiddleware::class,
            'after' => [
                'typo3/cms-frontend/site',
            ],
            'before' => [
                'typo3/cms-frontend/page-resolver',
            ],
        ],
    ],
];
