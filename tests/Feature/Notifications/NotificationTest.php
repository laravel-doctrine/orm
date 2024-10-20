<?php

use Doctrine\ORM\EntityManagerInterface;
use Mockery\Mock;
use PHPUnit\Framework\TestCase;

class NotificationTest extends TestCase
{
    /**
     * @var Mock
     */
    private $registry;

    /**
     * @var Mock
     */
    private $em;

    public function setUp(): void
    {
        $this->em = Mockery::spy(EntityManagerInterface::class);
    }

    public function testClassFunctions()
    {
        $entity = new \LaravelDoctrine\ORM\Notifications\Notification();

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
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($entity, 1);

        $entity->getId();
    }
}
