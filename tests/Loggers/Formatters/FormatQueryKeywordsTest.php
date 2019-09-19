<?php

use Doctrine\DBAL\Platforms\AbstractPlatform;
use LaravelDoctrine\ORM\Loggers\Formatters\FormatQueryKeywords;
use LaravelDoctrine\ORM\Loggers\Formatters\QueryFormatter;
use Mockery as m;
use Mockery\Mock;
use PHPUnit\Framework\TestCase;

class FormatQueryKeywordsTest extends TestCase
{
    /**
     * @var Mock
     */
    protected $mock;

    /**
     * @var FormatQueryKeywords
     */
    protected $formatter;

    protected $platform;

    protected function setUp()
    {
        $this->mock = m::mock(QueryFormatter::class);

        $this->platform = m::mock(AbstractPlatform::class);

        $this->formatter = new FormatQueryKeywords($this->mock);
    }

    public function test_formats_select_queries()
    {
        $sql    = "select * from table where condition is not null having count(id) > 10 limit 10 offset 10 group by parent order by position desc";
        $params = [];
        $types  = [];

        $this->decorate($this->platform, $sql, $params, $types);

        $this->assertEquals('SELECT * FROM table WHERE condition IS NOT NULL HAVING COUNT(id) > 10 LIMIT 10 OFFSET 10 GROUP BY parent ORDER BY position DESC', $this->formatter->format($this->platform, $sql, $params, $types));
    }

    public function test_formats_insert_queries()
    {
        $sql    = "insert into table (column1, column2, column3) values (value1, value2, value3)";
        $params = [];
        $types  = [];

        $this->decorate($this->platform, $sql, $params, $types);

        $this->assertEquals('INSERT INTO table (column1, column2, column3) VALUES (value1, value2, value3)', $this->formatter->format($this->platform, $sql, $params, $types));
    }

    public function test_formats_update_queries()
    {
        $sql    = "update table set column1=value, column2=value2 where some_column=some_value";
        $params = [];
        $types  = [];

        $this->decorate($this->platform, $sql, $params, $types);

        $this->assertEquals('UPDATE table SET column1=value, column2=value2 WHERE some_column=some_value', $this->formatter->format($this->platform, $sql, $params, $types));
    }

    public function test_formats_delete_queries()
    {
        $sql    = "update table set column1=value, column2=value2 where some_column=some_value";
        $params = [];
        $types  = [];

        $this->decorate($this->platform, $sql, $params, $types);

        $this->assertEquals('UPDATE table SET column1=value, column2=value2 WHERE some_column=some_value', $this->formatter->format($this->platform, $sql, $params, $types));
    }

    protected function tearDown()
    {
        m::close();
    }

    protected function decorate($platform, $sql, $params, $types)
    {
        $this->mock->shouldReceive('format')->once()->with($platform, $sql, $params, $types)->andReturn($sql);
    }
}
