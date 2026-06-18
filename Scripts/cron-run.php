<?php

declare(strict_types=1);

/*
 * URL-Auslöser für den KAS-Cronjob (ALL-INKL ruft Cronjobs per URL auf).
 * Deploy nach: neu13/public/_cron/run.php
 * Aufruf (alle 5 Min): https://www.xn--mobilitt-lernen-6kb.de/_cron/run.php?token=<TOKEN>
 *
 * - startet bei jedem Aufruf den TYPO3-Scheduler (Solr-Index, IP-Anonymisierung)
 * - führt den Tagesputz (bin/vms-maintenance.sh) höchstens 1x/Tag aus (selbstdrosselnd)
 *
 * Das erwartete Token steht in neu13/var/vms_cron_token (NICHT im Git, nicht
 * web-erreichbar). Ohne gültiges Token -> 403.
 */

$root = dirname(__DIR__, 2); // public/_cron -> neu13
$php = '/usr/bin/php83';

$tokenFile = $root . '/var/vms_cron_token';
$expected = is_file($tokenFile) ? trim((string)file_get_contents($tokenFile)) : '';
$given = (string)($_GET['token'] ?? '');

if ($expected === '' || !hash_equals($expected, $given)) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo "forbidden\n";
    exit;
}

header('Content-Type: text/plain; charset=utf-8');
@set_time_limit(300);

// 1) TYPO3-Scheduler bei jedem Lauf (Solr-IndexQueueWorker, IP-Anonymisierung)
@exec(escapeshellarg($php) . ' ' . escapeshellarg($root . '/vendor/bin/typo3') . ' scheduler:run 2>&1', $o1, $rc1);
echo 'scheduler:run rc=' . (int)$rc1 . "\n";

// 2) Tagesputz höchstens ~1x/Tag (Marker in var/, außerhalb des Webroots)
$marker = $root . '/var/vms_maint_last';
$now = time();
$last = is_file($marker) ? (int)file_get_contents($marker) : 0;
if (($now - $last) > 72000) { // ~20 Stunden
    @exec('/usr/bin/sh ' . escapeshellarg($root . '/bin/vms-maintenance.sh') . ' 2>&1', $o2, $rc2);
    @file_put_contents($marker, (string)$now);
    echo 'maintenance rc=' . (int)$rc2 . "\n";
} else {
    echo 'maintenance skipped (' . (int)(($now - $last) / 3600) . "h ago)\n";
}

echo "ok\n";
