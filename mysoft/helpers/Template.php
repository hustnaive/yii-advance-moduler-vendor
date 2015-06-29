<?php
/**
 * 模板助手类,全局静态类
 * @author yangzhen
 *
 */
namespace mysoft\helpers;

class Template
{
	/**
	 * 注册的全局参数变量
	 * @var array
	 */
	private static $_opt = [];
	

	/**
	 * 转化为当前的文件路径
	 * @param string $path
	 * @return string
	 */
	private static function getFilePath($path='')
	{
		
		if(static::is_current_tpl($path)) //如果是当前的模块
		{
			$path = static::$_opt['path'].'/'.$path;
		}
		
		$module_path = \Yii::getAlias('@modules'); //获取modules的根目录
		$file = $module_path.'/'.ltrim($path,'/');
		
		
		if(!file_exists($file)) throw E('模板助手找不到模板:'.$path);
		
		return $file;
	}
	
    
	/**
	 * 是否是当前模块的模板
	 * @param  string 
	 * @return boolean
	 */
	private static function is_current_tpl($tpl)
	{
		if(strpos($tpl,'/') === false) return true;
		
		return false;		
	}
	
	
	/**
	 * 当前注册一些信息在控制器里
	 * @param array $opt
	 */
	public static function current($opt)
	{
	     static::$_opt = $opt;	
	}
	
	
	/**
	 * 模板注册的全局变量
	 * @param  array $vars
	 * @return array|boolean
	 */
	public static function Vars($vars=false)
	{
		static $_vars = [];
		
		if($vars === false) return $_vars;
		
		$_vars = $vars;
		
		return true;
		
	}
	
	
	/**
	 * 运营指定模板获取数据
	 * @param string $tpl
	 * @return text
	 */
	 public static function run($tpl='')
	 {
	 	ob_start();
	 	$vars = static::Vars();//获取到全局的变量
	 	ob_implicit_flush(false);
	 	
	 	if($vars)@extract($vars);
	 	$file = static::getFilePath($tpl);
	 	
	 	include $file;
	 	
	 	return ob_get_clean();
	 } 
}