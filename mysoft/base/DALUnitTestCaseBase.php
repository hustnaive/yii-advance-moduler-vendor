<?php

/**
 * Description 数据层测试基类
 * @author tianl
 */
abstract class DALUnitTestCaseBase extends \PHPUnit_Extensions_Database_TestCase {
    /*
     * 获取配置库连接信息
     */
    function get_config_db_setting() {
        global $config;
        return $config["components"]["db"];
    }

    /**
     * 获取租户库连接信息
     */
    function get_tenant_db_setting($orgcode) {
        $orgcode = empty($orgcode) ? I("__orgcode") : $orgcode;
        if (empty($orgcode)) {
            throw new Exception("未找到租户编码", "110000");
        }
        $tenantDbSetting_cachekey = "tennatDbSetting_" . $orgcode;
        $tenantDbSetting = Yii::$app->cache->get($tenantDbSetting_cachekey);
        if ($tenantDbSetting != null) {
            return $tenantDbSetting;
        }

        $command = \Yii::$app->db->createCommand("select ConnectionString from myscrm_organization where UniqueName=:orgcode", [':orgcode' => $orgcode]);
        $connstr = $command->queryScalar();
        if (empty($connstr)) {
            throw new Exception("租户库链接为空！", "110001");
        }
        $myconnection = array();
        foreach (explode(';', $connstr) as $conn) {
            $connPara = explode('=', $conn);
            if (count($connPara) > 1) {
                $myconnection[$connPara[0]] = $connPara[1];
            }
        }
        $dbsetting = array(
            'dsn' => 'mysql:host=' . $myconnection["server"] . ';dbname=' . $myconnection["database"],
            'username' => $myconnection["uid"],
            'password' => $myconnection["pwd"],
            'dbname' => $myconnection["database"]
        );
        Yii::$app->cache->set($tenantDbSetting_cachekey, $dbsetting);
        return $dbsetting;
    }

    public abstract function getOrgCode();

    // 只实例化 pdo 一次，供测试的清理和基境读取使用。
    static private $pdo = null;
    // 对于每个测试，只实例化 PHPUnit_Extensions_Database_DB_IDatabaseConnection 一次。
    private $conn = null;

    //获取数据库连接,为PHPUnit默认的数据库操作提供连连接对象
    final public function getConnection() {
        $dbSetting = get_tenant_db_setting($this->getOrgCode());
        if ($this->conn === null) {
            if (self::$pdo == null) {
                self::$pdo = new PDO($dbSetting["dsn"], $dbSetting["username"], $dbSetting['password']);
            }
            $this->conn = $this->createDefaultDBConnection(self::$pdo, $dbSetting['dbname']);
        }

        return $this->conn;
    }

    public function testGetConfig() {
        
    }

}
