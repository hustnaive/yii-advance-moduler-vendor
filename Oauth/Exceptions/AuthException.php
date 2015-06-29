<?php
namespace Oauth\Exceptions;

class AuthException extends \Exception {
    
    public function __construct() {
        parent::__construct("获取授权失败.", 500);
    }
}
