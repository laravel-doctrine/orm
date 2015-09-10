<?php

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use LaravelDoctrine\ORM\Types\Json;
use Mockery as m;
use Mockery\Mock;

class JsonTypeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Json
     */
    protected $type;

    /**
     * @var Mock
     */
    protected $platform;

    protected function setUp()
    {
        $this->platform = m::mock(AbstractPlatform::class);

        if (!Type::hasType('json')) {
            Type::addType('json', Json::class);
        }
        $this->type = Type::getType('json');
    }

    public function test_it_returns_null_when_database_value_is_null()
    {
        $this->assertNull($this->type->convertToPHPValue(null, $this->platform));
    }

    public function test_it_returns_empty_array_when_database_value_is_empty()
    {
        $this->assertEquals([], $this->type->convertToPHPValue('', $this->platform));
    }

    public function test_it_returns_array_when_database_value_is_json()
    {
        $this->assertEquals(['value'], $this->type->convertToPHPValue(json_encode(['value']), $this->platform));
    }

    protected function tearDown()
    {
        m::close();
    }
}
