# Repositories

The Repository Design Pattern is one of the most useful and most widely applicable design patterns ever invented.
It works as an abstraction for your persistence layer, giving you a place to write collecting logic, build queries, etc.

Repositories are usually modeled as collections to abstract away persistence lingo, so it is very common to see methods
like `find($id)`, `findByName("Patrick")`, as if your entities would be in a `Collection` object instead of a database.

Doctrine comes with a generic `Doctrine\Common\Persistence\ObjectRepository` interface that lets you easily find one,
many or all entities by ID, by an array of filters or by complex `Criteria`, and an implementation of it in
`Doctrine\ORM\EntityRepository`.

## Getting a repository instance

The easiest way to get a repository is to let the EntityManager generate one for the Entity you want:

```php
$repository = EntityManager::getRepository(Scientist::class);
```

This will generate an instance of the `Doctrine\ORM\EntityRepository`, a generic implementation ready to be queried for
 the class that was given to it.

## Injecting repositories

You can inject generic repositories by using Laravel's [contextual binding](https://laravel.com/docs/container#contextual-binding).

```php
<?php
namespace App\Entities\Research;

use Doctrine\Common\Persistence\ObjectRepository;

class Laboratory
{
    /**
     * @var ObjectRepository
     */
    private $scientists;

    public function __construct(ObjectRepository $scientists)
    {
        $this->scientists = $scientists;
    }
}

// Then, in one of your ServiceProviders
use App\Entities\Research\Laboratory;
use App\Entities\Research\Scientist;
use Doctrine\Common\Persistence\ObjectRepository;

class AppServiceProvider
{
    public function register()
    {
        $this->app
            ->when(Laboratory::class)
            ->needs(ObjectRepository::class)
            ->give(function(){
                return EntityManager::getRepository(Scientist::class);
            });
    }
}
```

## Extending repositories

If you want to have more control over these repositories, instead of always calling it on the EntityManager, you can
create your own repository class. When we bind this concrete repository to an interface, it also makes that we can
easily swap the data storage behind them. It also makes testing easier, because we can easily swap the concrete
implementation for a mock.

Given we have a ScientistRepository:

```php
<?php

interface ScientistRepository
{
    public function find($id);
    public function findByName($name);
}
```

We should be able to make a concrete implementation of it with Doctrine:

```php
<?php

class DoctrineScientistRepository implements ScientistRepository
{
    public function find($id)
    {
        // implement your find method
    }

    public function findByName($name)
    {
        // implement your find by title method
    }
}
```

Of course, now that we've built our own object, we are missing some useful features from Doctrine's generic repositories.
Let's see two ways of reusing those generic objects inside our code.

### Reusing repositories through inheritance

Inheritance may be the simplest way of reusing repositories in Doctrine. We could change our implementation to something
like this:

```php
<?php

use Doctrine\ORM\EntityRepository;

class DoctrineScientistRepository extends EntityRepository implements ScientistRepository
{
    // public function find($id) already implemented in parent class!

    public function findByName($name)
    {
        return $this->findBy(['name' => $name]);
    }
}

// Then, in one of your ServiceProviders
use App\Entities\Research\Scientist;

class AppServiceProvider
{
    public function register()
    {
        $this->app->bind(ScientistRepository::class, function($app) {
            // This is what Doctrine's EntityRepository needs in its constructor.
            return new DoctrineScientistRepository(
                $app['em'],
                $app['em']->getClassMetaData(Scientist::class)
            );
        });
    }
}
```

### Reusing repositories through composition

Sometimes inheritance may not be your preferred way of reusing a library. If you'd rather decouple yourself from its
implementation, if you need a different one or if you are writing a library and don't want to force inheritance on your
consumers, you may choose to reuse Doctrine's generic repository implementation through *composition* instead.

```php
<?php

use Doctrine\Common\Persistence\ObjectRepository;

class DoctrineScientistRepository implements ScientistRepository
{
    private $genericRepository;
    
        public function __construct(ObjectRepository $genericRepository)
        {
            $this->genericRepository = $genericRepository;
        }
    
        public function find($id)
        {
            return $this->genericRepository->find($id);
        }
    
        public function findByName($name)
        {
            return $this->genericRepository->findBy(['name' => $name]);
        }
}

// Then, in one of your ServiceProviders
use App\Entities\Research\Scientist;

class AppServiceProvider
{
    public function register()
    {
        $this->app->bind(ScientistRepository::class, function(){
            return new DoctrineScientistRepository(
                EntityManager::getRepository(Scientist::class)
            );
        });
    }
}
```

This method gives you total control over your Repository API. If, for example, you don't want to allow fetching all
Scientist, you simply don't add that method to the interface / implementation, while inheriting the generic Doctrine
repository would force the `findAll()` method on to your `ScientistRepository` API.

## Using repositories

Inside your controller (or any object that will be constructed by Laravel), you can now inject your repository interface:

```php

class ExampleController extends Controller
{
    private $scientists;

    public function __construct(ScientistRepository $scientists)
    {
        $this->scientists = $scientists;
    }

    public function index()
    {
        $articles = $this->scientists->findAll();
    }

}
```

More about the EntityRepository: http://www.doctrine-project.org/api/orm/2.5/class-Doctrine.ORM.EntityRepository.html

Learning more about the Repository Pattern: http://shawnmc.cool/the-repository-pattern

### Pagination

If you want to add easy pagination, you can add the `LaravelDoctrine\ORM\Pagination\PaginatesFromRequest` trait to your repositories. It offers two methods: `paginateAll($perPage = 15, $pageName = 'page')` and `paginate($query, $perPage, $pageName = 'page', $fetchJoinCollection = true)`.

`paginateAll` will work out of the box, and will return all (non-deleted, when soft-deletes are enabled) entities inside Laravel's `LengthAwarePaginator`.

If you want to add pagination to your custom queries, you will have to pass the query object through `paginate()`

```php
public function paginateAllPublishedScientists($perPage = 15, $pageName = 'page')
{
    $builder = $this->createQueryBuilder('o');
    $builder->where('o.status = 1');

    return $this->paginate($builder->getQuery(), $perPage, $pageName);
}
```

The `PaginatesFromRequest` trait uses Laravel's `Request` object to fetch current page, just as `Eloquent` does by default. If this doesn't fit your scenario, you can also take advantage of pagination in Doctrine with the `LaravelDoctrine\ORM\Pagination\PaginatesFromParams` trait:

```php
namespace App\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use LaravelDoctrine\ORM\Pagination\PaginatesFromParams;

class DoctrineScientistRepository
{
    use PaginatesFromParams;
    
    /**
     * @return Scientist[]|LengthAwarePaginator
     */
    public function all(int $limit = 8, int $page = 1): LengthAwarePaginator
    {
        // paginateAll is already public, you may use it directly as well.
        return $this->paginateAll($limit, $page);
    }
    
    /**
     * @return Scientist[]|LengthAwarePaginator
     */
    public function findByName(string $name, int $limit = 8, int $page = 1): LengthAwarePaginator
    {
        $query = $this->createQueryBuilder('s')
            ->where('s.name LIKE :name')
            ->orderBy('s.name', 'asc')
            ->setParameter('name', "%$name%")
            ->getQuery();
            
        return $this->paginate($query, $limit, $page);
    }
}
```
