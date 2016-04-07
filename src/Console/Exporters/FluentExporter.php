<?php

namespace LaravelDoctrine\ORM\Console\Exporters;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Tools\Export\Driver\AbstractExporter;

class FluentExporter extends AbstractExporter
{
    /**
     * @var string
     */
    protected $_extension = '.php';

    /**
     * @const
     */
    const TAB = '    ';

    /**
     * {@inheritdoc}
     */
    public function exportClassMetadata(ClassMetadataInfo $metadata)
    {
        $lines   = [];
        $lines[] = '<?php';
        $lines[] = null;
        $lines[] = 'namespace App\Mappings;';
        $lines[] = null;

        foreach ($this->getImports($metadata) as $import) {
            $lines[] = $import;
        }

        $lines[] = null;
        $lines[] = 'class ' . $this->getClassNameShort($metadata) . 'Mapping extends ' . $this->getExtendShort($metadata) . ' {';
        $lines[] = null;

        foreach ($this->exportMapFor($metadata) as $line) {
            $lines[] = $line;
        }

        $lines[] = null;

        foreach ($this->exportMap($metadata) as $line) {
            $lines[] = $line;
        }

        $lines[] = '}';

        return implode("\n", $lines);
    }

    /**
     * @param mixed $var
     *
     * @return string
     */
    protected function _varExport($var)
    {
        $export = var_export($var, true);
        $export = str_replace("\n", PHP_EOL . str_repeat(' ', 8), $export);
        $export = str_replace('  ', ' ', $export);
        $export = str_replace('array (', 'array(', $export);
        $export = str_replace('array( ', 'array(', $export);
        $export = str_replace(',)', ')', $export);
        $export = str_replace(', )', ')', $export);
        $export = str_replace('  ', ' ', $export);

        return $export;
    }

    /**
     * @param  ClassMetadataInfo $metadata
     * @return string
     */
    protected function getClassName(ClassMetadataInfo $metadata)
    {
        return ltrim(str_replace($metadata->namespace, '', $metadata->name), '\\');
    }

    /**
     * @param ClassMetadataInfo $metadata
     *
     * @return string
     */
    private function getExtend(ClassMetadataInfo $metadata)
    {
        if ($metadata->isMappedSuperclass) {
            return 'LaravelDoctrine\Fluent\MappedSuperClassMapping';
        } elseif ($metadata->isEmbeddedClass) {
            return 'LaravelDoctrine\Fluent\EmbeddableMapping';
        } else {
            return 'LaravelDoctrine\Fluent\EntityMapping';
        }
    }

    /**
     * @param  ClassMetadataInfo $metadata
     * @return mixed
     */
    private function getExtendShort(ClassMetadataInfo $metadata)
    {
        return $this->classBaseName($this->getExtend($metadata));
    }

    /**
     * @param  string $className
     * @return string
     */
    private function classBaseName($className)
    {
        if (strpos($className, '\\') === false) {
            return $className;
        }

        return substr($className, strrpos($className, '\\') + 1);
    }

    /**
     * @param  ClassMetadataInfo $metadata
     * @return array
     */
    private function getImports(ClassMetadataInfo $metadata)
    {
        $extend = $this->getExtend($metadata);

        $imports = [
            'LaravelDoctrine\Fluent\Fluent',
            'Doctrine\ORM\Mapping\ClassMetadataInfo',
            $extend,
            $metadata->name
        ];

        // Sort alphabetically
        asort($imports);

        return array_map(function ($import) {
            return 'use ' . $import . ';';
        }, $imports);
    }

    /**
     * @param  ClassMetadataInfo $metadata
     * @return array
     */
    protected function exportMapFor(ClassMetadataInfo $metadata)
    {
        $lines = [
            '/**',
            ' *',
            ' * Returns the fully qualified name of the class that this mapper maps.',
            ' *',
            ' * @return string',
            ' */',
            'public function mapFor()',
            '{',
            self::TAB . 'return ' . $this->getClassNameShort($metadata) . '::class;',
            '}',
        ];

        return array_map(function ($line) {
            return self::TAB . $line;
        }, $lines);
    }

    /**
     * @param  ClassMetadataInfo $metadata
     * @return array
     */
    private function exportMap(ClassMetadataInfo $metadata)
    {
        $lines = [
            '/**',
            ' *',
            ' * Load the object\'s metadata through the Metadata Builder object.',
            ' *',
            ' * @param Fluent $builder',
            ' */',
            'public function map(Fluent $builder)',
            '{',
        ];

        foreach ($this->exportMapMethodContent($metadata) as $line) {
            $lines[] = $line;
        }

        $lines[] = '}';

        return array_map(function ($line) {
            return self::TAB . $line;
        }, $lines);
    }

