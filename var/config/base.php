<?php

return [
    'debugMode'  => 0,
    'baseUri'    => '/blog/',
    'cdnUrl'     => '',
    'cache_data' => [
        'front' => [
            'adapter' => 'Data',
            'params'  => [
                'lifetime' => 86400
            ]
        ],
        'back'  => [
            'adapter' => 'File',
            'params'  => [
                'cacheDir' => K_PATH . '/var/data/cache'
            ]
        ]
    ],
    'cache_view' => [
        'front' => [
            'adapter' => 'Output',
            'params'  => [
                'lifetime' => 86400
            ]
        ],
        'back'  => [
            'adapter' => 'File',
            'params'  => [
                'cacheDir' => K_PATH . '/var/data/cache'
            ]
        ]
    ],
    'paths'      => [
        'controllersDir' => '',
        'viewsDir'       => '',
        'pluginsDir'     => '',
    ],
    'namespaces' => [
        'Kitsune'             => K_PATH . '/library/Kitsune',
        'Kitsune\Controllers' => K_PATH . '/app/controllers',
        'Kitsune\Plugins'     => K_PATH . '/app/plugins',
    ],
    'routes'     => [
        '/rss' => [
            'controller' => 'posts',
            'action'     => 'rss'
        ],
        '/sitemap' => [
            'controller' => 'sitemap',
            'action'     => 'index'
        ],
        '/post/{slug:[0-9a-zA\-]+}'  => [
            'controller' => 'posts',
            'action'     => 'view'
        ],
        '/post/{timestamp:[0-9]+}/{slug:[0-9a-zA-Z\-]+}' => [
            'controller' => 'posts',
            'action'     => 'viewLegacy'
        ],
        '/'      => [
            'controller' => 'posts',
            'action'     => 'index'
        ],
    ],
];
