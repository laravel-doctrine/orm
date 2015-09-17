[
    'default' => {{{is_null($cacheProvider) ? 'env(\'DOCTRINE_CACHE\', \'array\')' : 'config(\'cache.'.$cacheProvider.'\')'}}},
    'second_level' => false,
],
