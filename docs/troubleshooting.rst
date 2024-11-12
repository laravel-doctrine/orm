===============
Troubleshooting
===============

Common issues that you may run into when configuring or running Laravel Doctrine.

The DatabaseTransactions trait is not working for tests
=======================================================

You will need to use a new trait instead of the default Laravel trait.
Here is an example of an implementation:

.. code-block:: php

  namespace Tests;

  use LaravelDoctrine\ORM\Facades\EntityManager;

  trait DoctrineDatabaseTransactions
  {
      public function setUpDoctrineDatabaseTransactions(): void
      {
          EntityManager::getConnection()->beginTransaction();
      }

      public function tearDownDoctrineDatabaseTransactions(): void
      {
          EntityManager::getConnection()->rollBack();
      }
  }


If you would like to also use ``assertDatabaseHas`` in your tests, you need
to tell Laravel to use the same connection as Doctrine is using so that it
can find rows that have not been committed in a transaction:

.. code-block:: php

  $pdo = app()->make(\Doctrine\ORM\EntityManagerInterface::class)->getConnection()
              ->getWrappedConnection();

  app()->make(\Illuminate\Database\ConnectionInterface::class)->setPdo($pdo);


Missing Proxy files
===================

  ErrorException: require(.../storage/proxies/__CG__Entity.php): Failed to open stream: No such file or directory

Proxies need to be generated before they can be used. You can generate the
proxies manually using the `php artisan doctrine:generate:proxies` command,
or you can enable proxy auto generation in your `doctrine.php` config file.
By default, you can set the `DOCTRINE_PROXY_AUTOGENERATE` environment value
to true to enable auto generation.

Note: Proxy auto generation should always be disabled in production for
performance reasons.


.. role:: raw-html(raw)
   :format: html

.. include:: footer.rst
