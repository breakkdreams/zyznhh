<?php
defined('IN_PHPCMS') or exit('No permission resources.');
pc_base::load_app_class('admin', 'admin', 0);
pc_base::load_sys_class('format', '', 0);
pc_base::load_sys_class('form', '', 0);
pc_base::load_app_func('global');

/**
 * @property bool|mixed member_db
 * @property bool|mixed get_db
 * @property bool|mixed member_detail_db
 * @property bool|mixed admin
 * @property bool|mixed report_db
 * @property bool|mixed community_db
 * @property bool|mixed zyconfig_db
 * @property bool|mixed module_db
 */
class community extends admin {
	function __construct() {
		parent::__construct();
		$this->get_db = pc_base::load_model('get_model');
		$this->admin = pc_base::load_model('admin_model');
		$this->member_db = pc_base::load_model('member_model');
		$this->member_detail_db = pc_base::load_model('member_detail_model');

		$this->report_db = pc_base::load_model('report_model');
		$this->community_db = pc_base::load_model('community_model');

		$this->zyconfig_db = pc_base::load_model('zyconfig_model');
		$this->module_db = pc_base::load_model('module_model');
		$this->config = $this->zyconfig_db->get_one('','url');
		
	}


	/**
	* 举报列表
	*/
	public function reportlist(){
		$where = ' 1 ';
		$page = $_GET['page'] ? $_GET['page'] : '1';
		$infos = $this->report_db->listinfo($where, $order = '', $page, $pagesize = 20);
		for($i=0;$i<sizeof($infos);$i++){
			$member=$this->member_db->get_one(array('userid'=>$infos[$i]['userid']));
			$infos[$i]['username'] = $member['nickname'];
		}
		$pages = $this->report_db->pages;
		include $this->admin_tpl('community');
	}

	/**
	* 查看文章详情
	*/
	public function communityInfo(){			
		$community = $this->community_db->get_one(array('id'=>$_GET['id']));	
		include $this->admin_tpl('communityinfo');
	}





	/**
	* 编辑banner
	*/
	public function communityedit(){

		if($_POST['dosubmit']){
			$id=$_POST['id'];
	
			$types = $_POST['type'];
			$score = $_POST['score'];
			$status = 0;
			if($types == 1){
				//同意(1.更改举报记录状态和积分值 2.增加积分)
				$status = 1;
			}elseif($types == 2){
				//拒绝(1.更改举报记录状态和积分值 2.减少积分)
				$status = -1;
			}
			
        	$data=[
        		'status'=>$status,
				'score'=>$score
        	];
        	
			$results=$this->report_db->update($data,array('id'=>$id));

			$info = $this->report_db->get_one(array('id'=>$id));

			$member=$this->member_db->get_one(array('userid'=>$info['userid']));
			if($types == 1){
				//同意(增加积分)
				$scoreres = $member['scoremoney'] + $score;
				$data=[
					'scoremoney'=>$scoreres,
				];
				$this->member_db->update($data,array('id'=>$info['userid']));
			}elseif($types == 2){
				//拒绝(减少积分)
				$scoreres = $member['scoremoney'] - $score;
				if($scoreres<0){
					$scoreres = 0;
				}
				$data=[
					'scoremoney'=>$scoreres,
				];
				$this->member_db->update($data,array('id'=>$info['userid']));
			}

        	showmessage(L('修改成功'), '?m=community&c=community&a=communityedits');
			
		}else{
						
			$info = $this->report_db->get_one(array('id'=>$_GET['id']));
			$type = $_GET['type'];

			include $this->admin_tpl('communityedit');
		}
	}

	/**
	* 添加中间跳转
	*/
	public function banneredits(){
		showmessage(L('operation_success'), '', '', 'edit');
	}

}
?>












