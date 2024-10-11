# Introduction

#### A drop-in Doctrine ORM 2 implementation for Laravel 6+

Doctrine 2 is an object-relational mapper (ORM) for PHP that provides transparent persistence for PHP objects. 
It uses the Data Mapper pattern at the heart, aiming for a complete separation of 
your domain/business logic from the persistence in a relational database management system.

The benefit of Doctrine for the programmer is the ability to focus on the object-oriented business 
logic and worry about persistence only as a secondary problem. This doesn’t mean persistence is downplayed by Doctrine 2, 
however it is our belief that there are considerable benefits for object-oriented programming if persistence and entities are kept separated.

*Laravel Doctrine offers:*

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
