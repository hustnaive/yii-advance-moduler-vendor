<?php
/**
 * Ali的消息队列
 * 
 * 组件配置参数
 * AccessKey    -  接入key
 * AccessSecret -  接入密钥
 * mqsurl 		-  队列地址
 * queueownerid -  使用密钥
 * 
 * 组件配置内容举例
 * 'mqs2'=>[
 *			  'class'=>'yunke\mqs\Ali',
 *			  'AccessKey'=>'FIGOQIidyBsGPf29',
 *			  'AccessSecret'=>'uFvE1MMZuQv9T65Q0CVh4YOc9NWyb3',
 *			  'queueownerid'=>'658rw',
 *			  'mqsurl'=>'mqs-cn-hangzhou.aliyuncs.com'
 *		
 *		] 
 * ~~~~ 
 * mqsurl 可以根据所在区域机房选择内外网
 * 
 * mqs-cn-hangzhou.aliyuncs.com  - 杭州公网地址
 * mqs-cn-hangzhou-internal.aliyuncs.com - 杭州内网地址
 * 
 * 
 * @author yangzhen
 */
namespace mysoft\mqs;
use yunke\http\Curl;

class Ali extends MqsAbs
{
	public $AccessKey 		= '';
	public $AccessSecret 	= '';
	public $CONTENT_TYPE 	= 'text/xml;utf-8';
	public $MQSHeaders		= '2014-07-08';
	public $queueownerid	= '';
	public $mqsurl			= '';


	/**
	 * 初始化处理
	 * @see \yii\base\Object::init()
	 */
	public function init()
	{
	     //TODO： 优先处理配置逻辑	
	}
	
	/**
	 * http请求
	 * @param string $request_uri
	 * @param string $request_method
	 * @param string $request_header
	 * @param string $request_body
	 * @return string
	 */
	
