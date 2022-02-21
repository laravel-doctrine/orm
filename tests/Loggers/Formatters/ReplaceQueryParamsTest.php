<?php

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\JsonType;
use Doctrine\DBAL\Types\Type;
use LaravelDoctrine\ORM\Loggers\Formatters\ReplaceQueryParams;
use Mockery as m;
use Mockery\Mock;
use PHPUnit\Framework\TestCase;

class ReplaceQueryParamsTest extends TestCase
{
    /**
     * @var ReplaceQueryParams
     */
    protected $formatter;

    /**
     * @var Mock
     */
    private $platform;

    protected function setUp(): void
    {
        $this->platform  = m::mock(AbstractPlatform::class);
        $this->formatter = new ReplaceQueryParams;
    }

    public function test_can_replace_string_param()
    {
        $sql    = 'SELECT * FROM table WHERE column = ?';
        $params = ['value'];

        $this->assertEquals(
            'SELECT * FROM table WHERE column = "value"',
            $this->formatter->format($this->platform, $sql, $params)
        );
    }

    public function test_can_replace_multiple_string_params()
    {
        $sql    = 'SELECT * FROM table WHERE column = ? AND column2 = ?';
        $params = ['value', 'value2'];

        $this->assertEquals(
            'SELECT * FROM table WHERE column = "value" AND column2 = "value2"',
            $this->formatter->format($this->platform, $sql, $params)
        );
    }

    public function test_cannot_replace_object_params_without__toString()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Given query param is an instance of ObjectClass and could not be converted to a string');

        $sql    = 'SELECT * FROM table WHERE column = ?';
        $params = [new ObjectClass];

        $this->formatter->format($this->platform, $sql, $params);
    }

    public function test_can_replace_object_params_with__toString()
    {
        $sql    = 'SELECT * FROM table WHERE column = ?';
        $params = [new StringClass];

        $this->assertEquals(
            'SELECT * FROM table WHERE column = "string"',
            $this->formatter->format($this->platform, $sql, $params)
        );
    }

    public function test_can_replace_datetime_objects()
    {
        $date   = new DateTime('now');
        $sql    = 'SELECT * FROM table WHERE column = ?';
        $params = [$date];

        $this->assertEquals(
            'SELECT * FROM table WHERE column = "' . $date->format('Y-m-d H:i:s') . '"',
            $this->formatter->format($this->platform, $sql, $params)
        );
    }

    public function test_can_replace_datetime_immutable_objects()
    {
        $date   = new DateTimeImmutable('now');
        $sql    = 'SELECT * FROM table WHERE column = ?';
        $params = [$date];

        $this->assertEquals(
            'SELECT * FROM table WHERE column = "' . $date->format('Y-m-d H:i:s') . '"',
            $this->formatter->format($this->platform, $sql, $params)
        );
    }

    public function test_can_replace_array_param()
    {
        $sql    = 'SELECT * FROM table WHERE column IN ?';
        $params = [['value1', 'value2']];

        $this->assertEquals(
            'SELECT * FROM table WHERE column IN ("value1", "value2")',
            $this->formatter->format($this->platform, $sql, $params)
        );
    }

    public function test_can_replace_nested_array_param()
    {
        $sql    = 'SELECT * FROM table WHERE column = ?';
        $params = [['key1' => 'value1', 'key2' => ['key21' => 'value22']]];

        $this->assertEquals(
            'SELECT * FROM table WHERE column = "' . json_encode(reset($params), JSON_UNESCAPED_UNICODE) . '"',
            $this->formatter->format($this->platform, $sql, $params)
        );
    }

    public function test_can_replace_nested_array_param2()
    {
        $sql    = 'INSERT INTO foo (column1) VALUES (?)';
        $params = [
            1 => [
                'id'     => 20,
                'foo'    => 'bar',
                'nested' => []
            ]
        ];

        $this->assertEquals(
            'INSERT INTO foo (column1) VALUES ("{"id":20,"foo":"bar","nested":[]}")',
            $this->formatter->format($this->platform, $sql, $params)
        );
    }

    public function test_replace_object_params_without__toString_but_type()
    {
        $sql    = 'UPDATE table foo SET column = ?';
        $params = [new ObjectClass()];
        $types  = ['object_type'];

        if (!Type::hasType('object_type')) {
            Type::addType('object_type', ObjectType::class);
        }

        $this->assertEquals(
            'UPDATE table foo SET column = "{"status":false}"',
            $this->formatter->format($this->platform, $sql, $params, $types)
        );
    }

    protected function tearDown(): void
    {
        m::close();
    }
}

class ObjectClass
{
    public $status = false;
}

class StringClass
{
    public function __toString()
    {
        return 'string';
    }
}

class ObjectType extends JsonType
{
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return json_encode(get_object_vars($value));
    }

    public function getName()
    {
        return 'object_type';
    }
}
