<?php

use LaravelDoctrine\ORM\Configuration\MetaData\Attributes;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class AttributesTest extends TestCase
{
    /**
     * @var Attributes
     */
    protected $meta;

    protected function setUp(): void
    {
        $this->meta = new Attributes();
    }

    public function test_can_resolve()
    {
        $resolved = $this->meta->resolve([
            'paths'   => ['entities'],
            'dev'     => true,
            'proxies' => ['path' => 'path'],
        ]);

        $this->assertInstanceOf(MappingDriver::class, $resolved);
        $this->assertInstanceOf(\Doctrine\ORM\Mapping\Driver\AttributeDriver::class, $resolved);
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
