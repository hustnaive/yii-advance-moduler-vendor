<?php
namespace Oauth\Exceptions;

class ConnectException extends \Exception{
    
public function __construct($msg,$code) {
    parent::__construct($msg ,$code, null);
    }
}
