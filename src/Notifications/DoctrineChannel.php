<?php

namespace LaravelDoctrine\ORM\Notifications;

use Doctrine\Common\Persistence\ManagerRegistry;
use Illuminate\Notifications\Notification as LaravelNotification;
use LaravelDoctrine\ORM\Exceptions\NoEntityManagerFound;

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
     * @param mixed        $notifiable
     * @param LaravelNotification $notification
     */
    public function send($notifiable, LaravelNotification $notification)
    {
        $entity = $notification->toEntity($notifiable);

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
}
