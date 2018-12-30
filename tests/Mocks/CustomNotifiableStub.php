<?php

namespace LaravelDoctrine\Tests\Mocks;

use LaravelDoctrine\ORM\Notifications\Notifiable;

class CustomNotifiableStub
{
    use Notifiable;

    public function routeNotificationForDoctrine()
    {
        return 'custom';
    }
}