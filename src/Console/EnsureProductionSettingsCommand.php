<?php

namespace LaravelDoctrine\ORM\Console;

use Doctrine\Persistence\ManagerRegistry;
use Exception;

/**
 * @deprecated
 */
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
