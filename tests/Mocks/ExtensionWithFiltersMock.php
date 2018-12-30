<?php

namespace LaravelDoctrine\Tests\Mocks;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\EventManager;
use Doctrine\ORM\EntityManagerInterface;
use LaravelDoctrine\ORM\Extensions\Extension;

class ExtensionWithFiltersMock implements Extension
{
    /**
     * @param EventManager $manager
     * @param EntityManagerInterface $em
     * @param Reader|null $reader
     */
    public function addSubscribers(EventManager $manager, EntityManagerInterface $em, Reader $reader = null)
    {
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            'filter' => 'FilterMock',
            'filter2' => 'FilterMock',
        ];
    }
}