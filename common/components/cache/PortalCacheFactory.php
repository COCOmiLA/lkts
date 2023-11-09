<?php

namespace common\components\cache;

use common\components\BooleanCaster;
use yii\caching\ApcCache;
use yii\caching\CacheInterface;
use yii\caching\DbCache;
use yii\caching\FileCache;

class PortalCacheFactory
{
    private bool $disable_cache;

    public function __construct()
    {
        $this->disable_cache = BooleanCaster::cast(getenv('DISABLE_CACHE')) || BooleanCaster::cast(defined('PORTAL_CONSOLE_INSTALLATION'));
    }

    public function createCache(): CacheInterface
    {
        if ($this->disable_cache) {
            return new \yii\caching\DummyCache();
        }
        $cache_class = DbCache::class;
        $cache_config = [
            'cacheTable' => '{{%db_cache}}',
        ];
        
        if (extension_loaded('apcu')) {
            $cache_class = ApcCache::class;
            $cache_config = [
                'useApcu' => true,
            ];
        }

        $cache = null;
        try {
            $cache = \Yii::createObject(array_merge(['class' => $cache_class], $cache_config));
            if ($cache instanceof DbCache) {
                
                $schemaCacheExclude = $cache->db->schemaCacheExclude;
                $cache->db->schemaCacheExclude = [$cache_config['cacheTable']];
                if (!$cache->db->schema->getTableSchema($cache_config['cacheTable'])) {
                    $cache = null;
                }
                $cache->db->schemaCacheExclude = $schemaCacheExclude;
            }
        } catch (\Throwable $e) {
            $cache = null;
        }
        if (!$cache) {
            $cache_class = FileCache::class;
            $cache_config = [
                'cachePath' => '@common/runtime/cache'
            ];
            $cache = \Yii::createObject(array_merge(['class' => $cache_class], $cache_config));
        }
        return $cache;
    }
}