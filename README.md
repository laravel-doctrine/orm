# Laravel Doctrine ORM

<img src="https://cloud.githubusercontent.com/assets/7728097/12726966/cf009822-c91a-11e5-8f19-63ce1d77e8b2.jpg"/>

[![GitHub release](https://img.shields.io/github/release/laravel-doctrine/orm.svg?style=flat-square)](https://packagist.org/packages/laravel-doctrine/orm)
[![Travis](https://img.shields.io/travis/laravel-doctrine/orm.svg?style=flat-square)](https://travis-ci.org/laravel-doctrine/orm)
[![StyleCI](https://styleci.io/repos/39036008/shield)](https://styleci.io/repos/39036008)
[![Scrutinizer](https://img.shields.io/scrutinizer/g/laravel-doctrine/orm.svg?style=flat-square)](https://github.com/laravel-doctrine/orm)
[![Packagist](https://img.shields.io/packagist/dm/laravel-doctrine/orm.svg?style=flat-square)](https://packagist.org/packages/laravel-doctrine/orm)
[![Packagist](https://img.shields.io/packagist/dt/laravel-doctrine/orm.svg?style=flat-square)](https://packagist.org/packages/laravel-doctrine/orm)

*A drop-in Doctrine ORM 2 implementation for Laravel 5+*

```php
$scientist = new Scientist(
    'Albert', 
    'Einstein'
);

$scientist->addTheory(
    new Theory('Theory of relativity')
);

EntityManager::persist($scientist);
EntityManager::flush();
```

* Easy configuration
* Pagination
* Pre-configured metadata, connections and caching
* Extendable: extend or add your own drivers for metadata, connections or cache
* Annotations, yaml, xml, config and static php metadata mappings
* Multiple entity managers and connections
* Laravel naming strategy
* Simple authentication implementation
* Password reminders implementation
* Doctrine console commands
* DoctrineExtensions supported
* Timestamps, Softdeletes and TablePrefix listeners 

## Documentation

[Read the full documentation](http://laraveldoctrine.org/docs/current/orm).

## Versions

Version | Supported Laravel Versions | Support
:---------|:----------|:----------
1.0.x |  5.1.x | EOL
1.1.x | 5.2.x |  EOL
1.2.x | 5.2.x, 5.3.x | Security releases
1.3.x | 5.4.x | Bugfix and security releases
1.4.x | 5.5.x | New features

Require this package  

```bash
composer require "laravel-doctrine/orm:1.3.*"
```

After adding the package, add the ServiceProvider to the providers array in `config/app.php`

```php
LaravelDoctrine\ORM\DoctrineServiceProvider::class,
```

Optionally you can register the EntityManager facade:

```php
'EntityManager' => LaravelDoctrine\ORM\Facades\EntityManager::class
```

To publish the config use:

```bash
php artisan vendor:publish --tag="config"
```

## License

This package is licensed under the [MIT license](https://github.com/laravel-doctrine/orm/blob/master/LICENSE).
