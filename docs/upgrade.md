# Upgrade Guide

- [Upgrading from older packages](#upgrade-older)
- [Upgrading from atrauzzi/laravel-doctrine](#upgrade-atrauzzi)
- [Upgrading from mitchellvanw/laravel-doctrine](#upgrade-mitchellvanw)

## <a name="upgrade-older"></a> Upgrading from older packages

An artisan command was added to make the upgrade path a bit lighter.

`php artisan doctrine:config:convert [author] [--source-file] [--dest-path]`

To learn more about flags and how it works see [Migrating Config Files From Other Packages](/docs/{{version}}/orm/config-migrator).

> **NOTE:** This tool is meant to be used AS A STARTING POINT for converting your configuration. In most cases you will still need to inspect and modify the generated configuration to suite your needs.

## <a name="upgrade-atrauzzi"></a> Upgrading from atrauzzi/laravel-doctrine

If you have used [atrauzzi/laravel-doctrine](https://github.com/atrauzzi/laravel-doctrine) in the past. You can easily update your config file using the following artisan command, a new `doctrine.php` config file will be generated based on your old config file:

`php artisan doctrine:config:convert atrauzzi [--source-file] [--dest-path]`

## <a name="upgrade-mitchellvanw"></a> Upgrading from mitchellvanw/laravel-doctrine

If you have used [mitchellvanw/laravel-doctrine](https://github.com/mitchellvanw/laravel-doctrine) in the past. You can easily update your config file using the following artisan command, a new `doctrine.php` config file will be generated based on your old config file:

`php artisan doctrine:config:convert mitchell [--source-file] [--dest-path]`
