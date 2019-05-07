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
		$this->zy_pinglun = pc_base::load_model('zypinglun_model');
		pc_base::load_sys_class('format','',0);
		pc_base::load_sys_class('form','',0);
		
	}
	
	//发布的文章列表
	public function wenzhang(){
		//条件
		$where= '1';
		if($_GET['type']){
			if($_GET['q']){
				if($_GET['type'] == 1){
					$where .= " and userid like '%".$_GET['q']."%' ";
				}
				if($_GET['type'] == 2){
					$where .= " and username ='".$_GET['q']."'";
				}
				if($_GET['type'] == 3){
					$where .= " and id like '%".$_GET['q']."%' ";
				}
				if($_GET['type'] == 4){
					$where .= " and title like '%".$_GET['q']."%' ";
				}
				
			}
		}		
		
		
		
		
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
		
		$page = $_GET['page'] ? $_GET['page'] : '1';
		$info = $this->zy_baike->listinfo($where,$order, $page, $pagesize = 20);
		
		
		$pages=$this->zy_baike->pages;
		
		include $this->admin_tpl('fabu_lists');//
	}
	
	//==========禁止/开启（状态）
	
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
		
	
		
		//===========举报查看
	public function jubao_view(){
		
		$show_header = false;		//去掉最上面的线条
		$id=intval($_GET['id']);//文章id
		
			$info = $this->zy_pinglun->listinfo(array('reportid'=>$id));
		
		//$info = $this->zy_baike->select();

		include $this->admin_tpl('jubao_view');
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
	
	
	/**
	 *评论
	 */
	public function pinglun(){

		$id=isset($_GET['id'])?$_GET['id']:'';
		$userid = isset($_GET['userid'])?$_GET['userid']:'';
		$articleid = isset($_GET['articleid'])?$_GET['articleid']:'';
		$time = isset($_GET['time'])?$_GET['time']:'';
		
		$where='';//模糊搜索
		 if(!empty($id)){
			   $where .="`id` like '%$id%' AND ";
		   }
		   if(!empty($userid)){
			   $where .="`userid` like '%$userid%' AND ";
		   }
		   if(!empty($articleid)){
			   $where .="`articleid` like '%$articleid%' AND ";
		   }														
		   if(!empty($time)){
			   $where .="`time` like '%$time%' AND ";
		   }
		  
		   $where .='1';//关键点$order='id desc';
		$order='id desc';
		$page = $_GET['page'] ? $_GET['page'] : '1';
		
		
		
		$info = $this->zy_pinglun->listinfo($where,$order, $page, $pagesize = 20);

		$pages=$this->zy_pinglun->pages;
    	include $this->admin_tpl('pinglun');//	
		
	
	}
	
	
	
	 /**
	 *	文章评论删除
	 */
	 public function del_pinglun(){
		 
			//删除单个
		$id=intval($_GET['id']);
		if($id){
			$result=$this->zy_pinglun->delete(array('id'=>$id));
			       $this->zy_pinglun->delete(array('pid'=>$id));
		
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
				$result=$this->zy_pinglun->delete(array('id'=>$pid));
			}
			showmessage(L('operation_success'),HTTP_REFERER);
		}

		//都没有选择删除什么
		if( empty($_POST['id'])){
			showmessage('请选择要删除的文章',HTTP_REFERER);
		}
	 }
	
		//===========文章评论查看
	public function pinglun_view(){
		
		$show_header = false;		//去掉最上面的线条
		$id=intval($_GET['id']);
		if($id){
			$info = $this->zy_pinglun->get_one(array('id'=>$id));
		} 
		
		//$info = $this->zy_baike->select();

		include $this->admin_tpl('pinglun_view');
	}
	
	

	
	
	
	
	
	
	
}




?>