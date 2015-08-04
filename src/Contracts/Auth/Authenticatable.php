<?php

namespace LaravelDoctrine\ORM\Contracts\Auth;

use Illuminate\Contracts\Auth\Authenticatable as LaravelAuthenticatable;

interface Authenticatable extends LaravelAuthenticatable {

    /**
     * Get the column name for the primary key
     * @return mixed
     */
    public function getAuthIdentifierName();
}
