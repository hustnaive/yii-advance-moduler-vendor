<?php
/**
 * 数据对象方法
 * @param string $orgcode
 * @param string $type  	master(可读可写)|slave（只读实例） 主从模式选择 ,默认是主实例，可读可写
 * @param bool   $auto_open 是否自动完成初始化open操作，兼容操作
 * @throws Exception
 * @return \yii\db\Connection  返回主从对象，根据type来判断
 */
function DB($orgcode="",$type = 'master',$auto_open = true)
{
    if($orgcode=="config")
    {
         return \Yii::$app->db;
    }

    $orgcode = empty($orgcode) ? I("__orgcode") : $orgcode;

    static $dbObj=[];
    if(empty($orgcode)){
        throw new Exception("未找到租户编码", "110000");
    }

    $dbObj_key = $orgcode.'_'.$type; // 租户主从类型对象标识
    
    if(isset($dbObj[$dbObj_key])){
        return $dbObj[$dbObj_key];
    }

    $dbconfig = _get_dbconfig($orgcode,$type);

    $db = new \yii\db\Connection([
            'dsn' => 'mysql:host='.$dbconfig["host"].';dbname='.$dbconfig["database"],
            'username' => $dbconfig["uid"],
            'password' => $dbconfig["pwd"]
        ]);

    if( $auto_open ) $db->open(); //是否开启自动连接模式，兼容只调用DB方法默认为开启状态
    
    $dbObj[$dbObj_key] = $db;
    
    return $db;
}

function _get_dbconfig($orgcode,$type="master"){
    $connstr_cachekey = "ConnectionString_".$orgcode;
    $connstr = Yii::$app->cache->get($connstr_cachekey);
    if(empty($connstr)){
        $db = DB("config");
        $command = $db->createCommand("select ConnectionString from myscrm_organization where UniqueName=:orgcode",
            [
                ':orgcode' => $orgcode,
            ]);
        $connstr = $command->queryScalar();
        if(empty($connstr)){
            throw new Exception("租户库链接为空！", "110001");
        }
        Yii::$app->cache->set($connstr_cachekey,$connstr);
    }
    $myconnection = array();
    foreach (explode(';', $connstr) as $conn){
        $connPara = explode('=', $conn);
        if(count($connPara)>1){
            $myconnection[$connPara[0]]= $connPara[1];
        }
    }


    $connect_host = $myconnection["server"];

    if( $type == 'slave' ){ //slave实例切换

        //获取主从实例对照参数表
        $m2s_instance_params =  isset(\Yii::$app->params['master_slave_instance']) && is_array(\Yii::$app->params['master_slave_instance'])
            ? \Yii::$app->params['master_slave_instance']
            : [];
        /**
         * 如果存在主从映射关系，则替换，否则主从实例为同一实例
         */
        if( isset($m2s_instance_params[$connect_host]) &&  $m2s_instance_params[$connect_host] ){
            $connect_host = $m2s_instance_params[$connect_host];
        }
    }
    return [
        "host"=>$connect_host,
        "database"=>$myconnection["database"],
        "uid"=>$myconnection["uid"],
        "pwd"=>$myconnection["pwd"],
    ];
}

/**
 * 获取多结果集的数据
 * @param $orgcode 租户编码
 * @param $query 查询语句
 * @param $opt 查新参数 ["type"=>"slave","timeout"=>30]
 * @return array
 * @throws Exception
 */
function multi_query($orgcode,$query,$opt=["type"=>"slave","timeout"=>30]){
    if(empty($opt["type"])){
        $opt["type"]="slave";
    }
    if(empty($opt["timeout"])){
        $opt["timeout"]=30;
    }
    $dbconfig = _get_dbconfig($orgcode,$opt["type"]);
    $mysqli=mysqli_connect($dbconfig["host"],$dbconfig["uid"],$dbconfig["pwd"],$dbconfig["database"]);
    $mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, $opt["timeout"]);
    $tables = [];
    try{
        if($mysqli->multi_query($query)){
            do{
                if($result=$mysqli->store_result()){
                    $tables[] = $result->fetch_all(MYSQLI_ASSOC);
                    $result->free();
                }
            }while($mysqli->more_results() && $mysqli->next_result());
        }
        return $tables;
        $mysqli->close();
    }catch (\Exception $e){
        $mysqli->close();
        throw $e;
    }
}

function I($name,$default="")
{
    $val = \Yii::$app->request->get($name);
    $val = $val!=NULL?$val : \Yii::$app->request->post($name);
    return $val!=NULL?$val : $default;
}

/**
 * 抛出异常处理
 * @param string $msg 异常消息
 * @param integer $code 异常代码 默认为0
 * @return \yunke\base\Exception
 * @throws Exception
 */
function E($msg, $code=0)
{
    return new \yunke\base\Exception($msg, $code);
}

/**
 * 链接
 *
 *  * // /index?r=site/index
 * echo U('site/index');
 *
 * // /site/index&src=ref1#name
 * echo U(['site/index', 'src' => 'ref1', '#' => 'name']);
 *
 * // http://www.example.com/site/index
 * echo U('site/index', true);
 *
 * // https://www.example.com/site/index
 * echo U('site/index', 'https');
 *
 * @param string|array $route
 * @param string|boolen $scheme
 *
 * @return string
 */
