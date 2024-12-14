<p align="center">
    <img src="https://placehold.co/10x10/337ab7/337ab7.png" width="100%" height="15px">
    <img width="450px" src="https://github.com/laravel-doctrine/orm/blob/3.0.x/docs/banner.png"/>
</p>

Laravel Doctrine ORM
====================

An integration library for Laravel and Doctrine ORM

[![Build Status](https://github.com/laravel-doctrine/orm/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/laravel-doctrine/orm/actions/workflows/continuous-integration.yml?query=branch%3Amain)
[![Code Coverage](https://codecov.io/gh/laravel-doctrine/orm/graph/badge.svg?token=3CpQzDXOWX)](https://codecov.io/gh/laravel-doctrine/orm)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%201-brightgreen.svg)](https://img.shields.io/badge/PHPStan-level%201-brightgreen.svg)
[![Documentation](https://readthedocs.org/projects/laravel-doctrine-orm-official/badge/?version=latest)](https://laravel-doctrine-orm-official.readthedocs.io/en/latest/)
[![Packagist Downloads](https://img.shields.io/packagist/dd/laravel-doctrine/orm)](https://packagist.org/packages/laravel-doctrine/orm)


Installation
------------

Via composer:

```bash
composer require laravel-doctrine/orm
```

The ServiceProvider and Facades are autodiscovered.

Publish the config:

```bash
php artisan vendor:publish --tag="config" --provider="LaravelDoctrine\ORM\DoctrineServiceProvider"
```


Documentation
-------------

Full documentation at https://laravel-doctrine-orm-official.readthedocs.io
or in the docs directory.


Versions
--------

* Version 3 supports DBAL ^4.0, ORM ^3.0, and PHP 8.2.  See the [upgrade guide](https://laravel-doctrine-orm-official.readthedocs.io/en/latest/upgrade.html) for more information.
* Version 2 supports Laravel 9 - 11, DBAL ^3.0, ORM ^2.0, and PHP ^8.0.
* Version 1 supports Laravel 6 - 9, DBAL ^2.0, ORM ^2.0, and PHP ^5.5 - ^8.0.
  See [documentation in version 2](https://github.com/laravel-doctrine/orm/tree/2.0?tab=readme-ov-file#versions)

License
-------

See [LICENSE](https://github.com/laravel-doctrine/orm/blob/master/LICENSE).
