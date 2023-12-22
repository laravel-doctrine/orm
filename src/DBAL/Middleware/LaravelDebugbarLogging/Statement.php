<?php

namespace LaravelDoctrine\ORM\DBAL\Middleware\LaravelDebugbarLogging;

use Doctrine\DBAL\Driver\Middleware\AbstractStatementMiddleware;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement as StatementInterface;
use Doctrine\DBAL\ParameterType;

class Statement extends AbstractStatementMiddleware
{
    use ExecutionTime;

    public array $params = [];

    /**
     * @param StatementInterface $statement
     * @param string             $sql
     */
    public function __construct(
        StatementInterface $statement,
        private string $sql,
    )
    {
        parent::__construct($statement);
    }

    /**
     * {@inheritDoc}
     */
    public function bindValue($param, $value, $type = ParameterType::STRING)
    {
        $this->params[$param] = $value;

        return parent::bindValue($param, $value, $type);
    }

    /**
     * {@inheritDoc}
     */
    public function execute($params = null): Result
    {
        return $this->time(fn() => parent::execute($params), $this->sql, $this->params);
    }
}
