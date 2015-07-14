<?php

namespace LaravelDoctrine\ORM\Extensions\TablePrefix;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use LaravelDoctrine\ORM\Extensions\Extension;

class TablePrefixExtension implements Extension
{
    /**
     * @param EventManager           $manager
     * @param EntityManagerInterface $em
     * @param Reader                 $reader
     */
    public function addSubscribers(EventManager $manager, EntityManagerInterface $em, Reader $reader)
    {
        $manager->addEventListener(
            Events::loadClassMetadata,
            new TablePrefixListener(
                $this->getPrefix($em->getConnection())
            )
        );
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return [];
    }

    /**
     * @param Connection $connection
     *
     * @return string
     */
    protected function getPrefix(Connection $connection)
    {
        $params = $connection->getParams();

        return isset($params['prefix']) ? $params['prefix'] : null;
    }
}
