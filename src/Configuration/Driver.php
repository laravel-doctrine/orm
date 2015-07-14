<?php

namespace LaravelDoctrine\ORM\Configuration;

interface Driver
{
    /**
     * @return mixed
     */
    public function resolve();

    /**
     * @return string
     */
    public function getName();

    /**
     * @param $name
     */
    public function setName($name);
}
