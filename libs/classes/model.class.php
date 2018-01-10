<?php
/**
 *数据模型基类 model
 *
 *
 *
 */
base::load_sys_class('db_factory','',0);
class model{

	/**
	 *所有数据库的配置项
	 *
	 */
	protected $db_config='';

	/**
	 *要调用的数据库配置项
	 *
	 */
	protected $db_set='default';

         /**
	  *数据库连接操作实例
	  *
	  *
	  */
        protected $db = '';
	/**
	 *要操作的表名
	 *
	 */

	protected $db_table='';

	/**
	 *表前缀
	 *
	 *
	 */
	protected $table_pre='';

	/**
	 *取得数据库实例连接
	 *
	 */

	public function __construct(){
		if(!isset($this->db_config[$this->db_set])){
                     $this->db_set='defalut';
		}
	 $this->table_pre = $this->db_config[$this->db_set]['tablepre'];
         $this->db_table = $this->table_pre.$this->db_table;
         $this->db=db_factory::get_instance($this->db_config)->get_database($this->db_set);

	}

        /**
	 *记录查询方法
	 *@parama array $fildes 要查询的字段 
	 *@parama string $where 要查询的条件
	 *@parama string $group 分组查询的条件
	 *@parama string $order 排序的条件
	 *@parama limit 查询的记录数
	 */
	public function select($fields,$where='',$group='',$order='',$limit=''){
		if(is_array($where)){
	            $where=$this->sqls($where);	
		} 
              return $this->db->select($fields,$this->db_table,$where,$group,$order,$limit);	
	
	}
        /**
	 *查询多条数据并分页
	 *@parama $page 当前是第几页
	 *@parama $pagesize 每页显示的条数
	 */
	public function listinfo($fields,$page=1,$pagesize=10,$where='',$group='',$order=''){
	      //当前页码
	      $page=max(intval($page),1);
	      //偏移量
	      $offest=($page-1)*$pagesize;
	      //每页显示的条数
	      $pagesize=$pagesize;
	      //一共多少页;
	      $count=$this->count($where);

	    return  $this->select($fields,$where,$group,$order,"$offest,$pagesize");
	
	}

	/**
	 *获取单条记录查询
	 *@parama array $fields 要查询的字段 
	 *@parama $where 要查询的条件
	 *@parama $group 分组的条件
	 *@parama $order 排序的条件
	 */

	public function get_one($fields=['*'],$where='',$group='',$order=''){
               if(is_array($where))$where=$this->sqls($where);	
               return $this->db->get_one($fields,$this->db_table,$where,$group,$order);	
	
	}
        /**
	 *统计查询的总数
	 *
	 */
	public function count($where=''){
         $res=$this->get_one(['COUNT(*) AS num'],$where);     	
         return $res['num'];
	}
        /**
	 *数组形式的转换成字符形式的
	 *@parama array $where 
	 *@parama string $font $where条件以什么逻辑语来连接，默认是以and来连接
	 */
	public function sqls($where,$font='AND'){ 
		if(is_array($where)){
	             $q='';
		     foreach($where as $k=>$v){
		       $q.=$q ? "$font `$k`= '$v' " : " `$k`= '$v' "; 
		     }	     
		     return $q;
		
		}else{
	             return $where;	
		
		}
	
	}

}


