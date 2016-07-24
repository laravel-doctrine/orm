<?php

namespace LaravelDoctrine\ORM\Auth\Passwords;

use LaravelDoctrine\ORM\AbstractTable;

class PasswordResetTable extends AbstractTable
{
    /**
     * @return array
     */
    public function columns()
    {
        return [
            $this->column('email', 'string'),
            $this->column('token', 'string'),
            $this->column('created_at', 'datetime')
        ];
    }

    /**
     * @return array
     */
    public function indices()
    {
        return [
            $this->index('pk', ['email', 'token'], true, true)
        ];
    }
}
