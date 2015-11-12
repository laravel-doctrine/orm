<?php


abstract class AbstractCacheProviderTest extends PHPUnit_Framework_TestCase
{
    abstract public function getProvider();

    abstract public function getExpectedInstance();

    public function test_can_resolve()
    {
        $this->assertInstanceOf($this->getExpectedInstance(), $this->getProvider()->resolve());
    }

    public function tearDown()
    {
        Mockery::close();
    }
}
