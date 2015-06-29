<?php
/**
 * HttpSqs 消息队列
 * @author yangzhen
 *
 */

namespace yunke\mqs;
use yunke\http\Curl;


class HttpSqs extends MqsAbs
{
	
	
	/**
	 * 构造http请求的URL
	 * @param  array $query
	 * @return string
	 */
	private function _getRequestUrl($query)
	{
	  
	  if($this->qName) 	    	 $query['name'] = $this->qName;
	  if($this->getauth()) 		 $query['auth'] = $this->getauth();
	  if($this->getCharset())    $query['charset'] = $this->getCharset();
      
	  return rtrim($this->getServer(),'/').'/?'.http_build_query($query);
		
	}
	
	
	/**
	 * 获取CURL对象，实现单例
	 * @return object \yunke\http\Curl
	 */
	private function _getCurl()
	{
		static $curl; //单例对象
		
		if($curl) return $curl;
		$curl = new Curl();
		$curl->setOption(CURLOPT_HEADER, true);//开启捕获header信息
		$curl->setOption(CURLOPT_VERBOSE, false);
		
		return $curl;		
	}
	
	
	/**
	 * CURL实现请求,默认GET方式
	 * @param  array  $query  请求参数
	 * @param  bool	  $getPos 是否获取队列中的pos值
	 * @return array
	 */
	private function _getContent($query,$getPos=true)
	{
	
		$arr = [];
		
		$curl = $this->_getCurl();
		
		$body = $curl->get($this->_getRequestUrl($query));
		array_push($arr,$body);

		if($getPos) array_push($arr,$curl->getHeader('Pos'));
		
		return $arr;		
	} 
	
	
	/**
	 * POST方式
	 * @param array  $query
	 * @param bool   $getPos
	 */
	private  function _getContentByPost($query,$getPos=true){
		 
		 $postdata = $query['data'];
		 unset($query['data']);
		 
		 $arr = [];
		 $curl  = $this->_getCurl();
		 $url   = $this->_getRequestUrl($query);
		 $body  = $curl->post($url, $postdata);
		 array_push($arr,$body);
		 
		 if($getPos) array_push($arr,$curl->getHeader('Pos'));
		 
		 return $arr;
		 
	}
	
	/**
	 * 获取服务器实例信息
	 * @return string
	 */
	public function serverInfo()
	{		
		return $this->getServer();
	}
	
	
	/**
	 * 实现进队列逻辑
	 * @see \yunke\mqs\MqsAbs::put()
	 * @param array|string $data
	 * @return array [$pos,$msg] $pos = -1 表示 失败,msg输出原因
	 */
	public function put($data)
	{
	 	$query = [];
	 	$query['opt'] = 'put';
	 	$query['data'] = json_encode($data);	
	 	list($res,$pos) = $this->_getContentByPost($query);
	 	
	 	switch($res){
	 		
	 		case 'HTTPSQS_PUT_ERROR' :
	 			return [-1,static ::QUEUE_PUT_ERR];
 				break;
	 	     
	 		case 'HTTPSQS_PUT_END'	 :
	 			return [-1,static :: QUEUE_PUT_END];
	 		    break;	
	 		 
	 		case  'HTTPSQS_PUT_OK':
	 			return [$pos,static ::QUEUE_PUT_OK];
	 			break;
	 			
	 		default :
	 			return [-1,static::QUEUE_UNKNOW_ERR . ',可能是网络连接问题'];
	 		
	 	}
	 	
	}
	 
	 /**
	  * 实现出队列逻辑
	  * @see \yunke\mqs\MqsAbs::get()
	  * @return array [$pos,$data] $pos =-1 时，$data 表示失败原因，否则为取出的元素
	  */
	 public function get()
	 {
	 	$query = [];
	 	$query['opt'] = 'get';
	 	list($res,$pos) = $this->_getContent($query);
	 	
	 	if($res == 'HTTPSQS_GET_END'){
	 		return [-1,static::QUEUE_GET_END];
	 	}
	 	
	 	return [$pos,json_decode($res,true)];
	 		 	
	 }
	
	 
	 /**
	  * 实现查看队列元素
	  * @see \yunke\mqs\MqsAbs::view()
	  * @param int  $pos
	  * @return array|string
	  */
	 public function view($pos=1)
	 {
	 	$query = [];
	 	$query['opt'] = 'view';
	 	$query['pos'] = $pos;
	 	list($res) = $this->_getContent($query,false);
	 	
	 	return json_decode($res,true);
	 	
	 }
	 
	 
	 /**
	  * 获取状态
	  * @param bool $isJson 是否以JSON格式返回，如果是则会先自动JSON处理成array
	  * @return array|string
	  */
	 public function status($isJson=false)
	 {
	 	 $query = [];
	 	 $query['opt'] = $isJson ? 'status':'status_json';
	 	 list($res) = $this->_getContent($query,false);
	 	 
	 	 if($isJson){
	     	$res = json_decode($res,true);		 	 	
	 	 }
	 	 
	 	 return $res;
	 }
	 
	 
	 /**
	  * 实现重设队列，会清空队列
	  * @see \yunke\mqs\MqsAbs::reset()
	  */
	 public function reset()
	 {
	 	//TODO：实现重设队列
	 	
	 }
	
}