	protected function requestCore( $request_uri, $request_method, $request_header, $request_body = "" )
	{
		if( $request_body != "" ){
			$request_header['Content-Length'] = strlen( $request_body );
		}
		$_headers = array(); foreach( $request_header as $name => $value )$_headers[] = $name . ": " . $value;
		$request_header = $_headers;
		$request_header[]='Expect:';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $request_uri);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request_method);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $request_header);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request_body);
		$res = curl_exec($ch);
		curl_close($ch);
		return $data = explode("\r\n\r\n",$res);
	}
	/**
	 * 获取错误handle
	 * @param string  $headers
	 * @return boolean|mixed
	 */
	protected function errorHandle( $headers )
	{
		preg_match('/HTTP\/[\d]\.[\d] ([\d]+) /', $headers, $code);
		if($code[1]){
			if( $code[1] / 100 > 1 && $code[1] / 100 < 4 ) return false;
			else return $code[1];
		}
	}
	
	/**
	 * 获取签名
	 * @param string $VERB
	 * @param string $CONTENT_MD5
	 * @param string $CONTENT_TYPE
	 * @param string $GMT_DATE
	 * @param string $CanonicalizedMQSHeaders
	 * @param string $CanonicalizedResource
	 * @return string
	 */
	protected function getSignature( $VERB, $CONTENT_MD5, $CONTENT_TYPE, $GMT_DATE, $CanonicalizedMQSHeaders = array(), $CanonicalizedResource = "/" )
	{
		$order_keys = array_keys( $CanonicalizedMQSHeaders );
		sort( $order_keys );
		$x_mqs_headers_string = "";
		foreach( $order_keys as $k ){
			$x_mqs_headers_string .= join( ":", array( strtolower($k), $CanonicalizedMQSHeaders[ $k ] . "\n" ) );
		}
		$string2sign = sprintf(
				"%s\n%s\n%s\n%s\n%s%s",
				$VERB,
				$CONTENT_MD5,
				$CONTENT_TYPE,
				$GMT_DATE,
				$x_mqs_headers_string,
				$CanonicalizedResource
		);
		$sig = base64_encode(hash_hmac('sha1',$string2sign,$this->AccessSecret,true));
		return "MQS " . $this->AccessKey . ":" . $sig;
	}
	
	
	/**
	 * 获取GMT时间
	 * @return string
	 */
	protected function getGMTDate()
	{
		date_default_timezone_set("UTC");
		return date('D, d M Y H:i:s', time()) . ' GMT';
	}
	
	/**
	 * 解析XML
	 * @param   string $strXml
	 * @return array|string
	 */
	protected function getXmlData($strXml)
	{
		$pos = strpos($strXml, '<?xml');
                //var_dump($pos);
                //echo $strXml;die;        
		if ($pos !== false) {
			$xmlCode=simplexml_load_string($strXml,'SimpleXMLElement', LIBXML_NOCDATA);
			$arrayCode=$this->get_object_vars_final($xmlCode);
			return $arrayCode ;
		} else {
			return '';
		}
	}
	
	/**
	 * 对象转换成数组
	 * @param object  $obj
	 * @return array
	 */
	protected function get_object_vars_final($obj)
	{
		if(is_object($obj)){
			$obj=get_object_vars($obj);
		}
		if(is_array($obj)){
			foreach ($obj as $key=>$value){
				$obj[$key]=$this->get_object_vars_final($value);
			}
		}
		return $obj;
	}
	
	
	
	/******************		队列实际操作	 ********/
	
	//创建一个新的消息队列。
	public function Createqueue($parameter=array())
	{
		$queueName = $this->qName;
		//默认参数值
		$queue=array('DelaySeconds'=>0,'MaximumMessageSize'=>65536,'MessageRetentionPeriod'=>345600,'VisibilityTimeout'=>30,'PollingWaitSeconds'=>30);
		foreach($queue as $k=>$v){
			foreach($parameter as $x=>$y){
				if($k==$x){	$queue[$k]=$y;	}		
			}
		}
		$VERB = "PUT";
		$CONTENT_BODY = $this->generatequeuexml($queue);
		$CONTENT_MD5 = base64_encode( md5( $CONTENT_BODY ) );
		$CONTENT_TYPE = $this->CONTENT_TYPE;
		$GMT_DATE = $this->getGMTDate();
		$CanonicalizedMQSHeaders = array(
				'x-mqs-version' => $this->MQSHeaders
		);
		$RequestResource = "/" . $queueName;
	
		$sign = $this->getSignature($VERB,$CONTENT_MD5,$CONTENT_TYPE,$GMT_DATE,$CanonicalizedMQSHeaders,$RequestResource);
	
		$headers = array(
				'Host' => $this->queueownerid.".".$this->mqsurl,
				'Date' => $GMT_DATE,
				'Content-Type' => $CONTENT_TYPE,
				'Content-MD5' => $CONTENT_MD5
		);
		foreach( $CanonicalizedMQSHeaders as $k => $v){
			$headers[ $k ] = $v;
		}
		$headers['Authorization'] = $sign;
		$request_uri = 'http://' . $this->queueownerid .'.'. $this->mqsurl . $RequestResource;
		$data=$this->requestCore($request_uri,$VERB,$headers,$CONTENT_BODY);
		//返回状态，正确返回ok,错误返回错误代码！
		$error = $this->errorHandle($data[0]);
		if($error){
			$msg['state']=$error;
			$msg['msg']=$this->getXmlData($data[1]);
		}else{
			$msg['state']="ok";
		}
		return $msg;
	}
	
	/**
	 * 转换成队列的xml
	 * @param array $queue
	 */
	private function generatequeuexml($queue=array())
	{
// 		header('Content-Type: text/xml;');
		$dom = new \DOMDocument("1.0", "utf-8");
		$dom->formatOutput = TRUE;
		$root = $dom->createElement("Queue");
		$dom->appendchild($root);
		$price=$dom->createAttribute("xmlns");
		$root->appendChild($price);
		$priceValue = $dom->createTextNode('http://mqs.aliyuncs.com/doc/v1/');
		$price->appendChild($priceValue);
	
		foreach($queue as $k=>$v){
			$queue = $dom->createElement($k);
			$root->appendChild($queue);
			$titleText = $dom->createTextNode($v);
			$queue->appendChild($titleText);
		}
		return $dom->saveXML();
	}
	
	/**
	 * 转换为消息的xml
	 * @param string $msgbody
	 * @param int $DelaySeconds
	 * @param int $Priority
	 */
	private function generatexml($msgbody,$DelaySeconds=0,$Priority=8){
// 		header('Content-Type: text/xml;');
		$dom = new \DOMDocument("1.0", "utf-8");
		$dom->formatOutput = TRUE;
		$root = $dom->createElement("Message");//创建消息的根节点
		$dom->appendchild($root);
		$price=$dom->createAttribute("xmlns");
		$root->appendChild($price);
		$priceValue = $dom->createTextNode('http://mqs.aliyuncs.com/doc/v1/');
		$price->appendChild($priceValue);
		$msg=array('MessageBody'=>$msgbody,'DelaySeconds'=>$DelaySeconds,'Priority'=>$Priority);
      
		foreach($msg as $k=>$v){
			$msg = $dom->createElement($k);
			$root->appendChild($msg);
			$titleText = $dom->createTextNode($v);
			$msg->appendChild($titleText);
		}
		return $dom->saveXML();
	}
	

	/**
	 * 发送消息
	 * @param string $msgbody
	 * @param int 	 $DelaySeconds
	 * @param int  	 $Priority
	 * @return array
	 */
	public function SendMessage($msgbody,$DelaySeconds=0,$Priority=8)
	{
		$queueName = $this->qName; 
		$VERB = "POST";
		$CONTENT_BODY = $this->generatexml($msgbody,$DelaySeconds,$Priority);
		$CONTENT_MD5  = base64_encode(md5($CONTENT_BODY));
		$CONTENT_TYPE = $this->CONTENT_TYPE;
		$GMT_DATE = $this->getGMTDate();
		$CanonicalizedMQSHeaders = array(
				'x-mqs-version' => $this->MQSHeaders
		);
		$RequestResource = "/" . $queueName . "/messages";
		$sign = $this->getSignature( $VERB, $CONTENT_MD5, $CONTENT_TYPE, $GMT_DATE, $CanonicalizedMQSHeaders, $RequestResource );
		$headers = array(
				'Host' => $this->queueownerid.".".$this->mqsurl,
				'Date' => $GMT_DATE,
				'Content-Type' => $CONTENT_TYPE,
				'Content-MD5' => $CONTENT_MD5
		);
		foreach( $CanonicalizedMQSHeaders as $k => $v){
			$headers[ $k ] = $v;
		}
		$headers['Authorization'] = $sign;
		$request_uri = 'http://' . $this->queueownerid .'.'. $this->mqsurl . $RequestResource;
		
		$data=$this->requestCore($request_uri,$VERB,$headers,$CONTENT_BODY);
		//返回状态，正确返回ok和返回值数组,错误返回错误代码和错误原因数组！
		$msg=array();
		$error = $this->errorHandle($data[0]);
                
		if($error){
			$msg['state']=$error;
			$msg['msg']=$this->getXmlData($data[1]);
		}else{
			$msg['state']="ok";
			$msg['msg']=$this->getXmlData($data[1]);
		}
		return $msg;
	}
	
	
	/**
	 * 读消息，只修改状态为Inactive，在NextVisibleTime后依然可读
	 * @param string $Second
	 * @return array
	 */
	public function ReceiveMessage($Second=false){
		$queueName = $this->qName;
		$VERB = "GET";
		$CONTENT_BODY = "";
		$CONTENT_MD5 = base64_encode( md5( $CONTENT_BODY ) );
		$CONTENT_TYPE = $this->CONTENT_TYPE;
		$GMT_DATE = $this->getGMTDate();
		$CanonicalizedMQSHeaders = array(
				'x-mqs-version' => $this->MQSHeaders
		);
		//$RequestResource = "/" . $queueName . "/messages?waitseconds=".$Second;
		$RequestResource = "/" . $queueName . "/messages";
		if($Second !== false) $RequestResource .= "?waitseconds=".$Second;
		
		$sign = $this->getSignature( $VERB, $CONTENT_MD5, $CONTENT_TYPE, $GMT_DATE, $CanonicalizedMQSHeaders, $RequestResource );
		$headers = array(
				'Host' => $this->queueownerid.".".$this->mqsurl,
				'Date' => $GMT_DATE,
				'Content-Type' => $CONTENT_TYPE,
				'Content-MD5' => $CONTENT_MD5
		);
		foreach( $CanonicalizedMQSHeaders as $k => $v){
			$headers[ $k ] = $v;
		}
		$headers['Authorization'] = $sign;
		$request_uri = 'http://' . $this->queueownerid .'.'. $this->mqsurl . $RequestResource;
		$data=$this->requestCore($request_uri,$VERB,$headers,$CONTENT_BODY);
            
		//返回状态，正确返回ok和返回值数组,错误返回错误代码和错误原因数组！
		$msg=array();
		$error = $this->errorHandle($data[0]);
		if($error){
			$msg['state']=$error;
			$msg['msg']=$this->getXmlData($data[1]);
		}else{
			$msg['state']="ok";
			$msg['msg']=$this->getXmlData($data[1]);
		}
		return $msg;
	}
	
	/**
	 * 删除消息
	 * 
	 * @param  string $ReceiptHandle  [本次获取消息产生的临时句柄,用于删除和修改处于 Inactive 消息,NextVisibleTime 之前有效。]
	 * @return array
	 */
	public function DeleteMessage($ReceiptHandle)
	{
		$queueName = $this->qName;
		$VERB = "DELETE";
		$CONTENT_BODY = "";
		$CONTENT_MD5 = base64_encode( md5( $CONTENT_BODY ) );
		$CONTENT_TYPE = $this->CONTENT_TYPE;
		$GMT_DATE = $this->getGMTDate();
		$CanonicalizedMQSHeaders = array(
				'x-mqs-version' => $this->MQSHeaders
		);
		$RequestResource = "/" . $queueName . "/messages?ReceiptHandle=".$ReceiptHandle;
		$sign = $this->getSignature($VERB,$CONTENT_MD5,$CONTENT_TYPE,$GMT_DATE,$CanonicalizedMQSHeaders,$RequestResource);
		$headers = array(
				'Host' => $this->queueownerid.".".$this->mqsurl,
				'Date' => $GMT_DATE,
				'Content-Type' => $CONTENT_TYPE,
				'Content-MD5' => $CONTENT_MD5
		);
		foreach( $CanonicalizedMQSHeaders as $k => $v){
			$headers[ $k ] = $v;
		}
		$headers['Authorization'] = $sign;
		$request_uri = 'http://' . $this->queueownerid .'.'. $this->mqsurl . $RequestResource;
		$data=$this->requestCore($request_uri,$VERB,$headers,$CONTENT_BODY);
		$error = $this->errorHandle($data[0]);
		
		//返回状态，正确返回ok和返回值数组,错误返回错误代码和错误原因数组！
		$msg = array();
		
		if($error){
			$msg['state']=$error;
		}else{
			$msg['state']="ok";
		}
		return $msg;
	}
	
	/**
	 * 查看队列顶部的消息，并修改消息状态
	 * 
	 * @return array
	 */
	public function PeekMessage()
	{
		$queueName = $this->qName;
		$VERB = "GET";
		$CONTENT_BODY = "";
		$CONTENT_MD5 = base64_encode(md5($CONTENT_BODY));
		$CONTENT_TYPE = $this->CONTENT_TYPE;
		$GMT_DATE = $this->getGMTDate();
		$CanonicalizedMQSHeaders = array(
				'x-mqs-version' => $this->MQSHeaders
		);
		$RequestResource = "/" . $queueName . "/messages?peekonly=true";
		$sign = $this->getSignature( $VERB, $CONTENT_MD5, $CONTENT_TYPE, $GMT_DATE, $CanonicalizedMQSHeaders, $RequestResource );
		$headers = array(
				'Host' => $this->queueownerid.".".$this->mqsurl,
				'Date' => $GMT_DATE,
				'Content-Type' => $CONTENT_TYPE,
				'Content-MD5' => $CONTENT_MD5
		);
		foreach( $CanonicalizedMQSHeaders as $k => $v){
			$headers[ $k ] = $v;
		}
		$headers['Authorization'] = $sign;
		$request_uri = 'http://' . $this->queueownerid .'.'. $this->mqsurl . $RequestResource;
		$data=$this->requestCore($request_uri,$VERB,$headers,$CONTENT_BODY);
		
		//返回状态，正确返回ok和返回值数组,错误返回错误代码和错误原因数组！
		$msg=array();
		$error = $this->errorHandle($data[0]);
		if($error){
			$msg['state']=$error;
			$msg['msg']=$this->getXmlData($data[1]);
		}else{
			$msg['state']="ok";
			$msg['msg']=$this->getXmlData($data[1]);
		}
		return $msg;
		
	}
	
	
	
	/********************             实现消息队列通用方法  					*********************/
	
	/**
	 * 消息投递
	 * @see \yunke\mqs\MqsAbs::put()
	 */
	public function put($data)
	{
	   $data 	= json_encode($data);
	   $result  = $this->SendMessage($data);
           
	   if( $result['state'] ==  'ok' )
	   {
	      return [$result['msg']['MessageId'],static ::QUEUE_PUT_OK];	
	   }
	   else
	   {
                  $error = isset($result['msg']['Message']) ? $result['msg']['Message'] : '';
	   	  return [-1,static ::QUEUE_PUT_ERR . ':'.$result['state'].','.$error];	
	   } 
	   
	}
	
	/**
	 * 获取到消息
	 *    先receive然后delete掉消息
	 * @see \yunke\mqs\MqsAbs::get()
	 */
	public function get()
	{
            
	   $result = $this->ReceiveMessage();//只读未删除
	   if( $result['state'] == 'ok' )
	   {
	   	 $MessageId	    = $result['msg']['MessageId'];
	   	 $ReceiptHandle = $result['msg']['ReceiptHandle'];
	   	 $MessageBody   = $result['msg']['MessageBody'];
	   	 $MessageBody	= json_decode($MessageBody,true);
	   	
	   	
	   	 //删除消息
	   	 $result2 = $this->DeleteMessage($ReceiptHandle);
	   	  
	   	 if( $result2['state'] == 'ok' )
	   	 {
	   	 	
	   	 	return [$MessageId , $MessageBody]; //成功则返回消息标识和消息内容
	   	 }
	   	 else 
	   	 {
	   	    return [-1,"delete message error:".$result2['state'].','.$result2['msg']['Message']];
	   	 }
	   	
	   }
	   else 
	   {
	   	    return [-1 , "receive message error:".$result['state'].','.$result['msg']['Message']];
	   }
		
		
	}
	
	
	public function view($pos)
	{
	    //TODO : 等同 PeekMessage
	}
	
	
	public function reset()
	{
	    //TODO : 队列清理逻辑,API暂时不支持
	}
	
	
}
