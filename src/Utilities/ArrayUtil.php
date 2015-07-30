<?php

namespace LaravelDoctrine\ORM\Utilities;

class ArrayUtil
{
    public static function get(&$var, $default = null)
    {
        return isset($var) ? $var : $default;
    }
}
