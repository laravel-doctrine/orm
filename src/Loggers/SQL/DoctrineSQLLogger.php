<?php

namespace LaravelDoctrine\ORM\Loggers\SQL;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Logging\SQLLogger;
use Exception;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Illuminate\Database\Connection as IlluminateConnection;
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
     * @var Connection
     */
    public $connection;
    /**
     * @var EventDispatcher
     */
    public $dispatcher;

    public function __construct(Connection $connection, EventDispatcher $dispatcher)
    {
        $this->connection = $connection;
        $this->dispatcher = $dispatcher;
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
        $wrappedConnection = $this->connection->getWrappedConnection();
        if (!$wrappedConnection instanceof PDO) {
            throw new Exception("Only PDO is supported");
        }
        $connection = new IlluminateConnection($wrappedConnection);
        $event      = new QueryExecuted($this->query, $this->params, $executionTime, $connection);

        $this->dispatcher->dispatch($event);
    }
}
