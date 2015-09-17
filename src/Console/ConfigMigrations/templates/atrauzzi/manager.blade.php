[
    'dev'  => env('APP_DEBUG'),
    'meta' => env('DOCTRINE_METADATA', '{{$driver}}'),
    'connection' => {{{ $connection != null ? $connection : 'config(\'database.default\')'  }}},
    @if(!empty($namespaces))
    'namespaces' => [
        @foreach($namespaces as $key => $val)
        '{{$key}}' => '{{$val}}',
            @endforeach
    ],
        @endif
    'paths' => [app_path()],
    @if($defaultRepo != null)
    'repository' => {{$defaultRepo}}::class,
        @endif
    @if($proxySettings != null)
    'proxies' => [
        'namespace' => {{{ \LaravelDoctrine\ORM\Utilities\ArrayUtil::get($proxySettings['namespace'],'false') }}},
        'path'          => {{{ \LaravelDoctrine\ORM\Utilities\ArrayUtil::get($data['directory'], 'storage_path(\'proxies\')') }}},
        'auto_generate' => {{{ \LaravelDoctrine\ORM\Utilities\ArrayUtil::get($data['auto_generate'], 'env(\'DOCTRINE_PROXY_AUTOGENERATE\', \'false\')') }}}
    ],
        @endif
    'events'     => [
        'listeners'   => [],
        'subscribers' => []
    ],
    'filters' => []
]
