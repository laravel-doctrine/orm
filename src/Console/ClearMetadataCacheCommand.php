<?php

namespace LaravelDoctrine\ORM\Console;

use Doctrine\ORM\Tools\Console\Command\ClearCache\MetadataCommand;

class ClearMetadataCacheCommand extends MetadataCommand
{
    public function __construct(EntityManagerProvider $entityManagerProvider)
    {
        parent::__construct($entityManagerProvider);
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setName('doctrine:clear:metadata:cache');
    }
}