function U($route,$scheme = false){
    $proj_id = I('proj_id');
    $token = I('token');

    if(is_array($route))
    {
        if (!isset($route['proj_id']) && $proj_id) $route = array_merge($route,['proj_id'=>$proj_id]);
        if (!isset($route['token']) && $token) $route = array_merge($route,['token'=>$token]);
    }
    else
    {
        $route = [$route];
        if($token)
        {
            $route['token']=$token;
        }
        if($proj_id)
        {
            $route['proj_id']=$proj_id;
        }
    }

    return \yunke\helpers\Url::toRoute($route,$scheme);
}

/**
 * 兼容旧路由
 * @example
 * //http://ydxs.myscrm.cn/a/b/c?key1=val1&key2=val2
 * 
 * echo OLD_U('a/b/c',['key1'=>val1,'key2'=>val2]);
 * 
 * @param string $route
 * @param array $params
 * @return string
 */
function OLD_U($route ,$params = [])
{
	$old_url  = \Yii::$app->params['OLD_URL'];	
	if (!isset($params['proj_id'])) {
		$proj_id  = I('proj_id');
		$params['proj_id'] = $proj_id;
	}
	if (!isset($params['token'])) {
		$token	  = \yunke\web\User::getInstance()->getToken();
		$params['token']   = $token;
	}	
    
        if(!is_string($route)) {
            throw E('route路由必须是String');
        }
    
    if(is_array($params) && $params)
    {
    	$route .= '?'.http_build_query($params);
    }  
  	 
    return rtrim($old_url,'/'). '/' . $route;
    
}

/**
 * Cookie 设置、获取、删除
 * @param string $name cookie名称
 * @param mixed $value cookie值
 * @param mixed $options cookie参数
 * @return mixed
 */
function cookie($name='', $value='', $option=null) {
	
	$params = \Yii::$app->params['Cookie'];//获取全局COOKIE参数设置
	
    // 默认设置
    $config = array(
        'prefix'    =>  $params['COOKIE_PREFIX'], // cookie 名称前缀
        'expire'    =>  $params['COOKIE_EXPIRE'], // cookie 保存时间
        'path'      =>  $params['COOKIE_PATH'], // cookie 保存路径
        'domain'    =>  $params['COOKIE_DOMAIN'], // cookie 有效域名
        'httponly'  =>  $params['COOKIE_HTTPONLY'], // httponly设置
    );
    
    // 参数设置(会覆盖黙认设置)
    if (!is_null($option)) {
        if (is_numeric($option))
            $option = array('expire' => $option);
        elseif (is_string($option))
            parse_str($option, $option);
        $config     = array_merge($config, array_change_key_case($option));
    }
    
    if(!empty($config['httponly'])){    	
        ini_set("session.cookie_httponly", 1);
    }
    
    // 清除指定前缀的所有cookie
    if (is_null($name)) {
        if (empty($_COOKIE))
            return;
        // 要删除的cookie前缀，不指定则删除config设置的指定前缀
        $prefix = empty($value) ? $config['prefix'] : $value;
        if (!empty($prefix)) {// 如果前缀为空字符串将不作处理直接返回
            foreach ($_COOKIE as $key => $val) {
                if (0 === stripos($key, $prefix)) {
                    setcookie($key, '', time() - 3600, $config['path'], $config['domain']);
                    unset($_COOKIE[$key]);
                }
            }
        }
        return;
    }elseif('' === $name){
        // 获取全部的cookie
        return $_COOKIE;
    }
    
    $name = $config['prefix'] . str_replace('.', '_', $name);
    
    if ('' === $value) {     	
        if(isset($_COOKIE[$name])){        	
            $value =    $_COOKIE[$name];
            /* if(0===strpos($value,'think:')){
            	$value  =   substr($value,6);
            	return array_map('urldecode',json_decode(MAGIC_QUOTES_GPC?stripslashes($value):$value,true));
            }else{
            	return $value;
            } */
            if (0===strpos($value,'think:')){
                $value  =   substr($value,6);
            }
			$temp_value = json_decode(get_magic_quotes_gpc()?stripslashes($value):$value,true);
			if(empty($temp_value)){
				$temp_value = $value;
			}						
			return is_array($temp_value) ? array_map('urldecode', $temp_value) : urldecode($temp_value);
        }else{
            return null;
        }
    } else {
    	
        if (is_null($value)) {
            setcookie($name, '', time() - 3600, $config['path'], $config['domain']);
            unset($_COOKIE[$name]); // 删除指定cookie
        } else {
            // 设置cookie
            if(is_array($value)){            	
                $value  = 'think:'.json_encode(array_map('urlencode',$value));
            }
            $expire = !empty($config['expire']) ? time() + intval($config['expire']) : 0;
            setcookie($name, $value, $expire, $config['path'], $config['domain']);
            $_COOKIE[$name] = $value;
        }
    }
}


/**
 * 导入公用模板
 * @param string $tpl
 * @param string $vars
 * @return string 
 */
function __include__($tpl,$vars='')
{
	if($vars) \yunke\helpers\Template::Vars($vars);
	echo  \yunke\helpers\Template::run($tpl);
	
}