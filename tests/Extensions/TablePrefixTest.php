<?php

use Brouwers\LaravelDoctrine\Extensions\TablePrefix\TablePrefixListener;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Mockery as m;

class TablePrefixTest extends \PHPUnit_Framework_TestCase
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

    public function testPrefixAdded()
    {
        $tablePrefix = new TablePrefixListener('someprefix_');
        $tablePrefix->loadClassMetadata($this->args);
        $this->assertEquals('someprefix_foo', $this->metadata->getTableName());
    }

    public function testPrefixAddedToSequence()
    {
        $this->metadata->setSequenceGeneratorDefinition(['sequenceName' => 'bar']);
        $this->metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_SEQUENCE);
        $tablePrefix = new TablePrefixListener('someprefix_');
        $tablePrefix->loadClassMetadata($this->args);
        $this->assertEquals('someprefix_foo', $this->metadata->getTableName());
    }

    public function testManyToManyHasPrefix()
    {
        $this->metadata->mapManyToMany(['fieldName' => 'fooBar', 'targetEntity' => 'bar']);
        $tablePrefix = new TablePrefixListener('someprefix_');
        $tablePrefix->loadClassMetadata($this->args);
        $this->assertEquals('someprefix_foo', $this->metadata->getTableName());
        $this->assertEquals('someprefix_foo_bar', $this->metadata->associationMappings['fooBar']['joinTable']['name']);
    }
}
