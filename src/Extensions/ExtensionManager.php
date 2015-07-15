<?php

namespace LaravelDoctrine\ORM\Extensions;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\DoctrineExtensions;

class ExtensionManager
{
    /**
     * @var array|Extension[]
     */
    protected $extensions = [];

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
     * @var DriverChain
     */
    private $driverChain;

    /**
     * @param ManagerRegistry $registry
     * @param DriverChain     $driverChain
     */
    public function __construct(ManagerRegistry $registry, DriverChain $driverChain)
    {
        $this->registry    = $registry;
        $this->driverChain = $driverChain;
    }

    /**
     * Boot the extensions
     */
    public function boot()
    {
        foreach ($this->registry->getManagers() as $em) {
            $this->em       = $em;
            $this->evm      = $this->em->getEventManager();
            $this->metadata = $this->em->getConfiguration();
            $this->reader   = $this->driverChain->getReader();

            foreach ($this->extensions as $extension) {
                $this->bootExtension($extension);
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
     * @param bool $all
     */
    public function enableGedmoExtensions($all = true)
    {
        if ($all) {
            DoctrineExtensions::registerMappingIntoDriverChainORM(
                $this->driverChain->getChain(),
                $this->driverChain->getReader()
            );
        } else {
            DoctrineExtensions::registerAbstractMappingIntoDriverChainORM(
                $this->driverChain->getChain(),
                $this->driverChain->getReader()
            );
        }
    }
}
