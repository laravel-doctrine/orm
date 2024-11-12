<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Feature\Notifications;

use Doctrine\ORM\EntityManagerInterface;
use LaravelDoctrine\ORM\Notifications\Notification;
use LaravelDoctrineTest\ORM\TestCase;
use Mockery;
use ReflectionClass;
use stdClass;

class NotificationTest extends TestCase
{
    private EntityManagerInterface $em;

    public function setUp(): void
    {
        $this->em = Mockery::spy(EntityManagerInterface::class);

        parent::setUp();
    }

    public function testClassFunctions(): void
    {
        $entity = new Notification();

        $entity->success();
        $this->assertEquals('success', $entity->getLevel());

        $entity->error();
        $this->assertEquals('error', $entity->getLevel());

        $entity->level('custom');
        $this->assertEquals('custom', $entity->getLevel());

        $entity->message('custom');
        $this->assertEquals('custom', $entity->getMessage());

        $entity->action('custom', 'url');
        $this->assertEquals('custom', $entity->getActionText());
        $this->assertEquals('url', $entity->getActionUrl());

        $user = new stdClass();
        $entity->to($user);
        $this->assertSame($user, $entity->getUser());

        $reflection = new ReflectionClass($entity);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($entity, 1);

        $entity->getId();
    }
}
