<?php
namespace mysoft\helpers;

/**
 * 全局通用类，助手静态加载
 * @author yangzhen
 *
 */
class G
{
	

	/**
	 * 缓存key生成规则
	 * 
	 * 	G::getCacheKey('userlist',['proj'=>xxxx,'token'=>xxxx]);
	 * 
	 * @return string
	 */
	public static function getCacheKey($rule_key,$params)
	{
		 $cache_key_rule  = \Yii::$app->params['Cache_Key_Rules'];
		
	     if (!isset($cache_key_rule[$rule_key])) 
	      	    throw new \yii\base\InvalidValueException("缓存规则key表示不存在!");
	      
	     $str = $cache_key_rule[$rule_key];
         //根据str中的{}数量判断参数中的数量  modify by sglz
	     if( count($params) != preg_match_all('({\w+})', $str) )
	     	    throw new \yii\base\InvalidValueException("缓存规则key的参数个数有问题，请检查key规则");
		
	    	     
	     foreach($params as $k=>$v)
	     {
	     	$r_key  = sprintf("{%s}",$k);
	     	
	     	if(strpos($str,$r_key) === false)
	     	    throw new \yii\base\InvalidParamException("缓存参数params错误，请检查key规则");
	     	
	     	$str = str_replace($r_key, $v, $str);
	     		     	
	     }
	     
	     return $str;
	     
	     
	}
	
	/**
	 * 反射，用来获取action注释信息中的动作点
	 * G::get_action_desc('index');
	 * @param string $class 
	 * - a string (e.g. "modules\admin\controllers\IndexController")
	 * @param string $actionid	 
	 * - a string (e.g. "add")
	 * @return string
	 */
	public static function get_action_desc($class,$actionid){
		$class_method = $class.'::action'.$actionid;
		$func  = new \ReflectionMethod($class_method);
		$tmp   = $func->getDocComment();
		$flag  = preg_match('/@actionid\s+(\d+)/',$tmp,$tmprlt);
		if(isset($tmprlt[1]))
		{
			return $tmprlt[1];
		}
		return 00;
	}
	
	
	/**
	 * 获取当前登陆用户当次时刻的请求唯一标识
	 * @return string
	 */
	public static function unique_id()
	{
		return sprintf('%s|%s',session_id(),date('YmdHis'));
	}
	
	
	/**
	 * Detect upload file type
	 *
	 * @param  array $file
	 * @return string  $flag,返回类型
	 */
	public static function detectUploadFileMIME($file)
	{
	
		// 1.through the file extension judgement 03 or 07
		$flag = '';
		$file_array = explode ( ".", $file ["name"] );
		$file_extension = strtolower ( array_pop ( $file_array ) );
	
		// 2.through the binary content to detect the file
		switch ($file_extension) {
			case "xls" :
				// 2003 excel
				$fh = fopen ( $file ["tmp_name"], "rb" );
				$bin = fread ( $fh, 8 );
				fclose ( $fh );
				$strinfo = @unpack ( "C8chars", $bin );
				$typecode = "";
				foreach ( $strinfo as $num ) {
					$typecode .= dechex ( $num );
				}
				if ($typecode == "d0cf11e0a1b11ae1") {
					//$flag = 1;
					$flag = 'xls';
				}
				break;
			case "xlsx" :
				// 2007 excel
				$fh = fopen ( $file ["tmp_name"], "rb" );
				$bin = fread ( $fh, 4 );
				fclose ( $fh );
				$strinfo = @unpack ( "C4chars", $bin );
				$typecode = "";
				foreach ( $strinfo as $num ) {
					$typecode .= dechex ( $num );
				}
	
				if ($typecode == "504b34") {
					//$flag = 1;
					$flag ='xlsx';
				}
				break;
		}
	
	
		// 3.return the flag
		return $flag;
	
	}
	
	/**
	 * 截取字符串,默认按utf8截取
	 * @param  string $string
	 * @param  int  $length
	 * @param  string $dot
	 * @param  string $charset
	 * @return string
	 */
	public static function cutstr($string, $length, $dot = '...',$charset = 'utf8') {
		if (strlen($string) <= $length) {
			return $string;
		}
		$strcut = '';
		if (strtolower($charset) == 'utf8') {
			$n = $tn = $noc = 0;
			while ($n < strlen($string)) {
				$t = ord($string[$n]);
				if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
					$tn = 1;
					$n++;
					$noc++;
				}
				elseif (194 <= $t && $t <= 223) {
					$tn = 2;
					$n += 2;
					$noc += 2;
				}
				elseif (224 <= $t && $t < 239) {
					$tn = 3;
					$n += 3;
					$noc += 2;
				}
				elseif (240 <= $t && $t <= 247) {
					$tn = 4;
					$n += 4;
					$noc += 2;
				}
				elseif (248 <= $t && $t <= 251) {
					$tn = 5;
					$n += 5;
					$noc += 2;
				}
				elseif ($t == 252 || $t == 253) {
					$tn = 6;
					$n += 6;
					$noc += 2;
				} else {
					$n++;
				}
				if ($noc >= $length) {
					break;
				}
			}
			if ($noc > $length) {
				$n -= $tn;
			}
			$strcut = substr($string, 0, $n);
		} else {
			for ($i = 0; $i < $length -strlen($dot) - 1; $i++) {
				$strcut .= ord($string[$i]) > 127 ? $string[$i] . $string[++ $i] : $string[$i];
			}
		}
		return $strcut . $dot;
	}
	
}