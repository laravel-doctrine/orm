<?php

namespace LaravelDoctrine\ORM\ConfigMigrations;

use Doctrine\ORM;
use LaravelDoctrine\ORM\Utilities\ArrayUtil;
use View;

class MitchellMigrator implements ConfigurationMigrator
{
    public function __construct()
    {
        View::addLocation(realpath(__DIR__ . '/templates'));
        View::addNamespace('mitchell', realpath(__DIR__ . '/templates/mitchell'));
    }

    public function convertConfiguration($sourceArray)
    {
        //determine if configuration is from FoxxMD fork or original Mitchell repo
        $isFork = ArrayUtil::get($sourceArray['entity_managers']) !== null;

        $managers = [];
        $cache = '';
        $dqls = null;

        if ($isFork) {
            foreach ($sourceArray['entity_managers'] as $key => $manager) {
                $managers[$key] = $this->convertManager($manager, $isFork);
            }
        } else {
            $managers['default'] = $this->convertManager($sourceArray, $isFork);
        }

        if ($isFork) {
            $dqls = $this->convertDQL($sourceArray['entity_managers']);
        }

        $cache    = $this->convertCache($sourceArray);

        $results = View::make('mitchell.master', ['managers' => $managers, 'cache' => $cache, 'dqls' => $dqls])->render();
        $unescaped = html_entity_decode($results, ENT_QUOTES);

        return $unescaped;
    }

    public function convertManager($sourceArray, $isFoxxMD)
    {
        $results = View::make('mitchell.manager', ['data' => $sourceArray, 'isFork' => $isFoxxMD])->render();
        $unescaped = html_entity_decode($results, ENT_QUOTES);
        return $unescaped;
    }

    public function convertCache($sourceArray)
    {
        $cacheProvider = ArrayUtil::get($sourceArray['cache_provider']);
        $results = View::make('mitchell.cache',['cacheProvider' => $cacheProvider])->render();
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
            $results = View::make('mitchel.dql', ['dql' => $dqls])->render();
            $unescaped = html_entity_decode($results, ENT_QUOTES);
            return $unescaped;
        } else {
            return null;
        }

    }
}
