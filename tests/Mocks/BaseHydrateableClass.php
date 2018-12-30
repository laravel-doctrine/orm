<?php

namespace LaravelDoctrine\Tests\Mocks;

class BaseHydrateableClass
{
    private $name;

    public function getName()
    {
        return $this->name;
    }
}