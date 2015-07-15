<?php

namespace LaravelDoctrine\ORM\ConfigMigrations;

use Doctrine\ORM;
use LaravelDoctrine\ORM\Utilities\ArrayUtil;
use Philo\Blade\Blade;

class MitchellMigrator implements ConfigurationMigrator
{
    private $defaultConfig;

    public function __construct()
    {
        $this->blade = new Blade(realpath(__DIR__ . '/templates/mitchell'), realpath(__DIR__ . '/templates'));
    }

    public function convertConfiguration($sourceArray)
    {
        //determine if configuration is from FoxxMD fork or original Mitchell repo
        $isFoxxMD = ArrayUtil::get($sourceArray['entity_managers']) !== null;

        $managers = [];
        $cache = '';
        $dqls = null;

        if ($isFoxxMD) {
            foreach ($sourceArray['entity_managers'] as $key => $manager) {
                $managers[$key] = $this->convertManager($manager, $isFoxxMD);
            }
        } else {
            $managers['default'] = $this->convertManager($sourceArray, $isFoxxMD);
        }

        if ($isFoxxMD) {
            $dqls = $this->convertDQL($sourceArray['entity_managers']);
        }

        $cache    = $this->convertCache($sourceArray);

        $results = $this->blade->view()->make('master', ['managers' => $managers, 'cache' => $cache, 'dqls' => $dqls])->render();
        $unescaped = html_entity_decode($results, ENT_QUOTES);

        return $unescaped;
    }

    public function convertManager($sourceArray, $isFoxxMD)
    {
        $results = $this->blade->view()->make('manager', ['data' => $sourceArray, 'isFork' => $isFoxxMD])->render();
        $unescaped = html_entity_decode($results, ENT_QUOTES);
        return $unescaped;
    }

    public function convertCache($sourceArray)
    {
        $cacheProvider = ArrayUtil::get($sourceArray['cache_provider']);
        $results = $this->blade->view()->make('cache', ['cacheProvider' => $cacheProvider])->render();
        $unescaped = html_entity_decode($results, ENT_QUOTES);
        return $unescaped;
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
