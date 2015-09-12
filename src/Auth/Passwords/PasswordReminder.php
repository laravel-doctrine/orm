<?php

namespace LaravelDoctrine\ORM\Auth\Passwords;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * @ORM\Entity
 * @ORM\Table(name="password_resets")
 */
class PasswordReminder
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    protected $email;

    /**
     * @ORM\Column(type="string")
     */
    protected $token;

    /**
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     * @var DateTime
     */
    protected $createdAt;

    /**
     * @param string $email
     * @param string $token
     */
    public function __construct($email, $token)
    {
        $this->email     = $email;
        $this->token     = $token;
        $this->createdAt = new DateTime;
    }

    /**
     * Returns when the reminder was created.
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Metadata definition for static_php metadata driver.
     * @param  ClassMetadata $metadata
     * @return void
     */
    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setPrimaryTable([
            'name' => 'password_resets'
        ]);

        $metadata->mapField([
            'id'        => true,
            'fieldName' => 'email',
            'type'      => 'string',
        ]);
        $metadata->mapField([
            'fieldName' => 'token',
            'type'      => 'string',
        ]);
        $metadata->mapField([
            'columnName' => 'created_at',
            'fieldName'  => 'createdAt',
            'type'       => 'datetime',
            'nullable'   => false,
        ]);
    }
}
