<?php

declare(strict_types=1);

defined('TYPO3') || die();

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

// Header-Erweiterungen (portiert aus vms_sitepackage tt_content_header.php)
$headerColumns = [
    'header_kicker' => [
        'l10n_mode' => 'prefixLangTitle',
        'label' => 'Kicker',
        'config' => [
            'type' => 'input',
            'size' => 50,
            'max' => 255,
        ],
    ],
    'tx_header_inside' => [
        'exclude' => 0,
        'label' => 'Header im Inhalt',
        'config' => [
            'type' => 'check',
        ],
    ],
    'tx_header_style' => [
        'exclude' => 1,
        'label' => 'Header-Stil',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [
                ['label' => 'Standard', 'value' => ''],
                ['label' => 'H2', 'value' => 'h2'],
                ['label' => 'H3', 'value' => 'h3'],
                ['label' => 'H4', 'value' => 'h4'],
                ['label' => 'H5', 'value' => 'h5'],
                ['label' => 'H6', 'value' => 'h6'],
            ],
        ],
    ],
    'description' => [
        'label' => 'Beschreibung',
        'config' => [
            'type' => 'text',
            'enableRichtext' => true,
            'richtextConfiguration' => 'default',
        ],
    ],
];

ExtensionManagementUtility::addTCAcolumns('tt_content', $headerColumns);

// Header-Felder in die vorhandene "headers"-Palette einreihen
ExtensionManagementUtility::addFieldsToPalette(
    'tt_content',
    'headers',
    'header_kicker,--linebreak--,tx_header_style,tx_header_inside',
    'after:header_position'
);
