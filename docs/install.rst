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

* ``DOCTRINE_CACHE``
* ``DOCTRINE_LOGGER``
* ``DOCTRINE_METADATA``
* ``DOCTRINE_PROXY_AUTOGENERATE``


Application Folder Structure
============================

Doctrine entities do not belong in the ``Model`` directory.
Because Eloquent is a part of the Laravel Framework, you will need a
directory structure just for Doctrine that is flexible enough to accomodate
two ORMs.

.. code-block:: bash

  ~/app/ORM/Doctrine
  ~/app/ORM/Doctrine/Entity
  ~/app/ORM/Doctrine/Repository
  ~/app/ORM/Doctrine/Subscriber
  ~/app/ORM/Doctrine/Listener

If you are using both Eloquent and Doctrine together in an application, it is
suggested you modify your directory structure to accomodate both in a logical
way.

.. code-block:: bash

  ~/app/ORM/Eloquent
  ~/app/ORM/Eloquent/Models

Change the ``config/doctrine.php`` file paths

.. code-block:: php

  'paths' => [
      base_path('app/ORM/Doctrine/Entity'),
  ],


Manual registration
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
