<?php
/**
 * yunke extension for Yii framework
 * 上传组件，抽象类定义
 * ```
 * 'components'=>[
 *  
 *     'upload'=>[
 *         'class' => 'yunke\upload\xxx',  //定义启用哪种上传组件
 *         'rootDirName'=>'xxxxx',         //设置根路径,不设置则默认为空
 *         'debug'=>true                   //调试状态，开启则组件里所有调试代信息会出现，默认关闭
 *     ]
 * 
 * ]
 * 
 * 关于调试也可以根据全局是否调试状态开启
 * if(YII_DEBUG) $config['components']['upload']['debug'] = true;
 * 
 * 
 * @author yangzhen
 *
 */
namespace mysoft\upload;
use yii\base\Component;

abstract class UploadAbs extends Component 
{
    /**
     * 是否调试信息,组件配置参数使用
     * @access protected
     * @var bool $debug,默认false
     */
    public $debug = false;
    
    /**
     * 新增定义根目录名，组件配置使用
     * @var string
     */
    public $rootDirName = '';    
    
   
    
    
    /**
     * 上传文件抽象,指定源文件和目标文件
     * @access public
     * @param string $source 上传源
     * @param string $object 目标地址
     * @return string  上传成功的地址，empty则表示失败
     */
    abstract  public function uploadFile($source,$object); 
    
    /**
     * 获取当期父级根目录名
     * @access public
     * @return string
     */
    abstract  public function getRoot();
    
    
    /**
     * 打印响应信息
     * @param mixed $response
     */
    protected function _format($response)
    {
        //TODO：
    	
    }
    
    
    /**
     * 是否开启调试模式
     * @param string $debug
     * @return $this
     */
    protected function debug($debug=true)
    {
       $this->debug = $debug;
       return $this;
        
    }
    
    
        
}