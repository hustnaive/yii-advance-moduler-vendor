<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace yunke\web\Profile;

/**
 * 用户个性化信息存储
 * @author tianl
 */
class UserProfile {

    /**
     * 获取Profile 
     * @param type $orgCode
     * @return \yunke\web\Profile\CacheProfileProfile
     */
    public static function getProfile($orgCode) {
        return new CacheProfileProfile($orgCode);
    }

}
