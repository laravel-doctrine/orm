<?php

namespace LaravelDoctrineTest\ORM\Assets\Configuration;

use Doctrine\ORM\Configuration;
use LaravelDoctrine\ORM\Contracts\ConfigurationHookInterface;

class ConfigurationHook implements ConfigurationHookInterface
{
    public function run(Configuration $configuration): void
    {
        $configuration->enableNativeLazyObjects(true);
    }
}
