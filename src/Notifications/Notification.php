<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Notifications;

use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
class Notification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected int $id;

    protected object $user;

    /**
     * The "level" of the notification (info, success, error).
     */
    #[ORM\Column(type: 'string')]
    protected string $level = 'info';

    /**
     * The message of the notification.
     */
    #[ORM\Column(type: 'string')]
    protected string $message;

    /**
     * The text / label for the action.
     */
    #[ORM\Column(type: 'string')]
    protected string $actionText;

    /**
     * The action URL.
     */
    #[ORM\Column(type: 'string')]
    protected string $actionUrl;

    /**
     * Indicate that the notification gives information about a successful operation.
     */
    public function success(): self
    {
        $this->level = 'success';

        return $this;
    }

    /**
     * Indicate that the notification gives information about an error.
     */
    public function error(): self
    {
        $this->level = 'error';

        return $this;
    }

    /**
     * Set the "level" of the notification (success, error, etc.).
     */
    public function level(string $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function message(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Configure the "call to action" button.
     */
    public function action(string $text, string $url): self
    {
        $this->actionText = $text;
        $this->actionUrl  = $url;

        return $this;
    }

    public function to(mixed $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getId(): mixed
    {
        return $this->id;
    }

    public function getUser(): mixed
    {
        return $this->user;
    }

    public function getLevel(): string
    {
        return $this->level;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getActionText(): string
    {
        return $this->actionText;
    }

    public function getActionUrl(): string
    {
        return $this->actionUrl;
    }
}
