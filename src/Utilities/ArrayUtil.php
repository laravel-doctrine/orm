<?php
/**
 * Created by IntelliJ IDEA.
 * User: mduncan
 * Date: 7/14/15
 * Time: 3:47 PM
 */

namespace LaravelDoctrine\ORM\Utilities;


class ArrayUtil
{
    public static function get(&$var, $default = null) {
        return isset($var) ? $var : $default;
    }
}