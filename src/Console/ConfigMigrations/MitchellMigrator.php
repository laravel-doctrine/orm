<?php

namespace LaravelDoctrine\ORM\Console\ConfigMigrations;

use Illuminate\Contracts\View\Factory;
use LaravelDoctrine\ORM\Utilities\ArrayUtil;

class MitchellMigrator implements ConfigurationMigrator
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
        $this->viewFactory->addNamespace('mitchell', realpath(__DIR__ . '/templates/mitchell'));
        $this->viewFactory->addNamespace('laraveldoctrine', realpath(__DIR__ . '/templates/laraveldoctrine'));
    }

    /**
     * Convert a configuration array from mitchellvanw/laravel-doctrine to a string representation of a php array configuration for this project
     *
     * @param array $sourceArray
     *
     * @return string
     */
    public function convertConfiguration($sourceArray)
    {
        $isFork = $this->isFork($sourceArray);

        $managers = [];
        $dqls     = null;

        if ($isFork) {
            foreach ($sourceArray['entity_managers'] as $key => $manager) {
                $manager['proxy'] = $sourceArray['proxy'];
                $managers[$key]   = $this->convertManager($manager, $isFork);
            }
        } else {
            $managers['default'] = $this->convertManager($sourceArray, $isFork);
        }

        if ($isFork) {
            $dqls = $this->convertDQL($sourceArray['entity_managers']);
        }

        $cache = $this->convertCache($sourceArray);

        $results = $this->viewFactory->make('laraveldoctrine.master', [
            'managers' => $managers,
            'cache'    => $cache,
            'dqls'     => $dqls
        ])->render();

        $unescaped = $this->unescape($results);

        return $unescaped;
    }

    /**
     * Convert an entity manager section from mitchellvanw/laravel-doctrine to a string representation of a php array configuration for an entity manager for this project
     *
     * @param array $sourceArray
     * @param bool  $isFork
     *
     * @return string
     */
    public function convertManager($sourceArray, $isFork)
    {
        $results = $this->viewFactory->make('mitchell.manager', [
            'data'   => $sourceArray,
            'isFork' => $isFork
        ])->render();

        $unescaped = $this->unescape($results);

        return $unescaped;
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
        $cacheProvider = ArrayUtil::get($sourceArray['cache_provider']);

        $results = $this->viewFactory->make('mitchell.cache', [
            'cacheProvider' => $cacheProvider
        ])->render();

        return $this->unescape($results);
    }

    /**
     * Convert the dql sections from the entity managers in a configuration from foxxmd/laravel-doctrine into a string representation of a php array configuration for custom string/numeric/datetime functions
     * Returns null if no dql sections were found.
     *
     * @param $sourceManagers
     *
     * @return null|string
     */
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

        if (!empty($dqls)) {
            $results = $this->viewFactory->make('mitchel.dql', [
                'dql' => $dqls
            ])->render();

            return $this->unescape($results);
        }
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

    /**
     * Determine if configuration is from FoxxMD fork or original Mitchell repo
     *
     * @param $sourceArray
     *
     * @return bool
     */
    protected function isFork($sourceArray)
    {
        return ArrayUtil::get($sourceArray['entity_managers']) !== null;
    }
}
