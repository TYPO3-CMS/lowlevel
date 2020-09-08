<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'System > Configuration + DB Check',
    'description' => 'Enables the \'Config\' and \'DB Check\' modules for technical analysis of the system. This includes raw database search, checking relations, counting pages and records etc.',
    'category' => 'module',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'author' => 'TYPO3 Core Team',
    'author_email' => 'typo3cms@typo3.org',
    'author_company' => '',
    'version' => '9.5.22',
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.22',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
