<?php
namespace  yunke\web;
use yunke\helpers\Identity;
use yunke\helpers\WeiXin;

/***
 * 用户管理
 * 
 * 配置结构
 * [
 *   'key' =>'',		   //key用于管理相关缓存
 *   'proj'  =>''          //项目值,通过私有方法转换成token
 *   'app'   =>'',         //app值
 *   'module'=>''  		   //决定访问权限
 *    
 * ]
 * 
 * role =>[
 * 
 *    module.controller.action   //模块.控制器.方法
 *    module.controller.*		 //模块.控制器。所有方法
 *    module.*					 //模块的所有方法
 *    
 *    
 * ]
 * 
 */
class User
{
	/**
	 * 用户管理配置
	 * @var array
	 */
	private  $_cfg ;
	
	
	/**
	 * 静态方法实例化
	 * @static
	 * @example
	 *  ~ User::getInstance($config)->info();
	 * 
	 * @param array $config
	 * @return \yunke\web\User
	 */
	public static function getInstance($config=[])
	{
		static $_user;

		if(!$_user){
		  	
		   $_user = new static($config);
		  	
		 }

		 return $_user;		
	}
	
	
	/**
	 * 初始化参数
	 * @example
	 * (new User($config))->info();
	 * 
	 * @param array $config
	 */
	public function __construct($config=[])
	{
		 $this->_cfg = $config;
		 
		 
		 //TODO : 完成初始化
		 if($this->_cfg['proj'])
		 {
		 	  $this->_cfg['key'] = $this->_getTokenByProj();//转换后将token指赋给全局配置
		 }
		 
		 
	}

	
	/**
	 * 获取当前token
	 * @return string:
	 */
	public function getToken()
	{
			
		return $this->_cfg['key'];
		
	}
	
	
	
	/**
	 * 根据proj换取token
	 * @param  string  $proj
	 * @return string
	 */
	private function _getTokenByProj()
	{
		if(empty($this->_cfg['orgcode'])){

            E("未找到租户编码", "110000");
		}
		
		$orgcode  = $this->_cfg['orgcode'];
		$proj	  = $this->_cfg['proj'];
		
	    $token = (new \yunke\dbprovider\DbProvider($orgcode))->fetch('pub/project/getTokenByProjId',[$proj]);

        if(empty($token)){

            E("未找到租户token", "110001");
        }
	    return $token;
	   
	}
 
	

	/**
	 * 检查用户状态
	 */
	public function check()
	{
		$userinfo = $this->info();

        if(empty($userinfo))
        {
            if(isset($this->_cfg['login_url']) && $this->_cfg['login_url'])
            {
                header('location: '.$this->_cfg['login_url']);exit;
            }
            return false;

        }
		
		return $userinfo;
		
	}



	
	//登陆
	public function login($userinfo)
	{
		$token = $this->_cfg['key'];
		$app   = $this->_cfg['app'];
		Identity::login($app, $userinfo,$token);
		
	}
		

	//退出
	public function logout()
	{
		$token = $this->_cfg['key'];
		$app   = $this->_cfg['app'];
		
	    Identity::logout($app,$token);	
	
	}

	
	
	/**
	 * 获取用户信息
	 * @return array
	 */
	public function info()
	{	
	   $token = $this->_cfg['key'];
	   $app   = $this->_cfg['app'];
	   return Identity::current($app,$token);
	}
	
	
	
	/**
	 * 获取微信openid
	 */
	public function getopenid()
	{
		$token = $this->_cfg['key'];
		return WeiXin::getopenid($token);
		
	}
	
	
	
}