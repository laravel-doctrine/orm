<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Extensions;

use Doctrine\Common\EventManager;
use Doctrine\ORM\EntityManagerInterface;

interface Extension
{
    public function addSubscribers(EventManager $manager, EntityManagerInterface $em): void;

    /** @return mixed[] */
    public function getFilters(): array;
}
