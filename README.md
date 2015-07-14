# Laravel Doctrine

<img src="https://cloud.githubusercontent.com/assets/7728097/8503648/de6beb86-21c2-11e5-9d70-ed4c24185a7e.jpg"/>

[![GitHub release](https://img.shields.io/github/release/patrickbrouwers/Laravel-Doctrine.svg?style=flat)](https://packagist.org/packages/brouwers/laravel-doctrine)
[![Travis](https://img.shields.io/travis/patrickbrouwers/Laravel-Doctrine.svg?style=flat)](https://travis-ci.org/patrickbrouwers/Laravel-Doctrine)
[![Scrutinizer](https://img.shields.io/scrutinizer/g/patrickbrouwers/Laravel-Doctrine.svg?style=flat)](https://github.com/patrickbrouwers/Laravel-Doctrine)
[![Packagist](https://img.shields.io/packagist/dd/brouwers/Laravel-Doctrine.svg?style=flat)](https://packagist.org/packages/brouwers/laravel-doctrine)
[![Packagist](https://img.shields.io/packagist/dm/brouwers/Laravel-Doctrine.svg?style=flat)](https://packagist.org/packages/brouwers/laravel-doctrine)
[![Packagist](https://img.shields.io/packagist/dt/brouwers/Laravel-Doctrine.svg?style=flat)](https://packagist.org/packages/brouwers/laravel-doctrine)

*A drop-in Doctrine ORM 2 implementation for Laravel 5+*

* Easy configuration
* Pagination
* Preconfigured metadata, connections and caching
* Extendable: extend or add your own drivers for metadata, connections or cache
* Change metadata, connection or cache settings easy with a resolved hook
* Annotations, yaml, xml, config and static php meta data mappings
* Multiple entity managers and connections
* Laravel naming strategy
* Simple authentication implementation
* Password reminders implementation
* Doctrine console commands
* DoctrineExtensions supported
* Timestamps, Softdeletes and TablePrefix listeners 

## Documentation

Begin reading [the full documentation](https://github.com/patrickbrouwers/Laravel-Doctrine/wiki) here or go to a specific chapter right away.

1. [Installation](https://github.com/patrickbrouwers/Laravel-Doctrine/wiki/Installation)
2. [Basics](https://github.com/patrickbrouwers/Laravel-Doctrine/wiki/Basics)
  1. [Entities](https://github.com/patrickbrouwers/Laravel-Doctrine/wiki/Entities)
  2. [Meta Data](https://github.com/patrickbrouwers/Laravel-Doctrine/wiki/Meta-Data)
      1. [Annotations](https://github.com/patrickbrouwers/Laravel-Doctrine/wiki/Meta-Data#annotations)
      2. [YAML](https://github.com/patrickbrouwers/Laravel-Doctrine/wiki/Meta-Data#yaml)
      3. [XML](https://github.com/patrickbrouwers/Laravel-Doctrine/wiki/Meta-Data#xml)
      4. [Config files](https://github.com/patrickbrouwers/Laravel-Doctrine/wiki/Meta-Data#config-files)
      5. [StaticPHP](https://github.com/patrickbrouwers/Laravel-Doctrine/wiki/Meta-Data#static-php)
  3. [EntityManager](https://github.com/patrickbrouwers/Laravel-Doctrine/wiki/EntityManager)
  4. [Multiple Connections](https://github.com/patrickbrouwers/Laravel-Doctrine/wiki/Multiple-Connections)
  5. [Repositories](https://github.com/patrickbrouwers/Laravel-Doctrine/wiki/Repositories)
  6. [Console Commands](https://github.com/patrickbrouwers/Laravel-Doctrine/wiki/Console-Commands)
3. [Configuration](https://github.com/patrickbrouwers/Laravel-Doctrine/wiki/Configuration)
  1. [Connections](https://github.com/patrickbrouwers/Laravel-Doctrine/wiki/Connections)
  2. [Meta Data](https://github.com/patrickbrouwers/Laravel-Doctrine/wiki/Meta-Data-Configuration)
  3. [Caching](https://github.com/patrickbrouwers/Laravel-Doctrine/wiki/Caching)
4. [Extensions](https://github.com/patrickbrouwers/Laravel-Doctrine/wiki/Extensions)
  1. [Authentication](https://github.com/patrickbrouwers/Laravel-Doctrine/wiki/Authentication)
  2. [Softdeletes](https://github.com/patrickbrouwers/Laravel-Doctrine/wiki/Softdeletes)
  3. [Timestamps](https://github.com/patrickbrouwers/Laravel-Doctrine/wiki/Timestamps)
  4. [Table Prefixing](https://github.com/patrickbrouwers/Laravel-Doctrine/wiki/Table-Prefixing)
  5. [DoctrineExtensions](https://github.com/patrickbrouwers/Laravel-Doctrine/wiki/DoctrineExtensions)
  6. [Writing your own extensions](https://github.com/patrickbrouwers/Laravel-Doctrine/wiki/Writing-your-own-extensions)

## Installation

Require this package in your `composer.json` and run `composer update`.

```php
"brouwers/laravel-doctrine": "~1.0.0"
```

After updating composer, add the ServiceProvider to the providers array in `config/app.php`

```php
'Brouwers\LaravelDoctrine\DoctrineServiceProvider',
```

Optionally you can register the EntityManager facade:

```php
'EntityManager' => 'Brouwers\LaravelDoctrine\Facades\EntityManager'
```

To publish the config use:

```php
php artisan vendor:publish --tag="config"
```

## Quick start

Out of the box this package uses the default Laravel connection which is provided in `config/database.php`, which means that you are ready to start fetching and persisting.

```php
<?php

$article = new Article;
$article->setTitle('Laravel Doctrine Quick start');

EntityManager::persist($article);
EntityManager::flush();
```
Unlike Eloquent, Doctrine is not an Active Record pattern, but a Data Mapper pattern. Every Active Record model extends a base class (with all the database logic), which has a lot of overhead and dramatically slows down your application with thousands or millions of records.
Doctrine entities don't extend any class. The domain/business logic is completely separated from the persistence logic. 
This means we have to tell Doctrine how it should map the columns from the database to our Entity class. In this example we are using annotations. Other possiblities are yaml, xml or php array's.
The `Article` entity used in the example above looks like this.

```php
<?php

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="articles")
 */
class Article
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $title;

    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }
}
```

To quickly create the `articles` table inside your database, run: `php artisan doctrine:schema:update`

Continue reading [the full documentation](https://github.com/patrickbrouwers/Laravel-Doctrine/wiki).

## License

This package is licensed under the [MIT license](https://github.com/mitchellvanw/laravel-doctrine/blob/master/LICENSE).
