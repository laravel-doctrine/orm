<?php

namespace LaravelDoctrine\ORM\Loggers\Formatters;

interface QueryFormatter
{
    /**
     * @param string     $sql
     * @param array|null $params
     *
     * @return string
     */
    public function format($sql, array $params = null);
}
