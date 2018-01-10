<?php
/**
 *
 *数据库操作类（底层操作类）
 *
 */


final class mysql{

	//数据库配置信息

	private $config =null;

        //数据库连接句柄
	
	public $link = null;

	//最近一次资源句柄查询

	public $lastqueryid=null;
	//数据库的查询次数的统计
        public $querycount=0;

        //构造函数，在类每次new一个对象前被调用

        public function __construct(){
	
	}

        /**
	 *
	 *与数据库建立一个链接，有可能并不真实的打开一个数据库的资源句柄
	 *@param $db_name is One-dimensional array
	 */ 
        public function open($db_name){

          $this->config=$db_name;	
	  if($db_name['autoconnect'] = 1){
	      $this->connect(); 
	  }	
	
	}

	/**
	 *
	 *打开一个数据库的真实链接句柄
	 *
	 */

	public function connect(){
	
             $func= $this->config['pconnect']==1 ? 'mysql_pconnect' : 'mysql_connect';	
             $result=@$func($this->config['hostname'],$this->config['username'],$this->config['password'],1); 
	     if($result){

	        $this->link=$result;	

	     }else{

	      $this->halt('connect mysql service faild!');

	      return false;

	     }	
	     //设置客户端与mysql服务端的字符集的转换
	     if($this->version()>'4.1'){
	       $charset= isset($this->config['charset']) ? $this->config['charset']:''; 
	       $serverset= $charset ? "character_set_connection='$charset',character_set_results='$charset',character_set_client=binary" :''; 
	        $serverset.= $this->version()>'5.0.1' ? (!empty($serverset) ? ",sql_mode=''" : '') : ''; 
	        $serverset && mysql_query("SET $serverset",$this->link); 

	     }	
            
	     if(isset($this->config['databases'])){
	         
		  if(!mysql_select_db($this->config['databases'],$this->link)){
		     $this->halt('not use databases',$this->config['databases']);   
		  } 
                  $this->databases=$this->config['databases'];	
	     }	
             
	     return $this->link;
	
	}

	/**
	 *
	 *selec 语句查询
	 *@parme $table 表名
	 *@parme $field 要查询的字段名 以数组形式 如：array('name','age','birthday')
	 *@parme $where 查询的条件 如：'id = 1'
	 *@parme $group 分组的条件
	 *@parme $order 排序的条件
	 *@parme $limit 获取记录的行数（一般用于分页）
	 *
	 */

	public function select($field,$table,$where='',$group='',$order='',$limit=''){
               $where = $where != '' ? ' WHERE '.$where :'';
	       $group = $group != '' ? ' GROUP BY '.$goup :'';
	       $order = $order != '' ? ' ORDER BY '.$order : '';
	       $limit = $limit != '' ? ' LIMIT '.$limit : '';
	       array_walk($field,array($this,'add_spcial_char'));

	       $field = implode(',',$field);
	       $sql= 'SELECT '.$field.' from `'.$table.'`'.$where.$group.$order.$limit;

	       $res=$this->excute($sql);

	       if(!$res){
	        return $this->lastqueryid; 
	       }
	       $resdata=array();

	       while($ar=$this->fetch_next()){
	            $resdata[]=$ar; 
	       }

	       $this->free_result();
	       return $resdata;
	}
        /**
	 *@parma $table 表名
	 *@parma $field 查询的字段名
	 *@parma $where 查询的条件
	 *@parma $group 分组的条件，默认是空
	 *@parma $order 排序的条件，默认是空
	 *@parma $return 返回值是数据
	 */
	public function get_one($field,$table,$where='',$group='',$order=''){
		$where = $where != '' ? ' WHERE '.$where :'';
		$group = $group != '' ? ' GROUP BY '.$group :'';
		$order = $order != '' ? ' ORDER BY '.$order :'';
                $limit = ' LIMIT 1';
		array_walk($field,array($this,'add_spcial_char'));

		$field =implode(',',$field);
		$sql='SELECT '.$field.' FROM '.$table.$where.$group.$order.$limit;
                $res=$this->excute($sql);	
                if(!$res){
	          return $this->lastqueryid;	
		}

		$resdata=$this->fetch_next();
		$this->free_result();
		return $resdata;

	}

