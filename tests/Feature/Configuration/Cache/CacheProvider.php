<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Feature\Configuration\Cache;

use LaravelDoctrineTest\ORM\TestCase;
use Mockery;

abstract class CacheProvider extends TestCase
{
    abstract public function getProvider(): mixed;

    abstract public function getExpectedInstance(): mixed;

    public function testCanResolve(): void
    {
        $this->assertInstanceOf($this->getExpectedInstance(), $this->getProvider()->resolve());
    }

    public function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }
}
