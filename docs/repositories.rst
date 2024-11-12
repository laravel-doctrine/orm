============
Repositories
============

The Repository Design Pattern is one of the most useful and most widely
applicable design patterns ever invented.
It works as an abstraction for your persistence layer, giving you a place to
write collecting logic, build queries, etc.

Repositories are usually modeled as collections to abstract away persistence
lingo, so it is very common to see methods
like ``findByName("Lasso")``, treating the repository as a collection.

Doctrine comes with a generic ``Doctrine\Common\Persistence\ObjectRepository``
interface that lets you easily find one,
many or all entities by ID, by an array of filters or by complex ``Criteria``,
and an implementation of it in ``Doctrine\ORM\EntityRepository``.

Getting a repository instance
=============================

The easiest way to get a repository is to let the EntityManager provide one
for the Entity you want.

.. code-block:: php

  use Doctrine\ORM\Mapping as ORM;
  use Doctrine\Common\Collections\ArrayCollection;
  use Doctrine\Common\Collections\Collection;

  #[ORM\Entity(repositoryClass: "App\Doctrine\ORM\Repository\ScientistRepository")]
  class Scientist
  {
      #[ORM\Id]
      #[ORM\Column(type: "integer")]
      #[ORM\GeneratedValue(strategy: "AUTO")]
      private int $id;

      #[ORM\Column(type: "string", nullable: false)]
      private string $firstName;

      #[ORM\Column(type: "string", nullable: false)]
      private string $lastName;

      #[ORM\OneToMany(targetEntity: Theory::class, mappedBy: "scientist")]
      private Collection $theories;

      public function __construct()
      {
          $this->theories = new ArrayCollection();
      }
  }

  $repository = EntityManager::getRepository(Scientist::class);

This will return an instance of the ``App\Doctrine\ORM\Repository\ScientistRepository``.
For entities that do not have a repositoryClass, an instnace of
the config ``reposiotry`` repository class is returned.


Injecting repositories
======================

Injecting repositories is **not** recommended.  Inject the entity manager
instead and always use it as a container for repositories.

The entity manager is both an entity manager and a container similar to
PSR-11.  Instead of injecting repositories, take a step back and be comfortable
injecting the container (entity manager).


Extending repositories
======================

If you want to have more control over these repositories, instead of always
calling it on the EntityManager, you can create your own repository class.
When we bind this concrete repository to an interface, it also makes that
we can easily swap the data storage behind them. It also makes testing easier,
because we can easily swap the concrete implementation for a mock.

Given we have a ScientistRepository:

.. code-block:: php

  interface ScientistRepository
  {
      public function find($id);
      public function findByName($name);
  }


We should be able to make a concrete implementation of it with Doctrine:

.. code-block:: php

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


Of course, now that we've built our own object, we are missing some useful
features from Doctrine's generic repositories.
Let's see two ways of reusing those generic objects inside our code.


Reusing repositories through inheritance
========================================

Inheritance may be the simplest way of reusing repositories in Doctrine.
We could change our implementation to something like this:

.. code-block:: php

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



Reusing repositories through composition
========================================

Sometimes inheritance may not be your preferred way of reusing a library.
If you'd rather decouple yourself from its implementation, if you need a
different one or if you are writing a library and don't want to force
inheritance on your consumers, you may choose to reuse Doctrine's generic
repository implementation through **composition** instead.

.. code-block:: php

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


This method gives you total control over your Repository API.
If, for example, you don't want to allow fetching all
Scientist, you simply don't add that method to the interface /
implementation, while inheriting the generic Doctrine
repository would force the ``findAll()`` method on to your
``ScientistRepository`` API.

## Using repositories

Inside your controller (or any object that will be constructed by Laravel),
you can now inject your repository interface:

.. code-block:: php

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

More about the EntityRepository:
https://www.doctrine-project.org/projects/doctrine-orm/en/latest/reference/working-with-objects.html

Learning more about the Repository Pattern:
http://shawnmc.cool/the-repository-pattern


.. role:: raw-html(raw)
   :format: html

.. include:: footer.rst
