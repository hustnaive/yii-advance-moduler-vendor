<?php
namespace yunke\helpers;
/**
 * 资源加载管理器
 * @static
 * 
 * ~文件路径特殊替换说明
 * @public  -- /public
 * @module  -- /modules/home/theme/default
 * @view    -- /modules/home/views
 * 				
 * @author yangzhen
 *
 */

class Assets 
{
	
	private static  $resource = [];
	
	private static  $regModule =['',''];
	
	
	/**
	 * 注册资源
	 * @param string $module 
	 */
	public static function register($module ='',$theme='')
	{
		$cfg = '';
		
	    if($module){//模块配置
	    	
	        $base  =  \Yii::getAlias("@modules").'/'.$module.'/assets/';
	        
	        if( isset(\Yii::$app->modules[$module]->assets) ){
	        	$cfg  = $base . \Yii::$app->modules[$module]->assets .'.php';
	        }
	        
	        static::$regModule = [$module,$theme];
	        
	        	
	     }else{//加载全局配置

	     	 $base =  \Yii::getAlias("@webRoot");
	     	 $cfg  =  $base .'/config/assets.php';
	     	 
	     	
	     }	
	     
	     if(file_exists($cfg)) {
		     
	     	 $resources = require($cfg);

		     //如果重复名字则覆盖
		     foreach($resources as $res_name => $vals)
		     {
		     	
		     	 $vals  = static::_parse($vals);
		     	 static::$resource[$res_name] = $vals;
		     	
		     }
		     
	     
	     }
	     
	}
	
	
	/**
	 * 解析单个配置信息
	 * @param  array  $vals
	 * @return array  [file_path,'include|output']
	 */
	private static function _parse($vals)
	{
		if(count($vals)<2) array_push($vals,'include');//确保默认include方式

		if(strncmp($vals[0],"//",2) == 0){ //自动填补http头
			$vals[0] = "http:".$vals[0];
		}elseif(strncmp($vals[0] ,"http",4) == 0){
			//TODO
		
		}else{ //内部资源
			 
			list($module,$theme) = static::$regModule;
			//特殊表示替换
			$vals[0] = str_replace(['@public','@module','@view','@adminview'],
                [
                    '/public',
                    '/modules/'.$module.'/themes/'.$theme,
                    '/modules/'.$module.'/views',
                    '/modules/admin'], $vals[0]);
		
		}
		return $vals;
		
	}
	
	
	
	/**
	 * @deprecated
	 * @param string $file
	 * @param string $show
	 */
	public static function publishOne($file,$show='include')
	{
		list($file,$show) = static::_parse([$file,$show]);
		
		
		switch($show){
			
			case 'include': //引用文件
				static::_include($file);
				break;
				 
			case  'output': //输出文件
				static::_output($file);
				break;
				 
		}
		
	}
	
	/**
	 * 发布资源
	 * 
	 */
	public static function publish()
	{

		foreach(func_get_args() as $res)
		{


			if(isset(static::$resource[$res])) //已经注册资源的
			{
		        list($file,$show) = static::$resource[$res];
			}else{//非注册资源
				
               /**
				*  非注册资源两结构
				*  资源文件#加载方式
				*  如:
				*  @public/abc/d.js#include OR
				*
				*  @public/abc/d ,系统默认填补include方式
                */


				 $show  = 'include';
				 $arr  = explode('#', $res);
				 $file = $arr[0];
				 if( isset($arr[1]) && $arr[1]) $show = $arr[1];
				  
				 list($file,$show) = static::_parse([$file,$show]);
				
				
			}

			switch($show){
				
				case 'include': //引用文件
					static::_include($file);
					break;
					 
				case  'output': //输出文件
					static::_output($file);
					break;
					 
			}


			
		
		}

		
	}

	
	/**
	 * 页面通过include包含的方式引入静态文件
	 * @param string  $file 
	 * @return void
	 */
	private static function _include($file)
	{
		
		$str ="";
		
		$filename = $file;
		if(strncmp($file,'http',4) != 0 )
		{
		     $filename .= "?v=".filemtime(\Yii::getAlias('@webRoot').$file);
		}

		$type  = static::_getFileType($file);
		
		
		switch(strtolower($type))
		{
			case 'js':
				$str = sprintf("\r\n<script type=\"text/javascript\"  src=\"%s\"></script>\r\n",$filename);
				break;
			case 'css':
				$str = sprintf("\r\n<link type=\"text/css\" rel=\"Stylesheet\" href=\"%s\" />\r\n",$filename);
				break;
			
		}
		
		if($str) echo $str;
	} 
	
	
	
	
	/**
	 * 直接将文件内容输出到页面
	 * @param string $file
	 * @return void
	 */
	private  static function _output($file)
	{
		
		if((strncmp($file,'http',4) == 0 )){ //不支持非本站点的output
			return false;
		}
		
		$type  = static::_getFileType($file);
		$filename =\Yii::getAlias('@webRoot').$file;

		 switch(strtolower($type))
		 {
		 	case 'js':
		 		echo "\r\n<script type='text/javascript' class='".pathinfo($file)['filename']."'>\r\n";
		 		echo "\r\n /** file:" .$file ." **/ \r\n\r\n";
		 		include($filename);
		 		echo "\r\n</script>\r\n";
		 		
		 	break;
		 	
		 	
		 	case 'css':
		 		echo "\r\n<style type='text/css' class='".pathinfo($file)['filename']."'>\r\n";
		 		echo "\r\n /** file:" .$file ." **/ \r\n\r\n";
		 		include($filename);
		 		echo "\r\n</style>\r\n";
		 		
		 	break;
		 	
		 	default :
		 		include $filename;
		 	
		 	
		 	
		 }
		
		
	}
	
	
	/**
	 * 获取扩展名
	 * @param  string $filename
	 * @return string
	 */
	private  static function _getFileType($filename)
	{
	     $ext  = pathinfo($filename)['extension'];
	     return $ext;			
	}
	
	
	
	private static function _cacheFile()
	{
		
		
	}
	
	
	
}