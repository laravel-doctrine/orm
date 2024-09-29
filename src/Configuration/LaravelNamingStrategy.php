<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Configuration;

use Doctrine\ORM\Mapping\NamingStrategy;
use Illuminate\Support\Str;

use function class_basename;
use function implode;
use function sort;

class LaravelNamingStrategy implements NamingStrategy
{
    public function __construct(protected Str $str)
    {
    }

    /**
     * Returns a table name for an entity class.
     *
     * @param string $className The fully-qualified class name.
     *
     * @return string A table name.
     */
    public function classToTableName(string $className): string
    {
        return $this->str->plural($this->classToFieldName($className));
    }

    /**
     * Returns a column name for a property.
     *
     * @param string      $propertyName A property name.
     * @param string|null $className    The fully-qualified class name.
     *
     * @return string A column name.
     */
    public function propertyToColumnName(string $propertyName, string|null $className = null): string
    {
        return $this->str->snake($propertyName);
    }

    /**
     * Returns the default reference column name.
     *
     * @return string A column name.
     */
    public function referenceColumnName(): string
    {
        return 'id';
    }

    /**
     * Returns a join column name for a property.
     *
     * @param string $propertyName A property name.
     *
     * @return string A join column name.
     */
    public function joinColumnName(string $propertyName, string $className): string
    {
        return $this->str->snake($this->str->singular($propertyName)) . '_' . $this->referenceColumnName();
    }

    /**
     * Returns a join table name.
     *
     * @param string      $sourceEntity The source entity.
     * @param string      $targetEntity The target entity.
     * @param string|null $propertyName A property name.
     *
     * @return string A join table name.
     */
    public function joinTableName(string $sourceEntity, string $targetEntity, string|null $propertyName = null): string
    {
        $names = [
            $this->classToFieldName($sourceEntity),
            $this->classToFieldName($targetEntity),
        ];

        sort($names);

        return implode('_', $names);
    }

    /**
     * Returns the foreign key column name for the given parameters.
     *
     * @param string      $entityName           An entity.
     * @param string|null $referencedColumnName A property.
     *
     * @return string A join column name.
     */
    public function joinKeyColumnName(string $entityName, string|null $referencedColumnName = null): string
    {
        return $this->classToFieldName($entityName) . '_' .
        ($referencedColumnName ?: $this->referenceColumnName());
    }

    protected function classToFieldName(string $className): string
    {
        return $this->str->snake(class_basename($className));
    }

    /**
     * Returns a column name for an embedded property.
     *
     * @param class-string $className
     * @param class-string $embeddedClassName
     */
    public function embeddedFieldToColumnName(
        string $propertyName,
        string $embeddedColumnName,
        string $className,
        string $embeddedClassName,
    ): string {
        return $propertyName . '_' . $embeddedColumnName;
    }
}
