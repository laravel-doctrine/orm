<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Contracts;

interface UrlRoutable
{
    public static function getRouteKeyNameStatic(): string;
}
