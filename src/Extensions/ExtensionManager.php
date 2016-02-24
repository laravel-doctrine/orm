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
     * @var array
     */
    protected $bootedExtensions = [];

    /**
     * Boot the extensions
     * @param ManagerRegistry $registry
     */
    public function boot(ManagerRegistry $registry)
    {
        foreach ($registry->getManagers() as $connection => $em) {
            foreach ($this->extensions as $extension) {
                if ($this->notBootedYet($connection, $extension)) {
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
    }

    /**
     * @return bool
     */
    public function needsBooting()
    {
        return count($this->extensions) > 0;
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
    protected function bootExtension(
        $connection,
        Extension $extension,
        EntityManagerInterface $em,
        EventManager $evm,
        Configuration $configuration
    ) {
        $extension->addSubscribers(
            $evm,
            $em,
            $configuration->getMetadataDriverImpl()->getReader()
        );

        if (is_array($extension->getFilters())) {
            foreach ($extension->getFilters() as $name => $filter) {
                $configuration->addFilter($name, $filter);
                $em->getFilters()->enable($name);
            }
        }

        $this->markAsBooted($connection, $extension);
    }

    /**
     * @param           $connection
     * @param Extension $extension
     *
     * @return bool
     */
    protected function notBootedYet($connection, Extension $extension)
    {
        return !isset($this->bootedExtensions[$connection][get_class($extension)]);
    }

    /**
     * @param           $connection
     * @param Extension $extension
     */
    protected function markAsBooted($connection, Extension $extension)
    {
        $this->bootedExtensions[$connection][get_class($extension)] = true;
    }

    /**
     * @return array|Extension[]
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * @return array
     */
    public function getBootedExtensions()
    {
        return $this->bootedExtensions;
    }
}
