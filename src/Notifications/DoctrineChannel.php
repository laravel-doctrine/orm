<?php

namespace LaravelDoctrine\ORM\Notifications;

use Doctrine\Common\Persistence\ManagerRegistry;
use Illuminate\Notifications\Notification as LaravelNotification;
use LaravelDoctrine\ORM\Exceptions\NoEntityManagerFound;
use RuntimeException;

class DoctrineChannel
{
    /**
     * @var ManagerRegistry
     */
    private $registry;

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Send the given notification.
     *
     * @param mixed               $notifiable
     * @param LaravelNotification $notification
     */
    public function send($notifiable, LaravelNotification $notification)
    {
        $entity = $this->getEntity($notifiable, $notification);

        if ($channel = $notifiable->routeNotificationFor('doctrine')) {
            $em = $this->registry->getManager($channel);
        } else {
            $em = $this->registry->getManagerForClass(get_class($entity));
        }

        if (is_null($em)) {
            throw new NoEntityManagerFound;
        }

        $em->persist($entity);
        $em->flush();
    }

    /**
     * @param  mixed               $notifiable
     * @param  LaravelNotification $notification
     * @return object
     */
    public function getEntity($notifiable, LaravelNotification $notification)
    {
        if (method_exists($notification, 'toEntity')) {
            return $notification->toEntity($notifiable);
        } elseif (method_exists($notification, 'toDatabase')) {
            return $notification->toDatabase($notifiable);
        }

        throw new RuntimeException(
            'Notification is missing toDatabase / toEntity method.'
        );
    }
}
