# Laravel Doctrine ORM

<img src="https://cloud.githubusercontent.com/assets/7728097/12726966/cf009822-c91a-11e5-8f19-63ce1d77e8b2.jpg"/>

[![GitHub release](https://img.shields.io/github/release/laravel-doctrine/orm.svg?style=flat-square)](https://packagist.org/packages/laravel-doctrine/orm)
[![Github actions](https://github.com/laravel-doctrine/orm/workflows/CI/badge.svg?branch=1.7)](https://github.com/laravel-doctrine/orm/actions?query=workflow%3ACI+branch%3A1.7)
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
* Fluent, Annotations, YAML, SimplifiedYAML, XML, SimplifiedXML, Config and Static PHP metadata mappings
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

Version | Supported Laravel Versions
:---------|:----------
~1.5 | 6.x
~1.6 | 7.x
~1.7 | 8.x

Require this package  

```bash
composer require "laravel-doctrine/orm:1.7.*"
```

Because of the auto package discovery feature Laravel 5.5 has, the ServiceProvider and Facades are automatically registered.

To publish the config use:

```bash
php artisan vendor:publish --tag="config" --provider="LaravelDoctrine\ORM\DoctrineServiceProvider"
```

## License

This package is licensed under the [MIT license](https://github.com/laravel-doctrine/orm/blob/master/LICENSE).
