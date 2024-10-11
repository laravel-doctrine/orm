#Entity Manager

The EntityManager is the central access point to ORM functionality. It can be used to find, persist, flush and remove entities.

## Using the EntityManager

#### Facade

You can use the facade to access the `EntityManager` methods

```php
EntityManager::flush();
```

#### Container

```php
app('em');
app('Doctrine\ORM\EntityManagerInterface');
```

#### Dependency injection

```php
<?php

use Doctrine\ORM\EntityManagerInterface;

class ExampleController extends Controller
{
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
}
```

#### Multiple connections

If you are using multiple connections `EntityManagerInterface` will return the default connection. If you want to have control over which EnityManager you want, you'll have to inject `Doctrine\Common\Persistence\ManagerRegistry`

```php
use Doctrine\Common\Persistence\ManagerRegistry;

class ExampleController extends Controller
{
    protected $em;

    public function __construct(ManagerRegistry $em)
    {
        $this->em = $em->getManager('otherConnection');
    }
}
```

## Finding entities

> Note: for making the examples more expressive, we will use the facade. However I do recommend to leverage dependency injection as much as possible. This makes mocking the EntityManager in your tests a lot easier.

Entities are objects with identity. Their identity has a conceptual meaning inside your domain. In a CMS application each article has a unique id. You can uniquely identify each article by that id.

In the example underneath, the Article entity is fetched from the entity manager twice, but was modified after the first find. Doctrine2 keeps track of all those changes. This pattern is called `Identity Map pattern`, which means that Doctrine keeps a map of each entity and ids that have been retrieved per request and keeps return the same instances on every find.

`$article` and `$article1` will be identical, eventhough we haven't persisted the changes to `$article` to the database yet.

```php
$article = EntityManager::find('App\Entities\Article', 1);
$article->setTitle('Different title');

$article2 = EntityManager::find('App\Entities\Article', 1);

if ($article === $article2) {
    echo "Yes we are the same!";
}
```

### Persisting

By passing the entity through the `persist` method of the EntityManager, that entity becomes MANAGED, which means that its persistence is from now on managed by an EntityManager. As a result the persistent state of such an entity will subsequently be properly synchronised with the database when `EntityManager::flush()` is invoked.

> Note: `persist()` doesn't do any `INSERT` queries.

```php
$article = new Article;
$article->setTitle('Let\'s learn about persisting');

EntityManager::persist($article);
EntityManager::flush();
```

### Flushing

Whenever you have changed the state of an Entity (updated, created, removed, ...) you will have to flush the entity manager to persist these changes to the database.

```php
// Flush all changes
EntityManager::flush();

// Only flush changes of given entity
EntityManager::flush($article);
```

### Removing

An entity can be removed from persistent storage by passing it to the `remove($entity)` method. By applying the remove operation on some entity, that entity becomes REMOVED, which means that its persistent state will be deleted once `flush()` is invoked.

```php
EntityManager::remove($article);
EntityManager::flush();
```

More information about the entity manager: https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/working-with-objects.html
