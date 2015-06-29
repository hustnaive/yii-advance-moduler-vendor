<?php
/**
 *调用oss 服务上传文件
 *@example : (new \yunke\oss\Service())->debug(false)->uploadFile(...);
 *@author : yangz03
 *@since  : 2015-01-08
 */
namespace yunke\oss;
use yunke\helpers\Conf;

require __DIR__.'/sdk.class.php';

class Service
{

   private $oss;
   private $bucket;
   private $domain;
   private $debug;
   private $root = 'sales'; //默认根目录
  
   public function __construct()
   {
      $this->bucket	  = Conf::fromCache('OSS_BUCKET') ? Conf::fromCache('OSS_BUCKET') : '';
      $this->domain	  = Conf::fromCache('OSS_ACCESS_URI') ? Conf::fromCache('OSS_ACCESS_URI') : '' ; //拼接文件返回地址的 http host部分
	  $hostname		  = Conf::fromCache('OSS_HOST') ? Conf::fromCache('OSS_HOST') : ''; //定义操作的指定节点hostname          
      $OSS_ACCESS_ID  = Conf::fromCache('OSS_ACCESS_KEY_ID') ? Conf::fromCache('OSS_ACCESS_KEY_ID') : '';
      $OSS_ACCESS_KEY = Conf::fromCache('OSS_ACCESS_KEY_SECRET') ? Conf::fromCache('OSS_ACCESS_KEY_SECRET'): '';
      
      $this->oss = new \ALIOSS($OSS_ACCESS_ID,$OSS_ACCESS_KEY,$hostname);
   }


   //是否开启调试模式
   public function debug($debug=true)
   {
       $this->debug = $debug;
       return $this;
   }

   /**
    *上传逻辑
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
    *格式化返回结果
    *
    **/
   private function _format($response) {
			echo '|-----------------------Start---------------------------------------------------------------------------------------------------'."\n";
			echo '|-Status:' . $response->status . "\n";
			echo '|-Body:' ."\n";
			echo $response->body . "\n";
			echo "|-Header:\n";
			print_r ( $response->header );
			echo '-----------------------End-----------------------------------------------------------------------------------------------------'."\n\n";
            exit;
	}




}
