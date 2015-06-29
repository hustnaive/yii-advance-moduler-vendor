<?php

namespace yunke\helpers;
use Oauth\API_SMS;
use yunke\dbprovider\DbProvider;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates and open the template
 * in the editor.
 */

/**
 * 发送短信方法、
 * 短信发送类、
 * 提供了短信每日发送条数限制方法、
 * 随机验证码生成方法、
 * 客户端重新发送短信时间间隔方法、
 * 服务端验证码有效时间间隔方法
 *
 * @author baol
 */

class SmsHelper {
	//短信接口参数
	private static $apiBaseURL;
	private static $authURL;
	private static $clientId;
	private static $clientSecret;
	
	//短信模版内容过滤正则
	private static $contentRegex = "/\/|\~|\!|\@|\#|\￥|\（|\）|\%|\{}|\^|\&|\*|\(|\)|\_|\+|\:|\<|\>|\?|\[|\]|\,|\.|\/|\;|\'|\`|\-|\=|\\\|\|/";
	
	/**
	 * 初始化函数
	 * @param string $orgcode
	 */
	public static function init() {	
		//短信接口参数
		self::$apiBaseURL = Conf::getConfig('PlatformSiteUrl');
		self::$authURL = Conf::getConfig('RestApiAuthUrl');
		self::$clientId = Conf::getConfig('RestApiClientId');
		self::$clientSecret = Conf::getConfig('RestApiClientSecret');
	}
	
    /**
     * 获取随机验证码
     * @return string
     */
    public static function GetSmsMessage() {
        return rand(100000, 999999); //获取随机数
    }

    /**
     * 校验验证码是否正确
     * @param string $userInputCode 用户输入的验证码
     * @param int $isRequestCode 用户是否请求了验证码
     * @param string $tel 用户电话
     * @return string
     */
    public static function CheckSmsMessage($userInputCode, $isRequestCode, $tel) {
        if (empty($userInputCode)) {
            return "请输入短信验证码";
        }
        if (empty($isRequestCode)) {
            return "请获取验证码";
        }
		
        //获取cookie上的验证码
        $cookieSmsCode = cookie("mysoft_sms_validate_" . $tel);
        if(!empty($isRequestCode) && (!isset($cookieSmsCode) || empty($cookieSmsCode))){
        	return "验证码已过期，请重新获取!";
        }
        $cacheSmsCode = $cookieSmsCode;
        /* if (!empty($isRequestCode) && (!isset($cacheSmsCode) || empty($cacheSmsCode))) {
            return "验证码已过期，请重新获取!";
        } */
        if ($cacheSmsCode != $userInputCode) {
            return "短信验证码错误!";
        }
        //验证码验证完，删除掉cookie
        cookie("mysoft_sms_validate_" . $tel,null);
        return "";
    }

    /**
     * 获取每天总共可以发送的短信个数
     * @return int
     */
    public static function GetSmsCount() {
        return 3;
    }

    /**
     * 判断短信是否超过每日次数
     * @return boolean
     */
    public static function IsSmsOutCount() {
        $count = cookie("mysoft_sms_count");
        if (!isset($count) || empty($count)) {
            $count = self::GetSmsCount();
            cookie("mysoft_sms_count", $count);
        }
        if ($count == 0) {
            return true;
        }
        return false;
    }

    /**
     * 获取短信在客户端重发时间间隔
     * @return int
     */
    public static function GetSmsOutTime() {
        return 60 * 1000;
    }

    /**
     * 获取短信验证码在服务端的有效时间
     * @return int
     */
    public static function GetSmsServerValidateTime() {
        return 60 * 3 * 1000;
    }

    /**
     * 发送短信
     * @param string $tel 电话号码
     * @param string $template 短信模版
     * @param string $msg 消息内容
     * @return array
     */
    public static function SendSms($orgcode, $tel, $msg, $token, $appcode, $scenecode)
    { 
    	//验证码
    	$code = $msg;
    	  
    	//获取短信消息内容
    	$msg = self::getSmsContent($orgcode, $token, $appcode, $scenecode, $msg);

    	//请求日志
    	$logId = self::AddSmsLog($orgcode, '', $tel, $msg, ''); 

    	//发送短信
    	$sms = new API_SMS(self::$apiBaseURL,self::$authURL,self::$clientId,self::$clientSecret);
    	$res = $sms->SendSMS(self::getOrganizationIdByToken($token), $tel, $msg);
    	
    	//响应日志
 		self::AddSmsLog($orgcode, $logId, '', '', $res);
    	
    	if ($res['Reslut']) {
    		$count = cookie("mysoft_sms_count");//短信计数
    		cookie("mysoft_sms_count", $count - 1);
    		cookie("mysoft_sms_validate_" . $tel, $code);
    		return ['msg' => '发送短信成功','isSuccess' => 1,'statusCode' =>$msg];
    	} 
    	return ['msg' => '发送短信失败','isSuccess' => 0,'statusCode' =>$msg];
    }   
    
