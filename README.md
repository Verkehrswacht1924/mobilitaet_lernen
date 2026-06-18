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
