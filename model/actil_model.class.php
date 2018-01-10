<?php

base::load_sys_class('model','',0);
class actil_model extends model{

       public function __construct(){
         $this->db_config=base::load_config('database');       
	 $this->db_table='actil';
	 $this->db_set='default';
         parent::__construct();
       }
}
