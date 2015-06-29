<?php

namespace Oauth;

use Oauth\RestRequest;
use Oauth\Exceptions\AuthException;
use Oauth\Exceptions\ConnectException;
use Oauth\Exceptions\InvalidParameterException;
use Oauth\Exceptions\UnauthorizedAccessException;


class RestClient {
    
    private $baseURL; 
     private static $boundary = '';
     private static $errno = 0;
     private static $errmsg = '';
     private static $isDebug=true;
 
    public  function __construct($baseURL)
    {
       $this->baseURL=$baseURL;
     }
     
     /**
     *  http/https 请求
     * 
     * @param string $url 请求URL
     * @param array $params 请求参数
     * @param string $httpMethod Http method, 'GET' or 'POST'
     * @param bool $multi Whether it's a multipart POST request
     * @return 如果成功，返回response 字符串,否则返回 failed
     */
	public  function Excute($resource,$request)
    {  
    	// when using bae(baidu app engine) to deploy the application,
    	// just comment the following line
    	$ch = curl_init();
    	//var_dump($request->GetSeting());die;
    	curl_setopt_array($ch, $request->GetSeting());
        $urlParams=$request->GetUrlParams();
        $requestUrl=$this->baseURL.$resource."?".$urlParams;
        curl_setopt($ch, CURLOPT_URL,$requestUrl);
        
        $result = curl_exec($ch);  
        $code=curl_errno($ch);
        if($code!=0)
        {
             $msg=curl_error($ch);
             return false;
        }
       
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if($http_code!=200)
        {   
            switch ($http_code)
            {
                case 500: 
                     throw new AuthException(); 
                case 400:
                    throw new InvalidParameterException();
                case 401:
                    throw new UnauthorizedAccessException(); 
            } 
        }
                
    	return $result;
    } 
}
