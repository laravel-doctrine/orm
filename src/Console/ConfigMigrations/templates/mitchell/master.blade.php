return [
        'dev'                       => config('app.debug'),
        'managers'                  => [
                @foreach($managers as $key => $manager)
                    '{{$key}}' => {{$manager}},
                    @endforeach

        ],
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
        'extensions'                => [
            //LaravelDoctrine\ORM\Extensions\TablePrefix\TablePrefixExtension::class,
        ],
        'custom_types'              => [
                'json' => LaravelDoctrine\ORM\Types\Json::class
        ],
        @if($dqls !== null)
            {{$dqls}}
            @endif
        'debugbar'                  => env('DOCTRINE_DEBUGBAR', false),
        {{$cache}}
];
