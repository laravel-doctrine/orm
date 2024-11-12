<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\Adapter\Phpunit\MockeryTestCaseSetUp;

abstract class MockeryTestCase extends TestCase
{
    use MockeryPHPUnitIntegration;
    use MockeryTestCaseSetUp;

    protected function mockeryTestSetUp(): void
    {
    }

    protected function mockeryTestTearDown(): void
    {
    }
}
