<?php
namespace Oauth\Exceptions;

class UnauthorizedAccessException extends \Exception {
     public function __construct() {
         parent::__construct("无效的访问授权.", 401);
     }
}
