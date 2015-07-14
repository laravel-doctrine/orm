<?php

namespace LaravelDoctrine\ORM\ConfigMigrations;

interface ConfigurationMigrator
{
    function convertConfiguration($sourceArray);
}