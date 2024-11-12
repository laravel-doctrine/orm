<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Feature\Configuration;

use Doctrine\DBAL\Exception as DBALException;
use LaravelDoctrine\ORM\Configuration\CustomTypeManager;
use LaravelDoctrineTest\ORM\Assets\Configuration\TypeMock;
use LaravelDoctrineTest\ORM\Assets\Configuration\TypeMock2;
use LaravelDoctrineTest\ORM\TestCase;

class CustomTypeManagerTest extends TestCase
{
    public function testCanAddType(): void
    {
        $manager = new CustomTypeManager();

        $manager->addType('type', TypeMock::class);

        $this->assertInstanceOf(TypeMock::class, $manager->getType('type'));
    }

    public function testCanOverrideType(): void
    {
        $manager = new CustomTypeManager();

        $manager->addType('type2', TypeMock::class);
        $this->assertInstanceOf(TypeMock::class, $manager->getType('type2'));

        $manager->addType('type2', TypeMock2::class);
        $this->assertInstanceOf(TypeMock2::class, $manager->getType('type2'));
    }

    public function testCanAddMultipleTypes(): void
    {
        $manager = new CustomTypeManager();

        $manager->addCustomTypes([
            'type3' => TypeMock::class,
            'type4' => TypeMock2::class,
        ]);

        $this->assertInstanceOf(TypeMock::class, $manager->getType('type3'));
        $this->assertInstanceOf(TypeMock2::class, $manager->getType('type4'));
    }

    public function testCannotGetNonExistingType(): void
    {
        $this->expectException(DBALException::class);

        $manager = new CustomTypeManager();
        $manager->getType('non_existing');
    }
}
