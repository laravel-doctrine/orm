<?php

namespace LaravelDoctrine\ORM;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\ORMSetup;

class ORMSetupResolver
{
    public function createConfiguration(
        bool $isDevMode = false,
        ?string $proxyDir = null,
    ): Configuration
    {
        return ORMSetup::createConfiguration(
           $isDevMode,
           $proxyDir,
        );
    }
}
