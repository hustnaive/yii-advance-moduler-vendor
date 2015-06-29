<?php
namespace Oauth;

use Oauth\RestClient;
use Oauth\AuthorizationServerHost;
use Oauth\Exceptions\AuthException;
use Oauth\Exceptions\UnauthorizedAccessException;


class WebApiBase {
    private $accessToken=null;
    private $authURL;
    private $clientId;
    private $clientSecret;
    
    private  $restClient; 
    
    public function __construct($apiBaseURL, $authURL,$clientId,$clientSecret) 
    {
             $this->authURL=$authURL;
             $this->clientId=$clientId;
             $this->clientSecret=$clientSecret;
             
             $this->restClient = new RestClient($apiBaseURL); 
    }
        
    protected function GetContent($resource, $apiMethod , $params)
    {
        $responseContent=null;
          // 如果出现授权错误，重试一次
            for ($i = 0; $i < 2; $i++)
            {
                if ($this->accessToken == null)
                {
                   $this->accessToken =$this->GetAccessToken();
                }

                try
                {
                    $responseContent = $this->GetResponse($resource, $apiMethod, $params,"json");
                    break;
                }
                catch (AuthException $ex)
                {
                    $this->accessToken = null;
                    if($i>0)
                    {
                        throw new UnexpectedValueException();
                    }
                }
            }
            
          $jsonObj= $value=json_decode($responseContent, true); 
          return $jsonObj;
    }
    
      private function GetAccessToken()
        {
           $authServer = new AuthorizationServerHost($this->authURL);
           $accessToken= $authServer->GetAuthenticator($this->clientId, $this->clientSecret, "");
           return $accessToken;
        }
        
        private function GetResponse($resource, $method, $params, $acceptType)
        {
            $request=new RestRequest($method);
            $request->SetAcceptType($acceptType);
            $request->SetAuthorization($this->accessToken);
            $request->SetParameters($params);
            
            return $this->restClient->Excute($resource,$request); 
        } 
         
}
