<?php
namespace mysoft\helpers;

class AesHelper {

	private static $_secret_key = 'myyk';
		
	public static function encode($data,$secret_key='') {
		if($secret_key==''){
			$secret_key = self::$_secret_key;
		}
		
		$td = mcrypt_module_open(MCRYPT_RIJNDAEL_256,'',MCRYPT_MODE_CBC,'');
		$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td),MCRYPT_RAND);
		mcrypt_generic_init($td,$secret_key,$iv);
		$encrypted = mcrypt_generic($td,$data);
		mcrypt_generic_deinit($td);

		return $iv . $encrypted;
	}
		
	public static function decode($data,$secret_key='') {
		if($secret_key==''){
			$secret_key = self::$_secret_key;
		}
		
		$td = mcrypt_module_open(MCRYPT_RIJNDAEL_256,'',MCRYPT_MODE_CBC,'');
		$iv = mb_substr($data,0,32,'latin1');
		mcrypt_generic_init($td,$secret_key,$iv);
		$data = mb_substr($data,32,mb_strlen($data,'latin1'),'latin1');
		$data = mdecrypt_generic($td,$data);
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);

		return trim($data);
	}
}