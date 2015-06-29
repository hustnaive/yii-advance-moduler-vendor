<?php
namespace yunke\base;

/**
 * Description of PHPUnit_TestCaseBase
 *
 * @author Young
 */
class PHPUnitTestCase extends \PHPUnit_Framework_TestCase{
    
    /**
     * 
     * @param type $service Provider所在对象
     * @param array $expect 期望值[$rount=$expectValue]
     * @return type
     */
    public function mockDbProvider($service, $expect) {
        $stub = $this->getMockBuilder('\\yunke\\dbprovider\\DbProvider')
                ->disableOriginalConstructor(["orgcode" => ""])
                ->getMock();
        $service->setDbProvider($stub);
        $stub->method('fetch')
            ->will($this->returnCallback(function ($arg) use (&$expect) {
                foreach ($expect as $route=> $expectVal) {
                    //判断路由是否存在
                    $RouteIsExist = $this->checkRouteIsExist($route);
                    if($RouteIsExist != ""){
                        return $RouteIsExist;
                    }
                    if ($route == $arg) {
                        return $expectVal;
                    }
                }
            }));

        return $stub;
    }
    
    private function checkRouteIsExist($route)
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

        $Instance = new $className('');
        if (!method_exists($Instance, $method))
        {
            return "not find method [".$method."] in DAL [".$className."]";
        }
        return "";

    }
    
    /**
     * 参数校验
     * @param type $service service对象
     * @param String $method  测试方法
     * @param type $param 参数
     * @param type $exceptionCode 异常编码
     */
    public function assertException($service,$method,$param,$exceptionCode) {
        try{
            call_user_func_array(array($service, $method), $param);
        }
        catch(\Exception $e)
        {
            $this->assertEquals($e->getCode(), $exceptionCode);
        }
    }

}
