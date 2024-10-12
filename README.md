<p align="center">
    <img src="https://placehold.co/10x10/337ab7/337ab7.png" width="100%" height="15px">
    <img width="450px" src="https://github.com/laravel-doctrine/orm/blob/3.0.x/logo.svg"/>
</p>

Laravel Doctrine ORM
====================

An integration library for Laravel and Doctrine ORM

[![Build Status](https://github.com/laravel-doctrine/orm/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/laravel-doctrine/orm/actions/workflows/continuous-integration.yml?query=branch%3Amain)
[![Code Coverage](https://codecov.io/gh/laravel-doctrine/orm/branch/main/graphs/badge.svg)](https://codecov.io/gh/laravel-doctrine/orm/branch/3.0.x)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%201-brightgreen.svg)](https://img.shields.io/badge/PHPStan-level%201-brightgreen.svg)
[![License](https://poser.pugx.org/laravel-doctrine/orm/license)](//packagist.org/packages/laravel-doctrine/orm)

Version 3.0 Notes
-----------------

This library has been around for years as version 1 and 2.  However these old versions don't
support the latest Doctrine libraries.  Version 3 does not try to maintain backwards compatibility
with the old versions of this library.  However, this author dropped it into the
[LDOG Stack](https://ldog.apiskeletons.dev)
and it worked without modification.  Version 3 supports DBAL ^4.0 and ORM ^3.0.


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

Full documentation of this library does not exist at this time.  However, the library is
similar to the 2.0 version of this library.


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

