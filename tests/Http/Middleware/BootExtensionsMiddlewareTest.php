<?php

use LaravelDoctrine\ORM\Http\Middleware\BootExtensionsMiddleware;
use Mockery as m;

class BootExtensionsMiddlewareTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testHandle()
    {
        $kernelMock = m::mock(LaravelDoctrine\ORM\Extensions\ExtensionManager::class)
            ->shouldReceive('boot')
            ->once()
            ->getMock();

        $requestMock = m::mock(Illuminate\Http\Request::class);

        $called = false;

        $nextMock = function () use (&$called) {
            $called = true;
        };

        /** @noinspection PhpParamsInspection */
        $middleware = new BootExtensionsMiddleware($kernelMock);

        /** @noinspection PhpParamsInspection */
        $middleware->handle($requestMock, $nextMock);

        $this->assertTrue($called);
    }
}
