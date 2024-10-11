# Installation in Lumen 6+
 
 Lumen    | Laravel Doctrine
:---------|:----------
 6.*      | ~1.5
 7.*      | ~1.6
 8.*      | ~1.7

To set up Laravel Doctrine in Lumen, we need some additional steps.

Install this package with composer:

```
composer require "laravel-doctrine/orm:1.7.*"
```

After updating composer, open `bootstrap/app.php` and register the Service Provider:

```php
$app->register(LaravelDoctrine\ORM\DoctrineServiceProvider::class);
```

Optionally you can register the EntityManager, Registry and/or Doctrine Facade. Don't forget to uncomment `$app->withFacades();`

```php
class_alias('LaravelDoctrine\ORM\Facades\EntityManager', 'EntityManager');
class_alias('LaravelDoctrine\ORM\Facades\Registry', 'Registry');
class_alias('LaravelDoctrine\ORM\Facades\Doctrine', 'Doctrine');
```

Uncomment `// Dotenv::load(__DIR__.'/../');`, so environment variables can be loaded

Next, you will need to create the `config/database.php` and `config/cache.php` config files. 

The database config file should look at least like this (assuming you are using MYSQL), but you can copy it from the Laravel source too:

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */
    'default' => env('DB_CONNECTION', 'mysql'),
    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */
    'connections' => [
        'mysql' => [
            'driver'    => 'mysql',
            'host'      => env('DB_HOST', 'localhost'),
            'database'  => env('DB_DATABASE'),
            'username'  => env('DB_USERNAME'),
            'password'  => env('DB_PASSWORD'),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
            'strict'    => false,
        ]
    ],
];
```

If you are using apc, file, memcached or redis cache, the following config should be added:

```php
<?php
 
 return [
    /*
    |--------------------------------------------------------------------------
    | Cache Stores
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the cache "stores" for your application as
    | well as their drivers. You may even define multiple stores for the
    | same cache driver to group types of items stored in your caches.
    |
    */
    'stores' => [
        'apc' => [
            'driver' => 'apc',
        ],
        'file' => [
            'driver' => 'file',
            'path'   => storage_path('framework/cache'),
        ],
        'memcached' => [
            'driver'  => 'memcached',
            'servers' => [
                [
                    'host' => '127.0.0.1', 'port' => 11211, 'weight' => 100,
                ],
            ],
        ],
        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
        ],
    ],
 ];
```

### Config

If you want to overrule the Doctrine config. You will have to create a `config/doctrine.php` file and copy the contents from the package config.

Available environment variables inside the config are: `APP_DEBUG`, `DOCTRINE_METADATA`, `DB_CONNECTION`, `DOCTRINE_PROXY_AUTOGENERATE`, `DOCTRINE_LOGGER` and `DOCTRINE_CACHE`
