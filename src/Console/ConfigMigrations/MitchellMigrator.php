<?php

namespace LaravelDoctrine\ORM\ConfigMigrations;

use Doctrine\ORM;
use LaravelDoctrine\ORM\Utilities\ArrayUtil;
use Philo\Blade\Blade;

class MitchellMigrator implements ConfigurationMigrator
{
    private $defaultConfig;

    public function __construct($defaultConfig)
    {
        $this->blade = new Blade('src/Console/ConfigMigrations/templates/mitchell', 'src/Console/ConfigMigrations/templates');
        $this->defaultConfig = $defaultConfig;
    }

    public function convertConfiguration($sourceArray)
    {
        //determine if configuration is from FoxxMD fork or original Mitchell repo
        $isFoxxMD = ArrayUtil::get($sourceArray['entity_managers']) !== null;

        $managers = [];
        $cache = '';
        $dqls = null;

        //$config['dev'] = config('app.debug');

        if ($isFoxxMD) {
            foreach ($sourceArray['entity_managers'] as $key => $manager) {
                $managers[$key] = $this->convertManager($manager, $isFoxxMD);
            }
        } else {
            $managers['default'] = $this->convertManager($sourceArray, $isFoxxMD);
        }

        //$config['meta']         = $this->defaultConfig['meta'];
        //$config['extensions']   = $this->defaultConfig['extensions'];
        //$config['custom_types'] = $this->defaultConfig['custom_types'];

        if ($isFoxxMD) {
            $dqls = $this->convertDQL($sourceArray['entity_managers']);
            /*            if (!empty($dqls)) {
                            array_push($config, $dqls);
                        }*/
        }

        //$config['debugbar'] = $this->defaultConfig['debugbar'];
        $cache    = $this->convertCache($sourceArray);

        $results = $this->blade->view()->make('defaults', ['managers' => $managers, 'cache' => $cache, 'dqls' => $dqls])->render();
        $unescaped = html_entity_decode($results, ENT_QUOTES);

        return $unescaped;
    }

    public function convertManager($sourceArray, $isFoxxMD)
    {
        $results = $this->blade->view()->make('manager', ['data' => $sourceArray, 'isFork' => $isFoxxMD])->render();
        $unescaped = html_entity_decode($results, ENT_QUOTES);
        return $unescaped;
        /*        return [
                    'meta'       => $isFoxxMD ? $sourceArray['metadata']['driver'] : 'annotations',
                    'connection' => $isFoxxMD ? $sourceArray['connection'] : config('database.default'),
                    'paths'      => ArrayUtil::get($sourceArray['metadata']['paths'], $sourceArray['metadata']),
                    'repository' => ArrayUtil::get($sourceArray['repository'], ORM\EntityRepository::class),
                    'proxies'    => [
                        'namespace'     => ArrayUtil::get($sourceArray['proxy']['namespace'], false),
                        'path'          => ArrayUtil::get($sourceArray['proxy']['directory'], storage_path('proxies')),
                        'auto_generate' => ArrayUtil::get($sourceArray['proxy']['auto_generate'], env('DOCTRINE_PROXY_AUTOGENERATE', false))
                    ]
                ];*/
    }

    public function convertCache($sourceArray)
    {
        $cacheProvider = ArrayUtil::get($sourceArray['cache_provider']);
        $results = $this->blade->view()->make('cache', ['cacheProvider' => $cacheProvider])->render();
        $unescaped = html_entity_decode($results, ENT_QUOTES);
        return $unescaped;
        /*        return [
                    'default'      => $cacheProvider == null ? config('cache.default') : config('cache.' . $cacheProvider),
                    'second_level' => false,
                ];*/
    }

    public function convertDQL($sourceManagers)
    {
        $dqls = [];
        foreach ($sourceManagers as $manager) {
            if (($dql = ArrayUtil::get($manager['dql'])) !== null) {
                if (($dt = ArrayUtil::get($manager['dql']['datetime_functions'])) !== null) {
                    array_push($dqls['custom_datetime_functions'], $dt);
                }
                if (($num = ArrayUtil::get($manager['dql']['numeric_functions'])) !== null) {
                    array_push($dqls['custom_numeric_functions'], $num);
                }
                if (($string = ArrayUtil::get($manager['dql']['string_functions'])) !== null) {
                    array_push($dqls['custom_string_functions'], $string);
                }
            }
        }

        if(!empty($dqls)){
            $results = $this->blade->view()->make('dql', ['dql' => $dqls])->render();
            $unescaped = html_entity_decode($results, ENT_QUOTES);
            return $unescaped;
        } else {
            return null;
        }

    }
}
