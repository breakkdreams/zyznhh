<?php
defined('IN_PHPCMS') or exit('No permission resources.');
pc_base::load_app_class('admin', 'admin', 0);
pc_base::load_sys_class('format', '', 0);
pc_base::load_sys_class('form', '', 0);
pc_base::load_app_func('global');

class banner extends admin {
	function __construct() {
		parent::__construct();
		$this->get_db = pc_base::load_model('get_model');
		$this->admin = pc_base::load_model('admin_model');
		$this->member_db = pc_base::load_model('member_model');
		$this->member_detail_db = pc_base::load_model('member_detail_model');

		//商品表
		$this->goods_db = pc_base::load_model('goods_model');
		//轮播图表
		$this->banner_db = pc_base::load_model('banner_model');
		//banner表
		$this->brand_db = pc_base::load_model('brand_model');
		//推荐位表
		$this->position_db = pc_base::load_model('goodsposition_model');
		//分类栏目表
		$this->goodscat_db = pc_base::load_model('goodscat_model');
		//商品类型表
		$this->goodstype_db = pc_base::load_model('goodstype_model');
		//商品推荐位表
		$this->positem_db = pc_base::load_model('goodspositem_model');

		//商品类型属性表
		$this->goodsattr_db = pc_base::load_model('goodsattr_model');
		//商品规格组合表
		$this->goods_specs_db = pc_base::load_model('goods_specs_model');
		//商品属性表
		$this->goods_attr_db = pc_base::load_model('goods_attr_model');


		$this->zyconfig_db = pc_base::load_model('zyconfig_model');
		$this->module_db = pc_base::load_model('module_model');
		$this->config = $this->zyconfig_db->get_one('','url');
		
	}


	/**
	* 轮播图
	*/
	public function bannerlist(){
		$where = ' 1 ';
		$page = $_GET['page'] ? $_GET['page'] : '1';
		$infos = $this->banner_db->listinfo($where, $order = 'sort ASC, id DESC', $page, $pagesize = 20);
		$pages = $this->banner_db->pages;
		//添加
		$big_menu = array('javascript:window.top.art.dialog({id:\'add\',iframe:\'?m=banner&c=banner&a=banneradd\', title:\'添加轮播图\', width:\'800\', height:\'500\', lock:true}, function(){var d = window.top.art.dialog({id:\'add\'}).data.iframe;var form = d.document.getElementById(\'dosubmit\');form.click();return false;}, function(){window.top.art.dialog({id:\'add\'}).close()});void(0);', '添加轮播图');	
		include $this->admin_tpl('banner');
	}


	/**
     * banner排序
     */
    public function bannerlistorder() {
    	// dump($_POST,true);
        if(isset($_POST['listorders'])) {
            foreach($_POST['listorders'] as $id => $listorder) {
                $this->banner_db->update(array('sort'=>$listorder),array('id'=>$id));
            }
            showmessage(L('operation_success'),'?m=banner&c=banner&a=bannerlist');
        } else {
            showmessage(L('operation_failure'),'?m=banner&c=banner&a=bannerlist');
        }
    }

	/**
	* 添加banner
	*/
	public function banneradd(){

		if($_POST['dosubmit']){
			$option = '';
			$types = $_POST['type'];
			if($types == 2){
				$option = $_POST['goodsid'];
			}elseif($types == 4){
				$option = '';
			}
			
        	$data=[
        		'type'=>$types,
        		'img'=>$_POST['thumb'],
				'option'=>$option,
				'sort'=>1
        	];
        	
        	$results=$this->banner_db->insert($data);

        	showmessage(L('添加成功'), '?m=banner&c=banner&a=banneradds');

		}else{
			$upload_allowext = 'jpg|jpeg|gif|png|bmp';
			$isselectimage = '1';
			$images_width = '';
			$images_height = '';
			$watermark = '0';
			$authkey = upload_key("1,$upload_allowext,$isselectimage,$images_width,$images_height,$watermark");

			$goodslist = $this->goods_db->select(array('isok'=>1,'on_sale'=>1));
			include $this->admin_tpl('banneradd');
		}
	}


	/**
	* 添加中间跳转
	*/
	public function banneradds(){
		showmessage(L('operation_success'), '', '', 'add');
	}


	/**
	* 编辑banner
	*/
	public function banneredit(){

		if($_POST['dosubmit']){
			$id=$_POST['id'];
			$option = '';
			$types = $_POST['type'];
			if($types == 2){
				$option = $_POST['goodsid'];
			}elseif($types == 4){
				$option = '';
			}
			
        	$data=[
        		'type'=>$types,
        		'img'=>$_POST['thumb'],
				'option'=>$option
        	];
        	
        	$results=$this->banner_db->update($data,array('id'=>$id));

        	showmessage(L('修改信息成功'), '?m=banner&c=banner&a=banneredits');
			
		}else{
			$upload_allowext = 'jpg|jpeg|gif|png|bmp';
			$isselectimage = '1';
			$images_width = '';
			$images_height = '';
			$watermark = '0';
			$authkey = upload_key("1,$upload_allowext,$isselectimage,$images_width,$images_height,$watermark");					
			$info = $this->banner_db->get_one(array('id'=>$_GET['id']));
			$goodslist = $this->goods_db->select(array('isok'=>1,'on_sale'=>1));
			include $this->admin_tpl('banneredit');
		}
	}

	/**
	* 添加中间跳转
	*/
	public function banneredits(){
		showmessage(L('operation_success'), '', '', 'edit');
	}



	/**
	* banner_删除
	*/
	public function bannerdel(){
		//删除单个
		$id=intval($_GET['id']);
		if($id){
			$result=$this->banner_db->delete(array('id'=>$id));
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
				$result=$this->banner_db->delete(array('id'=>$pid));
			}
			showmessage(L('operation_success'),HTTP_REFERER);
		}

		//都没有选择删除什么
		if( empty($_POST['id'])){
			showmessage('请选择要删除的记录',HTTP_REFERER);
		}
	}

}
?>












