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

    /**
     * @return array
     */
    public function getWidgets()
    {
        return [
            "doctrine" => [
                "icon"    => "arrow-right",
                "widget"  => "PhpDebugBar.Widgets.SQLQueriesWidget",
                "map"     => "doctrine",
                "default" => "[]"
            ],
            "doctrine:badge" => [
                "map"     => "doctrine.nb_statements",
                "default" => 0
            ]
        ];
    }
}
