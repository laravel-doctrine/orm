# Connections

Out of the box the database connections configured in `database.php` config are supported:

* mysql 
* sqlite 
* pqsql 
* sqlsrv 
* oci8

Please note that read/write connections are supported as well. Check out [laravel documentation](https://laravel.com/docs/database#read-and-write-connections) for more details.

By simply changing the `DB_CONNECTION` env variable you swap the database connection for Doctrine as well.
The additional settings per connection are applied by default.

## Extending or Adding Connections Drivers

You can replace existing connection drivers or add custom drivers using the `LaravelDoctrine\ORM\Configuration\Connections\ConnectionManager`. Should return an array of parameters.

```php
public function boot(ConnectionManager $connections) {
    $connections->extend('myDriver', function(array $settings, \Illuminate\Contracts\Container\Container $container) {
        return [
            'driver' => 'driver',
            'host'   => ...
        ];
    });
}
```

You can find the available connection parameters inside the Doctrine documentation: http://doctrine-dbal.readthedocs.org/en/latest/reference/configuration.html
