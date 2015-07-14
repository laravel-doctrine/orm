<?php

namespace Brouwers\LaravelDoctrine\Extensions\SoftDeletes;

use Brouwers\LaravelDoctrine\Extensions\Extension;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\EventManager;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\SoftDeleteable\Filter\SoftDeleteableFilter;
use Gedmo\SoftDeleteable\SoftDeleteableListener;

class SoftDeleteableExtension implements Extension
{
    /**
     * @param EventManager           $manager
     * @param EntityManagerInterface $em
     * @param Reader                 $reader
     */
    public function addSubscribers(EventManager $manager, EntityManagerInterface $em, Reader $reader)
    {
        $subscriber = new SoftDeleteableListener();
        $subscriber->setAnnotationReader($reader);

        $manager->addEventSubscriber(
            $subscriber
        );
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            'soft-deleteable' => SoftDeleteableFilter::class
        ];
    }
}
