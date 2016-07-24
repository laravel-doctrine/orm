<?php

namespace LaravelDoctrine\ORM;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;

abstract class AbstractTable
{
    /**
     * @var string
     */
    protected $table;

    /**
     * @param string $table
     */
    public function __construct($table)
    {
        $this->table = $table;
    }

    /**
     * @return Table
     */
    public function build()
    {
        return new Table(
            $this->table,
            $this->columns(),
            $this->indices()
        );
    }

    /**
     * @param  string $name
     * @param  string $type
     * @param  bool   $autoincrement
     * @return Column
     */
    protected function column($name, $type, $autoincrement = false)
    {
        $column = new Column($name, Type::getType($type));
        $column->setAutoincrement($autoincrement);

        return $column;
    }

    /**
     * @param  string $name
     * @param  array  $columns
     * @param  bool   $unique
     * @param  bool   $primary
     * @return Index
     */
    protected function index($name, array $columns, $unique = false, $primary = false)
    {
        return new Index($name, $columns, $unique, $primary);
    }

    /**
     * @return Column[]
     */
    abstract protected function columns();

    /**
     * @return Index[]
     */
    abstract protected function indices();
}
