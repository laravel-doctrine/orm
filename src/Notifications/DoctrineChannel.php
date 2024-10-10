<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Notifications;

use Doctrine\Persistence\ManagerRegistry;
use Illuminate\Notifications\Notification as LaravelNotification;
use LaravelDoctrine\ORM\Exceptions\NoEntityManagerFound;
use RuntimeException;

use function method_exists;

class DoctrineChannel
{
    public function __construct(private ManagerRegistry $registry)
    {
    }

    /**
     * Send the given notification.
     */
    public function send(Notifiable $notifiable, LaravelNotification $notification): void
    {
        $entity = $this->getEntity($notifiable, $notification);

        if (method_exists($notifiable, 'routeNotificationForDoctrine')) {
            $em = $this->registry->getManager(
                $notifiable->routeNotificationFor('doctrine', $notification),
            );
        } else {
            $em = $this->registry->getManagerForClass($entity::class);
        }

        if ($em === null) {
            throw new NoEntityManagerFound();
        }

        $em->persist($entity);
        $em->flush();
    }

    public function getEntity(mixed $notifiable, LaravelNotification $notification): object
    {
        if (method_exists($notification, 'toEntity')) {
            return $notification->toEntity($notifiable);
        }

        if (method_exists($notification, 'toDatabase')) {
            return $notification->toDatabase($notifiable);
        }

        throw new RuntimeException(
            'Notification is missing toDatabase / toEntity method.',
        );
    }
}
