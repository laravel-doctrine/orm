<?php

namespace LaravelDoctrine\ORM\Loggers;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;

interface Logger
{
    /**
     * @param EntityManagerInterface $em
     * @param Configuration          $configuration
     */
    public function register(EntityManagerInterface $em, Configuration $configuration);
}
