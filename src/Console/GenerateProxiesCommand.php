<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Console;

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
