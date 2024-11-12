<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Assets\Notifications;

use LaravelDoctrine\ORM\Notifications\Notifiable;

class CustomNotifiableStub
{
    use Notifiable;

    public function routeNotificationForDoctrine(): string
    {
        return 'custom';
    }
}
