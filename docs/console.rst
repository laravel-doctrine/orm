==========================
Console (Artisan Commands)
==========================

Many artisan commands are included in this repository.

* ``doctrine:clear:metadata:cache`` - Clear all metadata cache of the various
  cache drivers.
* ``doctrine:clear:query:cache`` - Clear all query cache of the various
  cache drivers.
* ``doctrine:clear:result:cache`` - Clear all result cache of the various
  cache drivers.
* ``doctrine:generate:proxies`` - Generates proxy classes for entity classes.
* ``doctrine:info`` - Show basic information about all mapped entities.
* ``doctrine:schema:create`` - Processes the schema and either create it
  directly on EntityManager Storage Connection or generate the SQL output.
* ``doctrine:schema:drop`` - Drop the complete database schema of EntityManager
  Storage Connection or generate the corresponding SQL output.
* ``doctrine:schema:update`` - Executes (or dumps) the SQL needed to update
  the database schema to match the current mapping metadata.
* ``doctrine:schema:validate`` - Validate the mapping files.

.. role:: raw-html(raw)
   :format: html

.. include:: footer.rst
