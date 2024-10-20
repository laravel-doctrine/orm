<p align="center">
    <img src="https://placehold.co/10x10/337ab7/337ab7.png" width="100%" height="15px">
    <img width="450px" src="https://github.com/laravel-doctrine/orm/blob/3.0.x/logo.svg"/>
</p>

Laravel Doctrine ORM
====================

An integration library for Laravel and Doctrine ORM

[![Build Status](https://github.com/laravel-doctrine/orm/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/laravel-doctrine/orm/actions/workflows/continuous-integration.yml?query=branch%3Amain)
[![Code Coverage](https://codecov.io/gh/laravel-doctrine/orm/branch/3.0.x/graph/badge.svg?token=3CpQzDXOWX)](https://codecov.io/gh/laravel-doctrine/orm)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%201-brightgreen.svg)](https://img.shields.io/badge/PHPStan-level%201-brightgreen.svg)
[![License](https://poser.pugx.org/laravel-doctrine/orm/license)](//packagist.org/packages/laravel-doctrine/orm)

Version 3.0 Notes
-----------------

Version 3 supports DBAL ^4.0 and ORM ^3.0.  See the 
[upgrade guide](https://laravel-doctrine-orm-official.readthedocs.io/en/latest/upgrade.html) 
for more information.


Installation
------------

Via composer:

```bash
composer require laravel-doctrine/orm ^3.0.0
```

Because of the auto package discovery feature Laravel has, the ServiceProvider and Facades 
are automatically registered.

To publish the config use:

```bash
php artisan vendor:publish --tag="config" --provider="LaravelDoctrine\ORM\DoctrineServiceProvider"
```


Documentation
-------------

Full documentation is available at https://laravel-doctrine-orm-official.readthedocs.io
or in the docs directory.


Features
--------

* Easy configuration
* Pre-configured metadata, connections and caching
* Support for multiple entity managers and connections
* Laravel naming strategy
* Pagination
* Simple authentication implementation
* Password reminders implementation
* Doctrine console commands
* DoctrineExtensions supported
* Timestamps and SoftDelete listeners
* Extendable: extend or add your own drivers for metadata, connections or cache


License
-------

See [LICENSE](https://github.com/laravel-doctrine/orm/blob/master/LICENSE).