	/*
	 *
	 *增加记录操作
	 *@parme $data 添加的数据，以数组的形式，键名为字段名，键值为取值.
	 *@parme $table 要增加记录的表名
	 *@parme $return_insert_id 新创建记录的id
	 *@parme $replace 是否用replace 方式来插入
	 *
	 *
	 */
	public function insert($data,$table,$return_insert_id=false,$replace=false){
	
               if(!is_array($data) || $table=='' || count($data)==0) return false;
	       
	           $fieldsdata=array_keys($data);
                   $valuesdata=array_values($data);	
	
               array_walk($fieldsdata,array($this,'add_spcial_char'));	
               array_walk($valuesdata,array($this,'escape_string'));	
              $field =implode(",",$fieldsdata);
	      $value =implode(",",$valuesdata);
	      $cmd= $replace ? "REPLACE INTO" :"INSERT INTO";
	      $sql= $cmd."`".$table."`(".$field.") values (".$value.")";

	      $return=$this->excute($sql);

	      return $return_insert_id ? $this->return_insert_id() : $return; 

	}
	/*
	 *sql语句执行函数
	 *
	 */

	public function excute($sql){

           if(!is_resource($this->link)){	
		   $this->connect();
	   }
           $this->lastqueryid = mysql_query($sql,$this->link) or $this->halt(mysql_error(),$sql);	
	   $this->querycount++;
	   return $this->lastqueryid;
	
	}
	/**
	 *
	 *返回数据库操作影响的行数
	 *
	 */
	public function affected_rows(){
           return mysql_affected_rows($this->link);	
	}
	/**
	 *@param $sql 参数是sql语句
	 *单独执行sql语句的方法
	 *@param $return 函数返回值，若sql语句是select类型，返回值是resource类型。
	 *若是insert，update，delete类型，返回的是boolean 类型，即：ture或false
	 *
	 */
	public function query($sql){
          return $this->excute($sql); 	
	}
	/**
	 *获取数据表的主键
	 *@parme $table 表名
	 *@parme $return 返回数据的主键值
	 *
	 */
	public function get_primary($table){
	      $this->excute('SHOW COLUMNS FROM '.$table);
	      while($arr=$this->fetch_next()){
		      if($arr['key']=='PRI'){ 
			      break;
		      } 
	      }
	      return $arr['Field'];
	}
	/**
	 *获取数据表的字段以及字段的类型
	 *@parme $table 表名
	 *@return 返回值为array
	 */
	public function get_fields($table){
          $this->excute('SHOW COLUMNS FROM '.$table);	
	  $fields=array();
	  while($r=$this->fetch_next()){
	       $fields[$r['Field']]=$r['Type']; 
	  }
          return $fields;	
	}
	/**
	 *检查不存在的字段名
	 *@parma $table 表名
	 *@parma $arry 以数组形式传入的字段
	 */
	public function check_fields($table,$array){
              $fields=$this->get_fields($table);
	      $nofields=array();
	      foreach($array as $v){

		      if(!array_key_exists($v,$fields)){
		           $nofields[]=$v; 
		      } 
	      }
             return $nofields;	
	}
        /*
	 *获得添加记录最后一行的主键号
	 *
	 */
	public function return_insert_id(){
          return mysql_insert_id($this->link);	
	}

        /**
	 *@parame $data     $data参数可以是数组和字符形式的,$data是要更新的数据
	 *                  若$data参数是数组形式的，则数组键名是字段名，键值是要更新的内容
	 *                  同时$data参数也可以是：array('name'=>'+=1','base'=>'-=1'),程序自动
	 *                  会帮你转成'name=name+1 和 base=base-1
	 *                  若$data参数是字符形式的，则是如例子所示："`name`='王五'"
	 *@parame $table    $table 参数可以是字符形式的。这个参数表示的是表的名称
	 *@parame $where    $where 参数是要更新内容的条件
	 *
	 */

	public function update($data,$table,$where=''){

          if($table == '' || $where == '') return false; 	
          $where=' WHERE '.$where;	
	  $field='';
	  if(is_string($data) && $data !=''){

	     $field = $data; 

	  }elseif(is_array($data) && count($data)>0){

		  foreach($data as $k=>$v){
                       $filds=array();
			  switch(substr($v,0,2)){
			  case '+=': 	  
				  $v=substr($v,2);
				  if(is_numeric($v)){
					  
					  $filds[]=$this->add_spcial_char($k).'='.$k.'+'.$this->escape_string($v,'',false);

				  }else{
					  continue;
				  }

			          break; 
			  case '-=':
				  $v=substr($v,2);
				  if(is_numeric($v)){
					  $filds[]=$this->add_spcial_char($k).'='.$k.'-'.$this->escape_string($v,'',false);
				  }else{ 
					  continue;
				  }

				  break;
			  default:
				 $filds[]=$this->add_spcial_char($k).'='.$this->escape_string($v);
			  } 
		  
		  } 

		  $field=implode(',',$filds);
	  }else{
	   return false; 
	  }

	  $sql='UPDATE `'.$table.'`SET '.$field.$where;
	  return $this->excute($sql);
	}

