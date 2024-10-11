# Accessing Entity Managers

Laravel Doctrine uses `DoctrineManager` to provide an easy method of hooking into the internals of an Entity Manager for more advanced configuration than is possible with just a configuration file.

It provides access to three facets of Doctrine:

* [Doctrine\ORM\Configuration](http://www.doctrine-project.org/api/dbal/2.1/class-Doctrine.DBAL.Configuration.html)
* [Doctrine\DBAL\Connection](http://www.doctrine-project.org/api/dbal/2.1/class-Doctrine.DBAL.Connection.html)
* [Doctrine\Common\EventManager](http://www.doctrine-project.org/api/common/2.2/class-Doctrine.Common.EventManager.html)

These objects are accessed **per Entity Manager** using the name configured for that EM in `doctrine.php`

## Using DoctrineManager

Boilerplate example of `DoctrineManager` using facade `LaravelDoctrine\ORM\Facades\Doctrine`

```php
Doctrine::extend('myManager', function(Configuration $configuration, Connection $connection, EventManager $eventManager) {
    //modify and access settings as is needed
});
```

Using dependency injection in `boot()` of a ServiceProvider

```php
public function boot(DoctrineManager $manager) {
    $manager->extend('myManager', function(Configuration $configuration, Connection $connection, EventManager $eventManager) {
        //modify and access settings as is needed
    });
}
```

## Implementing Your Own Extender

Additionally, you can write your own custom manager by implementing `LaravelDoctrine\ORM\DoctrineExtender`

```php
class MyDoctrineExtender implements DoctrineExtender
{
    /**
     * @param Configuration $configuration
     * @param Connection    $connection
     * @param EventManager  $eventManager
     */
    public function extend(Configuration $configuration, Connection $connection, EventManager $eventManager)
    {
        //your extending code...
    }
}
```

```php
$manager->extend('myManager', MyDoctrineExtender::class);
```
