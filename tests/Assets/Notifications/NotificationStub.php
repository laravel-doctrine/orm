<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Assets\Notifications;

use Illuminate\Notifications\Notification as IlluminateNotification;
use LaravelDoctrine\ORM\Notifications\Notification;

class NotificationStub extends IlluminateNotification
{
    public function toEntity(): Notification
    {
        return new Notification();
    }
}
