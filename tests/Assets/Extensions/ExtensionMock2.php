<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Assets\Extensions;

use Doctrine\Common\EventManager;
use Doctrine\ORM\EntityManagerInterface;
use LaravelDoctrine\ORM\Extensions\Extension;

class ExtensionMock2 implements Extension
{
    public function addSubscribers(EventManager $manager, EntityManagerInterface $em): void
    {
    }

    /** @return mixed[] */
    public function getFilters(): array
    {
        return [];
    }
}
