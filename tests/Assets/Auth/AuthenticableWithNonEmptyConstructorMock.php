<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Assets\Auth;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use LaravelDoctrine\ORM\Auth\Authenticatable;

class AuthenticableWithNonEmptyConstructorMock implements AuthenticatableContract
{
    use Authenticatable;

    /** @param string[] $passwords */
    public function __construct(array $passwords)
    {
        $this->password = $passwords[0];
    }
}
