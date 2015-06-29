<?php
namespace Oauth;
use Oauth\WebApiBase;


class API_AccessToken extends WebApiBase{
    private $API_AccessToken = '/PlatformApi/V1/AccessKey';
    
    public function __construct($apiBaseURL, $authURL, $clientId, $clientSecret) {
        parent::__construct($apiBaseURL, $authURL, $clientId, $clientSecret);
    }
    
    public function GetAccessKey($organizationId, $userId, $uniqueName, $userCode, $userName)
    {
        $parameters = array('organizationId' => $organizationId, 'userId' => $userId, 'uniqueName' => $uniqueName, 'userCode' => $userCode, 'userName' => $userName);
        return $this->GetContent($this->API_AccessToken, 'GET', $parameters);
    }
    
    public function Verify($accessKey)
    {
        $parameters = array('key' => $accessKey);
        return $this->GetContent($this->API_AccessToken, 'POST', $parameters);
    }
}
