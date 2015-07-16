<?php

namespace LaravelDoctrine\ORM\ConfigMigrations;

use Doctrine\ORM;
use Illuminate\Contracts\View\Factory;
use LaravelDoctrine\ORM\Utilities\ArrayUtil;

class MitchellMigrator implements ConfigurationMigrator
{
    private $viewFactory;

    public function __construct(Factory $viewFactory)
    {
        $this->viewFactory = $viewFactory;
        //add namespace for views
        $this->viewFactory->addNamespace('mitchell', realpath(__DIR__ . '/templates/mitchell'));
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

        $results = $this->viewFactory->make('mitchell.master', ['managers' => $managers, 'cache' => $cache, 'dqls' => $dqls])->render();
        $unescaped = html_entity_decode($results, ENT_QUOTES);

        return $unescaped;
    }

    public function convertManager($sourceArray, $isFork)
    {
        $results = $this->viewFactory->make('mitchell.manager', ['data' => $sourceArray, 'isFork' => $isFork])->render();
        $unescaped = html_entity_decode($results, ENT_QUOTES);
        return $unescaped;
    }

    public function convertCache($sourceArray)
    {
        $cacheProvider = ArrayUtil::get($sourceArray['cache_provider']);
        $results = $this->viewFactory->make('mitchell.cache',['cacheProvider' => $cacheProvider])->render();
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
            $results = $this->viewFactory->make('mitchel.dql', ['dql' => $dqls])->render();
            $unescaped = html_entity_decode($results, ENT_QUOTES);
            return $unescaped;
        } else {
            return null;
        }

    }
}