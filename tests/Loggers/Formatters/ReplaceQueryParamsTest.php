<?php

use LaravelDoctrine\ORM\Loggers\Formatters\ReplaceQueryParams;
use Mockery as m;

class ReplaceQueryParamsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ReplaceQueryParams
     */
    protected $formatter;

    protected function setUp()
    {
        $this->formatter = new ReplaceQueryParams;
    }

    public function test_can_replace_string_param()
    {
        $sql    = 'SELECT * FROM table WHERE column = ?';
        $params = ['value'];

        $this->assertEquals(
            'SELECT * FROM table WHERE column = "value"',
            $this->formatter->format($sql, $params)
        );
    }

    public function test_can_replace_multiple_string_params()
    {
        $sql    = 'SELECT * FROM table WHERE column = ? AND column2 = ?';
        $params = ['value', 'value2'];

        $this->assertEquals(
            'SELECT * FROM table WHERE column = "value" AND column2 = "value2"',
            $this->formatter->format($sql, $params)
        );
    }

    public function test_cannot_replace_object_params_without__toString()
    {
        $this->setExpectedException(
            Exception::class,
            'Given query param is an instance of ObjectClass and could not be converted to a string'
        );

        $sql    = 'SELECT * FROM table WHERE column = ?';
        $params = [new ObjectClass];

        $this->formatter->format($sql, $params);
    }

    public function test_can_replace_object_params_with__toString()
    {
        $sql    = 'SELECT * FROM table WHERE column = ?';
        $params = [new StringClass];

        $this->assertEquals(
            'SELECT * FROM table WHERE column = "string"',
            $this->formatter->format($sql, $params)
        );
    }

    public function test_can_replace_datetime_objects()
    {
        $date   = new DateTime('now');
        $sql    = 'SELECT * FROM table WHERE column = ?';
        $params = [$date];

        $this->assertEquals(
            'SELECT * FROM table WHERE column = "' . $date->format('Y-m-d H:i:s') . '"',
            $this->formatter->format($sql, $params)
        );
    }

    public function test_can_replace_array_param()
    {
        $sql    = 'SELECT * FROM table WHERE column IN ?';
        $params = [['value1', 'value2']];

        $this->assertEquals(
            'SELECT * FROM table WHERE column IN ("value1", "value2")',
            $this->formatter->format($sql, $params)
        );
    }

    public function test_can_replace_nested_array_param()
    {
        $sql    = 'SELECT * FROM table WHERE column = ?';
        $params = [['key1' => 'value1', 'key2' => ['key21' => 'value22']]];

        $this->assertEquals(
            'SELECT * FROM table WHERE column = "' . json_encode(reset($params), JSON_UNESCAPED_UNICODE) . '"',
            $this->formatter->format($sql, $params)
        );
    }

    protected function tearDown()
    {
        m::close();
    }
}

class ObjectClass
{
}

class StringClass
{
    public function __toString()
    {
        return 'string';
    }
}
