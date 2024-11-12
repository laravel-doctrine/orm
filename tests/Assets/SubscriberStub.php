<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Assets;

use Doctrine\Common\EventSubscriber;

class SubscriberStub implements EventSubscriber
{
    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return string[]
     */
    public function getSubscribedEvents(): array
    {
        return ['onFlush'];
    }
}
