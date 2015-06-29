<?php
/**
 * @example  \yunke\helpers\Identity::login(...);
 * @author yangzhen
 *
 */
namespace yunke\helpers;

class Identity
{
	/**
	 * 登陆
	 * @param type $app 用户类型
	 * @param type $userinfo 身份信息（id，username，tel,pwd）
	 * @param type $token 公众号
	 */
	public static function login($app,$userinfo,$token="") {
		cookie($app."_".$token.'_mycms_identity', $userinfo,86400);
	}
	
	/**
	 * 注销
	 * @param string $userType 用户类型
	 */
	public static function logout($app,$token=""){
		cookie($app."_".$token.'_mycms_identity',null);
	}
	
	/**
	 * 获取当前用户信息
	 * @param 字符串 $userType 用户类型
	 * @return array 用户信息；key为'userName','tel','uid'
	 *
	 */
	public static function current($app,$token=""){
		return cookie($app."_".$token.'_mycms_identity');
	}
	
}