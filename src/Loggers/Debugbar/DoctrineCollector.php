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
    protected $widgetName;


    /**
     * @param DebugStack|EntityManager $debugStackOrEntityManager
     * @param string                   $widgetName
     */
    public function __construct($debugStackOrEntityManager, $widgetName = 'queries')
    {
        parent::__construct($debugStackOrEntityManager);
        $this->widgetName = $widgetName;
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
            $this->widgetName => [
                "icon"    => "arrow-right",
                "widget"  => "PhpDebugBar.Widgets.SQLQueriesWidget",
                "map"     => "doctrine",
                "default" => "[]"
            ],
            sprintf('%s:badge', $this->widgetName) => [
                "map"     => "doctrine.nb_statements",
                "default" => 0
            ]
        ];
    }
}
