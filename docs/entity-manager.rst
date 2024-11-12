==============
Entity Manager
==============

The EntityManager is the central access point to ORM functionality. It can be
used to find, persist, remove, and flush entities.

Using the EntityManager
=======================

Facade
------

You can use the ``EntityManager`` facade to access the `EntityManager` methods.

.. code_block:: php

  EntityManager::flush();


Container
---------

.. code-block:: php

  app('em'); // alias
  app('Doctrine\ORM\EntityManager');
  app('Doctrine\ORM\EntityManagerInterface');


Dependency Injection
--------------------

.. code-block:: php

  use Doctrine\ORM\EntityManager;

  class ExampleController extends Controller
  {
      public function __construct(protected EntityManager $entityManager)
      {
      }
  }


Multiple Entity Managers
------------------------

If you are using multiple entity managers, dependency injection will only return
the default entity manager.  For control over which
entity manager you want, you'll have to inject
``Doctrine\Common\Persistence\ManagerRegistry``


.. code-block:: php

  use Doctrine\Common\Persistence\ManagerRegistry;
  use Doctrine\ORM\EntityManager;

  class ExampleController extends Controller
  {
      protected EntityManager $entityManager;

      public function __construct(ManagerRegistry $managerRegistry)
      {
          $this->entityManager = $managerRegistry->getManager('second');
      }
  }


Finding entities
----------------

.. note::

    For making the examples more expressive, facades are used.
    However it is recommend to leverage dependency injection as much as possible.
    This makes mocking the EntityManager in your tests a lot easier.

Entities are objects with identity. Their identity has a conceptual meaning
inside your domain. In a CMS application each article has a unique id. You
can uniquely identify each article by that id.

In the example below, the Article entity is fetched from the entity manager
twice, but was modified after the first find. Doctrine2 keeps track of all
those changes. This pattern is the `Identity Map Pattern <https://martinfowler.com/eaaCatalog/identityMap.html>`_,
which means that Doctrine keeps a map of each entity and ids that have been
retrieved per request and keeps return the same instances on every find.

``$entity`` and ``$entityCopy`` will be identical, eventhough we haven't
persisted the changes to ``$article`` to the database yet.

.. code-block:: php

  $entity = EntityManager::find('App\Entities\Article', 1);
  $entity->title = 'Different title';

  $entityCopy = EntityManager::find('App\Entities\Article', 1);

  assert($entityCopy->title === 'Different title');


Persisting
==========

By passing the entity through the ``$entityManager->persist()`` method of the EntityManager,
that entity becomes managed, which means that its persistence is from now
on managed by an EntityManager. As a result, the persistent state of such
an entity will subsequently be properly synchronised with the database
when ``$entityManager->flush()`` is invoked.

.. note::

  ``$entityManager->persist()`` doesn't execuate an ``INSERT`` query
  immediately.

.. code-block:: php

  $article = new Article();
  $article->title = 'Let\'s learn about persisting';

  EntityManager::persist($article);
  EntityManager::flush();


Removing (deleting)
===================

An entity can be removed from persistent storage by passing it to the
``$entityManager->remove($entity)`` method. By applying the remove operation on some entity,
that entity becomes REMOVED, which means that its persistent state will be
deleted once ``$entityManager->flush()`` is invoked.

.. code-block:: php

  EntityManager::remove($article);
  EntityManager::flush();


Flushing
========

Whenever the state of an Entity has changed (persisted, updated, removed),
flush the entity manager to persist the changes to the database.

.. code-block:: php

  // Flush all changes
  EntityManager::flush();

  // Only flush changes of given entity.
  // NOTE:  If you're doing it this way, one entity at a time, you're doing it wrong.
  EntityManager::flush($article);


More information about the entity manager:
https://www.doctrine-project.org/projects/doctrine-orm/en/latest/reference/working-with-objects.html


.. role:: raw-html(raw)
   :format: html

.. include:: footer.rst
