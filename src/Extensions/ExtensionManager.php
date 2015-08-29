<?php

namespace LaravelDoctrine\ORM\Extensions;

use Doctrine\Common\EventManager;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;

class ExtensionManager
{
    /**
     * @var array|Extension[]
     */
    protected $extensions = [];

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var array
     */
    protected $subscribedExtensions = [];

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Boot the extensions
     */
    public function boot()
    {
        foreach ($this->registry->getManagers() as $connection => $em) {
            foreach ($this->extensions as $extension) {
                $this->bootExtension(
                    $connection,
                    $extension,
                    $em,
                    $em->getEventManager(),
                    $em->getConfiguration()
                );
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
     * @param                        $connection
     * @param Extension              $extension
     * @param EntityManagerInterface $em
     * @param EventManager           $evm
     * @param Configuration          $configuration
     */
    public function bootExtension(
        $connection,
        Extension $extension,
        EntityManagerInterface $em,
        EventManager $evm,
        Configuration $configuration
    ) {
        if ($this->notSubscribedYet($connection, $extension)) {
            $extension->addSubscribers($evm, $em, $configuration->getMetadataDriverImpl());

            if (is_array($extension->getFilters())) {
                foreach ($extension->getFilters() as $name => $filter) {
                    $configuration->addFilter($name, $filter);
                    $em->getFilters()->enable($name);
                }
            }

            $this->markAsSubscribed($connection, $extension);
        }
    }

    /**
     * @param           $connection
     * @param Extension $extension
     *
     * @return bool
     */
    protected function notSubscribedYet($connection, Extension $extension)
    {
        return !isset($this->subscribedExtensions[$connection][get_class($extension)]);
    }

    /**
     * @param           $connection
     * @param Extension $extension
     */
    protected function markAsSubscribed($connection, Extension $extension)
    {
        $this->subscribedExtensions[$connection][get_class($extension)] = true;
    }
}
