==============================================
Doctrine Manager and Accessing Entity Managers
==============================================

Accessing Entity Managers
=========================

Laravel Doctrine uses ``DoctrineManager`` to provide an easy method of hooking
into the internals of an Entity Manager for more advanced configuration than
is possible with just a configuration file.

It provides access to three facets of Doctrine:

* `Doctrine\ORM\Configuration <https://www.doctrine-project.org/projects/doctrine-orm/en/latest/reference/configuration.html#installation-and-configuration>`_
* `Doctrine\DBAL\Connection <https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html>`_
* `Doctrine\Common\EventManager <https://www.doctrine-project.org/projects/doctrine-event-manager/en/latest/reference/index.html>`_

These objects are accessed **per Entity Manager** using the name configured
for that EM in ``doctrine.php``

Using DoctrineManager
=====================

Boilerplate example of ``DoctrineManager`` using facade ``LaravelDoctrine\ORM\Facades\Doctrine``

.. code-block:: php

  Doctrine::extend('myManager', function(Configuration $configuration, Connection $connection, EventManager $eventManager) {
      //modify and access settings as is needed
  });


Using dependency injection in ``boot()`` of a ServiceProvider

.. code-block:: php

  public function boot(DoctrineManager $manager) {
      $manager->extend('myManager', function(Configuration $configuration, Connection $connection, EventManager $eventManager) {
          //modify and access settings as is needed
      });
  }


Implementing Your Own Extender
==============================

Additionally, you can write your own custom manager by implementing
``LaravelDoctrine\ORM\DoctrineExtender``

.. code-block:: php

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


  $manager->extend('myManager', MyDoctrineExtender::class);


.. role:: raw-html(raw)
   :format: html

.. include:: footer.rst
