<?php

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Setup;
use Doctrine\Persistence\ManagerRegistry;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Factories\CrossJoinSequence;
use LaravelDoctrine\ORM\Testing\Factories\Factory;
use LaravelDoctrine\ORM\Testing\Factories\HasFactory;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class FactoriesTest extends MockeryTestCase
{
    /**
     * @var EntityManagerInterface|\Mockery\Mock
     */
    private $entityManager;

    protected function setUp(): void
    {
        $container = Container::getInstance();
        $registry = \Mockery::mock(ManagerRegistry::class);
        $container->instance(ManagerRegistry::class, $registry);

        $container->singleton(\Faker\Generator::class, function () {
            return \Faker\Factory::create('en_US');
        });

        $this->entityManager = \Mockery::mock(EntityManagerInterface::class);
        $registry
            ->shouldReceive('getManagerForClass')
            ->with(FactoriesEntityStub::class)
            ->andReturn($this->entityManager);

        $classMetadata = $this->getEntityManager()->getClassMetadata(FactoriesEntityStub::class);

        $this->entityManager->shouldReceive('getClassMetadata')
            ->with(FactoriesEntityStub::class)
            ->andReturn($classMetadata);

        $this->entityManager->shouldReceive('persist');
        $this->entityManager->shouldReceive('flush');
    }

    protected function getEntityManager()
    {
        $conn = [
            'driver' => 'pdo_sqlite',
            'database' => ':memory:',
        ];

        $config = Setup::createAnnotationMetadataConfiguration([__DIR__], true);

        return EntityManager::create($conn, $config);
    }

    public function test_it_makes_instances_of_the_class()
    {
        $instance = FactoriesEntityStub::factory()->make();

        $this->assertInstanceOf(FactoriesEntityStub::class, $instance);
        $this->assertNotNull($instance->id);
        $this->assertNotNull($instance->name);
    }

    public function test_it_creates_instances_of_the_class()
    {
        $instance = FactoriesEntityStub::factory()->create();

        $this->entityManager->shouldHaveReceived('persist')->with($instance)->once();
        $this->entityManager->shouldHaveReceived('flush')->once();
    }

    public function test_it_fills_to_many_relations_with_array_collections()
    {
        $instance = FactoriesEntityStub::factory()->make();

        $this->assertInstanceOf(ArrayCollection::class, $instance->others);
    }

    public function test_it_shouldnt_override_predefined_relations()
    {
        $instance = FactoriesEntityStub::factory()->state(['others' => ['Foo']])->make();

        $this->assertEquals(['Foo'], $instance->others);
    }

    public function test_it_should_execute_closures()
    {
        $instance = FactoriesEntityStub::factory()->state([
            'id' => function() { return 42; },
            'name' => function() { return 'Foo'; },
        ])->make();

        $this->assertEquals(42, $instance->id);
        $this->assertEquals('Foo', $instance->name);
    }

    public function test_it_should_persist_entities_returned_by_a_closure()
    {
        $newStub = new FactoriesEntityStub();
        FactoriesEntityStub::factory()->state([
            'others' => function() use ($newStub) { return [$newStub]; },
        ])->create();

        $this->entityManager->shouldHaveReceived('persist')->with($newStub)->once();
    }

    public function test_it_handles_states()
    {
        $instance = FactoriesEntityStub::factory()->suspended()->make();

        $this->assertEquals('name suspended', $instance->name);
        $this->assertLessThan(0, $instance->id);
    }

    public function test_it_handles_after_making_callback()
    {
        $instance = FactoriesEntityStub::factory()->make();

        $this->assertCount(1, FactoriesEntityStubFactory::$afterMakingInstances);
        $this->assertCount(0, FactoriesEntityStubFactory::$afterCreatingInstances);
        $this->assertEquals($instance, FactoriesEntityStubFactory::$afterMakingInstances[0]);
    }

    public function test_it_handles_after_making_callback_with_multiple_models()
    {
        $instances = FactoriesEntityStub::factory(3)->make();

        $this->assertCount(3, FactoriesEntityStubFactory::$afterMakingInstances);
        $this->assertCount(0, FactoriesEntityStubFactory::$afterCreatingInstances);
        $this->assertEquals($instances, FactoriesEntityStubFactory::$afterMakingInstances);
    }

    public function test_it_handles_after_creating_callback()
    {
        $instance = FactoriesEntityStub::factory()->create();

        $this->assertCount(1, FactoriesEntityStubFactory::$afterMakingInstances);
        $this->assertCount(1, FactoriesEntityStubFactory::$afterCreatingInstances);
        $this->assertEquals($instance, FactoriesEntityStubFactory::$afterCreatingInstances[0]);
    }

    public function test_it_handles_after_creating_callback_with_multiple_models()
    {
        $instances = FactoriesEntityStub::factory(3)->create();

        $this->assertCount(3, FactoriesEntityStubFactory::$afterMakingInstances);
        $this->assertCount(3, FactoriesEntityStubFactory::$afterCreatingInstances);
        $this->assertEquals($instances, FactoriesEntityStubFactory::$afterCreatingInstances);
    }

    public function test_sequences()
    {
        $instances = FactoriesEntityStub::factory(2)->sequence(
            ['name' => 'Taylor Otwell'],
            ['name' => 'Abigail Otwell'],
        )->create();

        $this->assertSame('Taylor Otwell', $instances[0]->name);
        $this->assertSame('Abigail Otwell', $instances[1]->name);

        $instances = FactoriesEntityStub::factory()->times(2)->sequence(function ($sequence) {
            return ['name' => 'index: '.$sequence->index];
        })->create();

        $this->assertSame('index: 0', $instances[0]->name);
        $this->assertSame('index: 1', $instances[1]->name);
    }

    public function test_counted_sequence()
    {
        $factory = FactoriesEntityStub::factory()->forEachSequence(
            ['name' => 'Taylor Otwell'],
            ['name' => 'Abigail Otwell'],
            ['name' => 'Dayle Rees']
        );

        $class = new ReflectionClass($factory);
        $prop = $class->getProperty('count');
        $prop->setAccessible(true);
        $value = $prop->getValue($factory);

        $this->assertSame(3, $value);
    }

    public function test_cross_join_sequences()
    {
        $assert = function ($users) {
            $assertions = [
                ['name' => 'Thomas', 'lastName' => 'Anderson'],
                ['name' => 'Thomas', 'lastName' => 'Smith'],
                ['name' => 'Agent', 'lastName' => 'Anderson'],
                ['name' => 'Agent', 'lastName' => 'Smith'],
            ];

            foreach ($assertions as $key => $assertion) {
                $this->assertSame(
                    $assertion,
                    [
                        'name' => $users[$key]->name,
                        'lastName' => $users[$key]->lastName
                    ],
                );
            }
        };

        $usersByClass = FactoriesEntityStub::factory(4)
            ->state(
                new CrossJoinSequence(
                    [['name' => 'Thomas'], ['name' => 'Agent']],
                    [['lastName' => 'Anderson'], ['lastName' => 'Smith']],
                ),
            )
            ->make();

        $assert($usersByClass);

        $usersByMethod = FactoriesEntityStub::factory(4)
            ->crossJoinSequence(
                [['name' => 'Thomas'], ['name' => 'Agent']],
                [['lastName' => 'Anderson'], ['lastName' => 'Smith']],
            )
            ->make();

        $assert($usersByMethod);
    }
}

