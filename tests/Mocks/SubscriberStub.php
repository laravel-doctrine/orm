<?php

namespace LaravelDoctrine\Tests\Mocks;

use Doctrine\Common\EventSubscriber;

class SubscriberStub implements EventSubscriber
{
    /**
     * Returns an array of events this subscriber wants to listen to.
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            'onFlush',
        ];
    }
}