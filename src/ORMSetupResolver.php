<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM;

use Doctrine\ORM\Configuration;

class ORMSetupResolver
{
    public function createConfiguration(
        bool $isDevMode = false,
        string|null $proxyDir = null,
    ): Configuration {
        $config = new Configuration();

        $config->setProxyDir($proxyDir);
        $config->setAutoGenerateProxyClasses($isDevMode);

        return $config;
    }
}
