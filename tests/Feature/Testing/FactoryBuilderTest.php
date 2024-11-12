<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Feature\Testing;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup as Setup;
use Doctrine\Persistence\ManagerRegistry;
use Faker;
use Faker\Generator;
use LaravelDoctrine\ORM\Testing\FactoryBuilder;
use LaravelDoctrineTest\ORM\Assets\Testing\EntityStub;
use LaravelDoctrineTest\ORM\MockeryTestCase;
use Mockery;
use Mockery\Mock;

use function array_merge;
use function random_int;

class FactoryBuilderTest extends MockeryTestCase
{
    private ManagerRegistry $aRegistry;

    private string $aClass;

    private string $aName;

    /** @var callable[]|Mock[] */
    private array $definitions;

    private Generator $faker;

    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->aRegistry   = Mockery::mock(ManagerRegistry::class);
        $this->aClass      = EntityStub::class;
        $this->aName       = 'default';
        $this->faker       = Mockery::mock(Faker\Generator::class);
        $this->definitions = [
            EntityStub::class => [
                $this->aName => static function () {
                    return [
                        'id'   => random_int(1, 9),
                        'name' => 'A Name',
                    ];
                },
            ],
        ];

        $this->aRegistry
            ->shouldReceive('getManagerForClass')
            ->with(EntityStub::class)
            ->andReturn($this->entityManager = Mockery::mock(EntityManagerInterface::class));

        $classMetadata = $this->getEntityManager()->getClassMetadata(EntityStub::class);

        $this->entityManager->shouldReceive('getClassMetadata')
                            ->with(EntityStub::class)
                            ->andReturn($classMetadata);

        $this->entityManager->shouldReceive('persist');
        $this->entityManager->shouldReceive('flush');

        parent::setUp();
    }

    /**
     * @param mixed[] $definitions
     * @param mixed[] $states
     * @param mixed[] $afterMaking
     * @param mixed[] $afterCreating
     */
    protected function getFactoryBuilder(array $definitions = [], array $states = [], array $afterMaking = [], array $afterCreating = []): FactoryBuilder
    {
        return FactoryBuilder::construct(
            $this->aRegistry,
            $this->aClass,
            $this->aName,
            array_merge($this->definitions, $definitions),
            $this->faker,
            $states,
            $afterMaking,
            $afterCreating,
        );
    }

    protected function getEntityManager(): EntityManager
    {
        $config = Setup::createAttributeMetadataConfiguration([__DIR__], true);

        $conn = DriverManager::getConnection([
            'driver'   => 'pdo_sqlite',
            'database' => ':memory:',
        ], $config);

        return new EntityManager($conn, $config);
    }

    public function testItMakesInstancesOfTheClass(): void
    {
        $instance = $this->getFactoryBuilder()->make();

        $this->assertInstanceOf(EntityStub::class, $instance);
    }

    public function testItMakesInstancesOfTheClassForObjectDefinition(): void
    {
        $this->definitions = [
            EntityStub::class => [
                $this->aName => static function () {
                    $obj       = new EntityStub();
                    $obj->id   = random_int(1, 9);
                    $obj->name = 'A Name';

                    return $obj;
                },
            ],
        ];

        $this->testItMakesInstancesOfTheClass();
    }

    public function testItCreatesInstancesOfTheClass(): void
    {
        $instance = $this->getFactoryBuilder()->create();

        $this->entityManager->shouldHaveReceived('persist')->with($instance)->once();
        $this->entityManager->shouldHaveReceived('flush')->once();
    }

    public function testItFillsToManyRelationsWithArrayCollections(): void
    {
        $instance = $this->getFactoryBuilder()->make();

        $this->assertInstanceOf(ArrayCollection::class, $instance->others);
    }

    public function testItShouldntOverridePredefinedRelations(): void
    {
        $instance = $this->getFactoryBuilder([
            EntityStub::class => [
                'default' => static function () {
                    return [
                        'id'     => 1,
                        'name'   => 'a name',
                        'others' => ['Foo'],
                    ];
                },
            ],
        ])->make();

        $this->assertEquals(['Foo'], $instance->others);
    }

    public function testItShouldPersistEntitiesReturnedByAClosure(): void
    {
        $madeInstance = new EntityStub();

        $instance = $this->getFactoryBuilder([
            EntityStub::class => [
                'default' => static function () use ($madeInstance) {
                    return [
                        'id'     => 1,
                        'name'   => 'a name',
                        'others' => static function () use ($madeInstance) {
                            return [$madeInstance];
                        },
                    ];
                },
            ],
        ])->create();

        $this->assertSame($madeInstance, $instance->others[0]);

        $this->entityManager->shouldHaveReceived('persist')->with($madeInstance)->once();
    }

    public function testItHandlesStates(): void
    {
        $states = [
            $this->aClass => [
                'withState' => static function () {
                    return ['id' => 2, 'name' => 'stateful'];
                },
                'other' => static function () {
                    return ['id' => 3];
                },
            ],
        ];

        $instance = $this->getFactoryBuilder([], $states)->states('withState')->make();

        $this->assertEquals('stateful', $instance->name);
        $this->assertEquals(2, $instance->id);
    }

    public function testItHandlesAfterMakingCallback(): void
    {
        $afterMakingRan = false;

        $this->getFactoryBuilder([], [], [
            $this->aClass => [
                'default' => [
                    static function () use (&$afterMakingRan): void {
                        $afterMakingRan = true;
                    },
                ],
            ],
        ])->make();

        $this->assertTrue($afterMakingRan);
    }

    public function testItHandlesAfterMakingCallbackWithMultipleModels(): void
    {
        $afterMakingRan = 0;

        $this->getFactoryBuilder([], [], [
            $this->aClass => [
                'default' => [
                    static function () use (&$afterMakingRan): void {
                        $afterMakingRan++;
                    },
                ],
            ],
        ], [])->times(2)->make();

        $this->assertEquals(2, $afterMakingRan);
    }

    public function testItHandlesAfterCreatingCallback(): void
    {
        $afterCreatingRan = false;

        $this->getFactoryBuilder([], [], [], [
            $this->aClass => [
                'default' => [
                    static function () use (&$afterCreatingRan): void {
                        $afterCreatingRan = true;
                    },
                ],
            ],
        ])->create();

        $this->assertTrue($afterCreatingRan);
    }

    public function testItHandlesAfterCreatingCallbackWithMultipleModels(): void
    {
        $afterCreatingRan = 0;

        $this->getFactoryBuilder([], [], [], [
            $this->aClass => [
                'default' => [
                    static function () use (&$afterCreatingRan): void {
                        $afterCreatingRan++;
                    },
                ],
            ],
        ])->times(2)->create();

        $this->assertEquals(2, $afterCreatingRan);
    }

    public function testItHandlesAfterCreatingWithStateCallback(): void
    {
        $afterCreatingRan = false;

        $this->getFactoryBuilder([], ['withState'], [], [
            $this->aClass => [
                'withState' => [
                    static function () use (&$afterCreatingRan): void {
                        $afterCreatingRan = true;
                    },
                ],
            ],
        ])->states('withState')->create();

        $this->assertTrue($afterCreatingRan);
    }

    public function testItHandlesAfterMakingWithStateCallback(): void
    {
        $afterMakingRan = false;

        $this->getFactoryBuilder([], ['withState'], [
            $this->aClass => [
                'withState' => [
                    static function () use (&$afterMakingRan): void {
                        $afterMakingRan = true;
                    },
                ],
            ],
        ])->states('withState')->make();

        $this->assertTrue($afterMakingRan);
    }
}
