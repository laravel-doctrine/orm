<?php

use Illuminate\Support\Str;
use LaravelDoctrine\ORM\Configuration\LaravelNamingStrategy;

class LaravelNamingStrategyTest extends PHPUnit_Framework_TestCase
{
    /**
     * @type LaravelNamingStrategy
     */
    protected $strategy;

    public function setUp()
    {
        $this->strategy = new LaravelNamingStrategy(new Str());
    }

    public function test_class_to_table_name()
    {
        $className = 'Acme\\ClassName';

        $tableName = $this->strategy->classToTableName($className);

        // Plural, snake_cased table name
        $this->assertEquals('class_names', $tableName);
    }

    public function test_property_to_column_name()
    {
        // Columns derive from snakeCased fields
        $field = 'createdAt';

        $columnName = $this->strategy->propertyToColumnName($field);

        // And columns are just the snake_cased field
        $this->assertEquals('created_at', $columnName);
    }

    public function test_property_to_column_name_with_class_name()
    {
        // Columns derive from snakeCased fields
        $field = 'createdAt';

        // Singular namespaced StudlyCase class
        $className = 'Acme\\ClassName';

        $columnName = $this->strategy->propertyToColumnName($field, $className);

        // Class name shouldn't affect how the column is called
        $this->assertEquals('created_at', $columnName);
    }

    public function test_embedded_field_to_column_name()
    {
        // Laravel doesn't have embeddeds
        $embeddedField = 'address';
        $field         = 'street1';

        $columnName = $this->strategy->embeddedFieldToColumnName($embeddedField, $field);

        // So this is just like Doctrine's default naming strategy
        $this->assertEquals('address_street1', $columnName);
    }

    public function test_reference_column_name()
    {
        // Laravel's convention is just 'id', like the default Doctrine
        $columnName = $this->strategy->referenceColumnName();

        $this->assertEquals('id', $columnName);
    }

    public function test_join_column_name()
    {
        // Given a User -> belongsTo -> Group
        $field = 'group';

        $columnName = $this->strategy->joinColumnName($field);

        // We expect to have a group_id in the users table
        $this->assertEquals('group_id', $columnName);
    }

    public function test_belongs_to_many_join_table()
    {
        // Laravel doesn't do as Doctrine's default here
        $sourceModel = 'Acme\\ClassName';

        // We don't care about "source" or "target"
        $targetModel = 'Acme\\AnotherClass';

        // We should have it sorted by alphabetical order
        $tableName = $this->strategy->joinTableName($sourceModel, $targetModel);
        $this->assertEquals('another_class_class_name', $tableName);

        // Let's test swapping parameters, just in case...
        $tableName = $this->strategy->joinTableName($targetModel, $sourceModel);
        $this->assertEquals('another_class_class_name', $tableName);
    }

    public function test_join_column_names()
    {
        // This case is similar to Doctrine's default as well
        $className = 'Acme\\Foo';

        // If no reference name is given, we use 'id'
        $columnName = $this->strategy->joinKeyColumnName($className);

        // And expect singular_snake_id column
        $this->assertEquals('foo_id', $columnName);

        // Given a reference name
        $columnName = $this->strategy->joinKeyColumnName($className, 'reference');

        // Same thing, but with that reference instead of 'id'
        $this->assertEquals('foo_reference', $columnName);
    }
}
