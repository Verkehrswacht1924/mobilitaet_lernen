<?php

declare(strict_types=1);

defined('TYPO3') || die();

use B13\Container\Tca\ContainerConfiguration;
use B13\Container\Tca\Registry;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/*
 * Container-Registrierung portiert aus dpx_container (auf b13/container).
 * Gleiche CType-Identifier UND colPos wie im Original, damit die importierten
 * Kind-Elemente (tx_container_parent + colPos) verlustfrei zugeordnet bleiben.
 */

$registry = GeneralUtility::makeInstance(Registry::class);

// ce_container – ein Inhaltsbereich (colPos 101)
$registry->configureContainer(
    (new ContainerConfiguration('ce_container', 'Container', 'Ein Inhaltsbereich', [
        [['name' => 'Inhalt', 'colPos' => 101]],
    ]))->setSaveAndCloseInNewContentElementWizard(true)
);

// ce_columns2 – zwei Spalten (101/102)
$registry->configureContainer(
    (new ContainerConfiguration('ce_columns2', '2 Spalten', 'Zwei Spalten', [
        [['name' => 'Spalte 1', 'colPos' => 101], ['name' => 'Spalte 2', 'colPos' => 102]],
    ]))->setSaveAndCloseInNewContentElementWizard(true)
);

// ce_columns3 – drei Spalten (101/102/103)
$registry->configureContainer(
    (new ContainerConfiguration('ce_columns3', '3 Spalten', 'Drei Spalten', [
        [['name' => 'Spalte 1', 'colPos' => 101], ['name' => 'Spalte 2', 'colPos' => 102], ['name' => 'Spalte 3', 'colPos' => 103]],
    ]))->setSaveAndCloseInNewContentElementWizard(true)
);

// ce_columns4 – vier Spalten (101–104)
$registry->configureContainer(
    (new ContainerConfiguration('ce_columns4', '4 Spalten', 'Vier Spalten', [
        [
            ['name' => 'Spalte 1', 'colPos' => 101], ['name' => 'Spalte 2', 'colPos' => 102],
            ['name' => 'Spalte 3', 'colPos' => 103], ['name' => 'Spalte 4', 'colPos' => 104],
        ],
    ]))->setSaveAndCloseInNewContentElementWizard(true)
);

// ce_grid – Raster/Slider (colPos 101)
$registry->configureContainer(
    (new ContainerConfiguration('ce_grid', 'Grid', 'Raster / Slider', [
        [['name' => 'Inhalt', 'colPos' => 101]],
    ]))->setSaveAndCloseInNewContentElementWizard(true)
);

// ce_accordion – Akkordeon (colPos 101)
$registry->configureContainer(
    (new ContainerConfiguration('ce_accordion', 'Akkordeon', 'Akkordeon', [
        [['name' => 'Inhalt', 'colPos' => 101]],
    ]))->setSaveAndCloseInNewContentElementWizard(true)
);

// ce_tabs – Tabs (colPos 101)
$registry->configureContainer(
    (new ContainerConfiguration('ce_tabs', 'Tabs', 'Tabs', [
        [['name' => 'Inhalt', 'colPos' => 101]],
    ]))->setSaveAndCloseInNewContentElementWizard(true)
);

/*
 * Zusatzspalten von dpx_container (TCA, damit editierbar). DB-Spalten in ext_tables.sql.
 * Werte feldgleich (gleiche value-Strings) zu dpx_container.
 */
$sliderStyleItems = [
    ['label' => 'Punkte', 'value' => 'slider-style-dots'],
    ['label' => 'Zahlen', 'value' => 'slider-style-numbers'],
];
$sliderNavItems = [
    ['label' => 'Unten', 'value' => 'slider-navigation-bottom'],
    ['label' => 'Oben', 'value' => 'slider-navigation-top'],
];
$sliderArrowItems = [
    ['label' => 'Innen', 'value' => 'slider-arrow-inside'],
    ['label' => 'Außen', 'value' => 'slider-arrow-outside'],
];

