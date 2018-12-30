<?php

namespace LaravelDoctrine\Tests\Mocks;

use Illuminate\Notifications\Notification as IlluminateNotification;
use LaravelDoctrine\ORM\Notifications\Notification as LaravelDoctrineNotification;

class NotificationStub extends IlluminateNotification
{
    public function toEntity()
    {
        return new LaravelDoctrineNotification;
    }
}