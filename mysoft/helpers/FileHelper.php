<?php
namespace mysoft\helpers;
use yii\helpers\BaseFileHelper;

class FileHelper extends BaseFileHelper
{
    public static function read($file) {
        $fp=fopen($file,"r"); 
        try {
            $data=""; 
            while(!feof($fp)) 
            { 
                $data.=fread($fp,4096); 
            } 
            return $data; 
        } catch (\Exception $e) {
            if(is_resource($fp)){
                fclose($fp);
            }
        }
    }
}
