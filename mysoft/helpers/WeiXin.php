<?php
/**
 * 微信逻辑帮手类
 * @author yangzhen
 *
 */
namespace mysoft\helpers;

class WeiXin
{
	
	/**
	 * 获取小猪站点地址
	 * @param  string  $token
	 * @return string
	 */
	public static function getShareToPigUrl($token){
		if(empty($token)){
			return "";
		}else{
			$return_url = 'http://' . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
			$url = Conf::fromCache('PigSiteUrl'). '/index.php?g=ApplibWap&m=Entrance&a=index&token=' . $token .'&return_url='.urlencode($return_url);
			return $url;
		}
	}
	
	/**
	 * 获取openid
	 * @param  string $mytoken
	 * @return mixed|void
	 */
	public static function getopenid($mytoken)
	{
				
		//加在这里，是因为请求openid必须要使用80端口。
		//我们现在是在Pigcms(占用80端口)里请求后，跳转到微网站的，因此微网站获取openid只能通过cookie或者get参数

		if(isset($_GET['devopenid']) && !empty($_GET['devopenid'])){
			cookie($mytoken.'_openid',$_GET['devopenid'],time()+8640000);
			return $_GET['devopenid'];
		}else{

			//如果 cookie 没有值，则获取微信服务器的openid
			if (!cookie($mytoken.'_openid'))
			{
				$url = self::getShareToPigUrl($mytoken);
				header('Location: ' . $url);				
				exit;
				
		
			}else{
				return cookie($mytoken.'_openid');
			}
		}
   }
	
	
}