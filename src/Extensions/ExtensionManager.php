<?php

namespace Brouwers\LaravelDoctrine\Extensions;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\DoctrineExtensions;
use Illuminate\Contracts\Events\Dispatcher;

class ExtensionManager
{
    /**
     * @var array|Extension[]
     */
    protected $extensions = [];

    /**
     * @var array
     */
    protected $gedmo = [
        'enabled' => false
    ];

    /**
     * @var MappingDriverChain
     */
    protected $chain;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Doctrine\Common\EventManager
     */
    protected $evm;

    /**
     * @var \Doctrine\ORM\Configuration
     */
    protected $metadata;

    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * @param ManagerRegistry $registry
     * @param Dispatcher      $dispatcher
     */
    public function __construct(ManagerRegistry $registry, Dispatcher $dispatcher)
    {
        $this->registry   = $registry;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Boot the extensions
     */
    public function boot()
    {
        foreach ($this->registry->getManagers() as $em) {
            $this->em       = $em;
            $this->chain    = new MappingDriverChain();
            $this->evm      = $this->em->getEventManager();
            $this->metadata = $this->em->getConfiguration();
            $this->reader   = method_exists($this->metadata->getMetadataDriverImpl(), 'getReader')
                ? $this->metadata->getMetadataDriverImpl()->getReader()
                : false;

            if ($this->gedmo['enabled']) {
                $this->bootGedmoExtensions($this->gedmo['namespace'], $this->gedmo['all']);
            }

            foreach ($this->extensions as $extenion) {
                $this->bootExtension($extenion);
            }
        }
    }

    /**
     * @param Extension $extension
     */
    public function register(Extension $extension)
    {
        $this->extensions[] = $extension;
    }

    /**
     * @param Extension $extension
     */
    public function bootExtension(Extension $extension)
    {
        $extension->addSubscribers($this->evm, $this->em, $this->reader);

        if (is_array($extension->getFilters())) {
            foreach ($extension->getFilters() as $name => $filter) {
                $this->metadata->addFilter($name, $filter);
                $this->em->getFilters()->enable($name);
            }
        }
    }

    /**
     * Enable Gedmo Doctrine Extensions
     *
     * @param string $namespace
     * @param bool   $all
     */
    public function enableGedmoExtensions($namespace = 'App', $all = true)
    {
        $this->gedmo = [
            'enabled'   => true,
            'namespace' => $namespace,
            'all'       => $all
        ];
    }

    /**
     * Enable Gedmo Doctrine Extensions
     *
     * @param array $namespaces
     * @param bool  $all
     */
    public function bootGedmoExtensions($namespaces = ['App'], $all = true)
    {
        if ($all) {
            DoctrineExtensions::registerMappingIntoDriverChainORM(
                $this->chain,
                $this->reader
            );
        } else {
            DoctrineExtensions::registerAbstractMappingIntoDriverChainORM(
                $this->chain,
                $this->reader
            );
        }

        $driver = $this->metadata->getMetadataDriverImpl();
        foreach ($namespaces as $namespace) {
            $this->chain->addDriver($driver, $namespace);
        }
        $this->metadata->setMetadataDriverImpl($this->chain);

        $this->dispatcher->fire('doctrine.driver-chain::booted', [
            $driver,
            $this->chain
        ]);
    }
}
