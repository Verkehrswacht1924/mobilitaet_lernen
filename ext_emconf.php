<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'VMS Sitepackage',
    'description' => 'Offener Rebuild (Content Blocks) für mobilität-lernen',
    'category' => 'templates',
    'author' => 'VMS',
    'state' => 'beta',
    'version' => '0.2.0',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-13.4.99',
            'content_blocks' => '',
            'container' => '',
        ],
    ],
];
