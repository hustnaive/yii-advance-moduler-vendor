<?php
namespace yunke\base;

class DALBase {
    protected $orgcode;
    /**
     * 主实例,master,可读写
     * @access private
     * @var \yii\db\Connection <master>
     */
     private $db;

    /**
     * 从实例,slave 只读实例
     * @access private
     * @var \yii\db\Connection <slave> 
     */
//      private $db_read;

    /**
     * 当前时间
     *
     * @var bool|string
     */
    protected $_current_date;
    
    public function __construct($orgcode)
    {
        $this->orgcode = $orgcode;
        $this->_current_date = date ( "Y-m-d H:i:s" );
    }


    /**
     * execute the method if the property not exists
     * 
     * 只针对
     * $this->db 	  触发只建立connection not open
     * $this->db_read 触发只建立connection not open
     * 
     * @param string $name 参数名
     * @param mixed|\yii\db\Connection 返回单例的connection对象
     */
    public function __get($name)
    {
    	if( !in_array($name,['db','db_read']) ) throw E("DAL属性[{$name}]不存在,请检查");
    	
    	static $db = [];  //make single instance for \yii\db\Connection
    	$static_key = $name .'_' .$this->orgcode;
    	
    	if(isset($db[$static_key])) return $db[$static_key];
//     	 echo $name,'|',$this->orgcode,'<br/>';
        if( $name == 'db') {		 //只构造master connection对象，并不open  	
        	 $db[$static_key] =  DB($this->orgcode,'master',false);
        }elseif( $name == 'db_read' ){ //只构造slave connection对象，并不open
        	 
        	 $db[$static_key] =  DB($this->orgcode,'slave',false);
        }
    	
        return $db[$static_key]; 
    }
    
    /**
     * 返回分页的sql片段
     *
     * @param array $limit
     * @return string
     */
    protected function _getLimitSql($limit = array())
    {
        $sql = '';
        if (count($limit))
        {
            $page = max(1,intval($limit[0]));
            $page_size = intval($limit[1]);
            if (isset($limit[2])) {
            	$move = $limit[2];
            } else {
            	$move = 0;
            }
            $offset = ($page-1)*$page_size + $move;
            $sql = ' LIMIT '.$offset.','.$page_size;
        }

        return $sql;
    }
}
