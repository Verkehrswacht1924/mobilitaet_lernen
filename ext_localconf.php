<?php

declare(strict_types=1);

defined('TYPO3') || die();

use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/*
 * Frontend-Rendering portiert aus dpx_template (lizenzfrei, ohne DPX-PHP-Klassen).
 */

// TypoScript als Content-Rendering-Template registrieren (lädt setup+constants
// nach fluid_styled_content) – ersetzt den alten dpx_template-Eintrag.
$GLOBALS['TYPO3_CONF_VARS']['FE']['contentRenderingTemplates'][] = 'vms_sitepackage/Configuration/TypoScript/';

// PageTS: Backend-Layouts, RTE, TCEFORM/TCEMAIN/TCADefaults
// (ContentElement-Wizard NICHT – Content Blocks + b13/container registrieren ihre CEs selbst)
foreach ([
    'Mod/WebLayout/BackendLayouts.tsconfig',
    'RTE.tsconfig',
    'TCADefaults.tsconfig',
    'TCEMAIN.tsconfig',
    'TCEFORM.tsconfig',
] as $tsconfig) {
    $file = 'EXT:vms_sitepackage/Configuration/TsConfig/Page/' . $tsconfig;
    if (file_exists(GeneralUtility::getFileAbsFileName($file))) {
        ExtensionManagementUtility::addPageTSConfig('@import "' . $file . '"');
    }
}

// RTE-Default-Preset
$rte = GeneralUtility::getFileAbsFileName('EXT:vms_sitepackage/Configuration/RTE/Default.yaml');
if ($rte !== '' && file_exists($rte)) {
    $GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets']['default'] = 'EXT:vms_sitepackage/Configuration/RTE/Default.yaml';
}

// EXT:form Basis-Konfiguration
if (ExtensionManagementUtility::isLoaded('form')) {
    $formYaml = GeneralUtility::getFileAbsFileName('EXT:vms_sitepackage/Resources/Extensions/form/Yaml/BaseSetup.yaml');
    if ($formYaml !== '' && file_exists($formYaml)) {
        ExtensionManagementUtility::addTypoScriptSetup('
            module.tx_form.settings.yamlConfigurations.110 = EXT:vms_sitepackage/Resources/Extensions/form/Yaml/BaseSetup.yaml
            plugin.tx_form.settings.yamlConfigurations.110 = EXT:vms_sitepackage/Resources/Extensions/form/Yaml/BaseSetup.yaml
        ');
    }
}

// Icons der Inhaltselemente (Backend), Quelle aus portierten Assets
$iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
foreach (['Audio', 'Video', 'Stage', 'Gallery', 'Grid', 'Container', 'Tabs', 'Accordion', 'Columns2', 'Columns3', 'Columns4', 'Frame', 'NoFrame'] as $icon) {
    $src = GeneralUtility::getFileAbsFileName('EXT:vms_sitepackage/Resources/Public/Images/Icons/' . $icon . '.svg');
    if ($src !== '' && file_exists($src)) {
        $iconRegistry->registerIcon(
            'tx_' . strtolower($icon),
            SvgIconProvider::class,
            ['source' => 'EXT:vms_sitepackage/Resources/Public/Images/Icons/' . $icon . '.svg']
        );
    }
}
