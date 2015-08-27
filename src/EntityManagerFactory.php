<?php

namespace LaravelDoctrine\ORM;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use LaravelDoctrine\ORM\Configuration\Connections\ConnectionManager;
use LaravelDoctrine\ORM\Configuration\MetaData\MetaDataManager;
use LaravelDoctrine\ORM\Exceptions\ClassNotFound;

class EntityManagerFactory
{
    /**
     * @param array $settings
     *
     * @return EntityManager
     */
    public function create($settings = [])
    {
        $configuration = MetaDataManager::resolve(array_get($settings, 'meta'));

        $this->registerPaths($settings, $configuration);
        $this->configureProxies($settings, $configuration);

        $configuration->setDefaultRepositoryClassName(
            array_get($settings, 'repository', EntityRepository::class)
        );

        $manager = EntityManager::create(
            ConnectionManager::resolve(array_get($settings, 'connection')),
            $configuration
        );

        $this->registerSubscribers($settings, $manager);
        $this->registerFilters($settings, $configuration, $manager);
        $this->registerListeners($settings, $manager);

        return $manager;
    }

    /**
     * @param array         $settings
     * @param EntityManager $manager
     */
    protected function registerListeners($settings = [], EntityManager $manager)
    {
        if (isset($settings['events']['listeners'])) {
            foreach ($settings['events']['listeners'] as $event => $listener) {
                $manager->getEventManager()->addEventListener($event, $listener);
            }
        }
    }

    /**
     * @param array         $settings
     * @param EntityManager $manager
     */
    protected function registerSubscribers($settings = [], EntityManager $manager)
    {
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
    }

    /**
     * @param array         $settings
     * @param Configuration $configuration
     * @param EntityManager $manager
     */
    protected function registerFilters($settings = [], Configuration $configuration, EntityManager $manager = null)
    {
        if (isset($settings['filters'])) {
            foreach ($settings['filters'] as $name => $filter) {
                $configuration->getMetadataDriverImpl()->addFilter($name, $filter);
                $manager->getFilters()->enable($name);
            }
        }
    }

    /**
     * @param array         $settings
     * @param Configuration $configuration
     */
    protected function registerPaths($settings = [], Configuration $configuration)
    {
        $paths = array_get($settings, 'paths', []);
        $meta  = $configuration->getMetadataDriverImpl();

        if (method_exists($meta, 'addPaths')) {
            $meta->addPaths($paths);
        } elseif (method_exists($meta, 'getLocator')) {
            $meta->getLocator()->addPaths($paths);
        }
    }

    /**
     * @param array         $settings
     * @param Configuration $configuration
     */
    protected function configureProxies($settings = [], Configuration $configuration)
    {
        $configuration->setProxyDir(
            array_get($settings, 'proxies.path', storage_path('proxies'))
        );

        $configuration->setAutoGenerateProxyClasses(
            array_get($settings, 'proxies.auto_generate', false)
        );

        if ($namespace = array_get($settings, 'proxies.namespace', false)) {
            $configuration->setProxyNamespace($namespace);
        }
    }
}
