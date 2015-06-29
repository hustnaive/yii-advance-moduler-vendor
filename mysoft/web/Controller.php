<?php
/**
 * 扩展控制器，继承优化
 * @author yangz03
 * @since 2015-01-17
 */
namespace yunke\web;
use yii;
use yii\web\Controller as CTR;
use yunke\helpers\String;
use yunke\web\User;

class Controller extends CTR
{ 
    public $enableCsrfValidation = false;
    protected $orgcode;
	/**
	 * 当前http请求
	 * @var string $webroot
	 */
	protected  $webUrl;
	/**
	 * 主题
	 * @var  string $theme
	 */
	protected  $theme;
	
	/**
	 * 主题公共文件
	 * @var string $pub
	 */
	protected  $pub;

	/**
	 *  模板变量
	 * @var  string
	 */
	public      $vars = [];
	
	/**
	 * 布局,默认main
	 * @var string
	 */
	public 		 $layout =  'main';
	
	/**
	 * 当前角色用户
	 * User::getInstance();
	 * @var \yunke\web\User
	 */
    protected    $_user;

    public  $ticket;

    /**
	 * 初始化函数
	 */
	public function actions()
	{
        //根据前端cookie backurl跳转
        $this->backUrlLocation();
		$this->webUrl   =  Yii::getAlias('@webUrl'); 	// HTTP根目录
		$this->theme    =  $this->theme ?  $this->theme  :  'default';  //当前主题皮肤
		$this->pub      = Yii::getAlias('@webUrl').'/modules/'.$this->module->id.'/themes/pub';
		
		//注册静态资源依赖关系
		\yunke\helpers\Assets::register();

        //重复提交表单验证
        $user_ticket = I('ticket');
        $this->ticket = Yii::$app->session->get('ticket');
        //若票据不一致，则返回错误信息
        if($user_ticket && $this->ticket != $user_ticket)
        {
            if (Yii::$app->request->isAjax)
            {
                $this->ajax_response('0','请不要重复提交','100000');
            }
            else
            {
                throw E("请不要重复提交",'100000');
            }
        }
        elseif ($user_ticket)
        {
            //一次请求成功后，重置ticket
            $this->ticket = NULL;
        }
        //重置ticket
        if (empty($this->ticket))
        {
            $this->ticket = String::uuid();
            Yii::$app->session->set('ticket',$this->ticket);
        }

		//全局赋值
		$this->assign('webUrl',	$this->webUrl);	
		$this->assign('theme',	$this->theme);
		$this->assign('pub',$this->pub);
		$this->orgcode = I("__orgcode");
						
	}
	
	/**
     * 自定义主题配置，根据当前的模块路径
	 */
	public function setTheme()
	{
        if(count($this->modules)>2)
        {
            $this->getView()->theme = Yii::createObject( [
                'class'=>'yii\base\Theme',
                'pathMap'=>[  '@app/modules/'.$this->module->module->id.'/'.$this->module->id => '@modules/'.$this->module->id.'/themes/'.$this->theme.'/'],
                'baseUrl'  =>'@webUrl/modules/'.$this->module->module->id.'/'.$this->module->id.'/themes/'.$this->theme.'/',

            ] );
        }
        else
        {
            $this->getView()->theme = Yii::createObject( [
                'class'=>'yii\base\Theme',
                'pathMap'=>[  '@app/modules/'.$this->module->id => '@modules/'.$this->module->id.'/themes/'.$this->theme.'/'],
                'baseUrl'  =>'@webUrl/modules/'.$this->module->id.'/themes/'.$this->theme.'/',

            ] );
        }
	}
	
	/**
	 * 模板传递变量
	 * @param string $key
	 * @param string $val
	 * @return void
	 */
	protected function assign($key,$val)
	{
		$this->vars[$key] = $val;

	}
	
	/**
	 * 模板渲染，简化
	 * @param bool	  $layout 是否启用布局,默认启用
     * @param string $view   模板名
	 * @void
	 */
	public  function display($layout=true,$view='')
	{
        //把所有的变量指给模板 add by sglz
        $this->vars = array_merge($this->vars,['action'=>$this->action->id]);
        $this->assign('urlparams',$_GET);
        
        $tplData  = $this->vars;
        
        if( isset($_SERVER['HTTP_USER_AGENT']))
        {
        	$isAndroid = ( stripos($_SERVER['HTTP_USER_AGENT'],'Android') !== false ) ? 1 : 0;  
            $tplData = array_merge($tplData,['isAndroid'=>$isAndroid]);	
        }
        
        $this->assign('tpldata',$tplData);

		
		$this->setTheme(); //设置主题
		
		\yunke\helpers\Assets::register($this->module->id,$this->theme);
		
		//注册模板助手信息
		\yunke\helpers\Template::current(['path'=>$this->module->id.'/views/'.$this->id]);
		\yunke\helpers\Template::Vars($this->vars);
				
		if( empty($view) ) $view = $this->action->id;
		$view = str_replace(' ', '', ucwords(str_replace('-', ' ', $view)));
		echo $this->fetch($view,$layout);
	}
	
	/**
	 * 获取渲染内容
	 * @param string $view
	 * @param bool   $layout 是否启用布局,默认启用
	 * @return string
	 */
	public function fetch($view,$layout=true)
	{
		$content = $layout ?  ( $this->render($view,$this->vars) ) : ( $this->renderPartial($view,$this->vars) );
		$content =  $this->replaceTags($content);
		
		return  $content;
	}
	
	/**
	 * 替换特殊标签
	 */
	private  function replaceTags($content)
	{
		$params	=	$this->tpl_tags();
		$keys		=	array_keys($params);
		$values		=  array_values($params);
				
		return str_replace($keys, $values, $content);
	}
	
	/**
	 * 替换标签,对应关系
	 * __BaseUrl__  => web请求根地址
	 * __Public__	 => 样式静态文件的根目录
	 * __Themes__  => 当前主题
	 * __SKIN__	     => 当期主题对应皮肤路径 ，由(module,$theme)决定
	 */
	private  function tpl_tags()
	{
		return [
				'__WEB__'       =>  Yii::getAlias('@webUrl'),
				'__PUBLIC__'    =>  Yii::getAlias('@webUrl').'/public',  //公共样式目录
				'__THEME__'     =>  $this->theme  ,			//默认是default主题
				'__SKIN__'      =>  $this->getView()->theme ->baseUrl, //主题皮肤样式路径,css,image
				'__PUB__'       =>  $this->pub, //主题皮肤公共静态文件夹
		];
		 
	}

    /**
     * ajax 统一返回格式
     *
     * @param int $code
     * @param string $msg
     * @param array $data
     *
     */
    public function ajax_response($isSuccess=1,$msg="",$result=[])
    {
        $response = Yii::$app->response;
        $response->format = $response::FORMAT_JSON;
        $sub_ticket = '';
        if (isset($result['sub_ticket'])) {
        	$sub_ticket = $result['sub_ticket'];
        }
        $result = ['isSuccess'=>$isSuccess,'ticket'=>$this->ticket,'message'=>$msg,'result'=>$result,'sub_ticket'=>$sub_ticket];
        $response->content = json_encode($result);
        $response->send();
        exit;
    }


    //根据cookie中的backurl重定向
    public function backUrlLocation()
    {
        $back = cookie('backUrl');
        if($back && !Yii::$app->request->getIsAjax())
        {
            cookie('backUrl',null);
            header("location: ".$back);
            exit;
        }
    }
	
}
