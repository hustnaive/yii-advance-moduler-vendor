<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace mysoft\db;

class OrgConnection extends \yii\db\Connection
{
    public function __construct()
    {

        $orgcode = I("__orgcode");
        if(empty($orgcode)){
            throw new Exception("未找到租户编码", "10000");
        }
        $connstr_cachekey = "ConnectionString_".$orgcode;
        $connstr = \Yii::$app->cache->get($connstr_cachekey);
        if(!$connstr)
        {
            $command = \Yii::$app->db->createCommand("select ConnectionString from myscrm_organization where UniqueName=:orgcode",
                [
                    ':orgcode' => $orgcode,
                ]);
            $connstr = $command->queryScalar();
            \Yii::$app->cache->set($connstr_cachekey,$connstr);
            if(empty($connstr)){
                throw new Exception("租户库链接为空！", "10001");
            }
        }

        $myconnection = array();
        foreach (explode(';', $connstr) as $conn){
            $connPara = explode('=', $conn);
            if(count($connPara)>1){
                $myconnection[$connPara[0]]= $connPara[1];
            }
        }

        $this->dsn="mysql:host=".$myconnection["server"].";dbname=".$myconnection["database"].";port=3306";
        $this->username = $myconnection["uid"];
        $this->password = $myconnection["pwd"];
    }
}
