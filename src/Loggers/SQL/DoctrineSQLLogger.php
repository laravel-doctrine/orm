<?php

namespace LaravelDoctrine\ORM\Loggers\SQL;

use Doctrine\DBAL\Logging\SQLLogger;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Illuminate\Database\Connection;
use Illuminate\Database\Events\QueryExecuted;
use PDO;

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
        $executionTime     = microtime(true) - $this->start;
        $wreppedConnection = $this->em->getConnection()->getWrappedConnection();
        if (!$wreppedConnection instanceof PDO) {
            throw new Exception("Only PDO is supported");
        }
        $connection = new Connection($wreppedConnection);
        $event      = new QueryExecuted($this->query, $this->params, $executionTime, $connection);

        event($event);
    }
}
