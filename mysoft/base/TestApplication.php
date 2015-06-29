<?php
/**
 * 测试脚本的初始化
 * 这里只需要初始化Yii::$app对象即可，规避从controller的初始化
 * 实际完成的是对 Component 初始化即可
 * (new yunke\base\TestApplication($config))->run();
 * @author yangzhen
 *
 */
namespace mysoft\base;

class TestApplication extends \yii\base\Application
{
    
    /**
     * Nothing Todo，覆盖父类方法，起到屏蔽作用
     * @see \yii\base\Application::run()
     */
    public function run(){}
    
    /**
     * Nothing Todo 覆盖父类方法，起到屏蔽作用
     * @see \yii\base\Application::handleRequest()
     */
    public function handleRequest($request){}
    
    /**
     * @inheritdoc
     */
    public function coreComponents()
    {
    	return array_merge(parent::coreComponents(), [
    			'request' => ['class' => 'yii\web\Request'],
    			'response' => ['class' => 'yii\web\Response'],
    			'session' => ['class' => 'yii\web\Session'],
    			'user' => ['class' => 'yii\web\User'],
    			'errorHandler' => ['class' => 'yii\console\ErrorHandler'],
    			]);
    }
    
    
}