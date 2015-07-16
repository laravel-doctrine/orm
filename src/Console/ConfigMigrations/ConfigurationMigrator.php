<?php

namespace LaravelDoctrine\ORM\ConfigMigrations;

interface ConfigurationMigrator
{
    /**
     * Convert a configuration array from another laravel-doctrine project in to a string representation of a php array configuration for this project
     *
     * @param array $sourceArray
     * @return string
     */
    public function convertConfiguration($sourceArray);
}
