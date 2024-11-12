=======
Caching
=======

This package supports these caching systems out of the box:

* redis
* memcached
* file
* apc
* array

Extending or Adding Cache Drivers
=================================

Drivers can be replaced or added using
``LaravelDoctrine\ORM\Configuration\Cache\CacheManager``. The return should
implement ``Doctrine\Common\Cache\Cache`` or extend ``Doctrine\Common\Cache\CacheProvider``

.. code-block:: php

  public function register()
  {
      $this->app->resolving(CacheManager::class, function (CacheManager $cache){
          $cache->extend('memcache', function(array $settings, Application $app) {
              $memcache = new \Memcache;
              return new MemcacheCache($memcache);
          });
      });
  }


.. role:: raw-html(raw)
   :format: html

.. include:: footer.rst
