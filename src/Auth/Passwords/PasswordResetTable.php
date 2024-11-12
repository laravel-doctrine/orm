<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Auth\Passwords;

use LaravelDoctrine\ORM\AbstractTable;

class PasswordResetTable extends AbstractTable
{
    /** @return mixed[] */
    public function columns(): array
    {
        return [
            $this->column('email', 'string'),
            $this->column('token', 'string'),
            $this->column('created_at', 'datetime'),
        ];
    }

    /** @return mixed[] */
    public function indices(): array
    {
        return [$this->index('pk', ['email', 'token'], true, true)];
    }
}
