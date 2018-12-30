<?php

namespace LaravelDoctrine\Tests\Mocks;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\EventManager;
use Doctrine\ORM\EntityManagerInterface;
use LaravelDoctrine\ORM\Extensions\Extension;
use LaravelDoctrine\Tests\Extensions\ExtensionManagerTest;

class ExtensionMock implements Extension
{
    /**
     * @param EventManager $manager
     * @param EntityManagerInterface $em
     * @param Reader|null $reader
     */
    public function addSubscribers(EventManager $manager, EntityManagerInterface $em, Reader $reader = null)
    {
        // Confirm it get's called
        ExtensionManagerTest::assertTrue(true);
    }

    public function getFilters()
    {
    }
}