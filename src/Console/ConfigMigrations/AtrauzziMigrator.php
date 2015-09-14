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
        $this->viewFactory->addNamespace('atruazzi', realpath(__DIR__ . '/templates/atruazzi'));
        $this->viewFactory->addNamespace('laraveldoctrine', realpath(__DIR__ . '/templates/laraveldoctrine'));
    }

    /**
     * Convert a configuration array from another laravel-doctrine project in to a string representation of a php array configuration for this project
     *
     * @param  array $sourceArray
     * @return string
     */
    public function convertConfiguration($sourceArray)
    {
        $dqls = $this->convertDQL($sourceArray['entity_managers']);
        $cache    = $this->convertCache($sourceArray);
        $customTypes = $this->convertCustomTypes($sourceArray);

        $results   = $this->viewFactory->make('laraveldoctrine.master', ['managers' => $managers, 'cache' => $cache, 'dqls' => $dqls, 'customTypes' => $customTypes])->render();
        $unescaped = html_entity_decode($results, ENT_QUOTES);

        return $unescaped;
    }

    public function convertManager($sourceArray)
    {
        $proxySettings = ArrayUtil::get($sourceArray['proxy_classes']);
        $defaultRepo   = ArrayUtil::get($sourceArray['default_repository']);

        //non default configuration
        if (count($sourceArray['metadata']) > 1) {
            $isChained = false;
            foreach ($sourceArray['metadata'] as $item) {
                if (is_array($item)) {
                    $isChained = true;
                }
            }
            //if it's chained we need to treat each chain as a separate EM
            if ($isChained) {
                foreach ($sourceArray['metadata'] as $item) {
                    //convert each chained metadata array EM
                }
            } //only specifying one non-default EM
            else {
                //convert metadata array to EM
            }
        } //one EM, default
        else {

        }
    }

    protected function getProxySettings($sourceArray)
    {
        if (isset($sourceArray['proxy_classes'])) {
            return [
                'auto_generate' => ArrayUtil::get($sourceArray['proxy_classes']['auto_generate']),
                'namespace' => ArrayUtil::get($sourceArray['proxy_classes']['namespace']),
                'path' => ArrayUtil::get($sourceArray['proxy_classes']['directory'])
            ];
        } else {
            return null;
        }
    }

    public function convertCustomTypes($sourceArray){
        $results   = $this->viewFactory->make('atrauzzi.customTypes', ['sourceArray' => $sourceArray])->render();
        $unescaped = html_entity_decode($results, ENT_QUOTES);
        return $unescaped;
    }

    /**
     * Convert a cache section from mitchellvanw/laravel-doctrine to a string representation of a php array configuration for a cache section for this project
     *
     * @param  array $sourceArray
     * @return string
     */
    public function convertCache($sourceArray)
    {
        if (isset($sourceArray['cache']['provider'])) {
            $cacheProvider = ArrayUtil::get($sourceArray['cache']['provider']);
            $results       = $this->viewFactory->make('atruazzi.cache', [
                'cacheProvider' => $cacheProvider,
                'extras' => count($sourceArray['cache']) > 1 //if user is mimicking cache arrays here we need to tell them to move these to cache.php
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