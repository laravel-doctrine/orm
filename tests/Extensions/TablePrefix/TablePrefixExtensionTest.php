<?php

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use LaravelDoctrine\ORM\Extensions\TablePrefix\TablePrefixExtension;
use Mockery as m;

class TablePrefixExtensionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Mockery\MockInterface
     */
    protected $evm;

    /**
     * @var \Mockery\MockInterface
     */
    protected $em;

    /**
     * @var \Mockery\MockInterface
     */
    protected $reader;

    public function setUp()
    {
        $this->evm = m::mock(EventManager::class);
        $this->evm->shouldReceive('addEventSubscriber')->once();

        $this->em     = m::mock(EntityManagerInterface::class);
        $this->reader = m::mock(Reader::class);
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
            $this->reader
        );

        $this->assertEmpty($extension->getFilters());
    }

    public function tearDown()
    {
        m::close();
    }
}
