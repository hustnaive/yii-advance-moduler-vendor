<?php
/**
 * 上传组件:阿里OSS文件服务<构造函数在Object>
 * 关联 yunke\upload\oss package
 * 当前组件的全局配置在父类里查看
 * 
 * @author yangzhen
 *
 */
namespace yunke\upload;
use yunke\helpers\Conf;

require __DIR__.'/oss/sdk.class.php';


class Oss extends UploadAbs
{
    private    $oss;
    private    $bucket;
    private    $domain;
    protected  $root = 'sales'; //默认根目录
    
      
    /**
     * 初始化参数 
     * @example 
     *  new \yunke\upload\Oss('sales');
     * @param array|string $config
     */
    public function init()
    {
        parent::init();
              
    	$this->bucket	  = Conf::fromCache('OSS_BUCKET') ? Conf::fromCache('OSS_BUCKET') : '';
    	$this->domain	  = Conf::fromCache('OSS_ACCESS_URI') ? Conf::fromCache('OSS_ACCESS_URI') : '' ; //拼接文件返回地址的 http host部分
    	$hostname		  = Conf::fromCache('OSS_HOST') ? Conf::fromCache('OSS_HOST') : ''; //定义操作的指定节点hostname
    	$OSS_ACCESS_ID    = Conf::fromCache('OSS_ACCESS_KEY_ID') ? Conf::fromCache('OSS_ACCESS_KEY_ID') : '';
    	$OSS_ACCESS_KEY   = Conf::fromCache('OSS_ACCESS_KEY_SECRET') ? Conf::fromCache('OSS_ACCESS_KEY_SECRET'): '';
    
    	if($this->rootDirName) $this->root = $this->rootDirName;
    	 
    	$this->oss = new \ALIOSS($OSS_ACCESS_ID,$OSS_ACCESS_KEY,$hostname);
    }
    
    
    /**
     *实现上传抽象方法
     */
    public function uploadFile($source,$object)
    {
    	$object = $this->root . '/' .ltrim($object,'/'); //add root用于区分
    
    	$response = $this->oss->upload_file_by_file($this->bucket,$object,$source);
    	if($this->debug)$this->_format($response);
    	if($response->status == '200') return $this->domain . $object;
    
    	return '';
    }
    
        
    /**
     *继承实现父类方法
     *格式化返回结果
     *
    **/
    protected  function _format($response) {
            echo '|-----------------------Start---------------------------------------------------------------------------------------------------'."\n";
			echo '|-Status:' . $response->status . "\n";
			echo '|-Body:' ."\n";
    			echo $response->body . "\n";
    			echo "|-Header:\n";
    			print_r ( $response->header );
    			echo '-----------------------End-----------------------------------------------------------------------------------------------------'."\n\n";
            exit;
	}
    
	
   
    /**
     * 获取当前根目录名
     * @return string
     */ 
	public function getRoot()
	{
		return $this->root;
	}
	
    
}