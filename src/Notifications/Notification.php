<?php

namespace LaravelDoctrine\ORM\Notifications;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
class Notification
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var object
     */
    protected $user;

    /**
     * The "level" of the notification (info, success, error).
     * @ORM\Column(type="string")
     * @var string
     */
    protected $level = 'info';

    /**
     * The message of the notification.
     * @ORM\Column(type="string")
     * @var string
     */
    protected $message;

    /**
     * The text / label for the action.
     * @ORM\Column(type="string")
     * @var string
     */
    protected $actionText;

    /**
     * The action URL.
     * @ORM\Column(type="string")
     * @var string
     */
    protected $actionUrl;

    /**
     * Indicate that the notification gives information about a successful operation.
     *
     * @return $this
     */
    public function success()
    {
        $this->level = 'success';

        return $this;
    }

    /**
     * Indicate that the notification gives information about an error.
     *
     * @return $this
     */
    public function error()
    {
        $this->level = 'error';

        return $this;
    }

    /**
     * Set the "level" of the notification (success, error, etc.).
     *
     * @param  string $level
     * @return $this
     */
    public function level($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * @param  string $message
     * @return $this
     */
    public function message($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Configure the "call to action" button.
     *
     * @param  string $text
     * @param  string $url
     * @return $this
     */
    public function action($text, $url)
    {
        $this->actionText = $text;
        $this->actionUrl  = $url;

        return $this;
    }

    /**
     * @param  mixed        $user
     * @return Notification
     */
    public function to($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getActionText()
    {
        return $this->actionText;
    }

    /**
     * @return string
     */
    public function getActionUrl()
    {
        return $this->actionUrl;
    }
}
