<?php
namespace yunke\base;


class Exception extends  \Exception
{
	public $status;
	public $msg;

	public function __construct($message,$code)
	{
		$this->msg 	  = $message;
		$this->status = $code;
		parent::__construct ($message, $code) ;
		
	}
	
	/**
	 * @return string the user-friendly name of this exception
	 */
	public function getName()
	{
		return 'Yunke Exception';
	}

	
	
}