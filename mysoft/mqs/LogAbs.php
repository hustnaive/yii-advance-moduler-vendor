<?php
namespace mysoft\mqs;

use yii\base\Component;
/**
 * 抽象队列日志类
 * @author yangzhen
 * 
 *  'qlog' => [
 *	
 *           'class'=>'yunke\mqs\log\File' //选择类型
 *       
 *        ]
 *
 * 
 *
 */

abstract class LogAbs extends Component
{
	/**
	 * 日志数据
	 * @var array
	 */
	private $messages = [];
	
	
	/**
	 * 日志间隔，考虑内存等问题，默认满1000条就直接写
	 * @var integer
	 */
	public $flushInteval = 1000;
	
	public function init()
	{
		parent::init();
		register_shutdown_function(function () {
            // make sure "flush()" is called last when there are multiple shutdown functions
            register_shutdown_function([$this, 'flush']);
        });
	   
		
	}
	
	/**
	 * 实现写日志操作，根据日志对象类型
	 * @param unknown $messages
	 */
    abstract protected function write($messages);

	
	public function log($message,$app='mqs')
	{
		
		
		$this->messages[] = [$message,$app,date('Y-m-d G:i:s')];
			
		if($this->flushInteval && count($this->messages) >= $this->flushInteval)
		{
			   
			  $this->flush();
		}
		
		
	}
	
	
	/**
	 * 写日志方法，子类实现LogAbs::write()方法
	 */
	public function flush()
	{
		$messages  = $this->messages;
		$this->messages = [];
		$this->write($messages);
		
	}
	
	
	
	
	
}