<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace mysoft\helpers;

use yii\base\Event;

/**
 * 事件帮助类
 * @author tianl
 */
class EventHelper {

    static $_eventDic = [];

    /**
     * 绑定事件处理程序 (扩展YII事件机制，在PHP请求生命周期类，同一个事件处理程序只能注册一次)
     * @param type $class
     * @param type $name
     * @param type $handler
     * @param type $handlerId
     * @param type $data
     * @param type $append
     * @return type
     */
    public static function on($class, $name, $handler, $handlerId, $data = null, $append = true) {
        //保证同一个ID的Hanlder只注册一次
        if (isset(static::$_eventDic[$name][$class][$handlerId])) {
            return;
        }
        static::$_eventDic[$name][$class][$handlerId] = [$handlerId];
        Event::on($class, $name, $handler, $data, $append);
    }

}