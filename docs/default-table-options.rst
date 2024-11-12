=====================
Default Table Options
=====================

You can set default table options for your database connections.

Modify your ``database.php`` configuration file to include the
``defaultTableOptions`` key

.. code-block:: php

  ...
      'mysql' => [
          'driver' => 'mysql',
          'host' => env('DB_HOST', '127.0.0.1'),
          'port' => env('DB_PORT', '3306'),
          'database' => env('DB_DATABASE', 'forge'),
          'username' => env('DB_USERNAME', 'forge'),
          'password' => env('DB_PASSWORD', ''),
          'unix_socket' => env('DB_SOCKET', ''),
          'defaultTableOptions' => [
              'charset' => 'utf8mb4',
              'collate' => 'utf8mb4_unicode_ci'
          ],
          'charset' => 'utf8mb4', // This will be the charset for the connection
          'prefix' => '',
          'strict' => true,
          'engine' => null,
      ],
  ...


* charset - Tables will be created with `CHARACTER SET utf8` by default
  unless otherwise specified in your metadata.
  **Even If** your database is set up with it's own default character set.
  Setting this property will ensure new tables are created with the given
  encoding
* collate - As above, this must be edited if you with your collation to be
  anything other than utf8_unicode_ci


.. role:: raw-html(raw)
   :format: html

.. include:: footer.rst
