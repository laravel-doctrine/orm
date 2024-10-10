<p align="center">
    <img src="https://placehold.co/10x10/337ab7/337ab7.png" width="100%" height="15px">
    <img width="450px" src="https://private-user-images.githubusercontent.com/493920/375536074-18bfa85b-082d-4b53-b6ce-d2757c4b163c.svg?jwt=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJnaXRodWIuY29tIiwiYXVkIjoicmF3LmdpdGh1YnVzZXJjb250ZW50LmNvbSIsImtleSI6ImtleTUiLCJleHAiOjE3Mjg1OTI1MTIsIm5iZiI6MTcyODU5MjIxMiwicGF0aCI6Ii80OTM5MjAvMzc1NTM2MDc0LTE4YmZhODViLTA4MmQtNGI1My1iNmNlLWQyNzU3YzRiMTYzYy5zdmc_WC1BbXotQWxnb3JpdGhtPUFXUzQtSE1BQy1TSEEyNTYmWC1BbXotQ3JlZGVudGlhbD1BS0lBVkNPRFlMU0E1M1BRSzRaQSUyRjIwMjQxMDEwJTJGdXMtZWFzdC0xJTJGczMlMkZhd3M0X3JlcXVlc3QmWC1BbXotRGF0ZT0yMDI0MTAxMFQyMDMwMTJaJlgtQW16LUV4cGlyZXM9MzAwJlgtQW16LVNpZ25hdHVyZT0yYzQ5ZWEyZWJkYjcwY2NkZmRkMjFhN2ZiOTlkMmZjMDJiNTUyMTIxNWFhYjg0Nzc0ZGQyODE0YTk2YmRlZTU3JlgtQW16LVNpZ25lZEhlYWRlcnM9aG9zdCJ9.rQ87Pep6Ki44HHXayqZWI7q8uXUObdVmzODUqq_HiPE"/>
</p>

Laravel Doctrine ORM
====================

An integration library for Laravel and Doctrine ORM


UNDER DEVELOPMENT
-----------------

Version 3.0 of this library is under active development.  If you would like to help, please
fork the repository at branch 3.0.x and submit a pull request.


Version 3.0 Notes
-----------------

This library has been around for years as version 1 and 2.  However these old versions don't
support the latest Doctrine libraries.  Version 3 does not try to maintain backwards compatibility
with the old versions of this library.  Version 3 supports DBAL ^4.0 and ORM ^3.0.


Installation
------------

Via composer:

~~composer require laravel-doctrine/orm ^3.0~~

**No stable release yet.  Use the development branch 3.0.x for now.**

Because of the auto package discovery feature Laravel has, the ServiceProvider and Facades 
are automatically registered.

To publish the config use:

```bash
php artisan vendor:publish --tag="config" --provider="LaravelDoctrine\ORM\DoctrineServiceProvider"
```

Documentation
-------------

Full documentation of this library does not exist at this time.  However, the library is
very similar to the old versions of this library.


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

