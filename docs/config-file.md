# Configuration File Overview

- [Sample Configuration](#sample-configuration)
- [Entity Manager](#entity-manager)
    - [Namespace Alias](#entity-manager-namespace-alias)
- [Extensions](#extensions)
- [Custom Types](#custom-types)
- [Custom Functions](#custom-functions)
- [Custom Hydration Modes](#custom-hydration-modes)
- [Logger](#logger)
- [Cache](#cache)
- [Gedmo Extensions](#gedmo)

This page provides a quick overview of all of the options provided in `doctrine.php`, the configuration file for Laravel Doctrine.

### <a name="sample-configuration"></a> Sample Configuration

```php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Entity Mangers
    |--------------------------------------------------------------------------
    |
    | Configure your Entity Managers here. You can set a different connection
    | and driver per manager and configure events and filters. Change the
    | paths setting to the appropriate path and replace App namespace
    | by your own namespace.
    |
    | Available meta drivers: fluent|annotations|yaml|simplified_yaml|xml|simplified_xml|config|static_php|php
    |
    | Available connections: mysql|oracle|pgsql|sqlite|sqlsrv
    | (Connections can be configured in the database config)
    |
    | --> Warning: Proxy auto generation should only be enabled in dev!
    |
    */
    'managers'                  => [
        'default' => [
            'dev'        => env('APP_DEBUG'),
            'meta'       => env('DOCTRINE_METADATA', 'annotations'),
            'connection' => env('DB_CONNECTION', 'mysql'),
            'namespaces' => [
                'App'
            ],
            'paths'      => [
                base_path('app')
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
            | The listener array expects the key to be a Doctrine event
            | e.g. Doctrine\ORM\Events::onFlush
            |
            */
            'events'     => [
                'listeners'   => [],
                'subscribers' => []
            ],
            'filters'    => []
        ]
    ],
    /*
    |--------------------------------------------------------------------------
    | Doctrine Extensions
    |--------------------------------------------------------------------------
    |
    | Enable/disable Doctrine Extensions by adding or removing them from the list
    |
    | If you want to require custom extensions you will have to require
    | laravel-doctrine/extensions in your composer.json
    |
    */
    'extensions'                => [
        //LaravelDoctrine\ORM\Extensions\TablePrefix\TablePrefixExtension::class,
        //LaravelDoctrine\Extensions\Timestamps\TimestampableExtension::class,
        //LaravelDoctrine\Extensions\SoftDeletes\SoftDeleteableExtension::class,
        //LaravelDoctrine\Extensions\Sluggable\SluggableExtension::class,
        //LaravelDoctrine\Extensions\Sortable\SortableExtension::class,
        //LaravelDoctrine\Extensions\Tree\TreeExtension::class,
        //LaravelDoctrine\Extensions\Loggable\LoggableExtension::class,
        //LaravelDoctrine\Extensions\Blameable\BlameableExtension::class,
        //LaravelDoctrine\Extensions\IpTraceable\IpTraceableExtension::class,
        //LaravelDoctrine\Extensions\Translatable\TranslatableExtension::class
    ],
    /*
    |--------------------------------------------------------------------------
    | Doctrine custom types
    |--------------------------------------------------------------------------
    */
    'custom_types'              => [
        'json' => LaravelDoctrine\ORM\Types\Json::class
    ],
    /*
    |--------------------------------------------------------------------------
    | DQL custom datetime functions
    |--------------------------------------------------------------------------
    */
    'custom_datetime_functions' => [],
    /*
    |--------------------------------------------------------------------------
    | DQL custom numeric functions
    |--------------------------------------------------------------------------
    */
    'custom_numeric_functions'  => [],
    /*
    |--------------------------------------------------------------------------
    | DQL custom string functions
    |--------------------------------------------------------------------------
    */
    'custom_string_functions'   => [],
    /*
    |--------------------------------------------------------------------------
    | Register custom hydrators
    |--------------------------------------------------------------------------
    */
    'custom_hydration_modes'     => [
        // e.g. 'hydrationModeName' => MyHydrator::class,
    ],
    /*
    |--------------------------------------------------------------------------
    | Enable query logging with laravel file logging,
    | debugbar, clockwork or an own implementation.
    | Setting it to false, will disable logging
    |
    | Available:
    | - LaravelDoctrine\ORM\Loggers\LaravelDebugbarLogger
    | - LaravelDoctrine\ORM\Loggers\ClockworkLogger
    | - LaravelDoctrine\ORM\Loggers\FileLogger
    |--------------------------------------------------------------------------
    */
    'logger'                    => env('DOCTRINE_LOGGER', false),
    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | Configure meta-data, query and result caching here.
    | Optionally you can enable second level caching.
    |
    | Available: acp|array|file|memcached|redis
    |
    */
    'cache'                     => [
        'second_level'     => false,
        'default'          => env('DOCTRINE_CACHE', 'array'),
        'namespace'        => null,
        'metadata'         => [
            'driver'       => env('DOCTRINE_METADATA_CACHE', env('DOCTRINE_CACHE', 'array')),
            'namespace'    => null,
        ],
        'query'            => [
            'driver'       => env('DOCTRINE_QUERY_CACHE', env('DOCTRINE_CACHE', 'array')),
            'namespace'    => null,
        ],
        'result'           => [
            'driver'       => env('DOCTRINE_RESULT_CACHE', env('DOCTRINE_CACHE', 'array')),
            'namespace'    => null,
        ],
    ],
    /*
    |--------------------------------------------------------------------------
    | Gedmo extensions
    |--------------------------------------------------------------------------
    |
    | Settings for Gedmo extensions
    | If you want to use this you will have to require
    | laravel-doctrine/extensions in your composer.json
    |
    */
    'gedmo'                     => [
        'all_mappings' => false
    ]
];
```

### <a name="entity-manager"></a> Entity Manager

An **Entity Manager (EM)** contains all of the information Doctrine needs to understand, retrieve, and manipulate a set of entities (models). 

You must have **at least one** EM in order to use Laravel Doctrine.

To use more than one EM simply create another entry in the `managers` array.

| Property | Explanation |
|:-----------|------------|
| **EM Name** | In the sample below the EM we have configured is named `default`. This is the EM that Laravel Doctrine will attempt to use if no argument is provided to `ManagerRegistry`. |
| **dev** | Whether this EM is in development mode. |
| **meta** | The metadata driver to use. Built-in options are `fluent|annotations|yaml|simplified_yaml|xml|simplified_xml|config|static_php|php` |
| **connection** | The connection driver to use. Built-in options are `mysql|oracle|pgsql|sqlite|sqlsrv` |
| **namespaces** | (Optional) If your entities are not located in the configured app namespace you can specify a different one here. |
| **paths** | An paths where the mapping configurations for your entities is located. |
| **repository** | (Optional) The default repository to use for this EM. |
| **decorator** | (Optional) Your custom EM decorator to overwrite the default EM. |
| **proxies.namespace** | Namespace (if different) specified for proxy classes |
| **proxies.path** | The path where proxy classes should be generated. |
| **proxies.auto_generate** | Should proxy classes be generated every time an EM is created? (Turn off production) |
| **events.subscribers** |Subscribers should implement `Doctrine\Common\EventSubscriber` |
| **events.listeners** | Key should be event type. E.g. `Doctrine\ORM\Events::onFlush`. Value should be the listener class |
| **filters** | Filter system that allows the developer to add SQL to the conditional clauses of queries, regardless the place where the SQL is generated |


```php
    'managers'                  => [
        'default' => [
            'dev'        => env('APP_DEBUG'),
            'meta'       => env('DOCTRINE_METADATA', 'annotations'),
            'connection' => env('DB_CONNECTION', 'mysql'),
            'namespaces' => [
                'App'
            ],
            'paths'      => [
                base_path('app')
            ],
            'repository' => Doctrine\ORM\EntityRepository::class,
            'proxies'    => [
                'namespace'     => false,
                'path'          => storage_path('proxies'),
                'auto_generate' => env('DOCTRINE_PROXY_AUTOGENERATE', false)
            ],
            'events' => ...
            'filters' => ...
        ]
    ]
```

#### <a name="entity-manager-namespace-alias"></a> Namespace Alias

To use namespace alias, you just have to specify then as key of each namespace.

Example:
```php
    'managers'                  => [
        'default' => [
            ...
            'connection' => env('DB_CONNECTION', 'mysql'),
            'namespaces' => [
                'Foo' => 'App\Model\Foo\Entities',
                'Bar' => 'App\Model\Bar\Entities',
            ],
            'paths'      => [
                base_path('app')
            ],
            ...
        ]
    ]
```

Whenever you need to specify entities in these namespaces, you can simple use the alias as follow:
```php
    SELECT f FROM Foo:SomeEntity
    or
    \EntityManager::getRepository('Bar:SomeEntity');
```

### <a name="extensions"></a> Extensions

Extensions can be enabled by adding them to this array. They provide additional functionality Entities (Timestamps, Loggable, etc.)

To use the extensions in this sample you must install the extensions package:

```
require laravel-doctrine/extensions
```

and follow the [installation instructions.](http://www.laraveldoctrine.org/docs/current/extensions/installation)

```php
'extensions'                => [
    //LaravelDoctrine\ORM\Extensions\TablePrefix\TablePrefixExtension::class,
    //LaravelDoctrine\Extensions\Timestamps\TimestampableExtension::class,
    //LaravelDoctrine\Extensions\SoftDeletes\SoftDeleteableExtension::class,
    //LaravelDoctrine\Extensions\Sluggable\SluggableExtension::class,
    //LaravelDoctrine\Extensions\Sortable\SortableExtension::class,
    //LaravelDoctrine\Extensions\Tree\TreeExtension::class,
    //LaravelDoctrine\Extensions\Loggable\LoggableExtension::class,
    //LaravelDoctrine\Extensions\Blameable\BlameableExtension::class,
    //LaravelDoctrine\Extensions\IpTraceable\IpTraceableExtension::class,
    //LaravelDoctrine\Extensions\Translatable\TranslatableExtension::class
],
```

### <a name="custom-types"></a> Custom Types

Custom types are classes that allow Doctrine to marshal data to/from the data source in a custom format. 

To register a custom type simple add the class to this list. [For more information on custom types refer to the Doctrine documentation.](https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/cookbook/custom-mapping-types.html)

### <a name="custom-functions"></a> Custom Functions

These are classes that extend the functionality of Doctrine's DQL language. More information on what functions are available [visit the repository.](https://github.com/beberlei/DoctrineExtensions) 

To use the extensions in this sample you must install the extensions package:

```
require laravel-doctrine/extensions
```

and follow the [installation instructions.](http://www.laraveldoctrine.org/docs/current/extensions/installation)

If you include `BeberleiExtensionsServiceProvider` all custom functions will automatically be registered.
 
To add a function simply add it to the correct list using this format:

`'FUNCTION_NAME' => 'Path\To\Class'`

```php
/*
|--------------------------------------------------------------------------
| DQL custom datetime functions
|--------------------------------------------------------------------------
*/
'custom_datetime_functions' => [],
/*
|--------------------------------------------------------------------------
| DQL custom numeric functions
|--------------------------------------------------------------------------
*/
'custom_numeric_functions'  => [],
/*
|--------------------------------------------------------------------------
| DQL custom string functions
|--------------------------------------------------------------------------
*/
'custom_string_functions'   => [],
```

### <a name="custom-hydration-modes"></a> Custom Hydration Modes

This option enables you to register your Hydrator classes to use as custom hydration modes. For more information about custom hydration modes see [doctrine documentation](http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/dql-doctrine-query-language.html#custom-hydration-modes).

To register custom hydrator, add it to the list in following format:

`'hydrationModeName' => MyHydrator::class`

```php
/*
|--------------------------------------------------------------------------
| Register custom hydrators
|--------------------------------------------------------------------------
*/
'custom_hydration_modes'     => [
    // e.g. 'hydrationModeName' => MyHydrator::class,
],
```

### <a name="logger"></a> Logger

Enable logging of Laravel Doctrine and Doctrine by using the logger functionality.

|Available loggers|
|--|
| `LaravelDoctrine\ORM\Loggers\LaravelDebugbarLogger` |
| `LaravelDoctrine\ORM\Loggers\ClockworkLogger` |
| `LaravelDoctrine\ORM\Loggers\FileLogger` |

` 'logger' => env('DOCTRINE_LOGGER', false),`

### <a name="cache"></a> Cache

Cache will be used to cache metadata, results and queries.

**Available cache providers:**

* apc
* array
* file
* memcached
* redis

** Config settings:**

|Property|Explanation |
|--|--|
| **cache.default** | The default cache provider to use. |
| **cache.namespace** |  Will add namespace to the cache key. This is useful if you need extra control over handling key names collisions in your Cache solution.|
| **cache.second_level** | The Second Level Cache is designed to reduce the amount of necessary database access. It sits between your application and the database to avoid the number of database hits as much as possible. When turned on, entities will be first searched in cache and if they are not found, a database query will be fired an then the entity result will be stored in a cache provider. When used, READ_ONLY is mostly used. ReadOnly cache can do reads, inserts and deletes, cannot perform updates|
| **cache.metadata** | Your class metadata can be parsed from a few different sources like YAML, XML, Annotations, etc. Instead of parsing this information on each request we should cache it using one of the cache drivers. |
| **cache.query** | Cache transformation of a DQL query to its SQL counterpart. |
| **cache.result** | The result cache can be used to cache the results of your queries so you don't have to query the database or hydrate the data again after the first time. |

### Gedmo

This is an option for use with **Extensions**

To use this option you must first install the extensions package:

```
require laravel-doctrine/extensions
```
and follow the [installation instructions.](http://www.laraveldoctrine.org/docs/current/extensions/installation)
