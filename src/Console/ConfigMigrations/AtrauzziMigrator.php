<?php

namespace LaravelDoctrine\ORM\Console\ConfigMigrations;

use Illuminate\Contracts\View\Factory;
use LaravelDoctrine\ORM\Utilities\ArrayUtil;

class AtrauzziMigrator implements ConfigurationMigrator
{
    /**
     * @var Factory
     */
    protected $viewFactory;

    /**
     * @param Factory $viewFactory
     */
    public function __construct(Factory $viewFactory)
    {
        $this->viewFactory = $viewFactory;
        $this->viewFactory->addNamespace('atrauzzi', realpath(__DIR__ . '/templates/atrauzzi'));
        $this->viewFactory->addNamespace('laraveldoctrine', realpath(__DIR__ . '/templates/laraveldoctrine'));
    }

    /**
     * Convert a configuration array from another laravel-doctrine project in to a string representation of a php array configuration for this project
     *
     * @param array $sourceArray
     *
     * @return string
     */
    public function convertConfiguration($sourceArray)
    {
        $dqls        = $this->convertDQL($sourceArray);
        $cache       = $this->convertCache($sourceArray);
        $customTypes = $this->convertCustomTypes($sourceArray);
        $managers    = [$this->convertManager($sourceArray)];

        $results = $this->viewFactory->make('laraveldoctrine.master', [
            'managers'    => $managers,
            'cache'       => $cache,
            'dqls'        => $dqls,
            'customTypes' => $customTypes
        ])->render();

        return $this->unescape($results);
    }

    /**
     * @param $sourceArray
     *
     * @return string
     */
    public function convertManager($sourceArray)
    {
        $proxySettings = ArrayUtil::get($sourceArray['proxy_classes']);
        $defaultRepo   = ArrayUtil::get($sourceArray['default_repository']);
        $namespaces    = [];
        $connection    = ArrayUtil::get($sourceArray['default']);

        // Non default configuration
        if (count($sourceArray['metadata']) > 1) {
            $hasNamespaces = false;
            $driver        = null;
            $sameDriver    = true;

            foreach ($sourceArray['metadata'] as $key => $item) {
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
            // Only do this if all the same driver
            if ($hasNamespaces && $sameDriver) {
                $driver = $sourceArray['metadata'][0]['driver'];

                // Convert each metadata entry into a namespace entry
                foreach ($sourceArray['metadata'] as $item) {
                    if (isset($item['alias'])) {
                        $namespaces[$item['alias']] = $item['namespace'];
                    } else {
                        array_push($namespaces, $item['namespace']);
                    }
                }
                // Only specifying one non-default EM
            } else {
                if (isset($sourceArray['metadata']['namespace'])) {
                    if (isset($sourceArray['metadata']['alias'])) {
                        $namespaces[$sourceArray['metadata']['alias']] = $sourceArray['metadata']['namespace'];
                    } else {
                        $namespaces[] = $sourceArray['metadata']['namespace'];
                    }
                }
            }
            // One EM, default
        } else {
            $driver = $sourceArray['metadata']['driver'];
        }

        $results = $this->viewFactory->make('atrauzzi.manager', [
            'namespaces'    => $namespaces,
            'proxySettings' => $proxySettings,
            'defaultRepo'   => $defaultRepo,
            'driver'        => $driver,
            'connection'    => $connection
        ])->render();

        return $this->unescape($results);
    }

    /**
     * @param $sourceArray
     *
     * @return string
     */
    public function convertCustomTypes($sourceArray)
    {
        $results = $this->viewFactory->make('atrauzzi.customTypes', [
            'sourceArray' => $sourceArray
        ])->render();

        return $this->unescape($results);
    }

    /**
     * Convert a cache section from mitchellvanw/laravel-doctrine to a string representation of a php array configuration for a cache section for this project
     *
     * @param array $sourceArray
     *
     * @return string
     */
    public function convertCache($sourceArray)
    {
        if (isset($sourceArray['cache']['provider'])) {
            $cacheProvider = ArrayUtil::get($sourceArray['cache']['provider']);
            $results       = $this->viewFactory->make('atrauzzi.cache', [
                'cacheProvider' => $cacheProvider,
                'extras'        => count($sourceArray['cache']) > 1
                //if user is mimicking cache arrays here we need to tell them to move these to cache.php
            ])->render();

            return $this->unescape($results);
        }

        return null;
    }

    /**
     * Convert the dql sections from the entity managers in a configuration from atruazzi/laravel-doctrine into a string representation of a php array configuration for custom string/numeric/datetime functions
     * Returns null if no dql sections were found.
     *
     * @param $sourceArray
     *
     * @return null|string
     */
    public function convertDQL($sourceArray)
    {
        $results = $this->viewFactory->make('atrauzzi.dql', ['dql' => $sourceArray])->render();

        return $this->unescape($results);
    }

    /**
     * @param $results
     *
     * @return string
     */
    protected function unescape($results)
    {
        return html_entity_decode($results, ENT_QUOTES);
    }
}
