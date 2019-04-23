<?php
defined('IN_PHPCMS') or exit('No permission resources.');
pc_base::load_app_func('global');
pc_base::load_sys_class('form', '', 0);
pc_base::load_sys_class('format', '', 0);



class index{
	function __construct() {
		$this->get_db = pc_base::load_model('get_model');
		$this->admin = pc_base::load_model('admin_model');
		$this->member_db = pc_base::load_model('member_model');
		$this->member_detail_db = pc_base::load_model('member_detail_model');
		
		//商品表
		$this->goods_db = pc_base::load_model('goods_model');
		//品牌表
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

		
		//登录后方可操作
		// $_userid = param::get_cookie('_userid');
		// if (!$_userid) showmessage(L('login_website'), APP_PATH.'index.php?m=member&c=index&a=login&forward='.urlencode($_SERVER["REQUEST_URI"]));	
	}


	/**
     *商品详情
     */
	public function goodsinfo(){

		$where =' id = '.$_GET['id'].' and isok = 1 and on_sale = 1 ';
		$info = $this->goods_db->get_one($where);
		if ( count($info) == 0 ) {
			showmessage('商品不存在或已下架', HTTP_REFERER);
		}
		
		include template('hpshop', 'goodsinfo');
	}


	/**
     *商品详情
     */
	public function goodsinfos(){

		$where =' id = '.$_GET['id'].' and isok = 1 and on_sale = 1 ';
		$info = $this->goods_db->get_one($where);
		if ( count($info) == 0 ) {
			showmessage('商品不存在或已下架', HTTP_REFERER);
		}
		
		include template('hpshop', 'goodsinfo');
	}



	/**
     *添加商品
     */
	public function goodsadd(){

		
		include template('hpshop', 'goodsadd');
	}



	/**
     *商品分类
     */
	public function allcat(){

		
		include template('hpshop', 'allcat');
	}


	/**
     *商品分类列表
     */
	public function cgoodslist(){

		
		include template('hpshop', 'cgoodslist');
	}


	/**
     *商品搜索
     */
	public function search(){

		
		include template('hpshop', 'search');
	}


	/**
     *商品结算
     */
	public function settlement(){

		
		include template('hpshop', 'settlement');
	}


	/**
     *购物车
     */
	public function goodscart(){

		
		include template('hpshop', 'goodscart');
	}   
	


	/**
     *店铺_出售中
     */
	public function store_sell(){

		
		include template('hpshop', 'store_sell');
	}   


	/**
     *店铺_未上架
     */
	public function store_wshelves(){

		
		include template('hpshop', 'store_wshelves');
	}   
	

	/**
     *店铺_已上架
     */
	public function store_yshelves(){

		
		include template('hpshop', 'store_yshelves');
	}   
	

	/**
     *店铺_未审核
     */
	public function store_waudit(){

		
		include template('hpshop', 'store_waudit');
	}   

	/**
     *店铺_商品发布
     */
	public function store_goodsfb(){

		
		include template('hpshop', 'store_goodsfb');
	}   
	



}
?>