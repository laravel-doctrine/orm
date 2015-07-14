<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Development state
    |--------------------------------------------------------------------------
    |
    | If set to false, metadata caching will become active
    |
    */
    'dev'                       => config('app.debug'),
    /*
    |--------------------------------------------------------------------------
    | Entity Mangers
    |--------------------------------------------------------------------------
    |
    */
    'managers'                  => [
        'default' => [
            'meta'       => 'annotations',
            'connection' => config('database.default'),
            'paths'      => [
                app_path()
            ],
            'repository' => Doctrine\ORM\EntityRepository::class,
            'proxies'    => [
                'namespace'     => false,
                'path'          => storage_path('proxies'),
                'auto_generate' => env('DOCTRINE_PROXY_AUTOGENERATE', false)
            ],
            /*
            |--------------------------------------------------------------------------
            | Doctrine events
            |--------------------------------------------------------------------------
            |
            | If you want to use the Doctrine Extensions from Gedmo,
            | you'll have to set this setting to true.
            |
            | The listener array expects the key to be a Doctrine event
            | e.g. Doctrine\ORM\Events::onFlush
            |
            */
            'events'     => [
                'listeners'   => [],
                'subscribers' => []
            ],
            'filters' => []
        ]
    ],
    /*
    |--------------------------------------------------------------------------
    | Doctrine Meta Data
    |--------------------------------------------------------------------------
    |
    | Available: annotations|yaml|xml
    |
    */
    'meta'                      => [
        'namespaces' => [
            'App'
        ],
        'drivers'    => [
            'annotations' => [
                'driver' => 'annotations',
                'simple' => false
            ],
            'yaml'        => [
                'driver' => 'yaml'
            ],
            'xml'         => [
                'driver' => 'xml'
            ],
            'config'      => [
                'driver'       => 'config',
                'mapping_file' => 'mappings'
            ],
            'static_php'  => [
                'driver' => 'static_php'
            ]
        ]
    ],
    /*
    |--------------------------------------------------------------------------
    | Gedmo Doctrine Extensions
    |--------------------------------------------------------------------------
    |
    | If you want to use the Doctrine Extensions from Gedmo,
    | you'll have to set this setting to true.
    |
    */
    'gedmo_extensions'          => [
        'enabled'      => false,
        'all_mappings' => true
    ],
    /*
    |--------------------------------------------------------------------------
    | Doctrine Extensions
    |--------------------------------------------------------------------------
    |
    | Enable/disable Doctrine Extensions by adding or removing them from the list
    |
    */
    'extensions'                => [
        //Brouwers\LaravelDoctrine\Extensions\Timestamps\TimestampableExtension::class,
        //Brouwers\LaravelDoctrine\Extensions\SoftDeletes\SoftDeleteableExtension::class,
        //Brouwers\LaravelDoctrine\Extensions\TablePrefix\TablePrefixExtension::class,
        //Brouwers\LaravelDoctrine\Extensions\Sluggable\SluggableExtension::class,
        //Brouwers\LaravelDoctrine\Extensions\Sortable\SortableExtension::class,
        //Brouwers\LaravelDoctrine\Extensions\Tree\TreeExtension::class,
        //Brouwers\LaravelDoctrine\Extensions\Loggable\LoggableExtension::class,
        //Brouwers\LaravelDoctrine\Extensions\Blameable\BlameableExtension::class,
        //Brouwers\LaravelDoctrine\Extensions\IpTraceable\IpTraceableExtension::class,
        //Brouwers\LaravelDoctrine\Extensions\Translatable\TranslatableExtension::class
    ],
    /*
    |--------------------------------------------------------------------------
    | Doctrine custom types
    |--------------------------------------------------------------------------
    */
    'custom_types'              => [
        'json' => Brouwers\LaravelDoctrine\Types\Json::class,
        //'CarbonDate'       => DoctrineExtensions\Types\CarbonDateType::class,
        //'CarbonDateTime'   => DoctrineExtensions\Types\CarbonDateTimeType::class,
        //'CarbonDateTimeTz' => DoctrineExtensions\Types\CarbonDateTimeTzType::class,
        //'CarbonTime'       => DoctrineExtensions\Types\CarbonTimeType::class
    ],
    /*
    |--------------------------------------------------------------------------
    | Doctrine custom datetime functions
    |--------------------------------------------------------------------------
    */
    'custom_datetime_functions' => [
        //'DATEADD'  => DoctrineExtensions\Query\Mysql\DateAdd::class,
        //'DATEDIFF' => DoctrineExtensions\Query\Mysql\DateDiff::class
    ],
    /*
    |--------------------------------------------------------------------------
    | Doctrine custom numeric functions
    |--------------------------------------------------------------------------
    */
    'custom_numeric_functions'  => [
        //'ACOS'    => DoctrineExtensions\Query\Mysql\Acos::class,
        //'ASIN'    => DoctrineExtensions\Query\Mysql\Asin::class,
        //'ATAN'    => DoctrineExtensions\Query\Mysql\Atan::class,
        //'ATAN2'   => DoctrineExtensions\Query\Mysql\Atan2::class,
        //'COS'     => DoctrineExtensions\Query\Mysql\Cos::class,
        //'COT'     => DoctrineExtensions\Query\Mysql\Cot::class,
        //'DEGREES' => DoctrineExtensions\Query\Mysql\Degrees::class,
        //'RADIANS' => DoctrineExtensions\Query\Mysql\Radians::class,
        //'SIN'     => DoctrineExtensions\Query\Mysql\Sin::class,
        //'TAN'     => DoctrineExtensions\Query\Mysql\Ta::class
    ],
    /*
    |--------------------------------------------------------------------------
    | Doctrine custom string functions
    |--------------------------------------------------------------------------
    */
    'custom_string_functions'   => [
        //'CHAR_LENGTH' => DoctrineExtensions\Query\Mysql\CharLength::class,
        //'CONCAT_WS'   => DoctrineExtensions\Query\Mysql\ConcatWs::class,
        //'FIELD'       => DoctrineExtensions\Query\Mysql\Field::class,
        //'FIND_IN_SET' => DoctrineExtensions\Query\Mysql\FindInSet::class,
        //'REPLACE'     => DoctrineExtensions\Query\Mysql\Replace::class,
        //'SOUNDEX'     => DoctrineExtensions\Query\Mysql\Soundex::class,
        //'STR_TO_DATE' => DoctrineExtensions\Query\Mysql\StrToDat::class
    ],
    /*
    |--------------------------------------------------------------------------
    | Enable Debugbar Doctrine query collection
    |--------------------------------------------------------------------------
    */
    'debugbar'                  => env('DOCTRINE_DEBUGBAR', false),
    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | By default the Laravel cache setting is used,
    | but it's possible to overrule here
    |
    | Available: acp|array|file|memcached|redis
    |
    */
    'cache'                     => [
        'default'      => config('cache.default'),
        'second_level' => false,
    ]
];
