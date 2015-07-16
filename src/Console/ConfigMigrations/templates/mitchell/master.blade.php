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
                @foreach($managers as $key => $manager)
                    '{{$key}}' => {{$manager}},
                    @endforeach

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
        | Doctrine Extensions
        |--------------------------------------------------------------------------
        |
        | Enable/disable Doctrine Extensions by adding or removing them from the list
        |
        */
        'extensions'                => [
            //LaravelDoctrine\ORM\Extensions\TablePrefix\TablePrefixExtension::class,
        ],
        /*
        |--------------------------------------------------------------------------
        | Doctrine custom types
        |--------------------------------------------------------------------------
        */
        'custom_types'              => [
                'json' => LaravelDoctrine\ORM\Types\Json::class
        ],
        @if($dqls !== null)
            {{$dqls}}
            @endif
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
        {{$cache}}
];
