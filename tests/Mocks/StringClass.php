<?php

namespace LaravelDoctrine\Tests\Mocks;

class StringClass
{
    public function __toString()
    {
        return 'string';
    }
}