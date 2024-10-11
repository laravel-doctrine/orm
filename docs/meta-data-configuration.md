# Meta Data

### Annotations

This package supports Doctrine annotations meta data and can be enabled inside the config. 

### XML

This package supports Doctrine xml meta data and can be enabled inside the config.
 
### SimplifiedXML
 
This package supports simplified Doctrine xml meta data and can be enabled inside the config. 

The format of the `paths` config value in `doctrine.php` config differs sligthly from the default. The path should be passed as key, the namespace as value.

```
'paths' => [
    '/path/to/files1' => 'MyProject\Entities',
    '/path/to/files2' => 'OtherProject\Entities'
],
```

Check the Doctrine documentation for more information: https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/xml-mapping.html#simplified-xml-driver

### YAML

> **NOTE:** The YAML driver is deprecated and will be removed in Doctrine 3.0.

This package supports Doctrine yml meta data and can be enabled inside the config. 

### SimplifiedYAML
 
 > **NOTE:** The YAML driver is deprecated and will be removed in Doctrine 3.0.
 
This package supports simplified Doctrine yml meta data and can be enabled inside the config. 

The format of the `paths` config value in `doctrine.php` config differs sligthly from the default. The path should be passed as key, the namespace as value.

```
'paths' => [
    '/path/to/files1' => 'MyProject\Entities',
    '/path/to/files2' => 'OtherProject\Entities'
],
```

Check the Doctrine documentation for more information: https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/yaml-mapping.html#simplified-yaml-driver

### StaticPhp

This package supports static PHP (`static_php`) meta data and can be enabled inside the config. 

### Config

This package supports using config meta data and can be enabled inside the config.

## Extending or Adding Metadata Drivers
Drivers can be replaced or added using `LaravelDoctrine\ORM\Configuration\MetaData\MetaDataManager`. The callback should return an instance of `\Doctrine\Common\Persistence\Mapping\Driver\MappingDriver`

```php
public function boot(MetaDataManager $metadata) {
    $metadata->extend('myDriver', function(Application $app) {
        return new FluentDriver();
    });
}
```