$containerColumns = [
    'tx_dpxcontainer_equal_heights' => ['exclude' => 1, 'label' => 'Gleiche Höhen', 'config' => ['type' => 'check']],
    'container_headline' => ['exclude' => 1, 'label' => 'Überschrift', 'config' => ['type' => 'input', 'size' => 50]],
    'container_tab_open' => ['exclude' => 1, 'label' => 'Tab offen (Index)', 'config' => ['type' => 'number', 'default' => 1]],
    'container_accordion_toggle_all' => ['exclude' => 1, 'label' => 'Alle auf/zu', 'config' => ['type' => 'check']],
    'container_accordion_toggle' => ['exclude' => 1, 'label' => 'Einzeln umschalten', 'config' => ['type' => 'check']],
    'container_accordion_open' => ['exclude' => 1, 'label' => 'Erstes offen', 'config' => ['type' => 'check', 'default' => 1]],
    'grid_container' => ['exclude' => 1, 'label' => 'In Container', 'config' => ['type' => 'check']],
    'grid_imgbg' => ['exclude' => 1, 'label' => 'Bild-Hintergrund', 'config' => ['type' => 'check']],
    'grid_parallax' => ['exclude' => 1, 'label' => 'Parallax', 'config' => ['type' => 'check']],
    'grid_bgfullsize' => ['exclude' => 1, 'label' => 'Vollbreite', 'config' => ['type' => 'check']],
    'grid_light' => ['exclude' => 1, 'label' => 'Heller Text', 'config' => ['type' => 'check']],
    'grid_bggradient' => ['exclude' => 1, 'label' => 'Verlauf', 'config' => ['type' => 'check']],
    'grid_bgcolor' => ['exclude' => 1, 'label' => 'Hintergrundfarbe', 'config' => ['type' => 'color', 'size' => 10]],
    'grid_type' => ['exclude' => 1, 'label' => 'Grid-Typ', 'config' => ['type' => 'input', 'size' => 30]],
    'grid_columns' => ['exclude' => 1, 'label' => 'Grid-Spalten', 'config' => ['type' => 'input', 'size' => 30]],
    'grid_bottom_image' => ['exclude' => 1, 'label' => 'Bild unten', 'config' => ['type' => 'input', 'size' => 30]],
    'columns_grid_col_1' => ['exclude' => 1, 'label' => 'Breite Spalte 1', 'config' => ['type' => 'input', 'size' => 30]],
    'columns_grid_col_2' => ['exclude' => 1, 'label' => 'Breite Spalte 2', 'config' => ['type' => 'input', 'size' => 30]],
    'columns_grid_col_3' => ['exclude' => 1, 'label' => 'Breite Spalte 3', 'config' => ['type' => 'input', 'size' => 30]],
    'columns_grid_col_4' => ['exclude' => 1, 'label' => 'Breite Spalte 4', 'config' => ['type' => 'input', 'size' => 30]],
    'slider' => ['exclude' => 1, 'label' => 'Als Slider', 'config' => ['type' => 'check']],
    'slider_type' => ['exclude' => 1, 'label' => 'Slider-Typ', 'config' => ['type' => 'input', 'size' => 30], 'displayCond' => 'FIELD:slider:=:1'],
    'slider_columns' => ['exclude' => 1, 'label' => 'Slider-Spalten', 'config' => ['type' => 'input', 'size' => 30], 'displayCond' => 'FIELD:slider:=:1'],
    'slider_style' => ['exclude' => 1, 'label' => 'Slider-Stil', 'config' => ['type' => 'select', 'renderType' => 'selectSingle', 'items' => $sliderStyleItems], 'displayCond' => 'FIELD:slider:=:1'],
    'slider_navigation' => ['exclude' => 1, 'label' => 'Slider-Navigation', 'config' => ['type' => 'select', 'renderType' => 'selectSingle', 'items' => $sliderNavItems], 'displayCond' => 'FIELD:slider:=:1'],
    'slider_arrows' => ['exclude' => 1, 'label' => 'Slider-Pfeile', 'config' => ['type' => 'select', 'renderType' => 'selectSingle', 'items' => $sliderArrowItems], 'displayCond' => 'FIELD:slider:=:1'],
];
ExtensionManagementUtility::addTCAcolumns('tt_content', $containerColumns);

// showitem je Container-Typ (Funktionsfelder ergänzen; b13 setzt children-Bereich selbst)
$GLOBALS['TCA']['tt_content']['types']['ce_container']['showitem'] =
    '--div--;Allgemein,--palette--;;general,header_kicker,header,subheader,'
    . '--div--;Container,grid_container,grid_bgcolor,grid_bggradient,grid_imgbg,grid_bgimage,grid_bgfullsize,grid_parallax,grid_bottom_image,grid_light,'
    . '--div--;Erscheinungsbild,--palette--;;frames,--palette--;;appearanceLinks,'
    . '--div--;Sprache,--palette--;;language,--div--;Zugriff,--palette--;;hidden,--palette--;;access';

$colHeader = '--div--;Allgemein,--palette--;;general,header_kicker,header,subheader,tx_dpxcontainer_equal_heights,';
$colAppearance = '--div--;Erscheinungsbild,--palette--;;frames,--palette--;;appearanceLinks,--div--;Sprache,--palette--;;language,--div--;Zugriff,--palette--;;hidden,--palette--;;access';

$GLOBALS['TCA']['tt_content']['types']['ce_columns2']['showitem'] =
    $colHeader . '--div--;Grid,columns_grid_col_1,columns_grid_col_2,grid_container,grid_bgcolor,grid_imgbg,grid_bgimage,grid_bgfullsize,grid_parallax,grid_bottom_image,grid_light,' . $colAppearance;
$GLOBALS['TCA']['tt_content']['types']['ce_columns3']['showitem'] =
    $colHeader . '--div--;Grid,columns_grid_col_1,columns_grid_col_2,columns_grid_col_3,grid_container,grid_bgcolor,grid_imgbg,grid_bgimage,grid_bgfullsize,grid_parallax,grid_bottom_image,grid_light,' . $colAppearance;
$GLOBALS['TCA']['tt_content']['types']['ce_columns4']['showitem'] =
    $colHeader . '--div--;Grid,columns_grid_col_1,columns_grid_col_2,columns_grid_col_3,columns_grid_col_4,grid_container,grid_bgcolor,grid_imgbg,grid_bgimage,grid_bgfullsize,grid_parallax,grid_bottom_image,grid_light,' . $colAppearance;
$GLOBALS['TCA']['tt_content']['types']['ce_grid']['showitem'] =
    $colHeader . '--div--;Grid,grid_type,grid_columns,grid_container,grid_bgcolor,grid_bggradient,grid_imgbg,grid_bgimage,grid_light,grid_bgfullsize,grid_parallax,grid_bottom_image,slider,slider_type,slider_columns,slider_style,slider_navigation,slider_arrows,' . $colAppearance;
$GLOBALS['TCA']['tt_content']['types']['ce_accordion']['showitem'] =
    $colHeader . '--div--;Akkordeon,container_headline,container_accordion_toggle_all,container_accordion_toggle,container_accordion_open,grid_container,' . $colAppearance;
$GLOBALS['TCA']['tt_content']['types']['ce_tabs']['showitem'] =
    $colHeader . '--div--;Tabs,container_tab_open,grid_container,' . $colAppearance;
