<?php
namespace Oauth;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Oauth\WebApiBase;
/**
 * Description of API_SMS
 *
 * @author likg
 */
class API_SMS extends WebApiBase {
    
   	private $URL_PATH="/PlatformApi/V1/SMSSend";
    
    public function __construct($apiBaseURL, $authURL, $clientId, $clientSecret)
    {
      parent::__construct($apiBaseURL, $authURL, $clientId, $clientSecret);  
    }
    
    public function SendSMS($organizationId,$phoneNumber,$content)
    {
        $params =array( "organizationId"=>$organizationId,"phoneNumber"=>$phoneNumber,"content"=>$content);  
       	return $this->GetContent($this->URL_PATH,"GET", $params);
     } 
}
