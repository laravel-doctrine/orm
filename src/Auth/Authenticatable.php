<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Auth;

use Doctrine\ORM\Mapping as ORM;

trait Authenticatable
{
    #[ORM\Column(type: 'string')]
    protected string $password;

    #[ORM\Column(name: 'remember_token', type: 'string', nullable: true)]
    protected string|null $rememberToken;

    /**
     * Get the column name for the primary key
     */
    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    /**
     * Get the unique identifier for the user.
     *
     * @codeCoverageIgnoreStart
     */
    public function getAuthIdentifier(): mixed
    {
        $name = $this->getAuthIdentifierName();

        return $this->{$name};
    }

    /** @codeCoverageIgnoreEnd */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * Get the password for the user.
     */
    public function getAuthPassword(): string
    {
        return $this->getPassword();
    }

    /**
     * Get the token value for the "remember me" session.
     */
    public function getRememberToken(): string|null
    {
        return $this->rememberToken;
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * phpcs:disable
     */
    public function setRememberToken($value): void
    {
        // phpcs:enable
        $this->rememberToken = $value;
    }

    /**
     * Get the column name for the "remember me" token.
     */
    public function getRememberTokenName(): string
    {
        return 'rememberToken';
    }

    /** @codeCoverageIgnoreStart */
    public function getAuthPasswordName(): string
    {
        return 'password';
    }

    // @codeCoverageIgnoreEnd
}
