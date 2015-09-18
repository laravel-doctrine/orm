<?php

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use LaravelDoctrine\ORM\Configuration\MetaData\Annotations;
use Mockery as m;

class AnnotationsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Annotations
     */
    protected $meta;

    protected function setUp()
    {
        $this->meta = new Annotations();
    }

    public function test_can_resolve()
    {
        $resolved = $this->meta->resolve([
            'paths'   => ['entities'],
            'dev'     => true,
            'proxies' => ['path' => 'path'],
            'simple'  => false
        ]);

        $this->assertInstanceOf(MappingDriver::class, $resolved);
        $this->assertInstanceOf(AnnotationDriver::class, $resolved);
    }

    protected function tearDown()
    {
        m::close();
    }
}
