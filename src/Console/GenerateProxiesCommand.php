<?php

namespace LaravelDoctrine\ORM\Console;

use Doctrine\ORM\Tools\Console\MetadataFilter;
use Doctrine\Persistence\ManagerRegistry;
use InvalidArgumentException;

class GenerateProxiesCommand extends \Doctrine\ORM\Tools\Console\Command\GenerateProxiesCommand
{
    public function __construct(EntityManagerProvider $entityManagerProvider)
    {
        parent::__construct($entityManagerProvider);
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setName('doctrine:generate:proxies');
    }
}
