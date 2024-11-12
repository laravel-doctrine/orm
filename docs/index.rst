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

Doctrine ORM is an object-relational mapper for PHP that provides
transparent persistence for PHP objects.
Doctrine uses the `data-mapper pattern <https://tsh.io/blog/active-record-vs-data-mapper-patterns-in-php/>`_,
aiming for a complete separation of your domain and business logic
from the persistence in a relational database management system.

The benefit of Doctrine for the programmer is the ability to focus on the
object-oriented business logic and worry about persistence only as a
secondary problem. This doesn’t mean persistence is downplayed by Doctrine ORM.
However, it is our belief that there are considerable benefits for
object-oriented programming if persistence and entities are seperate.

.. toctree::

  :caption: Table of Contents

  install
  configuration
  connections
  entities
  repositories
  entity-manager
  caching
  console

  doctrine-manager

  auth
  passwords
  notifications
  pagination
  validation
  testing

  multiple-connections
  troubleshooting
  upgrade


.. role:: raw-html(raw)
   :format: html

.. include:: footer.rst
