<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Console;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\EntityManagerProvider as DoctrineEntityManagerProvider;
use Doctrine\Persistence\ManagerRegistry;
use Illuminate\Contracts\Container\Container;

use function assert;

class EntityManagerProvider implements DoctrineEntityManagerProvider
{
    private ManagerRegistry|null $managerRegistry = null;

    public function __construct(private Container $container)
    {
    }

    private function getManagerRegistry(): ManagerRegistry
    {
        if ($this->managerRegistry === null) {
            $this->managerRegistry = $this->container->make(ManagerRegistry::class);
        }

        return $this->managerRegistry;
    }

    public function getDefaultManager(): EntityManagerInterface
    {
        $entityManager = $this->getManagerRegistry()->getManager();

        assert($entityManager instanceof EntityManagerInterface);

        return $entityManager;
    }

    public function getManager(string $name): EntityManagerInterface
    {
        $entityManager = $this->getManagerRegistry()->getManager($name);

        assert($entityManager instanceof EntityManagerInterface);

        return $entityManager;
    }
}
