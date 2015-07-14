<?php

namespace Brouwers\LaravelDoctrine\Extensions\Translatable;

use Brouwers\LaravelDoctrine\Extensions\Extension;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\EventManager;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Translatable\TranslatableListener;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;

class TranslatableExtension implements Extension
{
    /**
     * @var Application
     */
    protected $application;

    /**
     * @var Repository
     */
    protected $repository;

    /**
     * @param Application $application
     * @param Repository  $repository
     */
    public function __construct(Application $application, Repository $repository)
    {
        $this->application = $application;
        $this->repository  = $repository;
    }

    /**
     * @param EventManager           $manager
     * @param EntityManagerInterface $em
     * @param Reader                 $reader
     */
    public function addSubscribers(EventManager $manager, EntityManagerInterface $em, Reader $reader)
    {
        $subscriber = new TranslatableListener;
        $subscriber->setTranslatableLocale($this->application->getLocale());
        $subscriber->setDefaultLocale($this->repository->get('app.locale'));
        $subscriber->setAnnotationReader($reader);
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
