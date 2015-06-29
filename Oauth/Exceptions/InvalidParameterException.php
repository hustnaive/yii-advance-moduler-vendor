<?php
namespace Oauth\Exceptions;

class InvalidParameterException  extends \Exception{
    public function __construct() {
        parent::__construct("请求参数错误.", 400, null);
    }
}
