<?php

use Brouwers\LaravelDoctrine\Configuration\LaravelNamingStrategy;
use Illuminate\Support\Str;

class LaravelNamingStrategyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @type LaravelNamingStrategy
     */
    protected $laravelNamingStrategy;

    public function setUp()
    {
        $this->laravelNamingStrategy = new LaravelNamingStrategy(new Str());
    }

    public function testProperTableName()
    {
        $className = 'Acme\\ClassName';

        $tableName = $this->laravelNamingStrategy->classToTableName($className);

        // Plural, snake_cased table name
        $this->assertEquals('class_names', $tableName);
    }

    public function testProperColumnName()
    {
        // Columns derive from snakeCased fields
        $field = 'createdAt';

        $columnName = $this->laravelNamingStrategy->propertyToColumnName($field);

        // And columns are just the snake_cased field
        $this->assertEquals('created_at', $columnName);
    }

    public function testProperColumnNameWithClassName()
    {
        // Columns derive from snakeCased fields
        $field = 'createdAt';

        // Singular namespaced StudlyCase class
        $className = 'Acme\\ClassName';

        $columnName = $this->laravelNamingStrategy->propertyToColumnName($field, $className);

        // Class name shouldn't affect how the column is called
        $this->assertEquals('created_at', $columnName);
    }

    public function testEmbeddedColumnName()
    {
        // Laravel doesn't have embeddeds
        $embeddedField = 'address';
        $field         = 'street1';

        $columnName = $this->laravelNamingStrategy->embeddedFieldToColumnName($embeddedField, $field);

        // So this is just like Doctrine's default naming strategy
        $this->assertEquals('address_street1', $columnName);
    }

    public function testReferenceColumn()
    {
        // Laravel's convention is just 'id', like the default Doctrine
        $columnName = $this->laravelNamingStrategy->referenceColumnName();

        $this->assertEquals('id', $columnName);
    }

    public function testJoinColumnName()
    {
        // Given a User -> belongsTo -> Group
        $field = 'group';

        $columnName = $this->laravelNamingStrategy->joinColumnName($field);

        // We expect to have a group_id in the users table
        $this->assertEquals('group_id', $columnName);
    }

    public function testBelongsToManyJoinTable()
    {
        // Laravel doesn't do as Doctrine's default here
        $sourceModel = 'Acme\\ClassName';

        // We don't care about "source" or "target"
        $targetModel = 'Acme\\AnotherClass';

        // We should have it sorted by alphabetical order
        $tableName = $this->laravelNamingStrategy->joinTableName($sourceModel, $targetModel);
        $this->assertEquals('another_class_class_name', $tableName);

        // Let's test swapping parameters, just in case...
        $tableName = $this->laravelNamingStrategy->joinTableName($targetModel, $sourceModel);
        $this->assertEquals('another_class_class_name', $tableName);
    }

    public function testJoinKeyColumnName()
    {
        // This case is similar to Doctrine's default as well
        $className = 'Acme\\Foo';

        // If no reference name is given, we use 'id'
        $columnName = $this->laravelNamingStrategy->joinKeyColumnName($className);

        // And expect singular_snake_id column
        $this->assertEquals('foo_id', $columnName);

        // Given a reference name
        $columnName = $this->laravelNamingStrategy->joinKeyColumnName($className, 'reference');

        // Same thing, but with that reference instead of 'id'
        $this->assertEquals('foo_reference', $columnName);
    }
}
