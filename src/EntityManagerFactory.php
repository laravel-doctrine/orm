<?php

namespace LaravelDoctrine\ORM;

use LaravelDoctrine\ORM\Configuration\Connections\ConnectionManager;
use LaravelDoctrine\ORM\Configuration\MetaData\MetaDataManager;
use LaravelDoctrine\ORM\Exceptions\ClassNotFound;

class EntityManagerFactory
{
    public static function create($settings)
    {
        $manager = EntityManager::create(
            ConnectionManager::resolve(array_get($settings, 'connection')),
            MetaDataManager::resolve(array_get($settings, 'meta'))
        );

        $configuration = $manager->getConfiguration();

        // Listeners
        if (isset($settings['events']['listeners'])) {
            foreach ($settings['events']['listeners'] as $event => $listener) {
                $manager->getEventManager()->addEventListener($event, $listener);
            }
        }

        // Subscribers
        if (isset($settings['events']['subscribers'])) {
            foreach ($settings['events']['subscribers'] as $subscriber) {
                if (class_exists($subscriber, false)) {
                    $subscriberInstance = new $subscriber;
                    $manager->getEventManager()->addEventSubscriber($subscriberInstance);
                } else {
                    throw new ClassNotFound($subscriber);
                }
            }
        }

        // Filters
        if (isset($settings['filters'])) {
            foreach ($settings['filters'] as $name => $filter) {
                $configuration->getMetadataDriverImpl()->addFilter($name, $filter);
                $manager->getFilters()->enable($name);
            }
        }

        // Paths
        $paths = array_get($settings, 'paths', []);
        $meta = $configuration->getMetadataDriverImpl();

        if (method_exists($meta, 'addPaths')) {
            $meta->addPaths($paths);
        } elseif (method_exists($meta, 'getLocator')) {
            $meta->getLocator()->addPaths($paths);
        }

        // Repository
        $configuration->setDefaultRepositoryClassName(
            array_get($settings, 'repository', EntityRepository::class)
        );

        // Proxies
        $configuration->setProxyDir(
            array_get($settings, 'proxies.path', storage_path('proxies'))
        );

        $configuration->setAutoGenerateProxyClasses(
            array_get($settings, 'proxies.auto_generate', false)
        );

        if ($namespace = array_get($settings, 'proxies.namespace', false)) {
            $configuration->setProxyNamespace($namespace);
        }

        return $manager;
    }
}
