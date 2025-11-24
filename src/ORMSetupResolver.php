<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM;

use Doctrine\ORM\Configuration;
use LaravelDoctrine\ORM\Contracts\ConfigurationHookInterface;

class ORMSetupResolver
{
    /**
     * @param string|class-string<\LaravelDoctrine\ORM\Contracts\ConfigurationHookInterface>|null $configurationHook
     */
    public function createConfiguration(
        bool $isDevMode = false,
        string|null $proxyDir = null,
        string|null $configurationHook = null,
    ): Configuration {
        $config = new Configuration();

        $config->setProxyDir($proxyDir);
        $config->setAutoGenerateProxyClasses($isDevMode);

        if ($configurationHook) {
            if(! class_exists($configurationHook)) {
                throw new \InvalidArgumentException('ConfigurationHook ' . $configurationHook . ' does not exist');
            }

            if (! is_subclass_of($configurationHook, ConfigurationHookInterface::class, true)) {
                throw new \InvalidArgumentException('ConfigurationHook ' . $configurationHook . ' must implement ' . ConfigurationHookInterface::class);
            }

            $hook = new $configurationHook();
            $hook->run($config);
        }

        return $config;
    }
}
