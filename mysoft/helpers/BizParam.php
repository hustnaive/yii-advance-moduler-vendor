<?php

/**
 * 业务参数助手
 * @example
 * 
 *  \yunke\helpers\BizParam->reset();	  //是否重设缓存,true - 重设,false-不重设，即默认值
 *  
 *  ~ 一般调用参数前，先配置orgcode，然后调用方法
 *  \yunke\helpers\BizParam:：init(xxxx); //将orgcode传入
 *  $params = \yunke\helpers\BizParam->getOptionsByCode(...);
 * 
 * @author yangzhen
 *
 */

namespace mysoft\helpers;

use yunke\dbprovider\DbProvider;
use yii\base\InvalidParamException;

class BizParam {
    private $orgcode = '';

    public function __construct($orgcode) {
        $this->orgcode = $orgcode;
    }

    /**
     * 负责从API获取
     * @param string $orgcode
     * @param string $app
     * @param string $scopid
     * @return array
     */
    public function getBizFromApi($app, $scopid) {
        $param['app'] = $app;
        $param['scopid'] = $scopid;
        $orgcode = $this->orgcode;

        if (empty($orgcode)) {
            throw new InvalidParamException("缺失参数orgcode");
        }

        $bizparams = (new DbProvider($orgcode))->fetch('pub/bizParam/get', $param);
        return $bizparams;
    }

    /**
     * 获取某个公众号的所有参数，缓存，提高运行效率
     * @param type $app
     * @param type $token
     * @return type
     */
    public function getTokenParams($app, $token, $disabled = FALSE) {
    	
    	$is_disable = $disabled ? '1':'0';
    	
        $cache_key = md5($app . '#token#' .$disabled.'#'. $token);

        static $g_params = []; //用static保存当前访问对象，规避反复get cache 导致性能问题

        if (isset($g_params[$cache_key])) {
            return $g_params[$cache_key];
        }

        $rs = \Yii::$app->cache->get($cache_key);

        if ($rs === false) {
            $rs = array();

            $result = $this->getBizFromApi($app, $token);


            foreach ($result as $row) {

                if (!isset($rs['ParamCode'])) {
                    $rs[$row['ParamCode']]['group']['id'] = $row['mdl_ParamId'];
                    $rs[$row['ParamCode']]['group']['ismultlevel'] = ($row['Hierarchy'] > 1 ? 1 : 0);
                    $rs[$row['ParamCode']]['group']['editable'] = 0;
                    $rs[$row['ParamCode']]['group']['app'] = $row['Application'];
                    $rs[$row['ParamCode']]['group']['orders'] = 0;
                    $rs[$row['ParamCode']]['group']['groupcode'] = $row['ParamCode'];

                    //增加默认值
                    if ($row['IsDefault'] > 0)
                        $rs[$row['ParamCode']]['group']['defaultvalue'] = $row['Value'];
                }

                if ($disabled || !$row['Disabled']) {
                    $rs[$row['ParamCode']]['options'][] = array(
                        'id' => $row['myParamValueId'],
                        'name' => $row['Text'],
                        'value' => $row['Value'],
                        'group' => $row['ParamCode'],
                        'app' => $row['Application'],
                        'code' => $row['Code'],
                        'parentvalue' => $row['ParentCode'],
                        'orders' => $row['Order'],
                        'token' => $row['ScopeId'],
                        'issystem' => $row['IsSystem'],
                        'isdefault' => ($row['IsDefault'] > 0 ? 1 : 0)
                    );
                }
            }

            \Yii::$app->cache->set($cache_key, $rs, 60);

            $g_params[$cache_key] = $rs;
        }


        return $rs;
    }

    /**
     * 获取某个项目的所有参数，缓存，提高运行效率
     * @param type $app
     * @param type $projID
     * @return type
     */
    public function getProjParams($app, $projID,$disabled=FALSE) {
    	
    	$is_disable = $disabled ? '1':'0';
    	
        $cache_key = md5($app . '#projID#'.$disabled.'#'. $projID);

        static $g_params = []; //用static保存当前访问对象，规避反复get cache 导致性能问题

        if (isset($g_params[$cache_key])) {
            return $g_params[$cache_key];
        }


        $rs = \Yii::$app->cache->get($cache_key);
        if ($rs === false || empty($rs)) {
            $result = $this->getBizFromApi($app, $projID);

            $rs = array();
            foreach ($result as $row) {

                if (!isset($rs['ParamCode'])) {
                    $rs[$row['ParamCode']]['group'] = array(
                        'id' => $row['mdl_ParamId'],
                        'ismultlevel' => ($row['Hierarchy'] > 1 ? 1 : 0),
                        'editable' => 0,
                        'app' => $row['Applications'],
                        'orders' => 0,
                        'groupcode' => $row['ParamCode'],
                        'projid' => $row['ScopeId'],
                    );
                }

                if ($disabled || !$row['Disabled']) {
                    $rs[$row['ParamCode']]['options'][] = array(
                        'id' => $row['myParamValueId'],
                        'name' => $row['Text'],
                        'value' => $row['Value'],
                        'group' => $row['ParamCode'],
                        'enabled' => ($row['Disabled'] ? 0 : 1),
                        'app' => $row['Applications'],
                        'code' => $row['Code'],
                        'parentvalue' => $row['ParentCode'],
                        'orders' => $row['Order'],
                        'projid' => $row['ScopeId'],
                        'issystem' => $row['IsSystem'],
                    );
                }
            }


            \Yii::$app->cache->set($cache_key, $rs, 60);

            $g_params[$cache_key] = $rs;
        }


        return $rs;
    }

