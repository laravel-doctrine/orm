<?php

namespace LaravelDoctrine\Tests\Mocks;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use LaravelDoctrine\ORM\Auth\Authenticatable;

class AuthenticatableMock implements AuthenticatableContract
{
    use Authenticatable;

    public function __construct()
    {
        $this->password = 'myPassword';
    }
}