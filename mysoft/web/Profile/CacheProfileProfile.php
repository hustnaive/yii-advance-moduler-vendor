<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace yunke\web\Profile;

/**
 * Description of CacheProfileProfile
 *
 * @author tianl
 */
class CacheProfileProfile implements ICacheProfile {

    private $_orgCode;
    private $_dbProfile;

    public function __construct($orgCode) {
        $this->_orgCode = $orgCode;
        $this->_dbProfile = new DbProfileProvider($orgCode);
    }

    /**
     * 获取profile信息
     * @param type $settingType
     * @param type $key
     * @param type $value
     */
    public function getValue($settingType, $key) {
        $cacheKey = $this->_getCacheKey($settingType, $key);
        $cacheData = \Yii::$app->cache->get($cacheKey);
        if ($cacheData) {
            return $cacheData;
        }
        $value = $this->_dbProfile->GetValue($settingType, $key);
        if ($value != null) {
            \Yii::$app->cache->set($cacheKey, $value);
        }
        return $value;
    }

    /**
     * 设置profile信息
     * @param type $settingType
     * @param type $key
     * @param type $value
     */
    public function setValue($settingType, $key, $value) {
        $cacheKey = $this->_getCacheKey($settingType, $key);
        $this->_dbProfile->SetValue($settingType, $key, $value);
        \Yii::$app->cache->delete($cacheKey);
    }

    private function _getCacheKey($settingType, $key) {
        //'user_profile_key'=>'user_profile_{orgcode}_{stype}_{skey}',
        return \yunke\helpers\G::getCacheKey("user_profile_key", ["orgcode" => $this->_orgCode, "stype" => $settingType, "skey" => $key]);
    }

}