    /**
     * 获取租户id
     * @param string $token
     * @return mixed|string
     */
    public static function getOrganizationIdByToken($token)
    {
    	$orgInfo= (new DbProvider('config'))->fetch('pub/org/getOrganizationIdByToken', [$token]);
    	if(isset($orgInfo['OrganizationId'])){
    		return $orgInfo['OrganizationId'];
    	}
    	return '';
    }
    
    /**
     * 获取发送短信的内容
     * @param string $orgcode
     * @param string $token
     * @param string $appcode
     * @param string $scenecode
     * @param string $msg
     * @return string
     */
    public static function getSmsContent($orgcode, $token, $appcode, $scenecode,$msg)
    {
    	//获取短信模版
    	$template = self::getSmsTemplate($orgcode, $token, $appcode, $scenecode);
    	 
    	//将中文短信内容转码
    	if (empty($template) || $template == "404") {
    		$msg = iconv("utf-8", "gb2312", $msg);
    	} else {
    		$msg = str_replace('{$code}', $msg, $template);
    	}
    	
    	return $msg;
    }
    
    /**
     * 添加短信日志
     * @param string $orgcode 租户编码
     * @param unknown $logid  日志id
     * @param unknown $tel	    电话号码
     * @param unknown $msg	    短信内容
     * @param unknown $response 短信响应
     * @return string
     */
    public static function AddSmsLog($orgcode,$logId,$tel,$msg,$response)
    {
    	//新增
    	if(empty($logId)){
    		$logId = String::uuid();
    		$logRequest = [
    			'sms_logId'=> $logId,
    			'request'=>json_encode(
    				[
    					"apiBaseURL" => self::$apiBaseURL,
    					"authURL" => self::$authURL,
    					"clientId" => self::$clientId,
    					"clientSecret" => self::$clientSecret,
    					"tel" => $tel,
    					"msg" => $msg
    				]),
    			'startdate' => date('Y-m-d H:i:s')
    		];
    		$res =  (new DbProvider($orgcode))->fetch('pub/sms/addSmsLog', [$logId,$logRequest]);
    		return $logId;
    	}else {
    		//更新
    		$logResponse = ['response'=>json_encode($response),'enddate'=>date("Y-m-d H:i:s")];
    		$res =  (new DbProvider($orgcode))->fetch('pub/sms/modifySmsLog', [$logId,$logResponse]);
    	}
    }

    /*
     * 判断各状态下，短信是否开启
    * $token 公众号
    * $appcode项目区分5001粉丝，5002移动销售，5003全民营销
    * $scenecode 场景值register注册登录，forget忘记密码
    * $return 1开启，0未开启
    */
    public static function getSmsStatus($orgcode, $token, $appcode, $scenecode)
    {
    	$res = (new DbProvider($orgcode))->fetch('pub/sms/getSmsTemplate',[$token,$appcode,$scenecode]);
    	
    	if(isset($res) && $res['iseable']=='1'){
    		return 1;
    	}
    	return 0;
    }
    
    /*
     * 查找短信模板
    * $token 公众号
    * $appcode项目区分5001粉丝，5002移动销售，5003全民营销
    * $scenecode 场景值register注册登录，forget忘记密码
    * return 返回模板
    */
    public static function getSmsTemplate($orgcode, $token, $appcode, $scenecode)
    {
    	$res = (new DbProvider($orgcode))->fetch('pub/sms/getSmsTemplate',[$token,$appcode,$scenecode]);
    	 
    	if(isset($res) && !empty($res['content'])){
			$content = str_replace(' ','',$res['content']);
			return trim(preg_replace(self::$contentRegex,"",$content));
		}
		
		return 404;
    }
}
