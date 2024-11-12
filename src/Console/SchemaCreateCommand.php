<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Console;

use Doctrine\ORM\Tools\Console\Command\SchemaTool\CreateCommand;

class SchemaCreateCommand extends CreateCommand
{
    public function __construct(EntityManagerProvider $entityManagerProvider)
    {
        parent::__construct($entityManagerProvider);
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setName('doctrine:schema:create');
    }
}
