<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Console;

/** @deprecated */
class EnsureProductionSettingsCommand extends \Doctrine\ORM\Tools\Console\Command\EnsureProductionSettingsCommand
{
    public function __construct(EntityManagerProvider $entityManagerProvider)
    {
        parent::__construct($entityManagerProvider);
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setName('doctrine:ensure:production');
    }
}
