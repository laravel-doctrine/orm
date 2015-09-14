[
    'default' => {{{is_null($cacheProvider) ? 'env(\'DOCTRINE_CACHE\', \'array\')' : 'env(\'CACHE_DRIVER\',\''.$cacheProvider.'\')'}}},
    'second_level' => false,
],
