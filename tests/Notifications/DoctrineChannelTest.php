<?php

namespace LaravelDoctrine\Tests\Notifications;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use LaravelDoctrine\ORM\Exceptions\NoEntityManagerFound;
use LaravelDoctrine\ORM\Notifications\DoctrineChannel;
use LaravelDoctrine\Tests\Mocks\NotificationStub;
use Mockery;
use Mockery\Mock;
use LaravelDoctrine\ORM\Notifications\Notification;

class DoctrineChannelTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DoctrineChannel
     */
    private $channel;

    /**
     * @var Mock
     */
    private $registry;

    /**
     * @var Mock
     */
    private $em;

    public function setUp()
    {
        $this->em = Mockery::spy(EntityManagerInterface::class);

        $this->channel = new DoctrineChannel(
            $this->registry = Mockery::mock(ManagerRegistry::class)
        );
    }

    public function test_can_send_notification_on_default_em()
    {
        $this->registry->shouldReceive('getManagerForClass')
                       ->with(Notification::class)
                       ->andReturn($this->em);

        $this->channel->send(new \LaravelDoctrine\Tests\Mocks\NotifiableStub, new NotificationStub);

        $this->em->shouldHaveReceived('persist')->once();
        $this->em->shouldHaveReceived('flush')->once();
    }

    public function test_can_send_notification_on_custom_em()
    {
        $this->registry->shouldReceive('getManager')
                       ->with('custom')
                       ->andReturn($this->em);

        $this->channel->send(new \LaravelDoctrine\Tests\Mocks\CustomNotifiableStub, new NotificationStub);

        $this->em->shouldHaveReceived('persist')->once();
        $this->em->shouldHaveReceived('flush')->once();
    }

    public function test_it_should_throw_exception_when_it_does_not_find_an_em()
    {
        $this->expectException(NoEntityManagerFound::class);

        $this->registry->shouldReceive('getManager')
                       ->with('custom')
                       ->andReturnNull();

        $this->channel->send(new \LaravelDoctrine\Tests\Mocks\CustomNotifiableStub, new NotificationStub);
    }
}
