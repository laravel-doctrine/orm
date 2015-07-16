<?php

namespace LaravelDoctrine\ORM\ConfigMigrations;

interface ConfigurationMigrator
{
    public function convertConfiguration($sourceArray);
}
