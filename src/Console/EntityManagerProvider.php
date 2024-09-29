<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Console;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

use function assert;

class EntityManagerProvider implements \Doctrine\ORM\Tools\Console\EntityManagerProvider
{
    public function __construct(private ManagerRegistry $managerRegistry)
    {
    }

    public function getDefaultManager(): EntityManagerInterface
    {
        $entityManager = $this->managerRegistry->getManager();

        assert($entityManager instanceof EntityManagerInterface);

        return $entityManager;
    }

    public function getManager(string $name): EntityManagerInterface
    {
        $entityManager = $this->managerRegistry->getManager($name);

        assert($entityManager instanceof EntityManagerInterface);

        return $entityManager;
    }
}
