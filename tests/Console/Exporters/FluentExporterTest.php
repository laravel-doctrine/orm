<?php

use LaravelDoctrine\ORM\Console\Exporters\FluentExporter;

class FluentExporterTest extends PHPUnit_Framework_TestCase
{
    public function test_it_exports_a_simple_class_metadata()
    {
        $entityName = "App\\Entities\\Foo";

        $metadata = new \Doctrine\ORM\Mapping\ClassMetadata($entityName);
        $metadata->mapField([
            'fieldName' => 'id',
            'type'      => 'integer',
            'id'        => true
        ]);

        $expected = <<<PHP
<?php

namespace App\Mappings;

use $entityName;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use LaravelDoctrine\Fluent\EntityMapping;
use LaravelDoctrine\Fluent\Fluent;

class FooMapping extends EntityMapping {

    /**
     *
     * Returns the fully qualified name of the class that this mapper maps.
     *
     * @return string
     */
    public function mapFor()
    {
        return Foo::class;
    }

    /**
     *
     * Load the object's metadata through the Metadata Builder object.
     *
     * @param Fluent \$builder
     */
    public function map(Fluent \$builder)
    {
        \$builder->integer('id')->primary();
    }
}
PHP;
        $exporter = new FluentExporter();
        $this->assertEquals(str_replace(["\r", "\n", ' '], '', $expected), str_replace(["\r", "\n", ' '], '', $exporter->exportClassMetadata($metadata)));
    }
}
