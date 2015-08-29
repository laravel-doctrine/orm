<?php

namespace LaravelDoctrine\ORM\Extensions;

use Doctrine\Common\EventManager;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\ORM\EntityManagerInterface;

interface Extension
{
    /**
     * @param EventManager           $manager
     * @param EntityManagerInterface $em
     * @param MappingDriver          $reader
     */
    public function addSubscribers(EventManager $manager, EntityManagerInterface $em, MappingDriver $reader);

    /**
     * @return array
     */
    public function getFilters();
}
