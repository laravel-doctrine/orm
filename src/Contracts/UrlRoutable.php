<?php

namespace LaravelDoctrine\ORM\Contracts;

interface UrlRoutable
{
    public static function getRouteKeyName(): string;
}
