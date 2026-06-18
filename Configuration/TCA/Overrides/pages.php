<?php

declare(strict_types=1);

defined('TYPO3') || die();

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

// Seiten-Zusatzfelder (portiert aus vms_sitepackage pages.php)
$pageColumns = [
    'newsletter' => [
        'exclude' => 1,
        'label' => 'Newsletter-Box',
        'description' => 'Newsletter-Box im Footer anzeigen',
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
            'items' => [
                ['label' => '', 'value' => 'enabled', 'labelChecked' => 'An', 'labelUnchecked' => 'Aus'],
            ],
        ],
    ],
    'socialmedia' => [
        'exclude' => 1,
        'label' => 'Social-Media-Leiste',
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
            'items' => [
                ['label' => '', 'value' => 'enabled', 'labelChecked' => 'An', 'labelUnchecked' => 'Aus'],
            ],
        ],
    ],
    'breadcrumb' => [
        'exclude' => 1,
        'label' => 'Breadcrumb',
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
            'items' => [
                ['label' => '', 'value' => 'enabled', 'labelChecked' => 'An', 'labelUnchecked' => 'Aus'],
            ],
        ],
    ],
    'teaser_description' => [
        'label' => 'Teaser-Beschreibung',
        'config' => [
            'type' => 'text',
            'cols' => 60,
            'rows' => 10,
        ],
    ],
    'teaser_description_overview' => [
        'label' => 'Teaser-Beschreibung (Übersicht)',
        'config' => [
            'type' => 'text',
            'cols' => 60,
            'rows' => 10,
        ],
    ],
    'category_title' => [
        'label' => 'Kategorie-Titel',
        'config' => [
            'type' => 'text',
            'cols' => 60,
            'rows' => 3,
        ],
    ],
    'highlight' => [
        'exclude' => 1,
        'label' => 'Highlight',
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
        ],
    ],
    'event' => [
        'exclude' => 1,
        'onChange' => 'reload',
        'label' => 'Veranstaltungsseite',
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
            'items' => [['label' => '', 'value' => '1']],
            'default' => 0,
        ],
    ],
    'event_startdate' => [
        'exclude' => 1,
        'label' => 'Beginn',
        'config' => ['type' => 'input', 'size' => 20],
        'displayCond' => 'FIELD:event:=:1',
    ],
    'event_enddate' => [
        'exclude' => 1,
        'label' => 'Ende',
        'config' => ['type' => 'input', 'size' => 20],
        'displayCond' => 'FIELD:event:=:1',
    ],
    'event_location' => [
        'exclude' => 1,
        'label' => 'Ort',
        'config' => ['type' => 'input', 'size' => 48, 'max' => 255],
        'displayCond' => 'FIELD:event:=:1',
    ],
    'event_category' => [
        'exclude' => 1,
        'label' => 'Kategorie (optional)',
        'config' => ['type' => 'input', 'size' => 48, 'max' => 255],
        'displayCond' => 'FIELD:event:=:1',
    ],
    'event_address' => [
        'exclude' => 1,
        'label' => 'Adresse',
        'config' => ['type' => 'text', 'cols' => 80, 'rows' => 5],
        'displayCond' => 'FIELD:event:=:1',
    ],
    'event_past' => [
        'exclude' => 1,
        'label' => 'Vergangen',
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
            'items' => [['label' => '', 'value' => '1']],
            'default' => 0,
        ],
        'displayCond' => 'FIELD:event:=:1',
    ],
    'event_signup_link' => [
        'exclude' => 1,
        'label' => 'Link',
        'config' => ['type' => 'link'],
        'displayCond' => 'FIELD:event:=:1',
    ],
    'event_signup_link_label' => [
        'exclude' => 1,
        'label' => 'Link-Beschriftung',
        'config' => ['type' => 'input', 'size' => 48, 'max' => 255, 'default' => 'Anmelden'],
        'displayCond' => 'FIELD:event:=:1',
    ],
];

ExtensionManagementUtility::addTCAcolumns('pages', $pageColumns);

ExtensionManagementUtility::addFieldsToPalette(
    'pages',
    'layout',
    '--linebreak--,newsletter,socialmedia,breadcrumb,highlight',
    'after:layout'
);

ExtensionManagementUtility::addFieldsToPalette(
    'pages',
    'media',
    '--linebreak--,teaser_description,teaser_description_overview,category_title',
    'after:media'
);

$GLOBALS['TCA']['pages']['palettes']['event'] = [
    'showitem' => 'event,--linebreak--,event_startdate,event_enddate,--linebreak--,'
        . 'event_location,event_category,--linebreak--,event_address,event_past,--linebreak--,'
        . 'event_signup_link,event_signup_link_label',
];

ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    '--div--;Veranstaltung,--palette--;;event',
    '1',
    'after:nav_title'
);
