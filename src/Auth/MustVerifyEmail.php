<?php

namespace LaravelDoctrine\ORM\Auth;

use Illuminate\Auth\Notifications\VerifyEmail;

use Doctrine\ORM\Mapping as ORM;
use DateTime;

trait MustVerifyEmail
{
    /**
     * @ORM\Column(name="email_verified_at", type="string", nullable=true)
     */
    #[ORM\Column(name: 'email_verified_at', type: 'string', nullable: true)]
    protected $emailVerifiedAt;

    /**
     * Determine if the user has verified their email address.
     */
    public function hasVerifiedEmail()
    {
        return ! is_null($this->emailVerifiedAt);
    }

    /**
     * Mark the given user's email as verified.
     */
    public function markEmailAsVerified()
    {
        $this->emailVerifiedAt = new DateTime();

        return $this;
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail);
    }

    /**
     * Get the email address that should be used for verification.
     *
     * @return string
     */
    public function getEmailForVerification()
    {
        return $this->email;
    }
}
