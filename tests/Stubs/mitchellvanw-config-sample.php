<?php

return [
    'default_connection' => 'default',
    'entity_managers'    => [
        'default' => [
            'connection'         => 'sqlite',
            'cache_provider'     => null,
            'repository'         => 'Doctrine\ORM\EntityRepository',
            'simple_annotations' => false,
            'logger'             => null,
            'metadata'           => [
                'simple'    => false,
                'driver'    => 'yaml',
                'paths'     => 'app/Models/mappings',
                'extension' => '.dcm.yml'
            ],
        ],
        'production' => [
            'connection'     => 'mysql',
            'cache_provider' => 'file',
            'repository'     => 'Doctrine\ORM\EntityRepository',
            'logger'         => null,
            'metadata'       => [
                'simple'    => false,
                'driver'    => 'yaml',
                'paths'     => 'app/Models/mappings',
                'extension' => '.dcm.yml'
            ],
        ],
    ],
    'proxy' => [
        'auto_generate' => true,
        'directory'     => storage_path() . '/proxies',
        'namespace'     => null
    ],
    'cache_provider' => null,
];
