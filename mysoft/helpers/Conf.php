<?php
/**
 * 配置助手
 * @static class Conf
 * @author yangzhen
 *
 */
namespace yunke\helpers;
use yunke\dbprovider\DbProvider;

class Conf
{
    
    /**
     * 获取配内容
     * @access private
     * @return array
     */
    private static function _getConfig()
    {
    	
        $params = [];
    	$config = (new DbProvider('config'))->fetch('pub/configSettings/get', $params);
    	
    	
        $arr = array();
        foreach ($config as $list)
        {
        	$arr[$list['KeyName']] = $list['Value'];
        }
        
        return $arr;
    	
    }
    
    
    /**
     * 获取配置
     * @access public
     * @static
     * @example
     *  \yunke\helpers\Conf::getConfig('EmailPassword')
     * @param string $keyName
     * @param string $isLocal
     * @return mixed
     */
    public static function getConfig($keyName = '',$isLocal =  false)
    {
    	 if($isLocal){ //本地缓存读file
    	 	return static::fromLocal($keyName);
    	 	
    	 }else { //直接读cache组件配置
    	 	return static::fromCache($keyName);
    	 }
    	
    }
    
    
    /**
     * 从本地获取
     * @access public 
     * @example
     *   \yunke\helpers\Conf::fromLocal('EmailPassword')
     * @return array
     */
    public static function fromLocal($keyName='all')
    {
        if(!$keyName) return '';
        $cache  = \Yii::$app->localcache;
        $cache_key = 'local_config';
        $config = $cache->get($cache_key);
        
        if($config){
        
        	return $keyName == 'all' ? $config :$config[$keyName];
        
        }else{
        
        	$arr = static::_getConfig();
        
        	$cache->set($cache_key,$arr,60);
        
        	return $keyName == 'all' ? $arr :$arr[$keyName];
        }
        
        
    }
        
       
    /**
     * 从全局配置的缓存cache组件对象获取
     * @access public 
     * @example
     *    \yunke\helpers\Conf::fromCache('EmailPassword')
     * @return array
     */
    public static function fromCache($keyName='')
    {
        if(!$keyName)
        	return '';
        
        $config = \Yii::$app->cache->get('config');
        
        if($config)
        {
        	return $config[$keyName];
        }
        else
        {
        
            $arr = static::_getConfig();
            
        	\Yii::$app->cache->set('config',$arr,60);
        		
        	return $arr[$keyName];
        }
        
        
        
    }
    
}