<?php

namespace LaravelDoctrine\ORM;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager as BaseEntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query;
use InvalidArgumentException;
use LaravelDoctrine\ORM\Hydrators\ObjectsToCollectionHydrator;
use LaravelDoctrine\ORM\Hydrators\SimpleObjectsToCollectionHydrator;

class EntityManager extends BaseEntityManager
{
    /**
     * Factory method to create EntityManager instances.
     *
     * @param mixed         $conn         An array with the connection parameters or an existing Connection instance.
     * @param Configuration $config       The Configuration instance to use.
     * @param EventManager  $eventManager The EventManager instance to use.
     *
     * @throws InvalidArgumentException
     * @throws ORMException
     * @return EntityManager            The created EntityManager.
     */
    public static function create($conn, Configuration $config, EventManager $eventManager = null)
    {
        if (!$config->getMetadataDriverImpl()) {
            throw ORMException::missingMappingDriverImpl();
        }

        switch (true) {
            case (is_array($conn)):
                $conn = DriverManager::getConnection(
                    $conn, $config, ($eventManager ?: new EventManager())
                );
                break;

            case ($conn instanceof Connection):
                if ($eventManager !== null && $conn->getEventManager() !== $eventManager) {
                    throw ORMException::mismatchedEventManager();
                }
                break;

            default:
                throw new InvalidArgumentException("Invalid argument: " . $conn);
        }

        return new static($conn, $config, $conn->getEventManager());
    }

    /**
     * {@inheritDoc}
     */
    public function newHydrator($hydrationMode)
    {
        switch ($hydrationMode) {
            case Query::HYDRATE_OBJECT:
                return new ObjectsToCollectionHydrator($this);

            case Query::HYDRATE_SIMPLEOBJECT:
                return new SimpleObjectsToCollectionHydrator($this);
        }

        return parent::newHydrator($hydrationMode);
    }
}
