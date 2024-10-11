#Config File Migrator

Laravel Doctrine provides a command to help migrate the configuration from another Laravel/Doctrine package to Laravel Doctrine's format.

> This tool is meant to be used AS A STARTING POINT for converting your configuration. In most cases you will still need to inspect and modify the generated configuration to suite your needs.

## Supported Packages

| Package | Version |
|:----:|---|
|  [atrauzzi/laravel-doctrine](https://github.com/atrauzzi/laravel-doctrine)    | >=  [dfef4ad](https://github.com/atrauzzi/laravel-doctrine/commit/dfef4ad87801a746a45d94d944a996498086a137) |
| [mitchellvanw/laravel-doctrine](https://github.com/mitchellvanw/laravel-doctrine) | >= 0.5.0 or [FoxxMD's Fork](https://github.com/FoxxMD/laravel-doctrine) |


## Converting an Existing Configuration

You must have artisan installed in your project in order to use this command.

From the commandline usage is the following:

`php artisan doctrine:config:convert [author] [--source-file] [--dest-path]`

| Flag |Description|
|:----:|---|
|  `author`    |The author of the package migrating from. Available authors are: [mitchellvanw](https://github.com/mitchellvanw/laravel-doctrine) & [atrauzzi](https://github.com/atrauzzi/laravel-doctrine) |
| `--source-file` |Path to your existing configuration file from the root dir of your project. If not provided defaults to `config/doctrine.php`   |
|  `--dest-path`    |Path where the migrated configuration should be created. If not provided defaults to `config/`  |

If migration is successful the file `doctrine.generated.php` is created in the `dest-path` specified.

## Writing A Template for a Configuration

To create a new configuration file [blade templates](https://laravel.com/docs/master/blade) are used to create php code that is then rendered to a string and written to a file. Templates take in the original configuration as an array and output sections of the new configuration with the transformed values.

To create a template follow the following steps:

## Implement "ConfigurationMigrator"

### Implement the interface

First, create a new class that implements the interface [`LaravelDoctrine\ORM\ConfigMigrations`](https://github.com/laravel-doctrine/orm/blob/develop/src/Console/ConfigMigrations/ConfigurationMigrator.php)

### Write templates for each section of the config

**This is one way to use templates, but may not be the best for your scenario.** Use your discretion.

For each section in the configuration write a function that takes in the original configuration (or section of it) and provides it as an argument to a blade template.

**Example**

    /**
     * Convert an entity manager section from mitchellvanw/laravel-doctrine to a string representation of a php array configuration for an entity manager for this project
     *
     * @param array $sourceArray
     * @param boolean $isFork
     * @return string
     */
    public function convertManager($sourceArray, $isFork)
    {
        $results = $this->viewFactory->make('mitchell.manager', ['data' => $sourceArray, 'isFork' => $isFork])->render();
        $unescaped = html_entity_decode($results, ENT_QUOTES);
        return $unescaped;
    }

 Write the new section of the configuration using blade syntax to create patterns to iterate over (IE `foreach` over `entityManagers`).

**Example**

    [
        'meta' => '{{{ $isFork ? $data['metadata']['driver'] : 'annotations' }}}',
        'connection' => {{{ $isFork ? '\''.$data['connection'].'\'' : 'config("database.default")'  }}},
        'paths' => {{ var_export(ArrayUtil::get($data['metadata']['paths'], $data['metadata']), true) }},
        'repository' => '{{{ ArrayUtil::get($data['repository'], \LaravelDoctrine\ORM\EntityRepository::class) }}}',
        'proxies' => [
            'namespace' => {{{ isset($data['proxy']['namespace']) ? '\'' . $data['proxy']['namespace'] .'\'' : 'false' }}},
            'path'          => '{{{ ArrayUtil::get($data['proxy']['directory'], storage_path('proxies')) }}}',
            'auto_generate' => '{{{ ArrayUtil::get($data['proxy']['auto_generate'], env('DOCTRINE_PROXY_AUTOGENERATE', 'false')) }}}'
        ],
        'events'     => [
            'listeners'   => [],
            'subscribers' => []
        ],
        'filters' => []
    ]


**Use ["MitchellMigrator"](https://github.com/laravel-doctrine/orm/blob/master/src/Console/ConfigMigrations/MitchellMigrator.php) as a reference.**

## Add Your Migrator to "ConvertConfigCommand"

Finally, instantiate your Migrator and add a case for it in ["ConvertConfigCommand"](https://github.com/laravel-doctrine/orm/blob/master/src/Console/ConvertConfigCommand.php).
