<?php

use Doctrine\Common\Annotations\CachedReader;
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

    public function test_can_resolve_simple_with_cache()
    {
        /** @var AnnotationDriver $resolved */
        $resolved = $this->meta->resolve([
            'paths'   => ['entities'],
            'dev'     => true,
            'proxies' => ['path' => 'path'],
            'simple'  => true
        ]);

        $this->assertAnnotationDriver($resolved);
    }

    public function test_can_resolve()
    {
        /** @var AnnotationDriver $resolved */
        $resolved = $this->meta->resolve([
            'paths'   => ['entities'],
            'dev'     => true,
            'proxies' => ['path' => 'path'],
            'simple'  => false
        ]);

        $this->assertAnnotationDriver($resolved);
    }

    /**
     * @param AnnotationDriver $resolved
     */
    protected function assertAnnotationDriver($resolved)
    {
        $this->assertInstanceOf(MappingDriver::class, $resolved);
        $this->assertInstanceOf(AnnotationDriver::class, $resolved);
        $this->assertInstanceOf(CachedReader::class, $resolved->getReader());
    }

    protected function tearDown()
    {
        m::close();
    }
}
