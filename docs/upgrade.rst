================
Upgrade from 2.0
================

Version 3.0.x of this repository has several changes that may affect you if
you are upgrading from version 2.0.

* The Table Prefix extension has been removed - this was a problematic
  extension and doesn't fit with the goal of Laravel and Doctrine integration.
* EnsureProductionSettingsCommand has been removed - Doctrine no longer
  ships with this command.
* Annotations and YAML metadata drivers have been removed -
  these have been removed from Doctrine.


.. role:: raw-html(raw)
   :format: html

.. include:: footer.rst
