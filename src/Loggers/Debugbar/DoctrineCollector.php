<?php

namespace LaravelDoctrine\ORM\Loggers\Debugbar;

use DebugBar\Bridge\DoctrineCollector as DebugbarDoctrineCollector;
use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\ORM\EntityManager;

class DoctrineCollector extends DebugbarDoctrineCollector
{
    /**
     * @var DebugStack
     */
    protected $debugStack;

    /**
     * @var string
     */
    protected $name;


    /**
     * DoctrineCollector constructor.
     * @param DebugStack|EntityManager $debugStackOrEntityManager
     * @param string                   $name
     */
    public function __construct($debugStackOrEntityManager, $name = 'queries')
    {
        parent::__construct($debugStackOrEntityManager);
        $this->name = $name;
    }

    /**
     * @return DebugStack
     */
    public function getDebugStack()
    {
        return $this->debugStack;
    }

    /**
     * @return array
     */
    public function getWidgets()
    {
        return [
            $this->name => [
                "icon"    => "arrow-right",
                "widget"  => "PhpDebugBar.Widgets.SQLQueriesWidget",
                "map"     => "doctrine",
                "default" => "[]"
            ],
            sprintf('%s:badge', $this->name) => [
                "map"     => "doctrine.nb_statements",
                "default" => 0
            ]
        ];
    }
}
