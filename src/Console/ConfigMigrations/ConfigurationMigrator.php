<?php

namespace LaravelDoctrine\ORM\Console\ConfigMigrations;

use Illuminate\Contracts\View\Factory;

interface ConfigurationMigrator
{
    /**
     * @param Factory $viewFactory Laravel View Factory used to render blade templates
     */
    public function __construct(Factory $viewFactory);

    /**
     * Convert a configuration array from another laravel-doctrine project in to a string representation of a php array configuration for this project
     *
     * @param  array  $sourceArray
     * @return string
     */
    public function convertConfiguration($sourceArray);
}
