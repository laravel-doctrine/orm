<?php

namespace LaravelDoctrine\ORM;

use Doctrine\ORM\Configuration;

class ORMSetupResolver
{
    public function createConfiguration(
        bool $isDevMode = false,
        ?string $proxyDir = null,
    ): Configuration
    {
        $config = new Configuration();

        $config->setProxyDir($proxyDir);
        $config->setAutoGenerateProxyClasses($isDevMode);

        return $config;
    }
}
