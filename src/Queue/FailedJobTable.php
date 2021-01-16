<?php

namespace LaravelDoctrine\ORM\Queue;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Index;
use LaravelDoctrine\ORM\AbstractTable;

class FailedJobTable extends AbstractTable
{
    /**
     * @return Column[]
     */
    protected function columns()
    {
        return [
            $this->column('id', 'integer', true),
            $this->column('uuid', 'string'),
            $this->column('connection', 'string'),
            $this->column('queue', 'string'),
            $this->column('payload', 'text'),
            $this->column('failed_at', 'datetime'),
            $this->column('exception', 'text')->setNotnull(false),
        ];
    }

    /**
     * @return Index[]
     */
    protected function indices()
    {
        return [
            $this->index('pk', ['id'], true, true),
            $this->index('uuid_unique', ['uuid'], true)
        ];
    }
}
