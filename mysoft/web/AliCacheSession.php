<?php

namespace yunke\web;

use Yii;
use yii\caching\Cache;
use yii\di\Instance;
use yii\web\CacheSession;

/**
 * 重写CacheSession 解决 cache没有前缀的问题
 *
 * @author sglz
 * @since 2.0
 */
class AliCacheSession extends CacheSession
{
    /**
     * Initializes the application component.
     */
    public function init()
    {
        parent::init();
        $this->cache = Instance::ensure($this->cache, Cache::className());
        $this->cache->keyPrefix = '';
    }
}