    /**
     * @param  ClassMetadataInfo $metadata
     * @return array
     */
    private function exportMapMethodContent(ClassMetadataInfo $metadata)
    {
        if ($metadata->customRepositoryClassName) {
            $lines[] = "\$builder->entity()->setRepositoryClass('" . $metadata->customRepositoryClassName . ")';";
        }

        if ($metadata->table) {
            $lines[] = '$builder->table(\'' . $metadata->table['name'] . '\');';
        }

        if ($metadata->inheritanceType && $this->_getInheritanceTypeString($metadata->inheritanceType) !== 'NONE') {
            $dColumn = '';
            if ($metadata->discriminatorColumn) {
                $dColumn = '->column(\'' . $metadata->discriminatorColumn['name'] . '\', \'' . $metadata->discriminatorColumn['type'] . '\', \'' . $metadata->discriminatorColumn['length'] . '\')';
            }

            $dMap = '';
            if ($metadata->discriminatorMap) {
                $dMap = '->map(' . $this->_varExport($metadata->discriminatorMap) . ')';
            }

            $lines[] = '$builder->inheritance(ClassMetadataInfo::INHERITANCE_TYPE_' . $this->_getInheritanceTypeString($metadata->inheritanceType) . ')' . $dColumn . $dMap . ';';
        }

        if ($metadata->lifecycleCallbacks) {
            foreach ($metadata->lifecycleCallbacks as $event => $callbacks) {
                foreach ($callbacks as $callback) {
                    $lines[] = "\$builder->events()->$event('$callback');";
                }
            }
        }

        $lines[] = null;

        foreach ($metadata->fieldMappings as $fieldMapping) {
            $lines[] = $this->convertField($metadata, $fieldMapping);
        }

        $lines[] = null;

        foreach ($metadata->associationMappings as $associationMapping) {
            $lines[] = $this->exportAssociations($associationMapping);
        }

        return array_map(function ($line) {
            return self::TAB . $line;
        }, $lines);
    }

    /**
     * @param  ClassMetadataInfo $metadata
     * @param                    $fieldMapping
     * @return array
     */
    private function convertField(ClassMetadataInfo $metadata, $fieldMapping)
    {
        $type = $fieldMapping['type'];
        $name = $fieldMapping['fieldName'];

        $column = '';
        if (isset($fieldMapping['columnName']) && $fieldMapping['columnName'] != $name) {
            $column = $fieldMapping['columnName'];
            $column = "->name('$column')";
        }

        $length = '';
        if (isset($fieldMapping['length']) && !is_null($fieldMapping['length'])) {
            $length = $fieldMapping['length'];
            $length = "->length($length)";
        }

        $nullable = '';
        if (isset($fieldMapping['nullable']) && $fieldMapping['nullable'] === true) {
            $nullable = "->nullable()";
        }

        $unique = '';
        if (isset($fieldMapping['unique']) && $fieldMapping['unique'] === true) {
            $unique = "->unique()";
        }

        $primary = '';
        if (isset($fieldMapping['id']) && $fieldMapping['id'] === true) {
            $primary = "->primary()";
        }

        $scale = '';
        if (isset($fieldMapping['scale']) && $fieldMapping['scale'] != 0) {
            $scale = $fieldMapping['scale'];
            $scale = "->scale($scale)";
        }

        $precision = '';
        if (isset($fieldMapping['precision']) && $fieldMapping['precision'] != 0) {
            $precision = $fieldMapping['precision'];
            $precision = "->precision($precision)";
        }

        $generatedValue = '';
        if (isset($fieldMapping['id']) && $fieldMapping['id'] === true && !$metadata->isIdentifierComposite && $generatorType = $this->_getIdGeneratorTypeString($metadata->generatorType)) {
            $type    = 'increments';
            $primary = false;

            if ($generatorType != 'AUTO' && $generatorType != 'IDENTITY') {
                $generatorType  = strtolower($generatorType);
                $generatedValue = '->generatedValue()->' . $generatorType . '()';
            }
        }

        if ($type == 'smallint') {
            $type = 'smallInteger';
        }

        return "\$builder->$type('$name')$column$length$nullable$unique$primary$scale$precision$generatedValue;";
    }

