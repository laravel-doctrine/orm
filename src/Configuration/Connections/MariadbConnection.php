<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Configuration\Connections;

class MariadbConnection extends MysqlConnection
{
    // Only extends Mysql, as MariaDB is compatible with MySQL connection settings.
}
