<?php
/**
 *
 *
 *框架入口的基类文件
 *
 *
 */
error_reporting(E_ALL);
define('MYFRAME_PATH',dirname(__FILE__).DIRECTORY_SEPARATOR);
base::load_sys_func('global');


class base{

        /**
	 *加载系统类文件
	 *@param string $classname 类文件名
	 *@param string $path,扩展地址
	 *@param int $inialize 是否初始化（实例化）
	 */
	public static function load_sys_class($classname,$path='',$inialize=1){
                     return self::_load_class($classname,$path,$inialize);	
	
	}
        /**
	 *加载模型类的方法
	 *@parama $model 模型类名称
	 */
	public static function load_model($model){
              return self::_load_class($model,'model');	
	
	}

	/**
	 *加载系统函数
	 *@parama string $func 函数名称
	 *
	 */

	public static function load_sys_func($func){
              return self::_load_func($func);	
	}

	/**
	 *类文件加载基函数
	 *@param string $classname 类文件名
	 *@param string $path,路径名
	 *@param inter $inialize 是否初始化
	 */

	private static function _load_class($classname,$path='',$inialize=1){
		static $class=array();
		if($path == ''){ 
			$path='libs'.DIRECTORY_SEPARATOR.'classes';	
                }	
                $key=md5($path.$classname);	
		if(isset($class[$key])){

			if(!empty($class[$key])){
		            return $class[$key];  	
			}else{
		             return true;	
			}	
		}
		if(file_exists(MYFRAME_PATH.$path.DIRECTORY_SEPARATOR.$classname.'.class.php')){
	                     include MYFRAME_PATH.$path.DIRECTORY_SEPARATOR.$classname.'.class.php';	
			     $name=$classname;
			     if($my_path=self::my_path(MYFRAME_PATH.$path.DIRECTORY_SEPARATOR.$classname.'.class.php')){
			         include $my_path; 
				 $name='MY_'.$classname;
			     }

	                     if($inialize){
	                           $class[$key]=new $name;	
		             }else{
	                           $class[$key]=true;	
		             }
		}else{
		
	         return false;	
		
		}
		return $class[$key];
	}

        /**
	 *函数库加载基方法
	 *@parama string $func
	 *@parama string $path 参数是目录并且不带目录分隔符的
	 *@parama boolean $return
	 */
	private static function _load_func($func,$path=''){
              static $funcs=array();	
	      if($path == ''){
	         $path=MYFRAME_PATH.'libs'.DIRECTORY_SEPARATOR.'functions'; 
	      }

	      $path.=DIRECTORY_SEPARATOR.$func.'.func.php';
              $key=md5($path);	
              if(isset($funcs[$key]))return true;	
	      if(file_exists($path)){
	            include $path; 
		    $funcs[$key]=true;
	      }else{
	          return false; 
	      }

             return $funcs[$key];
	}


        /**
	 *
	 *加载配置的方法
	 *@param string $file 配置文件名称
	 *@param string $key 键值
	 *@param string $default 获取参数失败时返回 $default值
	 *@param boolean $reload 强制从新加载
	 */
	static public function load_config($file,$key='',$default='',$reload=false){
            static $configs=array();	
	    //判断并且缓存多次调用的值.
	    if(!$reload && isset($configs[$file])){
		    if(empty($key)){
		        return $configs[$file]; 
		    }elseif(isset($configs[$file][$key])){
		        return $configs[$file][$key]; 
		    }else{
		        return $default; 
		    }
	    }
            $path=MYFRAME_PATH.'configs'.DIRECTORY_SEPARATOR.$file.'.php';   	
	    if(file_exists($path)){
	      $configs[$file]= include $path; 
	    }else{
              echo 'file is not exists';	    
	    }	
	    if(empty($key)){
	       return $configs[$file]; 
	    }elseif(isset($configs[$file][$key])){
	       return $configs[$file][$key];
	    }else{
	       return $default; 
	    }

	}

	/**
	 *自己的扩展文件(自己新添加的系统类库文件的扩展文件)
	 *@parama $filepath 路径名称
	 *
	 */
	public static function my_path($filepath){
            $path_parts=pathinfo($filepath);	

	    if(file_exists($path_parts['dirname'].DIRECTORY_SEPARATOR.'MY_'.$path_parts['basename'])){

	       return $path_parts['dirname'].DIRECTORY_SEPARATOR.'MY_'.$path_parts['basename']; 
	    
	    }else{
	       return false; 
	    }	
	
	}


}
