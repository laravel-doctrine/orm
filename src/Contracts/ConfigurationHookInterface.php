<?php

namespace LaravelDoctrine\ORM\Contracts;

use Doctrine\ORM\Configuration;

interface ConfigurationHookInterface
{
    public function run(Configuration $configuration): void;
}
