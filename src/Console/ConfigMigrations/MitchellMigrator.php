<?php
/**
 * Created by IntelliJ IDEA.
 * User: mduncan
 * Date: 7/14/15
 * Time: 3:38 PM
 */

namespace LaravelDoctrine\ORM\ConfigMigrations;


use LaravelDoctrine\ORM\Utilities\ArrayUtil;
use Doctrine\ORM;

class MitchellMigrator implements ConfigurationMigrator
{
    private $defaultConfig;

    public function __construct($defaultConfig)
    {
        $this->defaultConfig = $defaultConfig;
    }

    function convertConfiguration($sourceArray)
    {
        //determine if configuration is from FoxxMD fork or original Mitchell repo
        $isFoxxMD = ArrayUtil::get($sourceArray['entity_managers']) !== null;

        $config['dev'] = config('app.debug');

        if ($isFoxxMD) {
            foreach ($sourceArray['entity_managers'] as $manager) {
                $config['managers'] = $this->convertManager($manager, $isFoxxMD);
            }
        } else {
            $config['managers']['default'] = $this->convertManager($sourceArray, $isFoxxMD);
        }

        $config['meta'] = $this->defaultConfig['meta'];
        $config['extensions'] = $this->defaultConfig['extensions'];
        $config['custom_types'] = $this->defaultConfig['custom_types'];

        if($isFoxxMD){
            $dqls = $this->convertDQL($sourceArray['entity_managers']);
            if(!empty($dqls)){
                array_push($config, $dqls);
            }
        }

        $config['debugbar'] = $this->defaultConfig['debugbar'];
        $config['cache'] = $this->convertCache($sourceArray);

        return $config;
    }

    function convertManager($sourceArray, $isFoxxMD)
    {
        return [
            'meta' => $isFoxxMD ? $sourceArray['metadata']['driver'] : 'annotations',
            'connection' => $isFoxxMD ? $sourceArray['connection'] : config('database.default'),
            'paths' => ArrayUtil::get($sourceArray['metadata']['paths'], $sourceArray['metadata']),
            'repository' => ArrayUtil::get($sourceArray['repository'], ORM\EntityRepository::class),
            'proxies' => [
                'namespace' => ArrayUtil::get($sourceArray['proxy']['namespace'], false),
                'path' => ArrayUtil::get($sourceArray['proxy']['directory'], storage_path('proxies')),
                'auto_generate' => ArrayUtil::get($sourceArray['proxy']['auto_generate'], env('DOCTRINE_PROXY_AUTOGENERATE', false))
            ]
        ];
    }

    function convertCache($sourceArray)
    {
        $cacheProvider = ArrayUtil::get($sourceArray['cache_provider']);
        return [
            'default' => $cacheProvider == null ? config('cache.default') : config('cache.' . $cacheProvider),
            'second_level' => false,
        ];
    }

    function convertDQL($sourceManagers)
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
        return $dqls;
    }
}