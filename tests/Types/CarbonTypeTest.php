<?php

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use LaravelDoctrine\ORM\Types\Carbon;
use Mockery as m;
use Mockery\Mock;

class CarbonTypeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Carbon
     */
    protected $type;

    /**
     * @var Mock
     */
    protected $platform;

    protected function setUp()
    {
        $this->platform = m::mock(AbstractPlatform::class);

        if (!Type::hasType('datetime')) {
            Type::addType('datetime', Carbon::class);
        } else {
            Type::overrideType('datetime', Carbon::class);
        }

        $this->type = Type::getType('datetime');
    }

    public function test_it_returns_null_when_database_value_is_null()
    {
        $this->assertNull($this->type->convertToPHPValue(null, $this->platform));
    }

    public function test_it_returns_a_carbon_instance_when_database_value_is_a_datetime()
    {
        $date = new DateTime();

        $this->assertInstanceOf(\Carbon\Carbon::class, $this->type->convertToPHPValue($date, $this->platform));
    }

    protected function tearDown()
    {
        m::close();
    }
}
