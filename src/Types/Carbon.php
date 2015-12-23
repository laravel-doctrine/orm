<?php

namespace LaravelDoctrine\ORM\Types;

use Carbon\Carbon as CarbonDateTime;
use DateTime;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\DateTimeType;

/**
 * Class Carbon
 * This add support to get Carbon instance instead of DateTime instance
 * @link http://blog.damirmiladinov.com/using-carbon-php-with-laravel-doctrine.html
 */
class Carbon extends DateTimeType
{
    /**
     * @param mixed            $value
     * @param AbstractPlatform $platform
     *
     * @return CarbonDateTime|mixed
     * @throws \Doctrine\DBAL\Types\ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        $value = parent::convertToPHPValue($value, $platform);

        if ($value instanceof DateTime) {
            return CarbonDateTime::instance($value);
        }

        return $value;
    }
}