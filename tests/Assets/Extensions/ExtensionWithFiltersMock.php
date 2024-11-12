<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Assets\Extensions;

use Doctrine\Common\EventManager;
use Doctrine\ORM\EntityManagerInterface;
use LaravelDoctrine\ORM\Extensions\Extension;

class ExtensionWithFiltersMock implements Extension
{
    public function addSubscribers(EventManager $manager, EntityManagerInterface $em): void
    {
    }

    /** @return mixed[] */
    public function getFilters(): array
    {
        return [
            'filter'  => 'FilterMock',
            'filter2' => 'FilterMock',
        ];
    }
}
