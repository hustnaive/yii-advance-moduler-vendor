<?php
namespace mysoft\mqs\log;

use yunke\mqs\LogAbs;
/**
 * 数据库操作方式
 * @author yangzhen
 *
 */
class Db extends LogAbs
{
	public function init()
	{
		parent::init();
	}
	
	
	protected function write($messages)
	{
		//TODO : how db write data 
	}
	
}