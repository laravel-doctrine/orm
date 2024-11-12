<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Configuration\MetaData;

use LaravelDoctrine\ORM\Configuration\Manager;

class MetaDataManager extends Manager
{
    /**
     * Get the default driver name.
     */
    public function getDefaultDriver(): string
    {
        return 'attributes';
    }

    public function getNamespace(): string
    {
        return __NAMESPACE__;
    }

    public function getClassSuffix(): string
    {
        return '';
    }
}
