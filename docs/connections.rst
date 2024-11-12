===========
Connections
===========

Database connections configured in ``config/database.php`` are supported:

* mysql
* sqlite
* pqsql
* sqlsrv
* oci8

Please note that read/write connections are supported as well. See the
`Laravel documentation <https://laravel.com/docs/database#read-and-write-connections>`_
for more details.

Changing the ``DB_CONNECTION`` environment variable swaps the database
connection for Doctrine as well.
The additional settings per connection are applied by default.


Extending or Adding Connections Drivers
=======================================

You can replace existing connection drivers or add custom drivers using the
``LaravelDoctrine\ORM\Configuration\Connections\ConnectionManager``.
This should return an array of parameters.

.. code-block:: php

  use LaravelDoctrine\ORM\Configuration\Connections\ConnectionManager;
  use Illuminate\Contracts\Container\Container;

  public function boot(ConnectionManager $connections): void
  {
      $connections->extend('myDriver', function(array $settings, Container $container) {
          return [
              'driver' => 'driver',
              'host'   => ...
          ];
      });
  }

You can find the available connection parameters inside the
`Doctrine documentation <https://doctrine-dbal.readthedocs.org/en/latest/reference/configuration.html>`_.


.. role:: raw-html(raw)
   :format: html

.. include:: footer.rst
