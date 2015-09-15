<?php

/**
 * Created by IntelliJ IDEA.
 * User: mduncan
 * Date: 9/14/15
 * Time: 11:25 AM
 */
namespace LaravelDoctrine\ORM\Console\ConfigMigrations;

use Illuminate\Contracts\View\Factory;
use LaravelDoctrine\ORM\Utilities\ArrayUtil;

class AtrauzziMigrator implements ConfigurationMigrator
{
    private $viewFactory;

    /**
     * @param Factory $viewFactory
     */
    public function __construct(Factory $viewFactory)
    {
        $this->viewFactory = $viewFactory;
        //add namespace for views
        $this->viewFactory->addNamespace('atrauzzi', realpath(__DIR__ . '/templates/atrauzzi'));
        $this->viewFactory->addNamespace('laraveldoctrine', realpath(__DIR__ . '/templates/laraveldoctrine'));
    }

    /**
     * Convert a configuration array from another laravel-doctrine project in to a string representation of a php array configuration for this project
     *
     * @param  array  $sourceArray
     * @return string
     */
    public function convertConfiguration($sourceArray)
    {
        $dqls        = $this->convertDQL($sourceArray);
        $cache       = $this->convertCache($sourceArray);
        $customTypes = $this->convertCustomTypes($sourceArray);
        $managers    = [$this->convertManager($sourceArray)];

        $results   = $this->viewFactory->make('laraveldoctrine.master', ['managers' => $managers, 'cache' => $cache, 'dqls' => $dqls, 'customTypes' => $customTypes])->render();
        $unescaped = html_entity_decode($results, ENT_QUOTES);

        return $unescaped;
    }

    public function convertManager($sourceArray)
    {
        $proxySettings = ArrayUtil::get($sourceArray['proxy_classes']);
        $defaultRepo   = ArrayUtil::get($sourceArray['default_repository']);
        $namespaces    = [];
        $driver        = null;
        $connection    = ArrayUtil::get($sourceArray['default']);

        //non default configuration
        if (count($sourceArray['metadata']) > 1) {
            $hasNamespaces = false;
            $index         = 0;
            $driver        = null;
            $sameDriver    = true;

            foreach ($sourceArray['metadata'] as $key => $item) {
                //get first driver
                if (is_null($driver)) {
                    if (is_array($item)) {
                        $driver = $item['driver'];
                    } elseif ($key == 'driver') {
                        $driver = $item;
                    }
                } else {
                    if (is_array($item) && $item['driver'] != $driver) {
                        $sameDriver = false;
                    }
                }
                if (is_array($item) && isset($item['namespace'])) {
                    $hasNamespaces = true;
                }
            }
            //only do this if all the same driver
            if ($hasNamespaces && $sameDriver) {
                $driver = $sourceArray['metadata'][0]['driver'];

                foreach ($sourceArray['metadata'] as $item) {
                    //convert each metadata entry into a namespace entry
                    if (isset($item['alias'])) {
                        $namespaces[$item['alias']] = $item['namespace'];
                    } else {
                        array_push($namespaces, $item['namespace']);
                    }
                }
            } //only specifying one non-default EM
            else {
                if (isset($sourceArray['metadata']['namespace'])) {
                    if (isset($sourceArray['metadata']['alias'])) {
                        $namespaces[$sourceArray['metadata']['alias']] = $sourceArray['metadata']['namespace'];
                    } else {
                        $namespaces[] = $sourceArray['metadata']['namespace'];
                    }
                }
            }
        } //one EM, default
        else {
            $driver = $sourceArray['metadata']['driver'];
        }
        $results   = $this->viewFactory->make('atrauzzi.manager', ['namespaces' => $namespaces, 'proxySettings' => $proxySettings, 'defaultRepo' => $defaultRepo, 'driver' => $driver, 'connection' => $connection])->render();
        $unescaped = html_entity_decode($results, ENT_QUOTES);

        return $unescaped;
    }

    public function convertCustomTypes($sourceArray)
    {
        $results   = $this->viewFactory->make('atrauzzi.customTypes', ['sourceArray' => $sourceArray])->render();
        $unescaped = html_entity_decode($results, ENT_QUOTES);

        return $unescaped;
    }

    /**
     * Convert a cache section from mitchellvanw/laravel-doctrine to a string representation of a php array configuration for a cache section for this project
     *
     * @param  array  $sourceArray
     * @return string
     */
    public function convertCache($sourceArray)
    {
        if (isset($sourceArray['cache']['provider'])) {
            $cacheProvider = ArrayUtil::get($sourceArray['cache']['provider']);
            $results       = $this->viewFactory->make('atrauzzi.cache', [
                'cacheProvider' => $cacheProvider,
                'extras'        => count($sourceArray['cache']) > 1 //if user is mimicking cache arrays here we need to tell them to move these to cache.php
            ])->render();
            $unescaped     = html_entity_decode($results, ENT_QUOTES);

            return $unescaped;
        }

        return null;
    }

    /**
     * Convert the dql sections from the entity managers in a configuration from atruazzi/laravel-doctrine into a string representation of a php array configuration for custom string/numeric/datetime functions
     *
     * Returns null if no dql sections were found.
     *
     * @param $sourceArray
     * @return null|string
     */
    public function convertDQL($sourceArray)
    {
        $results   = $this->viewFactory->make('atrauzzi.dql', ['dql' => $sourceArray])->render();
        $unescaped = html_entity_decode($results, ENT_QUOTES);

        return $unescaped;
    }
}