	/**
	 *删除记录操作
	 *@parame $table 参数是表名
	 *@parame $where 删除记录的条件
	 *
	 */
	public function deleted($table,$where){

		if($table == '' || $where == ''){
	          return false;	
		}	

             $where=' WHERE '.$where;	
             $sql='DELETE FROM`'.$table.'`'.$where;	
             return $this->excute($sql);
	}

        /**
	 * 检查表是否存在
	 *$table 参数为表名
	 *$return 函数返回值为 boolean true or false
	 */
	public function table_exits($table){
              	
          $tables=$this->list_tables();	

          return  in_array($table,$tables) ? 1 : 0;
	
	}
	/**
	 *列出数据库中所有的表
	 *
	 */

	public function list_tables(){

           $res=$this->excute('show tables');	
           $arr = array();
	   while($ar = mysql_fetch_array($res,MYSQL_ASSOC)){
               $arr[]= $ar['Tables_in_test']; 	   
	   }
	   return $arr;
	}
        /**
	 *遍历查询结果集
	 *@parme $type 结果是关联数组还是索引数组 MYSQL_ASSOC为关联数组，MYSQL_NUM为索引数组
	 */
	public function fetch_next($type=MYSQL_ASSOC){

		$res = mysql_fetch_array($this->lastqueryid,$type);
                if(!$res){
	           $this->free_result();	
		}
		
            return $res;	
	}
        /**
	 *
	 *释放查询结果集
	 *@parme $return 返回值为null
	 *
	 */
	public function free_result(){

		if(is_resource($this->lastqueryid)){
	            mysql_free_result($this->lastqueryid);	
		    $this->lastqueryid=null;
		}	
	}

	/*
	 *
	 *添加反引号（``）处理函数
	 *
	 */

	public function add_spcial_char(&$values){
	
		if('*'==$values || false !== strpos($values,'(') || false !== strpos($values,'.') || false !== strpos($values,'`')){
	            //不处理，包含于* 或者sql 方法(函数)	
		}else{
	         $values='`'.trim($values).'`';	
		} 	

		if(preg_match('/\b(select|insert|update|delete)\b/i',$values)){
	            $values=preg_replace('/\b(select|insert|updae|delete)\b/i','',$values);	
		}
		return $values;
	} 
	/*
	 *给数据值两边加引号，以保证数据库安全
	 *@param $values 数组值
	 *@param $key 数据键值
	 *@param $quotation 两边是否要加引号
	 */
	public function escape_string(&$values,$key='',$quotation=1){

		if($quotation){
	           $q='\'';	
		}else{
		
	          $q='';	
		}	
		$values=$q.$values.$q;
		return $values;
	
	}

	/**
	 *
	 *mysql错误信息返回，用于调试
	 *
	 *
	 */

	public function halt($message,$sql=''){
             if($this->config['debug']===true){
		     $this->errormsg="<b>mysql query :</b> $sql <br/><b> mysql error:<b/>".$this->error()."<br/><b>".$this->errno()."</b><br/><b>Message :<b/><br/><b>$message</b><a href='' target='_blank' style='color:red;'>need help</a>";
		    $msg=$this->errormsg;
		     echo '<div style="font-size:12px;text-align:left; border:1px solid #9cc9e0; padding:1px 4px;color:#000000;font-family:Arial, Helvetica,sans-serif;"><span>'.$msg.'</span></div>';	     
		     exit;
	     }else{
                 return false;	     
	     }	
        }

	/**
	 *检查字段是否存在
	 *@parame $table 表名
	 *@parame $filed 字段名
	 *@parame $return 返回值为boolean 类型，true or false
	 */
	public function field_exits($table,$filed){
              $fields=$this->get_fields($table);	
              return array_key_exists($filed,$fields);	
	}

	public function error(){
           return @mysql_error($this->link);	
	}
	public function errno(){
           return @mysql_errno($this->link);	
	}
	public function version(){
           return mysql_get_server_info($this->link);	
	}
	/**
	 *关闭数据库连接
	 *
	 */
	public function close(){
		if(is_resource($this->link)){
                    @mysql_close($this->link);		
		}	
	}

}
