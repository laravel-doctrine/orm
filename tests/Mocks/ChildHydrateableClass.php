<?php

namespace LaravelDoctrine\Tests\Mocks;

class ChildHydrateableClass extends BaseHydrateableClass
{
    private $description;

    public function getDescription()
    {
        return $this->description;
    }
}