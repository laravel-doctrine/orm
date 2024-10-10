<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Notifications;

use Illuminate\Notifications\RoutesNotifications;

abstract class Notifiable
{
    use RoutesNotifications;
}
