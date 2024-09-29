<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Console;

use Doctrine\ORM\Tools\Console\Command\SchemaTool\DropCommand;

class SchemaDropCommand extends DropCommand
{
    public function __construct(EntityManagerProvider $entityManagerProvider)
    {
        parent::__construct($entityManagerProvider);
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setName('doctrine:schema:drop');
    }
}
