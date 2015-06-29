<?php
namespace  yunke\mqs;
/**
 * 用于灰度发布的调度器组件
 * 
 * 灰度对象： AliYun的MQS服务
 * 灰度方法： 根据orgcode进行分租户的灰度
 * 
 * 
 * ~组件配置
 * [
 * 
 *   'mqs'=>[
 *   
 *     'class'	 =>'yunke\mqs\Dispatcher',
 *     'whitelist'=>['dev'],
 *	   'targets' =>[
 *	            	'httpsqs'=>[
 *	            		'class'=>'yunke\mqs\HttpSqs',
 *	            	 	'Server'=>'http://10.5.10.109:55555',
 *				 	 	],	
 *
 *				 	'ali'=>[
 *						 'class'=>'yunke\mqs\Ali',
 *					 	 'AccessKey'=>'FIGOQIidyBsGPf29',
 *					 	 'AccessSecret'=>'uFvE1MMZuQv9T65Q0CVh4YOc9NWyb3',
 *					 	 'queueownerid'=>'658rw',
 *					 	 'mqsurl'=>'mqs-cn-hangzhou.aliyuncs.com'
 *				        ]
 *          
 *                 ]
 *         
 *      ]
 * 
 *    ... ...  
 * 
 * ]
 * 
 * $orgcode = $_GET['__orgcode'];对比白名单判断是否满足灰度条件
 * 
 * 
 */
use Yii;
use yii\base\Component;

class Dispatcher extends Component
{
	/**
	 * 目标配置
	 * @var array
	 */
	public $targets ;
	
	/**
	 * 灰度白名单,orgcode
	 * @var array
	 */
	public $whitelist = [];
	
	/**
	 * 当前访问对象实例
	 * @var object
	 */
	private $_target;
	
	
	
    /**
     * 组织ID
     * @var array
     */
	private $_orgcode;
	
	
	/**
	 * 日志对象
	 * @var object
	 */
	private $_logger;
	
	
    public function init()
    { 
//       	echo 'the dispatcher start..',PHP_EOL;
	    $this->_orgcode = isset($_GET['__orgcode']) ? trim($_GET['__orgcode']) : '';
	    $this->_logger	    = Yii::createObject([
	    	  'class'=>'yunke\mqs\log\File',
	    	  'logFile'=>'@runtime/logs/dispatcher_'.date('Ymd').'.log'
	    		
	    	]);		 
	    
	    
	    if( is_array( $this->whitelist ) && in_array( $this->_orgcode , $this->whitelist ) )
	    {
	    	$cfg  = $this->targets['ali'];
	    	$this->_logger->log("Target:ali",$this->_orgcode);
	    	
	    }else{
	    	$this->_logger->log("Target:httpsqs",$this->_orgcode);
	    	$cfg  =  $this->targets['httpsqs'];
	    	
	    }
	    
	       
    	$this->_target = Yii::createObject($cfg);
    	
    	
    }	
	
    
    /**
     * 调度方法
     * @see \yii\base\Component::__call()
     */
    public function __call($name, $params)
    {  
       return call_user_func_array(array($this->_target,$name),$params);
    }
    
	
        
    
    
}