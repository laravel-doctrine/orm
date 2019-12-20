<?php

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Setup;
use Doctrine\Persistence\ManagerRegistry;
use LaravelDoctrine\ORM\Testing\FactoryBuilder;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class FactoryBuilderTest extends MockeryTestCase
{
    /**
     * @var ManagerRegistry|\Mockery\Mock
     */
    private $aRegistry;

    /**
     * @var string
     */
    private $aClass;

    /**
     * @var string
     */
    private $aName;

    /**
     * @var callable[]|\Mockery\Mock[]
     */
    private $definitions;

    /**
     * @var \Faker\Generator|\Mockery\Mock
     */
    private $faker;

    /**
     * @var EntityManagerInterface|\Mockery\Mock
     */
    private $entityManager;

    protected function setUp()
    {
        $this->aRegistry   = \Mockery::mock(ManagerRegistry::class);
        $this->aClass      = EntityStub::class;
        $this->aName       = 'default';
        $this->faker       = \Mockery::mock(Faker\Generator::class);
        $this->definitions = [
            EntityStub::class => [
                $this->aName => function () {
                    return [
                        'id'   => random_int(1, 9),
                        'name' => 'A Name',
                    ];
                }
            ]
        ];

        $this->aRegistry
            ->shouldReceive('getManagerForClass')
            ->with(EntityStub::class)
            ->andReturn($this->entityManager = \Mockery::mock(EntityManagerInterface::class));

        $classMetadata = $this->getEntityManager()->getClassMetadata(EntityStub::class);

        $this->entityManager->shouldReceive('getClassMetadata')
                            ->with(EntityStub::class)
                            ->andReturn($classMetadata);

        $this->entityManager->shouldReceive('persist');
        $this->entityManager->shouldReceive('flush');
    }

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
            $afterCreating
        );
    }

    protected function getEntityManager()
    {
        $conn = [
            'driver'   => 'pdo_sqlite',
            'database' => ':memory:',
        ];

        $config = Setup::createAnnotationMetadataConfiguration([__DIR__], true);

        return EntityManager::create($conn, $config);
    }

    public function test_it_makes_instances_of_the_class()
    {
        $instance = $this->getFactoryBuilder()->make();

        $this->assertInstanceOf(EntityStub::class, $instance);
    }

    public function test_it_makes_instances_of_the_class_for_object_definition()
    {
        $this->definitions = [
            EntityStub::class => [
                $this->aName => function () {
                    $obj = new EntityStub();
                    $obj->id = random_int(1, 9);
                    $obj->name = 'A Name';

                    return $obj;
                }
            ]
        ];

        $this->test_it_makes_instances_of_the_class();
    }

    public function test_it_creates_instances_of_the_class()
    {
        $instance = $this->getFactoryBuilder()->create();

        $this->entityManager->shouldHaveReceived('persist')->with($instance)->once();
        $this->entityManager->shouldHaveReceived('flush')->once();
    }

    public function test_it_fills_to_many_relations_with_array_collections()
    {
        $instance = $this->getFactoryBuilder()->make();

        $this->assertInstanceOf(ArrayCollection::class, $instance->others);
    }

    public function test_it_shouldnt_override_predefined_relations()
    {
        $instance = $this->getFactoryBuilder([
            EntityStub::class => [
                'default' => function () {
                    return [
                        'id'     => 1,
                        'name'   => 'a name',
                        'others' => ['Foo'],
                    ];
                }
            ]
        ])->make();

        $this->assertEquals(['Foo'], $instance->others);
    }

    public function test_it_should_persist_entities_returned_by_a_closure()
    {
        $madeInstance = new EntityStub();

        $instance = $this->getFactoryBuilder([
            EntityStub::class => [
                'default' => function () use ($madeInstance) {
                    return [
                        'id'     => 1,
                        'name'   => 'a name',
                        'others' => function () use ($madeInstance) {
                            return [$madeInstance];
                        },
                    ];
                }
            ]
        ])->create();

        $this->assertSame($madeInstance, $instance->others[0]);

        $this->entityManager->shouldHaveReceived('persist')->with($madeInstance)->once();
    }

    public function test_it_handles_states()
    {
        $states = [
            $this->aClass => [
                'withState' => function () {
                    return ['id' => 2, 'name' => 'stateful'];
                },
                'other' => function () {
                    return ['id' => 3];
                },
            ]
        ];

        $instance = $this->getFactoryBuilder([], $states)->states('withState')->make();

        $this->assertEquals('stateful', $instance->name);
        $this->assertEquals(2, $instance->id);
    }

    public function test_it_handles_after_making_callback()
    {
        $afterMakingRan = false;

        $this->getFactoryBuilder([], [], [$this->aClass => ['default' => [function () use (&$afterMakingRan) {
            $afterMakingRan = true;
        }]]])->make();

        $this->assertTrue($afterMakingRan);
    }

    public function test_it_handles_after_making_callback_with_multiple_models()
    {
        $afterMakingRan = 0;

        $this->getFactoryBuilder([], [], [$this->aClass => ['default' => [function () use (&$afterMakingRan) {
            $afterMakingRan++;
        }]]], [])->times(2)->make();

        $this->assertEquals(2, $afterMakingRan);
    }

    public function test_it_handles_after_creating_callback()
    {
        $afterCreatingRan = false;

        $this->getFactoryBuilder([], [], [], [$this->aClass => ['default' => [function () use (&$afterCreatingRan) {
            $afterCreatingRan = true;
        }]]])->create();

        $this->assertTrue($afterCreatingRan);
    }

    public function test_it_handles_after_creating_callback_with_multiple_models()
    {
        $afterCreatingRan = 0;

        $this->getFactoryBuilder([], [], [], [$this->aClass => ['default' => [function () use (&$afterCreatingRan) {
            $afterCreatingRan++;
        }]]])->times(2)->create();

        $this->assertEquals(2, $afterCreatingRan);
    }

    public function test_it_handles_after_creating_with_state_callback()
    {
        $afterCreatingRan = false;

        $this->getFactoryBuilder([], ['withState'], [], [$this->aClass => ['withState' => [function () use (&$afterCreatingRan) {
            $afterCreatingRan = true;
        }]]])->states('withState')->create();

        $this->assertTrue($afterCreatingRan);
    }

    public function test_it_handles_after_making_with_state_callback()
    {
        $afterMakingRan = false;

        $this->getFactoryBuilder([], ['withState'], [$this->aClass => ['withState' => [function () use (&$afterMakingRan) {
            $afterMakingRan = true;
        }]]])->states('withState')->make();

        $this->assertTrue($afterMakingRan);
    }
}

/**
 * @Entity
 */
class EntityStub
{
    /**
     * @Id @GeneratedValue @Column(type="integer")
     */
    public $id;

    /**
     * @Column(type="string")
     */
    public $name;

    /**
     * @ManyToMany(targetEntity="EntityStub")
     * @JoinTable(name="stub_stubs",
     *      joinColumns={@JoinColumn(name="owner_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="owned_id", referencedColumnName="id")}
     * )
     */
    public $others;
}
