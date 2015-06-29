<?php
ini_set('display_errors',true);
error_reporting(E_ALL^E_NOTICE);

require_once '../sdk.class.php';

$oss_sdk_service = new ALIOSS();

//设置是否打开curl调试模式
$oss_sdk_service->set_debug_mode(FALSE);
$host = 'http://mcyz1985.oss-cn-hangzhou.aliyuncs.com/';
try{

  $bucket = 'mcyz1985';
  $object = 'oss/image/apache3.jpg';
  $file_path = "/var/www/html/oss/apache.jpg";

  $response = $oss_sdk_service->upload_file_by_file($bucket,$object,$file_path);
   print_r($response);  
//_format($response);
  echo $host . $object;
}catch(Exception $ex){

   die($ex->getMessage());
}


//格式化返回结果
function _format($response) {
    echo '|-----------------------Start---------------------------------------------------------------------------------------------------'."\n";
    echo '|-Status:' . $response->status . "\n";
    echo '|-Body:' ."\n";
    echo $response->body . "\n";
    echo "|-Header:\n";
    print_r ( $response->header );
    echo '-----------------------End-----------------------------------------------------------------------------------------------------'."\n\n";
}

