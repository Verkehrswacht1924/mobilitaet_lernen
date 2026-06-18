#!/bin/sh
# vms-maintenance.sh — tägliche Wartung für mobilität-lernen.de (neu13)
#
# Hält Datenbank-Logtabellen und Log-Dateien klein, damit die Installation
# ohne manuelles Zutun auskommt. Liest die DB-Zugangsdaten aus settings.php
# (verlässt den Server nicht). Idempotent, gefahrlos wiederholbar.
#
# Einrichtung (KAS-Cronjob), täglich z.B. 04:00 Uhr:
#   /bin/sh /www/htdocs/w021b5d5/neu13/bin/vms-maintenance.sh
#
# Ergänzt den 5-Minuten-Cron, der den TYPO3-Scheduler fährt:
#   /usr/bin/php83 /www/htdocs/w021b5d5/neu13/vendor/bin/typo3 scheduler:run
set -u

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
PHP=/usr/bin/php83
cd "$ROOT" || exit 1

# DB-Zugang aus settings.php lesen (bleibt auf dem Server).
CREDS="$("$PHP" -r '$c=require $argv[1]; $d=$c["DB"]["Connections"]["Default"]??[]; echo implode("\n",[$d["host"]??"",$d["user"]??"",$d["password"]??"",$d["dbname"]??""]);' "$ROOT/config/system/settings.php" 2>/dev/null)"
DBH="$(printf '%s' "$CREDS" | sed -n 1p)"
DBU="$(printf '%s' "$CREDS" | sed -n 2p)"
DBP="$(printf '%s' "$CREDS" | sed -n 3p)"
DBN="$(printf '%s' "$CREDS" | sed -n 4p)"

if [ -n "$DBN" ]; then
    MYSQL_PWD="$DBP" /usr/bin/mysql -h "$DBH" -u "$DBU" "$DBN" -e "
        DELETE FROM sys_log     WHERE tstamp     < UNIX_TIMESTAMP(NOW() - INTERVAL 90 DAY);
        DELETE FROM sys_history WHERE tstamp     < UNIX_TIMESTAMP(NOW() - INTERVAL 30 DAY);
        DELETE FROM fe_sessions WHERE ses_tstamp < UNIX_TIMESTAMP(NOW() - INTERVAL 2 DAY);
        DELETE FROM be_sessions WHERE ses_tstamp < UNIX_TIMESTAMP(NOW() - INTERVAL 7 DAY);
    " 2>/dev/null
fi

# Schutznetz: einzelne Log-Dateien über 50 MB leeren (gegen plötzliche Ausreißer).
find "$ROOT/var/log" -name '*.log' -type f -size +50M -exec sh -c ': > "$1"' _ {} \; 2>/dev/null

exit 0
