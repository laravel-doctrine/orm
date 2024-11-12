=======
Install
=======

Installation of this module uses composer. For composer documentation, please
refer to `getcomposer.org <http://getcomposer.org/>`_ ::

  $ composer require laravel-doctrine/orm

To publish the config use:

.. code-block:: bash

  php artisan vendor:publish --tag="config" --provider="LaravelDoctrine\ORM\DoctrineServiceProvider"

Thanks to Laravel auto package discovery, the ServiceProvider and Facades are
automatically registered.  However they can still be manually registered if
required (see below).


Environment Variables
=====================

Environment variables used inside the config

* ``DOCTRINE_METADATA`` - The type of metadata for the Doctrine entities.
  Defaults to ``attributes``.
* ``DOCTRINE_PROXY_AUTOGENERATE`` - Whether to autogenerate proxies.  Should be
  set to ``false`` for production.
* ``DOCTRINE_CACHE`` - The cache handler.  Default is ``array``.
* ``DOCTRINE_METADATA_CACHE`` - The cache handler for metadata.
  Default is ``DOCTRINE_CACHE``.
* ``DOCTRINE_QUERY_CACHE`` - The cache handler for the query cache.
  Default is ``DOCTRINE_CACHE``.
* ``DOCTRINE_RESULT_CACHE`` - The cache handler for the results.
  Default is ``DOCTRINE_CACHE``.
* ``DOCTRINE_LOGGER`` - The logger to use to log DQL queries.


Application Folder Structure
============================

Doctrine entities do not belong in the ``Model`` directory.  Doctrine supplies
not just an ORM but also an `ODM <https://www.doctrine-project.org/projects/doctrine-mongodb-odm/en/2.9/index.html>`_.
So, when using Doctrine ORM, it is important to always address it as ORM.

This is the recommended directory structure for a Doctrine ORM Installation:

.. code-block:: bash

  ~/app/Doctrine/ORM

Underneath this directory you may choose to have one directory per entity
manager or, for an app with just one entity manager, the following directories
are suggested:

.. code-block:: bash

  ~/app/Doctrine/ORM/Entity
  ~/app/Doctrine/ORM/Repository
  ~/app/Doctrine/ORM/Subscriber
  ~/app/Doctrine/ORM/Listener

If you are new to Doctrine ORM, it is recommended you review the
`Repository Pattern <https://blog.mnavarro.dev/the-repository-pattern-done-right>`_.


Entity Metadata
===============

Change the ``config/doctrine.php`` file paths

.. code-block:: php

  'paths' => [
      base_path('app/Doctrine/ORM/Entity'),
  ],


Manual Registration
===================

After updating composer, add the ServiceProvider to the providers
array in ``config/app.php``

.. code-block:: php
  LaravelDoctrine\ORM\DoctrineServiceProvider::class,

Optionally, you can register the EntityManager, Registry and/or Doctrine facades

.. code-block:: php
  'EntityManager' => LaravelDoctrine\ORM\Facades\EntityManager::class,
  'Registry'      => LaravelDoctrine\ORM\Facades\Registry::class,
  'Doctrine'      => LaravelDoctrine\ORM\Facades\Doctrine::class,


.. role:: raw-html(raw)
   :format: html

.. include:: footer.rst
