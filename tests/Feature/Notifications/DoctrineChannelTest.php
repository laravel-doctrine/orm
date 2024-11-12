<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Feature\Notifications;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use LaravelDoctrine\ORM\Exceptions\NoEntityManagerFound;
use LaravelDoctrine\ORM\Notifications\DoctrineChannel;
use LaravelDoctrineTest\ORM\Assets\Notifications\CustomNotifiableStub;
use LaravelDoctrineTest\ORM\Assets\Notifications\NotifiableStub;
use LaravelDoctrineTest\ORM\Assets\Notifications\NotificationDatabaseStub;
use LaravelDoctrineTest\ORM\Assets\Notifications\NotificationInvalidStub;
use LaravelDoctrineTest\ORM\Assets\Notifications\NotificationStub;
use LaravelDoctrineTest\ORM\TestCase;
use Mockery;
use Mockery\Mock;
use RuntimeException;

class DoctrineChannelTest extends TestCase
{
    private DoctrineChannel $channel;

    /** @var Mock */
    private ManagerRegistry $registry;

    /** @var Mock */
    private EntityManagerInterface $em;

    public function setUp(): void
    {
        $this->em = Mockery::spy(EntityManagerInterface::class);

        $this->channel      = new DoctrineChannel(
            $this->registry = Mockery::mock(ManagerRegistry::class),
        );

        parent::setUp();
    }

    public function testCanSendNotificationOnDefaultEm(): void
    {
        $this->registry->shouldReceive('getManagerForClass')
                       ->with('LaravelDoctrine\ORM\Notifications\Notification')
                       ->andReturn($this->em);

        $this->channel->send(new NotifiableStub(), new NotificationStub());
        $this->channel->send(new NotifiableStub(), new NotificationDatabaseStub());

        $this->em->shouldHaveReceived('persist')->twice();
        $this->em->shouldHaveReceived('flush')->twice();

        $this->assertTrue(true);
    }

    public function testTriggerExceptionOnInvalidNotification(): void
    {
        $this->registry->shouldReceive('getManagerForClass')
            ->with('LaravelDoctrine\ORM\Notifications\Notification')
            ->andReturn($this->em);

        $this->expectException(RuntimeException::class);

        $this->channel->send(new NotifiableStub(), new NotificationInvalidStub());

        $this->em->shouldHaveReceived('persist')->once();
        $this->em->shouldHaveReceived('flush')->once();

        $this->assertTrue(true);
    }

    public function testCanSendNotificationOnCustomEm(): void
    {
        $this->registry->shouldReceive('getManager')
                       ->with('custom')
                       ->andReturn($this->em);

        $this->channel->send(new CustomNotifiableStub(), new NotificationStub());

        $this->em->shouldHaveReceived('persist')->once();
        $this->em->shouldHaveReceived('flush')->once();

        $this->assertTrue(true);
    }

    public function testItShouldThrowExceptionWhenItDoesNotFindAnEm(): void
    {
        $this->expectException(NoEntityManagerFound::class);

        $this->registry->shouldReceive('getManager')
                       ->with('custom')
                       ->andReturnNull();

        $this->channel->send(new CustomNotifiableStub(), new NotificationStub());
    }
}
