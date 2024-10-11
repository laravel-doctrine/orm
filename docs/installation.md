# Installation in Laravel 6+

 Laravel  | Laravel Doctrine
:---------|:----------
 6.*      | ~1.5
 7.*      | ~1.6
 8.*      | ~1.7
 9.*      | ~1.8

Install this package with composer:

```
composer require laravel-doctrine/orm
```

Thanks to Laravel auto package discovery feature, the ServiceProvider and Facades are automatically registered.  
However they can still be manually registered if required 

## Manual registration
After updating composer, add the ServiceProvider to the providers array in `config/app.php`

```php
LaravelDoctrine\ORM\DoctrineServiceProvider::class,
```

Optionally you can register the EntityManager, Registry and/or Doctrine facade:

```php
'EntityManager' => LaravelDoctrine\ORM\Facades\EntityManager::class,
'Registry'      => LaravelDoctrine\ORM\Facades\Registry::class,
'Doctrine'      => LaravelDoctrine\ORM\Facades\Doctrine::class,
```

## Config
To publish the config use:

```php
php artisan vendor:publish --tag="config" --provider="LaravelDoctrine\ORM\DoctrineServiceProvider"
```

Available environment variables inside the config are: `APP_DEBUG`, `DOCTRINE_METADATA`, `DB_CONNECTION`, `DOCTRINE_PROXY_AUTOGENERATE`, `DOCTRINE_LOGGER` and `DOCTRINE_CACHE`

> Important:
> By default, Laravel's application skeleton has its `Model` classes in the `app/Models` folder. With Doctrine, you'll need to
> create a dedicated folder for your `Entities` and point your `config/doctrine.php` `paths` array to it.
> If you don't, Doctrine will scan your whole `app/` folder for files, which will have a huge impact on performance!
> 
> ```
> 'paths' => [
>     base_path('app/Entities'),
> ],
> ```