    /**
     * 通过code获取企业级的参数
     * @param int $app
     * @param string $paramCode
     * @param string $disabled
     * @throws InvalidParamException
     * @return array
     */
    public function getBusParams($app, $paramCode, $disabled = FALSE)
    {
    	$is_disable = $disabled ? '1':'0';
    	 
    	$cache_key = md5($this->orgcode.'#'.$app . '#business#' .$disabled.'#'. $paramCode);
    	 
    	static $g_params = []; //用static保存当前访问对象，规避反复get cache 导致性能问题
    	 
    	if (isset($g_params[$cache_key])) {
    		return $g_params[$cache_key];
    	}
    	 
    	$rs = \Yii::$app->cache->get($cache_key);

    	if ($rs === false) {
    		$rs = array();
    		 
    		//根据code获取参数
    		$param['app'] = $app;
    		$param['paramCode'] = $paramCode;
    		$orgcode = $this->orgcode;
    		if (empty($orgcode)) {
    			throw new InvalidParamException("缺失参数orgcode");
    		}
    		$result = (new DbProvider($orgcode))->fetch('pub/bizParam/getParamsByCode', $param);
    		
    		foreach ($result as $row) {
    			 
    			if (!isset($rs['ParamCode'])) {
    				$rs[$row['ParamCode']]['group']['id'] = $row['mdl_ParamId'];
    				$rs[$row['ParamCode']]['group']['ismultlevel'] = ($row['Hierarchy'] > 1 ? 1 : 0);
    				$rs[$row['ParamCode']]['group']['editable'] = 0;
    				$rs[$row['ParamCode']]['group']['app'] = $row['Applications'];
    				$rs[$row['ParamCode']]['group']['orders'] = 0;
    				$rs[$row['ParamCode']]['group']['groupcode'] = $row['ParamCode'];
    				 
    				//增加默认值
    				if ($row['IsDefault'] > 0)
    					$rs[$row['ParamCode']]['group']['defaultvalue'] = $row['Value'];
    			}
    			 
    			if ($disabled || !$row['Disabled']) {
    				$rs[$row['ParamCode']]['options'][] = array(
    						'id' => $row['myParamValueId'],
    						'name' => $row['Text'],
    						'value' => $row['Value'],
    						'group' => $row['ParamCode'],
    						'app' => $row['Applications'],
    						'code' => $row['Code'],
    						'parentvalue' => $row['ParentCode'],
    						'orders' => $row['Order'],
    						'token' => $row['ScopeId'],
    						'issystem' => $row['IsSystem'],
    						'isdefault' => ($row['IsDefault'] > 0 ? 1 : 0)
    				);
    			}
    		}
    			 
    		\Yii::$app->cache->set($cache_key, $rs, 60);
    		 
    		$g_params[$cache_key] = $rs;
    	}
    
    	return $rs;
    }
    
    /**
     * 根据code获取某个值类型参数的值
     * @param type $app 
     * @param type $scope 
     * @param type $paramCode 
     * @param type $type 参数类型。2为公众号级别参数;3为项目级别参数
     * @return string
     */
    public function getParamsValueByCode($app, $scope, $paramCode, $type = "3", $disabled = false) {
        $result = array();
        $groups = array();

        switch (intval($type)) {
            case 2:
                $result = $this->getTokenParams($app, $scope, $disabled);
                break;
            case 3:
                $result = $this->getProjParams($app, $scope);
                break;
        }
        if (isset($result[$paramCode]))
            $groups = $result[$paramCode];


        return $groups;
    }

    /**
     * 根据选项的值获取选项文本
     * @param type $app
     * @param type $scope
     * @param type $paramCode
     * @param type $value
     * @param type $type 参数类型。2为公众号级别参数;3为项目级别参数
     * @return type
     */
    public function getOptionTextByValue($app, $scope, $paramCode, $values, $type = "3") {
        $params = $this->getParamsValueByCode($app, $scope, $paramCode, $type);
        $opts = $params['options'];
        $len = count($opts);
        $vals = explode(",", $values);
        $len_vals = count($vals);
        $texts = array();

        foreach ($vals as $key => $val) {
            for ($i = 0; $i < $len; $i++) {
                $p = $opts[$i];
                if ($p["value"] == $val) {
                    array_push($texts, $p["name"]);
                    break;
                }
            }
        }

        return join(",", $texts);
    }

