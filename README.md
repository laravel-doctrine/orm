# Laravel Doctrine

<img src="https://cloud.githubusercontent.com/assets/7728097/8503648/de6beb86-21c2-11e5-9d70-ed4c24185a7e.jpg"/>

[![GitHub release](https://img.shields.io/github/release/laravel-doctrine/orm.svg?style=flat)](https://packagist.org/packages/brouwers/laravel-doctrine)
[![Travis](https://img.shields.io/travis/laravel-doctrine/orm.svg?style=flat)](https://travis-ci.org/laravel-doctrine/orm)
[![Scrutinizer](https://img.shields.io/scrutinizer/g/laravel-doctrine/orm.svg?style=flat)](https://github.com/laravel-doctrine/orm)
[![Packagist](https://img.shields.io/packagist/dd/brouwers/Laravel-Doctrine.svg?style=flat)](https://packagist.org/packages/brouwers/laravel-doctrine)
[![Packagist](https://img.shields.io/packagist/dm/brouwers/Laravel-Doctrine.svg?style=flat)](https://packagist.org/packages/brouwers/laravel-doctrine)
[![Packagist](https://img.shields.io/packagist/dt/brouwers/Laravel-Doctrine.svg?style=flat)](https://packagist.org/packages/brouwers/laravel-doctrine)

### This software is STILL IN DEVELOPMENT.

It is working software but **breaking changes** may occur with no prior warning. **Do not use this in production!**

**For more information or to get started contributing visit us on [Slack](http://slack.laraveldoctrine.org/)**

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

Begin reading [the full documentation](https://github.com/laravel-doctrine/orm/wiki) here or go to a specific chapter right away.

1. [Installation](https://github.com/laravel-doctrine/orm/wiki/Installation)
2. [Basics](https://github.com/laravel-doctrine/orm/wiki/Basics)
  1. [Entities](https://github.com/laravel-doctrine/orm/wiki/Entities)
  2. [Meta Data](https://github.com/laravel-doctrine/orm/wiki/Meta-Data)
      1. [Annotations](https://github.com/laravel-doctrine/orm/wiki/Meta-Data#annotations)
      2. [YAML](https://github.com/laravel-doctrine/orm/wiki/Meta-Data#yaml)
      3. [XML](https://github.com/laravel-doctrine/orm/wiki/Meta-Data#xml)
      4. [Config files](https://github.com/laravel-doctrine/orm/wiki/Meta-Data#config-files)
      5. [StaticPHP](https://github.com/laravel-doctrine/orm/wiki/Meta-Data#static-php)
  3. [EntityManager](https://github.com/laravel-doctrine/orm/wiki/EntityManager)
  4. [Multiple Connections](https://github.com/laravel-doctrine/orm/wiki/Multiple-Connections)
  5. [Repositories](https://github.com/laravel-doctrine/orm/wiki/Repositories)
  6. [Console Commands](https://github.com/laravel-doctrine/orm/wiki/Console-Commands)
3. [Configuration](https://github.com/laravel-doctrine/orm/wiki/Configuration)
  1. [Connections](https://github.com/laravel-doctrine/orm/wiki/Connections)
  2. [Meta Data](https://github.com/laravel-doctrine/orm/wiki/Meta-Data-Configuration)
  3. [Caching](https://github.com/laravel-doctrine/orm/wiki/Caching)
4. [Extensions](https://github.com/laravel-doctrine/orm/wiki/Extensions)
  1. [Authentication](https://github.com/laravel-doctrine/orm/wiki/Authentication)
  2. [Softdeletes](https://github.com/laravel-doctrine/orm/wiki/Softdeletes)
  3. [Timestamps](https://github.com/laravel-doctrine/orm/wiki/Timestamps)
  4. [Table Prefixing](https://github.com/laravel-doctrine/orm/wiki/Table-Prefixing)
  5. [DoctrineExtensions](https://github.com/laravel-doctrine/orm/wiki/DoctrineExtensions)
  6. [Writing your own extensions](https://github.com/laravel-doctrine/orm/wiki/Writing-your-own-extensions)
5. [Configuration Migration](https://github.com/laravel-doctrine/orm/wiki/Migrating-Configurations)
  1. [Using the Configuration Migration Command](https://github.com/laravel-doctrine/orm/wiki/Using-the-configuration-migration-command)
  2. [Writing a template for configurations](https://github.com/laravel-doctrine/orm/wiki/Writing-a-template-for-a-configuration)

## Installation

Require this package in your `composer.json` and run `composer update`.

```php
"laravel-doctrine/orm": "@dev"
```

After updating composer, add the ServiceProvider to the providers array in `config/app.php`

```php
'LaravelDoctrine\ORM\DoctrineServiceProvider',
```

Optionally you can register the EntityManager facade:

```php
'EntityManager' => 'LaravelDoctrine\ORM\Facades\EntityManager'
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

Continue reading [the full documentation](https://github.com/laravel-doctrine/orm/wiki).

## License

This package is licensed under the [MIT license](https://github.com/laravel-doctrine/orm/blob/master/LICENSE).