/**
 * @Entity
 */
class FactoriesEntityStub
{
    use HasFactory;

    protected static function newFactory()
    {
        return FactoriesEntityStubFactory::new();
    }

    /**
     * @Id @GeneratedValue @Column(type="integer")
     */
    public $id;

    /**
     * @Column(type="string")
     */
    public $name;

    /**
     * @Column(type="string")
     */
    public $lastName;

    /**
     * @ManyToMany(targetEntity="EntityStub")
     * @JoinTable(name="stub_stubs",
     *      joinColumns={@JoinColumn(name="owner_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="owned_id", referencedColumnName="id")}
     * )
     */
    public $others;
}

class FactoriesEntityStubFactory extends Factory
{
    protected $class = FactoriesEntityStub::class;

    public static $afterMakingInstances = [];
    public static $afterCreatingInstances = [];

    public function configure()
    {
        self::$afterMakingInstances = [];
        self::$afterCreatingInstances = [];

        return $this->afterMaking(function (FactoriesEntityStub $stub){
            self::$afterMakingInstances[] = $stub;
        })->afterCreating(function (FactoriesEntityStub $stub){
            self::$afterCreatingInstances[] = $stub;
        });
    }

    /**
     * @return static
     */
    public function suspended()
    {
        return $this->state(function (array $attributes) {
            return [
                'id' => -1 * $attributes['id'],
                'name' => "{$attributes['name']} suspended",
            ];
        });
    }

    public function definition()
    {
        return [
            'id' => $this->faker->unique()->randomNumber(),
            'name' => 'name',
            'lastName' => 'lastName',
        ];
    }
}
