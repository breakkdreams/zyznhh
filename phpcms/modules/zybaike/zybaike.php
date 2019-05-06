<?php

defined('IN_PHPCMS') or exit('No permission resources.');
pc_base::load_app_class('admin', 'admin', 0);	//加载应用类方法
pc_base::load_sys_class('form', 0, 0);
pc_base::load_app_func('admin', 'set_config', 0);	//加载应用类方法
pc_base::load_app_func('global');

class zybaike extends admin{
	
	
	public function __construct(){
		session_start();
		parent::__construct();
		$this->zy_baike = pc_base::load_model('zybaike_model');
		pc_base::load_sys_class('format','',0);
		pc_base::load_sys_class('form','',0);
		
	}
	
	//发布的文章列表
	public function wenzhang(){
		$title=isset($_GET['title'])?$_GET['title']:'';
		$username = isset($_GET['username'])?$_GET['username']:'';
		$time = isset($_GET['time'])?$_GET['time']:'';
		
		$where='';//模糊搜索
		 if(!empty($title)){
			   $where .="`title` like '%$title%' AND ";
		   }
		   if(!empty($username)){
			   $where .="`username` like '%$username%' AND ";
		   }														
		   if(!empty($time)){
			   $where .="`time` like '%$time%' AND ";
		   }
		  
		   $where .='1';//关键点$order='id desc';
		
		 $order='id desc';
		
		//$page = $_GET['page'] ? $_GET['page'] : '1';
		$info = $this->zy_baike->listinfo($where,$order, $page=1, $pagesize = 20);
		
		
		$pages=$this->zy_baike->pages;
		
		include $this->admin_tpl('fabu_lists');//
	}
	
	public function baike_islock(){
		
		
		if($_GET['islock']==1){
			//禁止
			$this->zy_baike->update(['islock'=>1],['id'=>$_GET['id']]);
		}elseif ($_GET['islock']==0) {
			//开启
			$this->zy_baike->update(['islock'=>0],['id'=>$_GET['id']]);
		}
		showmessage(L('operation_success'),HTTP_REFERER);
		
		
	}
	
	
	//===========文章查看
	public function wenzhang_view(){
		
		$show_header = false;		//去掉最上面的线条
		$id=intval($_GET['id']);
		if($id){
			$info = $this->zy_baike->get_one(array('id'=>$id));
		} 
		
		//$info = $this->zy_baike->select();

		include $this->admin_tpl('wenzhang_view');
	}
		
	
	
	
	
	
	 /**
	 *	文章删除
	 */
	 public function del_wenzhang(){
		 
			//删除单个
		$id=intval($_GET['id']);
		if($id){
			$result=$this->zy_baike->delete(array('id'=>$id));
			//$this->member_detail_db->delete(array('userid'=>$id));
			//$this->sso_members_db->delete(array('uid'=>$id));
			if($result)
			{
				showmessage(L('operation_success'),HTTP_REFERER);
			}else {
				showmessage(L("operation_failure"),HTTP_REFERER);
			}
		}

		//批量删除；
		if(is_array($_POST['id'])){
			foreach($_POST['id'] as $pid) {
				$result=$this->zy_baike->delete(array('id'=>$pid));
			}
			showmessage(L('operation_success'),HTTP_REFERER);
		}

		//都没有选择删除什么
		if( empty($_POST['id'])){
			showmessage('请选择要删除的文章',HTTP_REFERER);
		}
	 }
	
	
}




?>