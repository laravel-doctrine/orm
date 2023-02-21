<?php

namespace LaravelDoctrine\ORM\Console;

use Doctrine\ORM\Mapping\MappingException;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

class InfoCommand extends \Doctrine\ORM\Tools\Console\Command\InfoCommand
{
    public function __construct(EntityManagerProvider $entityManagerProvider)
    {
        parent::__construct($entityManagerProvider);
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setName('doctrine:info');
    }
}
