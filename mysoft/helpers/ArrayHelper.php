<?php

namespace mysoft\helpers;

use yii\helpers\BaseArrayHelper;

class ArrayHelper extends BaseArrayHelper {

    public static function filter($array, $properties = []) {
        $arr = [];
        foreach ($properties as $key) {
            $arr[$key] = empty($array[$key]) ?null:$array[$key];
        }
        return $arr;
    }

    /**
     * 数组做数据库  left join 操作
     * @param array $array1
     * @param array $array2
     * @param array $fieldMapping 连接条件 [[array1Field1,array2Filed1],[array1Field2,array2Filed2]...]
     */
    public static function arrayLeftJoin(array $array1, array $array2, array $fieldMapping) {
        return self::_arrayJoin($array1, $array2, $fieldMapping);
    }

    /**
     * 数组做数据库  inner join 操作
     * @param array $array1
     * @param array $array2
     * @param array $fieldMapping 连接条件 [[array1Field1,array2Filed1],[array1Field2,array2Filed2]...]
     */
    public static function arrayInnerJoin(array $array1, array $array2, array $fieldMapping) {
        return self::_arrayJoin($array1, $array2, $fieldMapping, true);
    }

    private static function _arrayJoin(array $array1, array $array2, array $fieldMapping, $isInnerJoin = FALSE) {
        if (empty($fieldMapping)) {
            throw E('参数缺失', '100010');
        }

        $array1KeyFields = [];
        $array2KeyFields = [];
        foreach ($fieldMapping as $mapping) {
            $array1KeyFields[] = $mapping[0];
            $array2KeyFields[] = $mapping[1];
        }
        //为array2生成字典方便查询
        $array2WithMappingKeyDic = [];
        foreach ($array2 as $row) {
            $mappingKey = self::_createArrayJoinMappingKey($array2KeyFields, $row);
            $array2WithMappingKeyDic[$mappingKey] = $row;
        }
        //遍历array1将array2的行进行合并
        $mergeArray = [];
        foreach ($array1 as $row) {
            $mappingKey = self::_createArrayJoinMappingKey($array1KeyFields, $row);
            $array2Row = [];
            if (array_key_exists($mappingKey, $array2WithMappingKeyDic)) {
                $array2Row = $array2WithMappingKeyDic[$mappingKey];
            }else {
                //内联则跳过
                if ($isInnerJoin) {
                    continue;
                }
            }
            $mergeArray[] = array_merge($row, $array2Row);
        }
        return $mergeArray;
    }

    private static function _createArrayJoinMappingKey($keyFields, $row) {
        $mappingFieldValue = [];
        foreach ($keyFields as $key) {
            $mappingFieldValue[] = $row[$key];
        }
        $mappingKey = sprintf("jk_%s", implode("__", $mappingFieldValue));
        return $mappingKey;
    }
    
    /**
     * 数组转换为字典结构，根据数组中的唯一键字段生成字典Key，Value为数组项，不区分大小写可用 ArrayHelper Index方法
     * @param array $sourceArray
     * @param type $keyFd
     * @return array
     */
    public static function toSimpleDic(array $sourceArray, $keyFd,$isKeyLower=false) {
        $dicResult = [];
        foreach ($sourceArray as $arrItem) {
            $key = $arrItem[$keyFd];
            if($isKeyLower){
                $key = strtolower($key);
            }
            $dicResult[$key] = $arrItem;
        }
        return $dicResult;
    }

    /**
     * 数组按指定的分组字段进行分组，返回结果集
     * @param array $sourceArray
     * @param type $groupByFd  分组字段名
     */
    public static function groupBy(array $sourceArray, $groupByFd) {
        $dicResult = [];
        $groupByFds = [];
        if (is_array($groupByFd)) {
            $groupByFds = $groupByFd;
        } else {
            $groupByFds[] = $groupByFd;
        }
        foreach ($sourceArray as $arrItem) {
            $key = NULL;
            foreach ($groupByFds as $fd) {
                if ($key === NULL) {
                    $key = $arrItem[$fd];
                } else {
                    $key = sprintf('%s_%s', $key, $arrItem[$fd]);
                }
            }
            $dicResult[$key][] = $arrItem;
        }
        return $dicResult;
    }

    /**
     * 数组是否包含给定值
     * @param array $sourceArray
     * @param type $val
     */
    public static function Contain(array $sourceArray, $val) {
        foreach ($sourceArray as $value) {
            if ($value === $val) {
                return true;
            }
        }
        return FALSE;
    }

    /**
     * 二维数组过滤重复
     * @param $array2D
     */
    public static function array_unique_2D($array2D){
        foreach ($array2D as $v){
            $v = join(",",$v);
            $temp[] = $v;
        }
        $temp = array_unique($temp);
        foreach ($temp as $k => $v){
            $temp[$k] = explode(",",$v);
        }
        return $temp;
    }

}
