<?php

namespace LaravelDoctrine\ORM\Utilities;

class ArrayUtil
{
    public static function get(&$var, $default = null)
    {
        return isset($var) ? $var : $default;
    }

    /**
     * @param array $array
     *
     * @return string
     */
    public static function hashArray(array $array)
    {
        return md5(json_encode($array));
    }
}
