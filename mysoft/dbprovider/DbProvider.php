<?php
namespace mysoft\dbprovider;
use Yii;
use yii\base\InvalidRouteException;

/**
 * Description of DbProvider
 *
 * @author Young
 */
class DbProvider {
    protected $orgcode;

    public function __construct($orgcode)
    {
        $this->orgcode = $orgcode;
    }
    
    public function fetch($route,$param=[])
    {
		$arr = explode('/', $route);

		$module = $dal_class = $method ='';

		switch (count($arr))
		{
			case 2:
			   $dal_class = ucfirst($arr[0]);
			   $method	  = $arr[1];
			   $className = "\\dbaccess\\".$dal_class."DAL";
			   break;
			case 3:
			   $module	  = $arr[0];
			   $dal_class = ucfirst($arr[1]);
			   $method	  = $arr[2];
			   $className = "\\dbaccess\\".$module."\\".$dal_class."DAL";
			   break;

		}
        static $DAL_Instances=[];
        $md5_classname = md5($this->orgcode.'_'.$className);
        if(isset($DAL_Instances[$md5_classname])){
            $Instance = $DAL_Instances[$md5_classname];
        }else{
            $Instance = new $className($this->orgcode);
            $DAL_Instances[$md5_classname] = $Instance;
        }


        //检查参数
        $this->_chkParam($param);
        if (!method_exists($Instance, $method))
        {
           throw E("DAL[".$className."]方法[".$method."]不存在", 100012);
        }
        return call_user_func_array(array($Instance, $method), $param);
    }
    
    
    /**
     * 检查参数
     * @param array $param
     */
    private function _chkParam(& $param)
    {
        if (!is_array($param) && !is_object($param))
        {
            $param = array();
        }
    	foreach($param as $key=>$val)
    	{
    		if(is_object($val) && $val instanceof \yii\base\Model)
    			 $param[$key] = $val->getAttributes(); //将继承于model类的对象转换为数组类型
    		
    	}
    	
    }
}
