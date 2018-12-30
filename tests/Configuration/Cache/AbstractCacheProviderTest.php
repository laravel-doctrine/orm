<?php

namespace LaravelDoctrine\Tests\Configuration\Cache;

abstract class AbstractCacheProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return \LaravelDoctrine\ORM\Configuration\Driver
     */
    abstract public function getProvider();

    abstract public function getExpectedInstance();

    public function test_can_resolve()
    {
        $this->assertInstanceOf($this->getExpectedInstance(), $this->getProvider()->resolve());
    }

    public function tearDown()
    {
        \Mockery::close();
    }
}
