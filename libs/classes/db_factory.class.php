<?php
/**
 *
 *数据工厂类实例化
 *
 *
 */
final class db_factory{

     /**
      *返回数据工厂类的实例对象
      *
      */
     public static $db_factory;

     /**
      *返回数据库的配置信息
      *
      */

     private $db_config = array();

     /**
      *返回数据库操作实例化列表
      *
      */

     public $db_list=array();

     /**
      *构造函数
      *
      */
     public function __construct(){
     
     }

     /**
      *或得数据工厂类的实例
      *@parama $db_config 配置信息
      *@return 返回数据工厂对象
      */

     public static function get_instance($db_config=''){
	     if($db_config == ''){
	        $db_config = base::load_config('database');   
	     }
	     if(db_factory::$db_factory == ''){

		     db_factory::$db_factory= new db_factory(); 
	     } 

	     if($db_config !='' && $db_config != db_factory::$db_factory->db_config){
	         db_factory::$db_factory->db_config=array_merge($db_config,db_factory::$db_factory->db_config); 
	     }
        return db_factory::$db_factory;

     }

     /**
      * 获得数据库操作实例
      * @parama $db_name 数据库的配置项
      *
      */

     public function get_database($db_name){
        if(!isset($this->db_list[$db_name]) || !is_object($this->db_list[$db_name])){     

                           $this->db_list[$db_name]= $this->connect($db_name); 
	} 
     
          return $this->db_list[$db_name]; 
     } 

     /**
      *加载数据库的驱动文件
      *
      *
      */
     public function connect($db_name){
	   $default=$this->db_config[$db_name];
	   $object=null;
           switch($default['type']){
	   case 'mysql' :
		   base::load_sys_class('mysql','',0);
		   $object=new mysql();
		   break;
	   case 'mysqli' :
	           base::load_sys_class('db_mysqli','',0);	   
	           $object=new db_mysqli(); 
		   break;
	   case 'access' :
		   base::load_sys_class('access','',0);
		   $object=new access();
		   break;
	   default :
		   base::load_sys_class('mysql','',0); 
		   $object=new mysql();
	   } 
           $object->open($default);
	   return $object;
     
     }

     /**
      *关闭数据库连接
      *
      */
     protected function close(){
     
	     foreach($this->db_list as $db){
	         $db->close(); 
	     } 
     
     }

     public function __destruct(){
          $this->close(); 
     }

}
