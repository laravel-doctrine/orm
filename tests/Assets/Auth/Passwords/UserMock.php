<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Assets\Auth\Passwords;

use Illuminate\Contracts\Auth\CanResetPassword;

class UserMock implements CanResetPassword
{
    /**
     * Get the e-mail address where password reset links are sent.
     */
    public function getEmailForPasswordReset(): string
    {
        return 'user@mockery.mock';
    }

    /**
     * Send the password reset notification.
     */
    // phpcs:disable
    public function sendPasswordResetNotification($token): void
    {
    }
    // phpcs:enable
}
