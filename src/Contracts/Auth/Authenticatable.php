<?php

namespace LaravelDoctrine\ORM\Contracts\Auth;

use Illuminate\Contracts\Auth\Authenticatable as LaravelAuthenticatable;

interface Authenticatable extends LaravelAuthenticatable {

    /**
     * Get the unique identifier field name for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifierName();
}