    /**
     * 根据选项的值获取子级选项集合
     * @param type $app
     * @param type $scope
     * @param type $paramCode
     * @param type $parentValue
     * @param type $type 参数类型。2为公众号级别参数;3为项目级别参数
     * @return type
     */
    public function getOptionsByParentValue($app, $scope, $paramCode, $parentValue, $type = "3") {
        $result = array();
        switch (intval($type)) {
            case 2:
                $result = $this->getTokenParams($app, $scope);
                break;
            case 3:
                $result = $this->getProjParams($app, $scope);
                break;
        }

        $ops = array();
        if ($result && isset($result[$paramCode]['options'])) {
            foreach ($result[$paramCode]['options'] as $val) {
                if ($val['parentvalue'] == $parentValue || ($val['parentvalue'] == '' && $parentValue == -1)) {
                    array_push($ops, $val);
                }
            }
        }

        return $ops;
    }
    
    /**
     * 根据选项的值获取企业级参数自选项集合
     * @param int $app
     * @param string $paramCode
     * @param string $parentValue
     * @return array:
     */
    public function getOptionsByBusParentValue($app, $paramCode, $parentValue)
    {
    	$result = array();
    	$result = $this->getBusParams($app, $paramCode);
    	
    	$ops = array();
    	if ($result && isset($result[$paramCode]['options'])) {
    		foreach ($result[$paramCode]['options'] as $val) {
    			if ($val['parentvalue'] == $parentValue || ($val['parentvalue'] == '' && $parentValue == -1)) {
    				array_push($ops, $val);
    			}
    		}
    	}
    	
    	return $ops;
    }
    
    /**
     * 根据code获取参数选项
     * @param string $app 应用编码
     * @param string $scope 项目GUID
     * @param string $paramCode 参数编码
     * @param string $type 参数类型。2为公众号级别参数;3为项目级别参数
     * @return array
     */
    public function getOptionsByCode($app, $scope, $paramCode, $type = "3") {
        $ismultlevel = $this->isMultlevelParams($app, $scope, $paramCode, $type);

        if ($ismultlevel == 1) {
            //如果是多层级关系的
            $array = array();
            $array["first"] = array("subs" => $this->getOptionsByParentValue($app, $scope, $paramCode, -1, $type));
            foreach ($array["first"]["subs"] as $key => $value) {
                $array[$value['value']] = array("subs" => $this->getOptionsByParentValue($app, $scope, $paramCode, $value['code'], $type));
            }
            return $array;
        } else {
            $result = array();
            switch (intval($type)) {
                case 2:
                    $result = $this->getTokenParams($app, $scope);
                    break;
                case 3:
                    $result = $this->getProjParams($app, $scope);
                    break;
            }
            //如果是单层结构则直接返回options的集合
            if(array_key_exists($paramCode, $result))
            {
            	if(array_key_exists('options', $result[$paramCode]))
	            {
	            	return $result[$paramCode]['options'];
	            }            	
            }
        }
        return null;
    }

    /**
     * 是否多层级参数,判断是否采取多层显示
     * @param type $app 应用编号
     * @param type $scope 范围，公众号token或项目GUID
     * @param type $paramCode
     */
    public function isMultlevelParams($app, $scope, $paramCode, $type = '3') {
        $result = array();
        switch (intval($type)) {
            case 2:
                $result = $this->getTokenParams($app, $scope);
                break;
            case 3:
                $result = $this->getProjParams($app, $scope);
                break;
        }

        if (isset($result[$paramCode])) {
            return $result[$paramCode]["group"]["ismultlevel"];
        }
    }

    /**
     * 根据code 获取企业级参数选项
     * @param int $app
     * @param string $paramCode
     * @return array
     */
    public function getOptionsByBusParamCode($app, $paramCode)
    {
    	$ismultlevel = $this->isMultlevelBusParams($app, $paramCode);
    	
    	if ($ismultlevel == 1) {
    		//如果是多层级关系的
    		$array = array();
    		$array["first"] = array("subs" => $this->getOptionsByBusParentValue($app, $paramCode, -1));
    		foreach ($array["first"]["subs"] as $key => $value) {
    			$array[$value['value']] = array("subs" => $this->getOptionsByBusParentValue($app, $paramCode, $value['code']));
    		}
    		return $array;
    	} else {
    		$result = $this->getBusParams($app, $paramCode);
    		//如果是单层结构则直接返回options的集合
    		if(isset($result) && isset($result[$paramCode]) && isset($result[$paramCode]['options'])){
    			return $result[$paramCode]['options'];
    		}else {
    			return [];
    		}
    	}
    }
    
    /**
     * 判断企业级参数是否多层级
     * @param int $app
     * @param string $paramCode
     * @return array
     */
    public function isMultlevelBusParams($app, $paramCode)
    {
    	$result = $this->getBusParams($app, $paramCode);
    	
     	if (isset($result[$paramCode])) {
            return $result[$paramCode]["group"]["ismultlevel"];
        }
        return [];
    }
}
