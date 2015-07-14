<?php

namespace Brouwers\LaravelDoctrine\Extensions\Blameable;

use Brouwers\LaravelDoctrine\Extensions\Extension;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\EventManager;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Blameable\BlameableListener;
use Illuminate\Contracts\Auth\Guard;

class BlameableExtension implements Extension
{
    /**
     * @var Guard
     */
    protected $guard;

    /**
     * @param Guard $guard
     */
    public function __construct(Guard $guard)
    {
        $this->guard = $guard;
    }

    /**
     * @param EventManager           $manager
     * @param EntityManagerInterface $em
     * @param Reader                 $reader
     */
    public function addSubscribers(EventManager $manager, EntityManagerInterface $em, Reader $reader)
    {
        $subscriber = new BlameableListener();
        $subscriber->setAnnotationReader($reader);

        if ($this->guard->check()) {
            $subscriber->setUserValue($this->guard->user());
        }

        $manager->addEventSubscriber($subscriber);
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return [];
    }
}
