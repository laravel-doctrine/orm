<?php

namespace LaravelDoctrine\ORM;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;

/** @interal  */
abstract class AbstractTable
{
    /**
     * @var string
     */
    protected string $table;

    /**
     * @param string $table
     */
    public function __construct(string $table)
    {
        $this->table = $table;
    }

    /**
     * @return Table
     */
    public function build(): Table
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
    protected function column($name, string $type, bool $autoincrement = false): Column
    {
        $column = new Column($name, Type::getType($type));
        $column->setAutoincrement($autoincrement);

        return $column;
    }

    /**
     * @param  string $name
     * @param  string[]  $columns
     * @param  bool   $unique
     * @param  bool   $primary
     * @return Index
     */
    protected function index(string $name, array $columns, bool $unique = false, bool $primary = false): Index
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
