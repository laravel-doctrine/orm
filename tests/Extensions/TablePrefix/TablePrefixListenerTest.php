<?php

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use LaravelDoctrine\ORM\Extensions\TablePrefix\TablePrefixListener;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class TablePrefixListenerTest extends TestCase
{
    /**
     * @var ClassMetadataInfo
     */
    protected $metadata;

    /**
     * @var \Doctrine\Persistence\ObjectManager
     */
    protected $objectManager;

    /**
     * @var LoadClassMetadataEventArgs
     */
    protected $args;

    public function setUp()
    {
        $this->metadata = new ClassMetadataInfo('\Foo');
        $this->metadata->setPrimaryTable(['name' => 'foo']);

        $this->objectManager = m::mock('Doctrine\Persistence\ObjectManager');
        $this->args          = new LoadClassMetadataEventArgs($this->metadata, $this->objectManager);
    }

    public function test_prefix_was_added()
    {
        $tablePrefix = new TablePrefixListener('someprefix_');
        $tablePrefix->loadClassMetadata($this->args);
        $this->assertEquals('someprefix_foo', $this->metadata->getTableName());
    }

    public function test_prefix_was_added_to_sequence()
    {
        $this->metadata->setSequenceGeneratorDefinition(['sequenceName' => 'bar']);
        $this->metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_SEQUENCE);
        $tablePrefix = new TablePrefixListener('someprefix_');
        $tablePrefix->loadClassMetadata($this->args);
        $this->assertEquals('someprefix_foo', $this->metadata->getTableName());
    }

    public function test_many_to_many_has_prefix()
    {
        $this->metadata->mapManyToMany(['fieldName' => 'fooBar', 'targetEntity' => 'bar']);
        $tablePrefix = new TablePrefixListener('someprefix_');
        $tablePrefix->loadClassMetadata($this->args);
        $this->assertEquals('someprefix_foo', $this->metadata->getTableName());
        $this->assertEquals('someprefix_foo_bar', $this->metadata->associationMappings['fooBar']['joinTable']['name']);
    }

    public function test_many_to_many_in_parent_class_with_prefix()
    {
        $baseMetadata = new ClassMetadataInfo('\Base');
        $baseMetadata->setPrimaryTable(['name' => 'base']);
        $baseMetadata->mapManyToMany(['fieldName' => 'fooBar', 'targetEntity' => 'bar']);
        $tablePrefix = new TablePrefixListener('someprefix_');
        $tablePrefix->loadClassMetadata(new LoadClassMetadataEventArgs($baseMetadata, $this->objectManager));
        //simulating method Doctrine\ORM\Mapping\ClassMetadataFactory:addInheritedRelations
        $baseMetadata->associationMappings['fooBar']['inherited'] = '\Base';
        $this->metadata->addInheritedAssociationMapping($baseMetadata->associationMappings['fooBar']);
        $tablePrefix->loadClassMetadata($this->args);
        $this->assertEquals('someprefix_foo', $this->metadata->getTableName());
        $this->assertEquals('someprefix_base_bar', $this->metadata->associationMappings['fooBar']['joinTable']['name']);
    }
}
