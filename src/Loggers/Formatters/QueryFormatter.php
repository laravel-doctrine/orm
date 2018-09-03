<?php

namespace LaravelDoctrine\ORM\Loggers\Formatters;

use Doctrine\DBAL\Platforms\AbstractPlatform;

interface QueryFormatter
{
    /**
     * @param AbstractPlatform $platform
     * @param string           $sql
     * @param array|null       $params
     * @param array|null       $types
     *
     * @return string
     */
    public function format($platform, $sql, array $params = null, array $types = null);
}
