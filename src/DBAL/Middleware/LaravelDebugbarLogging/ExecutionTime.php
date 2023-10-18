<?php

namespace LaravelDoctrine\ORM\DBAL\Middleware\LaravelDebugbarLogging;

use Illuminate\Database\Connection as DatabaseConnection;
use Illuminate\Database\Events\QueryExecuted;

trait ExecutionTime
{
    /**
     * Measure the execution time of a callable and log it as a query execution event.
     *
     * @param  callable   $callable
     * @param  string     $sql
     * @param  array|null $params
     *
     * @return mixed
     */
    protected function time(callable $callable, string $sql, ?array $params)
    {
        $start = microtime(true);
        $result = $callable();
        $end = microtime(true);

        event(new QueryExecuted($sql, $params ?: [], ($end - $start) * 1000, app(DatabaseConnection::class)));

        return $result;
    }
}
