<?php

use LaravelDoctrine\ORM\Loggers\Formatters\FormatQueryKeywords;
use LaravelDoctrine\ORM\Loggers\Formatters\QueryFormatter;
use Mockery as m;
use Mockery\Mock;

class FormatQueryKeywordsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mock
     */
    protected $mock;

    /**
     * @var FormatQueryKeywords
     */
    protected $formatter;

    protected function setUp()
    {
        $this->mock = m::mock(QueryFormatter::class);

        $this->formatter = new FormatQueryKeywords($this->mock);
    }

    public function test_formats_select_queries()
    {
        $sql    = "select * from table where condition is not null having count(id) > 10 limit 10 offset 10 group by parent order by position desc";
        $params = [];

        $this->decorate($sql, $params);

        $this->assertEquals('SELECT * FROM table WHERE condition IS NOT NULL HAVING COUNT(id) > 10 LIMIT 10 OFFSET 10 GROUP BY parent ORDER BY position DESC', $this->formatter->format($sql, $params));
    }

    public function test_formats_insert_queries()
    {
        $sql    = "insert into table (column1, column2, column3) values (value1, value2, value3)";
        $params = [];

        $this->decorate($sql, $params);

        $this->assertEquals('INSERT INTO table (column1, column2, column3) VALUES (value1, value2, value3)', $this->formatter->format($sql, $params));
    }

    public function test_formats_update_queries()
    {
        $sql    = "update table set column1=value, column2=value2 where some_column=some_value";
        $params = [];

        $this->decorate($sql, $params);

        $this->assertEquals('UPDATE table SET column1=value, column2=value2 WHERE some_column=some_value', $this->formatter->format($sql, $params));
    }

    public function test_formats_delete_queries()
    {
        $sql    = "update table set column1=value, column2=value2 where some_column=some_value";
        $params = [];

        $this->decorate($sql, $params);

        $this->assertEquals('UPDATE table SET column1=value, column2=value2 WHERE some_column=some_value', $this->formatter->format($sql, $params));
    }

    protected function tearDown()
    {
        m::close();
    }

    protected function decorate($sql, $params)
    {
        $this->mock->shouldReceive('format')->once()->with($sql, $params)->andReturn($sql);
    }
}
