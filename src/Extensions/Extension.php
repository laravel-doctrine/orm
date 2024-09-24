<?php

namespace LaravelDoctrine\ORM\Extensions;

use Doctrine\Common\EventManager;
use Doctrine\ORM\EntityManagerInterface;

interface Extension
{
    /**
     * @param EventManager           $manager
     * @param EntityManagerInterface $em
     */
    public function addSubscribers(EventManager $manager, EntityManagerInterface $em);

    /**
     * @return array
     */
    public function getFilters();
}
