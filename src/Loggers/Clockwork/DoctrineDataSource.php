<?php

namespace LaravelDoctrine\ORM\Loggers\Clockwork;

use Clockwork\DataSource\DataSource;
use Clockwork\Request\Request;
use Doctrine\DBAL\Logging\SQLLogger;
use LaravelDoctrine\ORM\Loggers\Formatters\FormatQueryKeywords;
use LaravelDoctrine\ORM\Loggers\Formatters\QueryFormatter;
use LaravelDoctrine\ORM\Loggers\Formatters\ReplaceQueryParams;

class DoctrineDataSource extends DataSource
{
    /**
     * @var SQLLogger
     */
    protected $logger;

    /**
     * @var
     */
    protected $connection;

    /**
     * @var QueryFormatter
     */
    protected $formatter;

    /**
     * @param SQLLogger $logger
     * @param           $connection
     */
    public function __construct(SQLLogger $logger, $connection)
    {
        $this->logger     = $logger;
        $this->connection = $connection;
        $this->formatter  = new FormatQueryKeywords(new ReplaceQueryParams);
    }

    /**
     * Adds ran database queries to the request
     *
     * @param Request $request
     *
     * @return Request
     */
    public function resolve(Request $request)
    {
        $request->databaseQueries = array_merge($request->databaseQueries, $this->getDatabaseQueries());

        return $request;
    }

    /**
     * Returns an array of runnable queries and their durations from the internal array
     */
    protected function getDatabaseQueries()
    {
        $queries = [];
        foreach ($this->logger->queries as $query) {
            $queries[] = [
                'query'      => $this->formatter->format($query['sql'], $query['params']),
                'duration'   => $query['executionMS'] * 1000,
                'connection' => $this->connection
            ];
        }

        return $queries;
    }
}
