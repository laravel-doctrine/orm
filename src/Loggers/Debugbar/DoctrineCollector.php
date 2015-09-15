<?php

namespace LaravelDoctrine\ORM\Loggers\Debugbar;

use DebugBar\Bridge\DoctrineCollector as DebugbarDoctrineCollector;
use Doctrine\DBAL\Logging\DebugStack;

class DoctrineCollector extends DebugbarDoctrineCollector
{
    /**
     * @var DebugStack
     */
    protected $debugStack;

    /**
     * @return DebugStack
     */
    public function getDebugStack()
    {
        return $this->debugStack;
    }
}
