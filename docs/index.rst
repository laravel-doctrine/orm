====================
Laravel Doctrine ORM
====================

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

  installation
  lumen
  entities
  meta-data
  entity-manager
  multiple-connections
  repositories
  console

  config-file
  doctrine-manager
  connections
  meta-data-configuration
  caching
  troubleshooting

  auth
  passwords
  testing
  validation
  notifications

  upgrade
  contributions


Features
--------

* Easy configuration
* Pagination
* Preconfigured metadata, connections and caching
* Extendable: extend or add your own drivers for metadata, connections or cache
* Change metadata, connection or cache settings easy with a resolved hook
* Annotations, yaml, xml, config and static php meta data mappings
* Multiple entity managers and connections
* Laravel naming strategy
* Simple authentication implementation
* Password reminders implementation
* Doctrine console commands
* DoctrineExtensions supported
* Timestamps, Softdeletes and TablePrefix listeners

.. role:: raw-html(raw)
   :format: html

.. include:: footer.rst