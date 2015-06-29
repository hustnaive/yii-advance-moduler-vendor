<?php
namespace Oauth;

use Oauth\RestRequest;
use Oauth\RestClient;

class AuthorizationServerHost
{	
    private $restClient;
    
    /**
     * Constructor
     * 
     * @param string $authorizationRUL 授权服务URL. 
     */
    public function __construct($authorizationRUL)
    {
    	$this->restClient = new RestClient($authorizationRUL); 
    }
   
    public function GetAuthenticator( $clientId,  $clientSecret,  $scope)
    {
           $request =$this->CreateTokenRequest( $clientId,  $clientSecret,  $scope);
           $response =$this->restClient->Excute("",$request); 
           $accessToken =$this->GetUserAccessToken($response); 
            return $accessToken;
    }    
    
        private function GetUserAccessToken($response)
        {
            $obj = json_decode($response, true); 
            return $obj["access_token"];
        }
 
        private  function CreateTokenRequest($clientId, $clientSecret, $scope)
        {
            $request = new RestRequest("POST",true); 
            $params=array(
                "Content-Type"=> "application/x-www-form-urlencoded; charset=utf-8",
                "grant_type"=>"client_credentials",
                 "client_id"=>$clientId,
                "client_secret"=> $clientSecret,
                "scope"=>$scope,
            ); 
            $request->SetParameters($params);
            return $request;
        } 
}
