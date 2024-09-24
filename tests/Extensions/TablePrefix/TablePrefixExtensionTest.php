<?php

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use LaravelDoctrine\ORM\Extensions\TablePrefix\TablePrefixExtension;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class TablePrefixExtensionTest extends TestCase
{
    /**
     * @var \Mockery\MockInterface
     */
    protected $evm;

    /**
     * @var \Mockery\MockInterface
     */
    protected $em;

    public function setUp(): void
    {
        $this->evm = m::mock(EventManager::class);
        $this->evm->shouldReceive('addEventSubscriber')->once();

        $this->em     = m::mock(EntityManagerInterface::class);
    }

    public function test_can_register_extension()
    {
        $connection = m::mock(Connection::class);
        $this->em->shouldReceive('getConnection')
                 ->once()
                 ->andReturn($connection);

        $connection->shouldReceive('getParams')->once()->andReturn([
            'prefix' => 'prefix'
        ]);

        $extension = new TablePrefixExtension();

        $extension->addSubscribers(
            $this->evm,
            $this->em,
        );

        $this->assertEmpty($extension->getFilters());
    }

    public function tearDown(): void
    {
        m::close();
    }
}