    /**
     * @param  array $associationMapping
     * @return array
     */
    private function exportAssociations(array $associationMapping)
    {
        $cascade = ['remove', 'persist', 'refresh', 'merge', 'detach'];
        foreach ($cascade as $key => $value) {
            if (!$associationMapping['isCascade' . ucfirst($value)]) {
                unset($cascade[$key]);
            }
        }

        if (count($cascade) === 5) {
            $cascade = ['all'];
        }

        $fieldName    = $associationMapping['fieldName'];
        $targetEntity = $associationMapping['targetEntity'];
        $fetch        = isset($associationMapping['fetch']) ? $associationMapping['fetch'] : '';

        $mappedBy      = '';
        $inversedBy    = '';
        $joinColumns   = [];
        $joinTable     = '';
        $orphanRemoval = '';
        $orderBy       = '';

        if ($associationMapping['type'] & ClassMetadataInfo::ONE_TO_ONE) {
            $method        = 'oneToOne';
            $mappedBy      = $associationMapping['mappedBy'];
            $inversedBy    = $associationMapping['inversedBy'];
            $joinColumns   = $associationMapping['joinColumns'];
            $orphanRemoval = $associationMapping['orphanRemoval'];
        } elseif ($associationMapping['type'] & ClassMetadataInfo::MANY_TO_ONE) {
            $method        = 'manyToOne';
            $mappedBy      = $associationMapping['mappedBy'];
            $inversedBy    = $associationMapping['inversedBy'];
            $joinColumns   = $associationMapping['joinColumns'];
            $orphanRemoval = $associationMapping['orphanRemoval'];
        } elseif ($associationMapping['type'] == ClassMetadataInfo::ONE_TO_MANY) {
            $method                             = 'oneToMany';
            $potentialAssociationMappingIndexes = [
                'mappedBy',
                'orphanRemoval',
                'orderBy',
            ];
            foreach ($potentialAssociationMappingIndexes as $index) {
                if (isset($associationMapping[$index])) {
                    $oneToManyMappingArray[$index] = $associationMapping[$index];
                }
            }

            if (isset($oneToManyMappingArray['mappedBy'])) {
                $mappedBy = $oneToManyMappingArray['mappedBy'];
            }
            if (isset($oneToManyMappingArray['orphanRemoval'])) {
                $orphanRemoval = $oneToManyMappingArray['orphanRemoval'];
            }
            if (isset($oneToManyMappingArray['orderBy'])) {
                $orderBy = $oneToManyMappingArray['orderBy'];
            }
        } elseif ($associationMapping['type'] == ClassMetadataInfo::MANY_TO_MANY) {
            $method                             = 'manyToMany';
            $potentialAssociationMappingIndexes = [
                'mappedBy',
                'joinTable',
                'orderBy',
            ];
            foreach ($potentialAssociationMappingIndexes as $index) {
                if (isset($associationMapping[$index])) {
                    $manyToManyMappingArray[$index] = $associationMapping[$index];
                }
            }

            if (isset($manyToManyMappingArray['mappedBy'])) {
                $mappedBy = $manyToManyMappingArray['mappedBy'];
            }
            if (isset($manyToManyMappingArray['joinTable'])) {
                $joinTable = $manyToManyMappingArray['joinTable'];
            }
            if (isset($manyToManyMappingArray['orderBy'])) {
                $orderBy = $manyToManyMappingArray['orderBy'];
            }
        }

        if ($mappedBy != '') {
            $mappedBy = "->mappedBy('$mappedBy')";
        }

        if ($inversedBy != '') {
            $inversedBy = "->inversedBy('$inversedBy')";
        }

        if ($fetch != '') {
            switch ($fetch) {

                case ClassMetadataInfo::FETCH_LAZY:
                    $fetch = ''; // Lazy is default anyway
                    break;

                case ClassMetadataInfo::FETCH_EAGER:
                    $fetch = "->fetchEager()";
                    break;

                case ClassMetadataInfo::FETCH_EXTRA_LAZY:
                    $fetch = "->fetchExtraLazy()";
                    break;
            }
        }

        $joinColumn = '';
        if ($joinColumns) {
            foreach ($joinColumns as $c) {
                $columnName           = $c['name'];
                $referencedColumnName = $c['referencedColumnName'];
                $nullable             = !isset($c['nullable']) || $c['nullable'] == true ? 'true' : 'false';
                $unique               = !isset($c['unique']) || $c['nullable'] == false ? 'false' : 'true';
                $onDelete             = isset($c['onDelete']) ? '\'' . $c['onDelete'] . '\'' : 'null';
                $columnDefinition     = isset($c['columnDefinition']) ? '\'' . $c['columnDefinition'] . '\'' : 'null';

                $joinColumn .= "->addJoinColumn('$fieldName', '$columnName', '$referencedColumnName', $nullable, $unique, $onDelete, $columnDefinition)";
            }
        }

        if ($orphanRemoval != '' && $orphanRemoval == true) {
            $orphanRemoval = "->orphanRemoval()";
        }

        $cascadeChain = '';
        if (count($cascade) > 0) {
            foreach ($cascade as $option) {
                $cascadeChain .= '->cascade' . ucfirst($option) . '()';
            }
        }

        $joinTableChain = '';
        if ($joinTable != '') {
            if (isset($joinTable['name'])) {
                $joinTableChain .= '->joinTable(\'' . $joinTable['name'] . '\')';
            }
        }

        return "\$builder->$method('$targetEntity', '$fieldName')$mappedBy$inversedBy$cascadeChain$fetch$joinColumn$orphanRemoval$orderBy$joinTableChain;";
    }

    private function getClassNameShort(ClassMetadataInfo $metadata)
    {
        return $this->classBaseName($metadata->name);
    }
}
