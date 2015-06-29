<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace yunke\web\Profile;

/**
 *
 * @author tianl
 */
interface ICacheProfile {

    /**
     * 获取profile信息
     * @param type $settingType
     * @param type $key
     * @param type $value
     */
    public function getValue($settingType, $key);

    /**
     * 设置profile信息
     * @param type $settingType
     * @param type $key
     * @param type $value
     */
    public function setValue($settingType, $key, $value);
}
