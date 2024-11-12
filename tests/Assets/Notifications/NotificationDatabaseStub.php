<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Assets\Notifications;

use Illuminate\Notifications\Notification as IlluminateNotification;
use LaravelDoctrine\ORM\Notifications\Notification;

class NotificationDatabaseStub extends IlluminateNotification
{
    public function toDatabase(): Notification
    {
        return new Notification();
    }
}
