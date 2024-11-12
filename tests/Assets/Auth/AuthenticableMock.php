<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Assets\Auth;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use LaravelDoctrine\ORM\Auth\Authenticatable;

class AuthenticableMock implements AuthenticatableContract
{
    use Authenticatable;

    public function __construct()
    {
        $this->password = 'myPassword';
    }
}
