<?php

namespace LaravelDoctrine\ORM\Contracts;

interface UrlRoutable
{
    public static function getRouteKeyNameStatic(): string;
}
