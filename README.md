# vms_sitepackage

TYPO3-13-Sitepackage für **mobilität-lernen.de** (MobiLe) — lizenzfreier Rebuild
des früheren proprietären DPX-Setups (TYPO3 11). Enthält alle selbstgeschriebenen
bzw. portierten Teile der Seite: TypoScript, Fluid-Templates, Content-Blocks-
Inhaltselemente, Container-/SEO-/Security-Konfiguration und die Frontend-Assets.

## Inhalt

| Pfad | Zweck |
|------|-------|
| `ContentBlocks/ContentElements/` | Inhaltselemente: banner, gallery, stage, file, mobility, video, audio (feldgleich zu den alten dpx-CTypes) |
| `Configuration/TypoScript/` | PAGE/lib, Navigation, Footer, `95.SeoSecurity.typoscript` (Permissions-Policy, JSON-LD, OpenGraph), Container-Rendering |
| `Configuration/TCA/Overrides/` | Header-/Seiten-Zusatzfelder, b13/container-Registrierung (`container.php`) |
| `Configuration/Extensions/` | fluid_styled_content-, seo-, solr-TypoScript |
| `Classes/Solr/PublicationIndexer.php` | Lizenzfreier Ersatz der DPX-Solr-Index-userFuncs (Datei-URL/Pfad/Endung/Größe der Publikationen; liest `sys_file_reference` direkt, da die Publikations-Tabelle keine TCA hat) |
| `Classes/Middleware/ZipDownloadMiddleware.php` | ZIP-Sammeldownload der Publikationssuche (Auswahl-POST → validiert UIDs → streamt ZIP); ersetzt den alten DPX-`ZipResults`-Controller |
| `Resources/Public/` | CSS/JS/Fonts/Bilder (Design) |
| `Resources/Private/` | Layouts, Partials, Container-/CE-Templates |
| `ext_localconf.php` | Content-Rendering-Templates, PageTS, RTE, Icons |
| `ext_tables.sql` | geteilte Nicht-CB-Spalten (Container, pages-Felder, Header) |

## Abhängigkeiten (Composer, im Projekt-`composer.json`)
`typo3/cms-core ^13.4`, `friendsoftypo3/content-blocks`, `b13/container`,
`apache-solr-for-typo3/solr`, `fluidtypo3/vhs`.

## Deployment
Live unter `…/neu13/packages/vms_sitepackage` (Composer Path-Repo, symlinked).
Nach Änderungen: Dateien dorthin übertragen, dann
`php vendor/bin/typo3 cache:flush` (bzw. `extension:setup` bei DB-Schema-Änderungen).

## Wichtige Server-Konfiguration AUSSERHALB dieses Pakets (nicht versioniert)
- **Asset-Verzeichnis:** `public/_assets/<hash>` muss ein **reales Verzeichnis**
  (Kopie von `Resources/Public`) sein, kein Symlink — KAS folgt dem Symlink nicht
  (403). Solange die Kopie existiert, lässt Composer sie in Ruhe.
- **Kanonik-Redirect:** Regel in `public/.htaccess` (alle Hosts → `www.mobilität-lernen.de`).
- **Redis-Caching:** `config/system/settings.php` (pages/hash/rootline/pagesection/imagesizes → Redis db3–7).
- **trustedHostsPattern**, **Site-Config** (`config/sites/dev-typo3/config.yaml`), **DB-Zugang** liegen ebenfalls außerhalb dieses Pakets.
- **Publikationssuche (Solr):** Die facettierte Suche liegt auf den Themenseiten
  (`/schulungsmaterial/*`, `/zu-fuss`, `/mit-bus-und-bahn`, `/suche`), NICHT auf
  `/publikationen`. Datensätze: `tx_dpxtemplate_domain_model_publication` (pid 77,
  ohne TCA). Index-Worker = Scheduler-Task „IndexQueueWorkerTask" (`scheduler:run
  --task=<uid> --force`). `allowedSites = __solr_current_site` (apache-solr 13.x hasht
  die Site-Identität – explizite Domains erzeugen einen siteHash-Mismatch → 0 Treffer).
  Solr ist ein **geteilter** Core auf `localhost:8983` (core_de/core_en) – NICHT pauschal
  per `*:*` löschen.
- **Neue Frontend-Assets (CSS/JS):** `public/_assets/<hash>` ist eine reale Kopie –
  neue Dateien in `Resources/Public/` müssen **manuell** dorthin kopiert werden
  (z. B. `swzip.js`), Composer fasst die bestehende Kopie nicht an.
- **CSS-Minifizierung:** Das aus dem dpx-Setup importierte Root-Template (`sys_template`
  uid 1, DB) setzt im Config-Block hart `compressCss = 0` und überschreibt damit die
  Sitepackage-Einstellung. Daher ist im DB-Config-Feld am Ende `config.compressCss = 1`
  angehängt (Override). `config/.../95.SeoSecurity.typoscript` enthält die gleiche
  Einstellung als dokumentierte Absicht; effektiv wirkt der DB-Override.
