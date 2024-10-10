<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;

// phpcs:disable SlevomatCodingStandard.Classes.SuperfluousAbstractClassNaming.SuperfluousPrefix
abstract class AbstractTable
{
    public function __construct(protected string $table)
    {
    }

    public function build(): Table
    {
        return new Table(
            $this->table,
            $this->columns(),
            $this->indices(),
        );
    }

    protected function column(string $name, string $type, bool $autoincrement = false): Column
    {
        $column = new Column($name, Type::getType($type));
        $column->setAutoincrement($autoincrement);

        return $column;
    }

    /** @param  string[] $columns */
    protected function index(string $name, array $columns, bool $unique = false, bool $primary = false): Index
    {
        return new Index($name, $columns, $unique, $primary);
    }

    /** @return Column[] */
    abstract protected function columns(): array;

    /** @return Index[] */
    abstract protected function indices(): array;
}
