<?php

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use LaravelDoctrine\ORM\Extensions\TablePrefix\TablePrefixListener;
use Mockery as m;

class TablePrefixListenerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ClassMetadataInfo
     */
    protected $metadata;

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $objectManager;

    /**
     * @var LoadClassMetadataEventArgs
     */
    protected $args;

    public function setUp()
    {
        $this->metadata = new ClassMetadataInfo('\Foo');
        $this->metadata->setTableName('foo');

        $this->objectManager = m::mock('Doctrine\Common\Persistence\ObjectManager');
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
}
