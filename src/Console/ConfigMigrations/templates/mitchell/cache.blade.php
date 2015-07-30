[
    'default' => {{{is_null($cacheProvider) ? 'config("cache.default")' : 'config("cache.'.$cacheProvider.'")'}}},
    'second_level' => false,
]