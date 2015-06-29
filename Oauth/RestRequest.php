<?php
namespace Oauth;

class RestRequest {   
    
    private $curl_opts;
    private $method;
    private $params=array();
    private $urlParam="";
    private $httpHeader=array();
    private $acceptXmlType="Accept:application/xml";
    private $acceptJsonType="Accept:application/json";
    
    public  function __construct($method,$isHttps=false)
    {
        $this->method=$method;
        $this->curl_opts = array(
			CURLOPT_CONNECTTIMEOUT	=> 3,
			CURLOPT_TIMEOUT			=> 5,
			CURLOPT_USERAGENT		=> 'MysoftRequester',
	    	        CURLOPT_HTTP_VERSION	=> CURL_HTTP_VERSION_1_1,
                        CURLOPT_RETURNTRANSFER	=> true,
                        CURLOPT_HEADER			=> false,
                        CURLOPT_FOLLOWLOCATION	=> false
                        );   
        
        if($isHttps)
        {
            $this->curl_opts[CURLOPT_SSL_VERIFYPEER]=false;
            $this->curl_opts[CURLOPT_SSL_VERIFYHOST]=2;
        }
    }
    
    public function SetParameters($params)
    {
        $this->params=$params;
    }
    
    public function SetAuthorization($accessToken)
    {
        $this->httpHeader[0]="Authorization:Bearer ".$accessToken;
    } 
    
    public function SetAcceptType($acceptType)
    {
        if($acceptType=="json")
        {
            $this->httpHeader[1] = $this->acceptJsonType;
        }
        else
        {
            $this->httpHeader[1] = $this->acceptXmlType;
        }
    }
    
    public function GetUrlParams()
    {
        return $this->urlParam;
    } 
    
    public function GetSeting()
    { 
        if($this->method=="POST")
        {
            // post数据
            $this->curl_opts[CURLOPT_POST]=1;
            $this->curl_opts[CURLOPT_POSTFIELDS]=http_build_query($this->params, '', '&'); 
        }
        else {
            $urlParam="";
            if($this->params!=null)
            {
                foreach ($this->params as $key => $value) {
                    $urlParam=$urlParam."$key=$value"."&";
                }
            }
            $this->urlParam=$urlParam;
        } 
        
        $this->curl_opts[CURLOPT_HTTPHEADER]=$this->httpHeader;
        
        return  $this->curl_opts;
    } 
}
