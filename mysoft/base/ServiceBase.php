<?php
namespace yunke\base;
use yunke\dbprovider\DbProvider;

class ServiceBase {
    protected $orgcode;
    protected $dbProvider;
    protected $_current_date;
    
    public function __construct($orgcode)
    {
        $this->orgcode = $orgcode;
        $this->dbProvider = new DbProvider($orgcode);
        $this->_current_date = date( "Y-m-d H:i:s" );
    }
    /**
     * 
     * @param type $provider 数据Provider
     */
    public function setDbProvider($provider)
    {
        $this->dbProvider = $provider;
    }
}
