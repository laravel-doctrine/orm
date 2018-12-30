<?php

namespace LaravelDoctrine\Tests\Mocks;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use LaravelDoctrine\ORM\Auth\Authenticatable;

class AuthenticatableWithNonEmptyConstructorMock implements AuthenticatableContract
{
    use Authenticatable;

    public function __construct(array $passwords)
    {
        $this->password = $passwords[0];
    }
}