<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace yunke\web\Profile;

use yunke\base\DALBase;

/**
 * Description of DbProfileProvider
 * @author tianl
 */
class DbProfileProvider extends DALBase implements ICacheProfile {
    //SELECT mdl_UserSettingId, SettingType,SettingKey,SettingValue from mdl_UserSetting where SettingType='项目选择器控件默认值';

    /**
     * 获取profile信息
     * @param type $settingType
     * @param type $key
     * @param type $value
     */
    public function getValue($settingType, $key) {
        $sql = "SELECT mdl_UserSettingId, SettingType,SettingKey,SettingValue from mdl_UserSetting where SettingType=:SettingType and SettingKey=:SettingKey limit 1";
        $result = $this->db->createCommand($sql, [":SettingType" => $settingType, ":SettingKey" => $key])->queryOne();
        $traceSql=$this->db->createCommand($sql, [":SettingType" => $settingType, ":SettingKey" => $key])->getRawSql();
        return \yii\helpers\ArrayHelper::getValue($result, "SettingValue", null);
    }

    /**
     * 设置profile信息
     * @param type $settingType
     * @param type $key
     * @param type $value
     */
    public function setValue($settingType, $key, $value) {
        $delKeySql = "DELETE FROM mdl_usersetting where SettingKey=:SettingKey and SettingType=:SettingType";
        $this->db->createCommand($delKeySql, [":SettingType" => $settingType, ":SettingKey" => $key])->execute();
        $data = [
            "mdl_UserSettingId" => \yunke\helpers\String::uuid(),
            "SettingType" => $settingType,
            "SettingKey" => $key,
            "SettingValue" => $value,
            "versionnumber"=>'1' 
        ];
        $this->db->createCommand()->insert("mdl_usersetting", $data)->execute();
    }

}
