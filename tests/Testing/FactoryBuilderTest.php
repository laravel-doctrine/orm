<?php

namespace {

    use Doctrine\Common\Collections\ArrayCollection;
    use Doctrine\Common\Persistence\ManagerRegistry;
    use Doctrine\ORM\EntityManager;
    use Doctrine\ORM\EntityManagerInterface;
    use Doctrine\ORM\Tools\Setup;
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

        protected function getFactoryBuilder(array $definitions = []): FactoryBuilder
        {
            return new FactoryBuilder(
                $this->aRegistry,
                $this->aClass,
                $this->aName,
                array_merge($this->definitions, $definitions),
                $this->faker
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
}
namespace Faker {
    interface Generator
    {
    }
}
