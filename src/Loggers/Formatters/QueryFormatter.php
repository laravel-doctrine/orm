<?php

namespace LaravelDoctrine\ORM\Loggers\Formatters;

interface QueryFormatter
{
    /**
     * @param string $sql
     * @param array  $params
     *
     * @return string
     */
    public function format($sql, $params);
}
