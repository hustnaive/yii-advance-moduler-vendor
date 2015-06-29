<?php
/**
 * MSQ的抽象类
 * @example
 *       \Yii::$app->mqs->Q(xxx)->put();
 *   OR  \Yii::$app->mqs->Q(xxx)->setServer(XXX)->setAuth(XXXX)->setCharset(XXX)->put();
 *    
 *   支持局部定义授权密钥和编码
 *   
 * ~~~ 全局组件配置
 *         'mqs'=>[
	        'class'=>'yunke\mqs\HttpSqs',			/--必须--/
	        'Server'=>'http://10.5.10.109:55555/',  /--必须--/
	          ...
	         'Auth' =>xxxx
	         'Charset'=>'utf-8'
	        ] 
 *   
 * 
 * @author yangzhen
 *
 */
namespace yunke\mqs;

use yii\base\Component;

abstract class MqsAbs extends Component
{
	
    const QUEUE_UNKNOW_ERR 	= '未知的系统错误' ;
	
	const QUEUE_PUT_ERR 	= '入队列失败';
	
	const QUEUE_PUT_OK  	= '入队列成功';
	
	const QUEUE_PUT_END 	= '队列已满';

	const QUEUE_GET_END 	= '队列已取完';
	

	/**
	 * 队列名
	 * @var string $qName
	 */
	protected  $qName  = '';
	
	
	
	/**
	 * 消息对列名
	 * @param string $qName
	 * @return $this
	 */
	public function Queue($qName)
	{
		$this->qName = $qName;
		return $this;
	}
	
	
	/**
	 * 服务队列实例
	 * @var string
	 */
	private $_server = '';
	
	/**
	 * 获取server
	 * @return string
	 */
	public function getServer()
	{
		return $this->_server;
	}
	
	/**
	 * 设置server
	 * @param string $serv
	 */
	public function setServer($serv)
	{
		 $this->_server = $serv;
		 return $this;
	}
	
	
	/**
	 * 编码
	 * @var string $_charset
	 */
	private $_charset = 'utf-8';
	
	/**
	 * 获取编码
	 * @return string
	 */
	public function getCharset()
	{
		return $this->_charset;		
	}
	
	/**
	 * 设置编码
	 * @param unknown $charset
	 * @return \yunke\mqs\MsqAbs
	 */
	public function setCharset($charset)
	{
		$this->_charset = $charset;
		return $this;
	}
	
	
	
	/**
	 * 授权密钥
	 * @var string
	 */
	private $_auth;
	
	/**
	 * 获取授权密钥
	 * @return string
	 */
	public function getAuth()
	{
		return $this->_auth;		
	}
	
	/**
	 * 授权密钥
	 * @param  string $auth
	 * @return \yunke\mqs\MsqAbs
	 */
	public function setAuth($auth)
	{
		$this->_auth = $auth;
		return $this;
	}
	
	/**
	 * 入队列抽象方法
	 * @abstract 
	 * @access public
	 * @param  array|string $data
	 * @return array
	 */
	abstract public function put($data);
	
	/**
	 * 出队列操作
	 * @abstract
	 * @access public
	 * @return array [$pos,$data]   
	 */
	abstract public function get();
	
	/**
	 * 查看队列元素
	 * @abstract 
	 * @access public 
	 * @param  int $pos
	 * @return mixed
	 */
	abstract  public function view($pos);
	
	/**
	 * 重置队列
	 * @abstract
	 * @access public
	 */
	abstract  public function reset();
	
	
}