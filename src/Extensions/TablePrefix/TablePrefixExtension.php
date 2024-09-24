<?php

namespace LaravelDoctrine\ORM\Extensions\TablePrefix;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Support\Arr;
use LaravelDoctrine\ORM\Extensions\Extension;

class TablePrefixExtension implements Extension
{
    /**
     * @param EventManager           $manager
     * @param EntityManagerInterface $em
     */
    public function addSubscribers(EventManager $manager, EntityManagerInterface $em)
    {
        $manager->addEventSubscriber(
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

        return Arr::get($params, 'prefix');
    }
}
