<?php
namespace yunke\helpers;
/**
 * Layouts布局助手
 * ~ 
 *  Layout::subAssign('pub',[...]); // pub 为layout的名字，这里直接给这个子布局赋值
 *  Layout::subAssign('footer,[...]');
 * 
 * ~ 布局层
 * Layout::register($this); //注册view对象
 * ...
 * Layout::load('pub');
 * 
 * ...
 * Layout::load('footer');
 * 
 * 
 * 
 * 
 * @author yangzhen
 *
 */
class Layout
{
	private static $_view ;
	
	private static $_vars = [];
	
	private static $_module = '';
	
	/**
	 * 获取模块布局路径
	 * @param unknown $layout
	 * @return string
	 */
	private static function _getLayoutPath($layout)
	{
	 	return rtrim(\Yii::getAlias('@modules'),'/') .'/'.static ::$_module.'/views/layouts/'.$layout.'.php'; 
	}
	
	/***
	 * 在layout里注册一个视图对象
	 * @param View $view
	 */
	public static function register($view)
	{
		 
		 if( $view  instanceof \yii\base\View )
		 {
		 	  static :: $_view   = $view;
		 	  static :: $_module = static::$_view->context->module->id;
		 	
		 }else{
		 	
		 	  throw new  \yii\base\NotSupportedException('invalid view object');
		 	
		 }
		 
		 
	}
	
	
	/***
	 * 设置布局变量
	 * @param string $layout  ,布局标识
	 * @param array  $data	  ,布局变量
	 */
	public static function subAssign($layout,$data )
	{
		if(!is_array($data)) throw new \yii\base\InvalidValueException('format not array');
		
	    static::$_vars[$layout] = isset(static::$_vars[$layout]) 
								? array_merge(static::$_vars[$layout],$data) 
								: $data;
		 
	}
	
		
	/**
	 * 加载布局
	 * @param  string  $layout
	 * @param  array   $opt
	 * @return void
	 */
	public static function load($layout,$opt = [])
	{
		if(empty($layout)) return false;
		
		$layout_file =  static::_getLayoutPath($layout);
		
		if(!file_exists($layout_file)) return false;
		
		$data = isset(static::$_vars[$layout]) ? static::$_vars[$layout] :[];
		ob_start();
		ob_implicit_flush(false);
		
		if($data) @extract($data);
		include $layout_file;
		 
	    $layout_data = $block = ob_get_clean();
		
	    echo $layout_data;
		
		
		
	}
	
	
	
	
	
	
}