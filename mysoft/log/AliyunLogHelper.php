<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace mysoft\log;

use \yunke\helpers\Conf;

require_once realpath(dirname(__FILE__) . '/../../Aliyun/sls/Sls_Autoload.php');

/**
 * Description of AliyunLogHelper
 *
 * @author tianl
 */
class AliyunLogHelper {

    static $project;
    static $logstore;

    const AliyunLogRequestMaxRetries = 3;

    /**
     * 创建SLS客户端对象
     * @return \vendor\yunke\log\Aliyun_Sls_Client
     */
    public static function createClient() {
        $endpoint = 'cn-hangzhou.sls.aliyuncs.com';
        $accessKeyId = 'FIGOQIidyBsGPf29';
        $accessKey = 'uFvE1MMZuQv9T65Q0CVh4YOc9NWyb3';
        self::$project = 'myscrm-tengine';
        self::$logstore = 'test';

        //Conf::getConfig("ACCESS_KEY_ID");
        //Conf::getConfig("ACCESS_KEY_SECRET");
        //Conf::getConfig("SLS_ACCESS_URI");
        //Conf::getConfig("SLS_PROJECT");
        //Conf::getConfig("SLS_LOGSTORE");

        return new \Aliyun_Sls_Client($endpoint, $accessKeyId, $accessKey);
    }

    /**
     * 发送写日志请求
     * @param function $sendRequestFun 发日志方法
     * @param type $request 请求参数
     * @return type 响应对象
     */
    public static function sendLogRequest($sendRequestFun, $request, $client) {
        $response = null;
        for ($index = 0; $index < self::AliyunLogRequestMaxRetries; $index++) {
            try {
               $response= $sendRequestFun($request, $client);
                break;
            } catch (\Aliyun_Sls_Exception $exc) {
                if ($exc->getErrorCode() == "SLSRequestError") {
                    if ($index == self::AliyunLogRequestMaxRetries) {
                        echo sprintf("请求异常,已重试%s次后失败退出请求", self::AliyunLogRequestMaxRetries);
                        break;
                    }
                } else {
                    echo $exc->getTraceAsString();
                    break;
                }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
                break;
            }
        }
        return $response;
    }

    /**
     * 获取阿里云日志
     * @param type $logTopic日志主题
     * @param type $from 查询日志起始时间
     * @param type $to 查询日志终止时间
     * @param type $query 查询条件
     * @param type $line 
     * @param type $offset
     */
    public static function getLogs($client, $logTopic, $from, $to, $query, $line = 100, $offset = 0) {
        $result = [];
        $fromTicket = strtotime($from);
        $toTicket = strtotime($to);
        $response = null;
        do {
            $request = new \Aliyun_Sls_Models_GetLogsRequest(self::$project, self::$logstore, $fromTicket, $toTicket, $logTopic, $query, $line, $offset, TRUE);
            $getLogFun = function($slsRequest, $client) {
                return $client->getLogs($slsRequest);
            };
            $response = self::sendLogRequest($getLogFun, $request, $client);
            if ($response) {
                $logs = $response->getLogs();
                foreach ($logs as $log) {
                    $content = $log->getContents();
                    $result[] = $content;
                }
            }
        } while ($response != null && !$response->isCompleted());

        return $result;
    }

}
