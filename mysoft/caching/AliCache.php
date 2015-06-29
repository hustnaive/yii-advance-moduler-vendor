<?php
/**
 * AliYun的ocs缓存
 * @author yangzhen
 *
 */
namespace mysoft\caching;

use yii\caching\Cache;
use yii\base\InvalidConfigException;

class AliCache extends Cache
{

    /**
     * @var string an ID that identifies a Memcached instance. This property is used only when [[useMemcached]] is true.
     * By default the Memcached instances are destroyed at the end of the request. To create an instance that
     * persists between requests, you may specify a unique ID for the instance. All instances created with the
     * same ID will share the same connection.
     * @see http://ca2.php.net/manual/en/memcached.construct.php
     */
    public $persistentId;
    /**
     * @var array options for Memcached. This property is used only when [[useMemcached]] is true.
     * @see http://ca2.php.net/manual/en/memcached.setoptions.php
     */
    public $options;
    
    /**
     * @var  \Memcached the Memcache instance
     */
    private $_cache = null;
  

    /**
     * Initializes this application component.
     * It creates the memcache instance and adds memcache servers.
     */
    public function init()
    {
        parent::init();
        $this->getMemcached();
    }

   
    /**
     * Returns the underlying  memcached object.
     * @return \Memcached the  memcached object used by this cache component.
     * @throws InvalidConfigException if memcached extension is not loaded
     */
    public function getMemcached()
    {
        if ($this->_cache === null) {
           
            if (!extension_loaded('memcached')) {
                throw new InvalidConfigException("MemCache requires PHP  extension to be loaded.");
            }

            
            $global =  \yunke\helpers\Conf::fromLocal();//获取配置

            $this->options = array (
            		'host'        =>  $global['OCS_HOST'] ? $global['OCS_HOST'] : '127.0.0.1',
            		'port'        =>  $global['OCS_PORT'] ? $global['OCS_PORT'] : 11211,
            		'user'		  =>  $global['OCS_SASL_USER'] ? $global['OCS_SASL_USER'] : '',
            		'passwd'	  =>  $global['OCS_SASL_PWD'] ? $global['OCS_SASL_PWD'] : ''
            );

            $this->_cache = $this->persistentId !== null ? new \Memcached($this->persistentId) : new \Memcached;
            
            $this->_cache->setOption(\Memcached::OPT_COMPRESSION, false);
            $this->_cache->setOption(\Memcached::OPT_BINARY_PROTOCOL, true);
            $this->_cache->addServer($this->options['host'], $this->options['port']);
           /* 
            if ( !empty($this->options['user']) && !empty($this->options['passwd']) ) {
            	$this->_cache->setOption(\Memcached::OPT_BINARY_PROTOCOL, true);
            	$this->_cache->setSaslAuthData($this->options['user'], $this->options['passwd']);
            }
            */
            
                       
        }

        return $this->_cache;
    }



    /**
     * Retrieves a value from cache with a specified key.
     * This is the implementation of the method declared in the parent class.
     * @param string $key a unique key identifying the cached value
     * @return string|boolean the value stored in cache, false if the value is not in the cache or expired.
     */
    protected function getValue($key)
    {
        return $this->_cache->get($key);
    }

    /**
     * Retrieves multiple values from cache with the specified keys.
     * @param array $keys a list of keys identifying the cached values
     * @return array a list of cached values indexed by the keys
     */
    protected function getValues($keys)
    {
        return  $this->_cache->getMulti($keys) ;
    }

    /**
     * Stores a value identified by a key in cache.
     * This is the implementation of the method declared in the parent class.
     *
     * @param string $key the key identifying the value to be cached
     * @param string $value the value to be cached
     * @param integer $duration the number of seconds in which the cached value will expire. 0 means never expire.
     * @return boolean true if the value is successfully stored into cache, false otherwise
     */
    protected function setValue($key, $value, $duration)
    {
        $expire = $duration > 0 ? $duration + time() : 0;

        return  $this->_cache->set($key, $value, $expire);
    }

    /**
     * Stores multiple key-value pairs in cache.
     * @param array $data array where key corresponds to cache key while value is the value stored
     * @param integer $duration the number of seconds in which the cached values will expire. 0 means never expire.
     * @return array array of failed keys. Always empty in case of using memcached.
     */
    protected function setValues($data, $duration)
    {
       
        $this->_cache->setMulti($data, $duration > 0 ? $duration + time() : 0);

        return [];
       
    }

    /**
     * Stores a value identified by a key into cache if the cache does not contain this key.
     * This is the implementation of the method declared in the parent class.
     *
     * @param string $key the key identifying the value to be cached
     * @param string $value the value to be cached
     * @param integer $duration the number of seconds in which the cached value will expire. 0 means never expire.
     * @return boolean true if the value is successfully stored into cache, false otherwise
     */
    protected function addValue($key, $value, $duration)
    {
        $expire = $duration > 0 ? $duration + time() : 0;

        return  $this->_cache->add($key, $value, $expire);
    }

    /**
     * Deletes a value with the specified key from cache
     * This is the implementation of the method declared in the parent class.
     * @param string $key the key of the value to be deleted
     * @return boolean if no error happens during deletion
     */
    protected function deleteValue($key)
    {
        return $this->_cache->delete($key, 0);
    }

    /**
     * Deletes all values from cache.
     * This is the implementation of the method declared in the parent class.
     * @return boolean whether the flush operation was successful.
     */
    protected function flushValues()
    {
       return $this->_cache->flush();
    }
}
