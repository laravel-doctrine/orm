<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Console;

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
