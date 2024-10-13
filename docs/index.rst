====================
Laravel Doctrine ORM
====================

.. image:: banner.png
   :align: center
   :scale: 25 %

This is the documentation for `laravel-doctrine/orm <https://github.com/laravel-doctrine/orm>`_

An integration library for Laravel and Doctrine ORM.
Version 3 of this library supports Laravel 10+,
Doctrine ORM ^3.0, and Doctrine DBAL ^4.0.


Introduction
============

Doctrine 2 is an object-relational mapper (ORM) for PHP that provides
transparent persistence for PHP objects.  It uses the Data Mapper pattern at
the heart, aiming for a complete separation of your domain/business logic
from the persistence in a relational database management system.

The benefit of Doctrine for the programmer is the ability to focus on the
object-oriented business logic and worry about persistence only as a
secondary problem. This doesn’t mean persistence is downplayed by Doctrine 2.
However, it is our belief that there are considerable benefits for
object-oriented programming if persistence and entities are seperate.

.. toctree::

  :caption: Table of Contents

  install
  entities
  metadata
  entity-manager
  multiple-connections
  repositories
  console

  configuration
  doctrine-manager
  connections
  caching
  troubleshooting

  auth
  passwords
  testing
  validation
  notifications
  pagination


.. role:: raw-html(raw)
   :format: html

.. include:: footer.rst