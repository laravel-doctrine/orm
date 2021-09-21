<?php

namespace LaravelDoctrine\ORM\Loggers\SQL;

use Doctrine\DBAL\Logging\SQLLogger;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Database\Events\QueryExecuted;

class DoctrineSQLLogger implements SQLLogger
{
    /** @var float|null */
    public $start = null;

    /** @var string|null */
    public $query = null;

    /** @var array|null */
    public $params = null;

    /**
     * @var EntityManagerInterface
     */
    public $em;
    /**
     * @var Configuration
     */
    public $configuration;

    public function __construct(EntityManagerInterface $em, Configuration $configuration)
    {
        $this->em            = $em;
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, ?array $params = null, ?array $types = null)
    {
        $this->start  = microtime(true);
        $this->query  = $sql;
        $this->params = $params;
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery()
    {
        $executionTime = microtime(true) - $this->start;
        // TODO get connection name
        // this class is Laravel connection wrapper (QueryExecuted needs getName())
        $object                    = new class {
            public $connectionName = "";

            public function getName()
            {
                return $this->connectionName;
            }
        };
        $event = new QueryExecuted($this->query, $this->params, $executionTime, $object);
        event($event);
    }
}
