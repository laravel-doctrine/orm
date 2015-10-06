<?php

namespace LaravelDoctrine\ORM\Loggers\File;

use Doctrine\DBAL\Logging\SQLLogger;
use LaravelDoctrine\ORM\Loggers\Formatters\FormatQueryKeywords;
use LaravelDoctrine\ORM\Loggers\Formatters\ReplaceQueryParams;
use Psr\Log\LoggerInterface as Log;

class DoctrineFileLogger implements SQLLogger
{
    /**
     * @var Log
     */
    protected $logger;

    /**
     * @var FormatQueryKeywords
     */
    protected $formatter;

    /**
     * @var float
     */
    protected $start;

    /**
     * @var string
     */
    protected $query;

    /**
     * @param Log $logger
     */
    public function __construct(Log $logger)
    {
        $this->logger    = $logger;
        $this->formatter = new FormatQueryKeywords(new ReplaceQueryParams);
    }

    /**
     * Logs a SQL statement somewhere.
     *
     * @param string     $sql    The SQL to be executed.
     * @param array|null $params The SQL parameters.
     * @param array|null $types  The SQL parameter types.
     *
     * @return void
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        $this->start = microtime(true);
        $this->query = $this->formatter->format($sql, $params);
    }

    /**
     * Marks the last started query as stopped. This can be used for timing of queries.
     * @return void
     */
    public function stopQuery()
    {
        $this->logger->debug($this->getQuery(), [$this->getExecutionTime()]);
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return mixed
     */
    protected function getExecutionTime()
    {
        return microtime(true) - $this->start;
    }
}
