<?php
defined('IN_zy') or exit('No permission resources.');
pc_base::load_app_func('global');
pc_base::load_sys_class('form', '', 0);
pc_base::load_sys_class('format', '', 0);



class goodsApi{

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
		//商品属性组合表
		$this->goods_specs_db = pc_base::load_model('goods_specs_model');
		//商品属性表
		$this->goods_attr_db = pc_base::load_model('goods_attr_model');
		//购物车表
		$this->goodscarts_db = pc_base::load_model('goodscarts_model');
		//商品搜索历史表
		$this->goods_sh_db = pc_base::load_model('goods_sh_model');

		
	}

    /**
     * 首页
     * param:rid(首页1),
     */
    public function index_page(){
        $rid = $_POST['rid'];//推荐位ID，实际参考后台推荐位内容
        if ( $rid == null ) {
            $result = [
                'status' => 'error',
                'code' => 0,
                'message' => '参数错误！',
            ];
            exit(json_encode($result,JSON_UNESCAPED_UNICODE));
        }
        $where = 'a.id = b.goodsid and a.isok = 1 and a.on_sale = 1 and b.pos_id = '.$rid;
        $sql ='SELECT a.id,a.goods_name,a.thumb,a.summary,a.market_price,a.shop_price FROM zy_goods a,zy_goodspositem b WHERE '.$where.' ORDER BY a.addtime DESC';
        $page = $_POST['page'] ? $_POST['page'] : '1';
        $goods_list = $this->get_db->multi_listinfo($sql,$page,$pagesize = 10);
        //banner图

        //头条

        //二级分类
        $catsql = 'SELECT id,cate_name,cate_img FROM zy_goodscat WHERE pid > 0 ORDER BY sort';
        $cate_list = $this->get_db->multi_listinfo($catsql,$page = 1,$pagesize = 8);

        $result = [
            'status'=>'success',
            'code'=>200,
            'message'=>'成功',
            'goods_list'=>$goods_list,
            'cate_list'=>$cate_list,
        ];
        exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
    }

    /**
     *商品列表
     * param:uid(用户id),is_search(搜索传1),sercon(搜索的标题),is_sort(销量1 价格2 新品3),sort(排序1.asc 2.desc),catid(分类id),page(分页页数)
     */
    public function goods_list_page(){
        $_userid = param::get_cookie('_userid');
        $userid = $_POST['uid'];
        if($_userid){
            $uid = $_userid;
        }else{
            $uid = $userid;
        }
        $is_search = $_POST['is_search'];//1是搜索
        $where = ' isok = 1 and on_sale = 1 ';
        if($is_search == 1){
            if (!empty($_POST['sercon'])) {
                $where .= " and goods_name like '%".$_POST['sercon']."%' ";
                if ( !empty($uid) ) {
                    $his = $this->goods_sh_db->get_one(['userid'=>$uid]);
                    if ( count($his) == 0 ) {
                        $hisarr = [];
                        $hisarr[] = $_POST['sercon'];
                        $hiscon = array2string($hisarr);
                        $this->goods_sh_db->insert(['userid'=>$uid,'searchHistory'=>$hiscon]);
                    } else {
                        $hisarr = string2array($his['searchHistory']);
                        foreach ($hisarr as $k => $v) {
                            if ( $_POST['sercon'] == $v ) {
                                unset($hisarr[$k]);
                                array_values($hisarr);
                                break;
                            }
                        }
                        if ( count($hisarr) < 10 ) {
                            //$hisarr[] = $_POST['sercon'];
                            array_unshift($hisarr,$_POST['sercon']);
                        } else {
                            unset($hisarr[9]);
                            array_unshift($hisarr,$_POST['sercon']);
                        }
                        $hiscon = array2string($hisarr);
                        $this->goods_sh_db->update(['searchHistory'=>$hiscon],['userid'=>$uid]);
                    }
                }
            }
        }

        $rid = $_POST['catid'];
        if ( $rid != null ) {
            $where .=' and catid in ('.$rid.')';
        }

        $order =' id desc ';
        $is_sort = $_POST['is_sort'];//销量1 价格2 新品3
        $sort = $_POST['sort'];//排序(1.asc 2.desc)
        if($is_sort == 1){
            $order =' salesnum '.$sort;
        }else if($is_sort == 2){
            $order =' shop_price '.$sort;
        }else if($is_sort == 3){
            $order =' addtime '.$sort;
        }

        $sql = 'SELECT id,goods_name,thumb,summary,market_price,shop_price,salesnum,score_price FROM zy_goods WHERE '.$where.'ORDER BY'.$order;
        $page = $_POST['page'] ? $_POST['page'] : '1';
        $info = $this->get_db->multi_listinfo($sql,$page,$pagesize = 10);

        $sqls = 'SELECT COUNT(*) as num FROM zy_goods WHERE '.$where.'ORDER BY'.$order;
        $res = $this->goods_db->query($sqls);
        $page = $this->goods_db->fetch_array($res);
        $totalnum = $page[0]['num'];
        $totalpage = ceil($totalnum/10);

        $result = [
            'status' => 'success',
            'code' => 200,
            'message' => '查询成功',
            'data' => $info,
            'page' => [
                'pagesize'=>10,
                'totalpage'=>$totalpage,
                'totalnum' => $totalnum
            ]
        ];
        $jg = json_encode($result,JSON_UNESCAPED_UNICODE);
        $jg = stripslashes($jg);
        exit($jg);
    }

	/**
     *获取用户商品搜索记录,分类
     * param:uid(用户id)
     */
	public function goods_sh(){
        require('classes/PHPTree.class.php');//加载树形结构类
		$_userid = param::get_cookie('_userid');
		$userid = $_POST['uid'];

		if($_userid){
			$uid = $_userid;
		}else{
			$uid = $userid;
		}
        $hisarr = '';
		if ( $uid != null ) {
            $info = $this->goods_sh_db->get_one(['userid'=>$uid]);
            $hisarr = string2array($info['searchHistory']);
		}
		//
        $infos = $this->goodscat_db->select('','id,cate_name,cate_img,pid','',$order = 'sort ASC,id ASC');
        $data = catetree($infos);
        //dump($infos,true);
        $r=PHPTree::makeTree($data, array(

        ));

		$result = [
			'status' => 'success',
			'code' => 200,
			'message' => '查询成功',
			'data' => [
				'hiscon' => $hisarr,
                'content' => $r
			]
		];

		exit(json_encode($result,JSON_UNESCAPED_UNICODE));
	}

    /**
     *商品详情
     * param:gid(商品id)
     */
    public function goodsinfo(){

        $gid = $_POST['gid'];

        if ( !$gid || !is_numeric($gid) ) {
            $result = [
                'status' => 'error',
                'code' => 0,
                'message' => '参数异常！',
            ];
            exit(json_encode($result,JSON_UNESCAPED_UNICODE));
        }

        $where =' id = '.$gid.' and isok = 1 and on_sale = 1 ';
        $info = $this->goods_db->get_one($where);
        if ( count($info) == 0 ) {
            $result = [
                'status' => 'error',
                'code' => -1,
                'message' => '商品不存在或已经下架！',
            ];
            exit(json_encode($result,JSON_UNESCAPED_UNICODE));
        }
        $info['album'] = string2array($info['album']);
//        unset($info['content']);
       if ( $info['isspec'] == 1) {
           $where = ' goodsid = '.$gid;
           $sinfo = $this->goods_specs_db->select($where,'id,specid,specids,specprice,specstock,status','',$order = ' id ASC ');
           $info['specdata'] = $sinfo;
       }

        $result = [
            'status' => 'success',
            'code' => 1,
            'message' => 'OK',
            'data' => $info,
        ];
        $jg = json_encode($result,JSON_UNESCAPED_UNICODE);
        $jg = stripslashes($jg);

        exit($jg);
    }

    /**
     *商品配置
     * param:tid(类型id)
     */
    public function goodsSpecList(){

        $tid = $_POST['tid'];

        if ( !$tid || !is_numeric($tid) ) {
            $result = [
                'status' => 'error',
                'code' => 0,
                'message' => '参数异常！',
            ];
            exit(json_encode($result,JSON_UNESCAPED_UNICODE));
        }

        $where =' goodstypeid = '.$tid.' and isshow = 1 ';
        $info = $this->goodsattr_db->select($where);
        if ( count($info) == 0 ) {
            $result = [
                'status' => 'error',
                'code' => -1,
                'message' => '商品无配置！',
            ];
            exit(json_encode($result,JSON_UNESCAPED_UNICODE));
        }

        $result = [
            'status' => 'success',
            'code' => 1,
            'message' => 'OK',
            'data' => $info,
        ];
        $jg = json_encode($result,JSON_UNESCAPED_UNICODE);
        $jg = stripslashes($jg);

        exit($jg);
    }

    /**
     *商品根据配置获取数量和价格
     * param:specids(配置ids(名称以逗号隔开)),gid(商品id)
     */
    public function goodsSpecInfo(){

        $gid = $_POST['gid'];
        $specids = $_POST['specids'];

        if ( !$gid || !is_numeric($gid) ) {
            $result = [
                'status' => 'error',
                'code' => 0,
                'message' => '参数异常！',
            ];
            exit(json_encode($result,JSON_UNESCAPED_UNICODE));
        }

        $where =" goodsid = ".$gid." and specids like '%".$specids."%' ";
        $info = $this->goods_specs_db->get_one($where);
        if ( count($info) == 0 ) {
            $result = [
                'status' => 'error',
                'code' => -1,
                'message' => '商品无配置！',
            ];
            exit(json_encode($result,JSON_UNESCAPED_UNICODE));
        }

        $result = [
            'status' => 'success',
            'code' => 1,
            'message' => 'OK',
            'data' => $info,
        ];
        $jg = json_encode($result,JSON_UNESCAPED_UNICODE);
        $jg = stripslashes($jg);

        exit($jg);
    }




/*************************************************************************************************************************************************************************/


    /**
     *购物车修改操作
     */
	public function operacars(){
		//dump($_POST,true);
		$_userid = param::get_cookie('_userid');
		$userid = $_POST['uid'];

		if($_userid){
			$uid = $_userid;
		}else{
			$uid = $userid;
		}

		if ( !$uid ) {
			$result = [
				'status' => 'error',
				'code' => 0,
				'message' => '请先登录！',
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE));
		}
		if (!$_POST['gid'] || !isset($_POST['spec']) || !$_POST['cnum']) {
			$result = [
				'status' => 'error',
				'code' => -1,
				'message' => '访问受限，缺少必要参数',
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE));
		}

		$check = checkspec($_POST['gid'],$_POST['spec']);
		if ( $check['code'] == -2 ) {
			exit(json_encode($check,JSON_UNESCAPED_UNICODE));
		}

		if ( $check['data']['stock'] < $_POST['cnum'] ) {
			$result = [
				'status' => 'error',
				'code' => -3,
				'message' => '加入购物车失败，商品库存不足',
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE));
		}

		// if ( $check['data']['sid'] == $uid ) {
		// 	$result = [
		// 		'status' => 'error',
		// 		'code' => -4,
		// 		'message' => '商家不能购买自己店铺的商品',
		// 	];
		// 	exit(json_encode($result,JSON_UNESCAPED_UNICODE));
		// }

		//$info = $this->goodscarts_db->get_one(['userid'=>$uid,'goodsspecId'=>$_POST['spec'],'goodsid'=>$_POST['gid']]);

		// if ( count($info) == 0 ) {
		// 	$data = [];
		// 	$data['userid'] = $uid;
		// 	$data['goodsid'] = $_POST['gid'];
		// 	$data['goodsspecid'] = $_POST['spec'];
		// 	$data['ischeck'] = 1;
		// 	$data['cartnum'] = $_POST['cnum'];
		// 	$this->goodscarts_db->insert($data);
		// }else{
		$this->goodscarts_db->update(['cartnum'=>/*'+='.*/$_POST['cnum']],['userid'=>$uid,'goodsspecId'=>$_POST['spec'],'goodsid'=>$_POST['gid']]);
		// }

		$result = [
			'status' => 'success',
			'code' => 1,
			'message' => '操作成功',
		];
		exit(json_encode($result,JSON_UNESCAPED_UNICODE));

		
	}



	/**
     *加入购物车
     */
	public function addbuycart(){
		//dump($_POST,true);
		$_userid = param::get_cookie('_userid');
		$userid = $_POST['uid'];

		if($_userid){
			$uid = $_userid;
		}else{
			$uid = $userid;
		}

		if ( !$uid ) {
			$result = [
				'status' => 'error',
				'code' => 0,
				'message' => '请先登录！',
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE));
		}
		if (!$_POST['gid'] || !isset($_POST['spec']) || !$_POST['cnum']) {
			$result = [
				'status' => 'error',
				'code' => -1,
				'message' => '访问受限，缺少必要参数',
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE));
		}

		$check = checkspec($_POST['gid'],$_POST['spec']);
		if ( $check['code'] == -2 ) {
			exit(json_encode($check,JSON_UNESCAPED_UNICODE));
		}

		if ( $check['data']['stock'] < $_POST['cnum'] ) {
			$result = [
				'status' => 'error',
				'code' => -3,
				'message' => '加入购物车失败，商品库存不足',
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE));
		}

		// if ( $check['data']['sid'] == $uid ) {
		// 	$result = [
		// 		'status' => 'error',
		// 		'code' => -4,
		// 		'message' => '商家不能购买自己店铺的商品',
		// 	];
		// 	exit(json_encode($result,JSON_UNESCAPED_UNICODE));
		// }

		$info = $this->goodscarts_db->get_one(['userid'=>$uid,'goodsspecId'=>$_POST['spec'],'goodsid'=>$_POST['gid']]);

		if ( count($info) == 0 ) {
			$data = [];
			$data['userid'] = $uid;
			$data['goodsid'] = $_POST['gid'];
			$data['goodsspecid'] = $_POST['spec'];
			$data['ischeck'] = 1;
			$data['cartnum'] = $_POST['cnum'];
			$this->goodscarts_db->insert($data);
		}else{
			$this->goodscarts_db->update(['cartnum'=>'+='.$_POST['cnum']],['userid'=>$uid,'goodsspecId'=>$_POST['spec'],'goodsid'=>$_POST['gid']]);
		}

		$result = [
			'status' => 'success',
			'code' => 1,
			'message' => '操作成功',
		];
		exit(json_encode($result,JSON_UNESCAPED_UNICODE));

		
	}


	/**
     *删除购物车商品
     */
	public function delcars(){
		//dump($_POST,true);
		$_userid = param::get_cookie('_userid');
		$userid = $_POST['uid'];

		if($_userid){
			$uid = $_userid;
		}else{
			$uid = $userid;
		}

		if ( !$uid ) {
			$result = [
				'status' => 'error',
				'code' => 0,
				'message' => '请先登录！',
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE));
		}
		if (!$_POST['cid']) {
			$result = [
				'status' => 'error',
				'code' => -1,
				'message' => '访问受限，缺少必要参数',
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE));
		}

		if(is_numeric($_POST['cid'])){
			$op = $this->goodscarts_db->delete(['userid'=>$uid,'id'=>$_POST['cid']]);
		}else{
			$op = $this->goodscarts_db->delete(' id in ('.$_POST['cid'].') and userid = '.$uid.' and ischeck <> 2');
		}
		
		if ( $op ) {
			$result = [
				'status' => 'success',
				'code' => 1,
				'message' => '删除商品成功',
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE));
		}
		$result = [
			'status' => 'success',
			'code' => -2,
			'message' => '删除商品失败',
		];
		exit(json_encode($result,JSON_UNESCAPED_UNICODE));

		
	}



	/**
     *购物车数据
     */
	public function getcarts(){
		
		$_userid = param::get_cookie('_userid');
		$userid = $_POST['uid'];
		//exit($_POST['uid']);
		if($_userid){
			$uid = $_userid;
		}else{
			$uid = $userid;
		}

		if ( !$uid ) {
			$result = [
				'status' => 'error',
				'code' => 0,
				'message' => '请先登录！',
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE));
		}

        $sql = 'SELECT b.id, b.shopid, b.thumb, b.goods_name, b.shop_price, b.stock, a.id as cartid, a.goodsspecid, a.cartnum, c.specprice, c.specstock, c.specid, c.specids FROM zy_goodscarts a INNER JOIN zy_goods b ON a.goodsid = b.id and a.userid = '.$uid.' and a.ischeck <> 2 LEFT OUTER JOIN zy_goods_specs c ON a.goodsspecid = c.specid and c.shopid = b.shopid and c.goodsid = b.id';
        // $sql = 'SELECT a.id, a.thumb, a.goods_name, a.shop_price, a.stock, b.goodsspecid, b.cartnum, c.specprice, c.specid, c.specids FROM zy_goods a LEFT OUTER JOIN zy_goodscarts b ON a.id = b.goodsid and b.userid = '.$uid.' LEFT OUTER JOIN zy_goods_specs c ON a.id = c.goodsid and c.shopid = '.$uid;
        $sqls = 'SELECT b.shopid as id FROM zy_goodscarts a INNER JOIN zy_goods b ON a.goodsid = b.id and a.userid = '.$uid.' LEFT OUTER JOIN zy_goods_specs c ON a.goodsspecid = c.specid and c.shopid = '.$uid.' group by b.shopid ';
        $page = $_POST['page'] ? $_POST['page'] : '1';
        $info = $this->get_db->multi_listinfo($sql,$page,88888888);
        $infos = $this->get_db->multi_listinfo($sqls,$page,88888888);
        $idarr = '';
        foreach ($infos as $key => $value) {
        	if ( empty($idarr) ) {
        		$idarr = $value['id'];
        	}else{
        		$idarr .= ','.$value['id'];
        	}
        }

        $token_url= APP_PATH.'index.php?m=zymember&c=zymember_api&a=zyshop_nickname';
        $data = array ('ids' => $idarr);
        $content = http_build_query($data);
        $content_length = strlen($content);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' =>
                "Content-type: application/x-www-form-urlencoded\r\n" .
                "Content-length: $content_length\r\n",
                'content' => $content
            )
        );
		$token = json_decode(file_get_contents($token_url,false,stream_context_create($options)));
	    $rs =  json_decode(json_encode($token),true);
	    $snamarr = [];
	    foreach ($rs['data'] as $ks => $vs) {
        	$snamarr[$vs['userid']] = $vs;
        }
        //dump($snamarr,true);
        
        $narr = [];
        foreach ($info as $k => $v) {
        	if(!isset($narr[$v['shopid']])){
        		$narr[$v['shopid']] = [
	        		'shopid' => $v['shopid'],
	        		'shopname' => $snamarr[$v['shopid']]['shopname'],
        		];
        	}
        	
        	if ( $v['goodsspecid'] != 0 ) {
        		$jg = $v['specprice'];
        	} else {
        		$jg = $v['shop_price'];
        	}
        	$narr[$v['shopid']]['cartinfo'][] = [
        		'cartid' => $v['cartid'],
        		'goodsid' => $v['id'],
        		'goodsname' => $v['goods_name'],
        		'goodsimg' => $v['thumb'],
        		'goodsspec' => $v['specid'],
        		'goodsspecs' => $v['specids'],
        		'goodsprice' => $jg,
        		'cartnum' => $v['cartnum'],
        	];

        }

		$result = [
			'status' => 'success',
			'code' => 1,
			'message' => 'OK',
			'data' => [
				'carts' => array_values($narr),
				'uid' => $uid
			],
		];
		$jg = json_encode($result,JSON_UNESCAPED_UNICODE);
        $jg = stripslashes($jg);

        exit($jg);
	}	



	/**
     *立即购买前置操作
     */
	public function buynow(){
		//dump($_POST,true);
		$_userid = param::get_cookie('_userid');
		$userid = $_POST['uid'];

		if($_userid){
			$uid = $_userid;
		}else{
			$uid = $userid;
		}

		if ( !$uid ) {
			$result = [
				'status' => 'error',
				'code' => 0,
				'message' => '请先登录！',
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE));
		}
		if (!$_POST['gid'] || !isset($_POST['spec']) || !$_POST['cnum']) {
			$result = [
				'status' => 'error',
				'code' => -1,
				'message' => '访问受限，缺少必要参数',
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE));
		}

		$check = checkspec($_POST['gid'],$_POST['spec']);
		if ( $check['code'] == -2 ) {
			exit(json_encode($check,JSON_UNESCAPED_UNICODE));
		}

		if ( $check['data']['stock'] < $_POST['cnum'] ) {
			$result = [
				'status' => 'error',
				'code' => -3,
				'message' => '购买失败，商品库存不足',
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE));
		}

		if ( $check['data']['stock'] < $_POST['cnum'] ) {
			$result = [
				'status' => 'error',
				'code' => -3,
				'message' => '购买失败，商品库存不足',
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE));
		}

		$info = $this->goodscarts_db->get_one(['userid'=>$uid,'ischeck'=>2]);

		if ( count($info) == 0 ) {
			$data = [];
			$data['userid'] = $uid;
			$data['goodsid'] = $_POST['gid'];
			$data['goodsspecid'] = $_POST['spec'];
			$data['ischeck'] = 2;
			$data['cartnum'] = $_POST['cnum'];
			$this->goodscarts_db->insert($data);
		}else{
			$data = [];
			$data['goodsid'] = $_POST['gid'];
			$data['goodsspecid'] = $_POST['spec'];
			$data['cartnum'] = $_POST['cnum'];
			$this->goodscarts_db->update($data,['userid'=>$uid,'ischeck'=>2]);
		}

		$result = [
			'status' => 'success',
			'code' => 1,
			'message' => 'OK',
		];
		exit(json_encode($result,JSON_UNESCAPED_UNICODE));
		
	}


	/**
     *购物车结算前置操作
     */
	// public function cartsettle(){
	// 	//dump($_POST,true);
	// 	$_userid = param::get_cookie('_userid');
	// 	$userid = $_POST['uid'];
	// 	$cids = $_POST['cids'];

	// 	if($_userid){
	// 		$uid = $_userid;
	// 	}else{
	// 		$uid = $userid;
	// 	}

	// 	if ( !$uid ) {
	// 		$result = [
	// 			'status' => 'error',
	// 			'code' => 0,
	// 			'message' => '请先登录！',
	// 		];
	// 		exit(json_encode($result,JSON_UNESCAPED_UNICODE));
	// 	}
	// 	if (!$cids) {
	// 		$result = [
	// 			'status' => 'error',
	// 			'code' => -1,
	// 			'message' => '访问受限，缺少必要参数',
	// 		];
	// 		exit(json_encode($result,JSON_UNESCAPED_UNICODE));
	// 	}


		
	// }



	/**
     *订单结算预览
     */
	public function settlement(){
		//dump($_POST,true);
		$_userid = param::get_cookie('_userid');
		$userid = $_POST['uid'];
		$method = $_POST['met'];
		$cids = $_POST['cids'];

		if($_userid){
			$uid = $_userid;
		}else{
			$uid = $userid;
		}

		if ( !$uid ) {
			$result = [
				'status' => 'error',
				'code' => 0,
				'message' => '请先登录！',
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE));
		}
		if ($method == 1) {
			$info = $this->goodscarts_db->select(['userid'=>$uid,'ischeck'=>2]);
			if(count($info) == 0){
				$result = [
					'status' => 'error',
					'code' => -1,
					'message' => '访问受限，参数无效',
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE));
			}
			$cids = $info[0]['id'];	
		} else {
			if ( empty($cids) ) {
				$result = [
					'status' => 'error',
					'code' => -2,
					'message' => '访问受限，缺少参数',
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE));
			}
			$arr = explode(',', $cids);
			$where = ' userid = '.$uid.' and id in('.$cids.') and ischeck <> 2 ';	
			$info = $this->goodscarts_db->select($where);
			if ( count($info) != count($arr) ) {
				$result = [
					'status' => 'error',
					'code' => -1,
					'message' => '访问受限，参数无效',
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE));
			}	
		}


		$sql = 'SELECT b.id, b.shopid, b.thumb, b.goods_name, b.shop_price, b.stock, a.id as cartid, a.goodsspecid, a.cartnum, c.specprice, c.specstock, c.specid, c.specids FROM zy_goodscarts a INNER JOIN zy_goods b ON a.goodsid = b.id and a.userid = '.$uid.' and a.id in('.$cids.') LEFT OUTER JOIN zy_goods_specs c ON a.goodsspecid = c.specid and c.shopid = b.shopid and c.goodsid = b.id';

		$sqls = 'SELECT b.shopid as id FROM zy_goodscarts a INNER JOIN zy_goods b ON a.goodsid = b.id and a.userid = '.$uid.' and a.id in('.$cids.') LEFT OUTER JOIN zy_goods_specs c ON a.goodsspecid = c.specid and c.shopid = '.$uid.' group by b.shopid ';

        $page = $_GET['page'] ? $_GET['page'] : '1';
        $info = $this->get_db->multi_listinfo($sql,$page,88888888);
        //dump($info,true);
        $infos = $this->get_db->multi_listinfo($sqls,$page,88888888);
        $idarr = '';
        foreach ($infos as $key => $value) {
        	if ( empty($idarr) ) {
        		$idarr = $value['id'];
        	}else{
        		$idarr .= ','.$value['id'];
        	}
        }

        $token_url= APP_PATH.'index.php?m=zymember&c=zymember_api&a=zyshop_nickname';
        $data = array ('ids' => $idarr);
        $content = http_build_query($data);
        $content_length = strlen($content);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' =>
                "Content-type: application/x-www-form-urlencoded\r\n" .
                "Content-length: $content_length\r\n",
                'content' => $content
            )
        );
		$token = json_decode(file_get_contents($token_url,false,stream_context_create($options)));
	    $rs =  json_decode(json_encode($token),true);
	    $snamarr = [];
	    foreach ($rs['data'] as $ks => $vs) {
        	$snamarr[$vs['userid']] = $vs;
        }

        $narr = [];

        $total = 0;
        $tnum = 0;

        foreach ($info as $k => $v) {
        	if(!isset($narr[$v['shopid']])){
        		$narr[$v['shopid']] = [
	        		'shopid' => $v['shopid'],
	        		'shopname' => $snamarr[$v['shopid']]['shopname'],
	        		'stprice'=>0,
	        		'stnum'=>0
        		];
        	}
        	
        	if ( $v['goodsspecid'] != 0 ) {
        		$jg = $v['specprice'];
        	} else {
        		$jg = $v['shop_price'];
        	}
        	$narr[$v['shopid']]['stprice'] += $jg*$v['cartnum'];
        	$narr[$v['shopid']]['stnum'] += $v['cartnum'];
        	$total += $jg*$v['cartnum'];
        	$tnum += $v['cartnum'];
        	$narr[$v['shopid']]['cartinfo'][] = [
        		'cartid' => $v['cartid'],
        		'goodsid' => $v['id'],
        		'goodsname' => $v['goods_name'],
        		'goodsimg' => $v['thumb'],
        		'goodsspec' => $v['specid'],
        		'goodsspecs' => $v['specids'],
        		'goodsprice' => $jg,
        		'cartnum' => $v['cartnum'],
        	];

        }

        $result = [
			'status' => 'success',
			'code' => 1,
			'message' => 'OK',
			'data' => [
				'shops' => array_values($narr),
				'uid' => $uid,
				'totalprice' => $total,
				'totalnum' => $tnum
			],
		];
		$jg = json_encode($result,JSON_UNESCAPED_UNICODE);
        $jg = stripslashes($jg);

        exit($jg);
		
	}



	/**
     *订单确认订单生成
     */
	public function sureMakeOrder(){
		//dump($_POST,true);
		$_userid = param::get_cookie('_userid');
		$userid = $_POST['uid'];
		$method = $_POST['met'];
		$cids = $_POST['cids'];
		$addid = $_POST['address'];

		if($_userid){
			$uid = $_userid;
		}else{
			$uid = $userid;
		}

		if ( !$uid ) {
			$result = [
				'status' => 'error',
				'code' => 0,
				'message' => '请先登录！',
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE));
		}
		if ($method == 1) {
			$info = $this->goodscarts_db->select(['userid'=>$uid,'ischeck'=>2]);
			if(count($info) == 0){
				$result = [
					'status' => 'error',
					'code' => -1,
					'message' => '访问受限，参数无效',
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE));
			}
			$cids = $info[0]['id'];	
		} else {
			if ( empty($cids) ) {
				$result = [
					'status' => 'error',
					'code' => -2,
					'message' => '访问受限，缺少参数',
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE));
			}
			$arr = explode(',', $cids);
			$where = ' userid = '.$uid.' and id in('.$cids.') and ischeck <> 2 ';	
			$info = $this->goodscarts_db->select($where);
			if ( count($info) != count($arr) ) {
				$result = [
					'status' => 'error',
					'code' => -1,
					'message' => '访问受限，参数无效',
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE));
			}	
		}

		if ( empty($addid) ) {
			$result = [
				'status' => 'error',
				'code' => -5,
				'message' => '访问受限，缺少参数',
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE));
		}

		$token_url= APP_PATH.'index.php?m=zyaddr&c=zyaddr_api&a=getaddr';       
        $data = array (
        	'userid' => $uid,
        	'id' => $addid,   		
        );
        $content = http_build_query($data);
        $content_length = strlen($content);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' =>
                "Content-type: application/x-www-form-urlencoded\r\n" .
                "Content-length: $content_length\r\n",
                'content' => $content
            )
        );
		$token = json_decode(file_get_contents($token_url,false,stream_context_create($options)));
	    $rs =  json_decode(json_encode($token),true);

		$province = $rs['data']['province']/*$_POST['province']*/;  //收货地址省 
		$city = $rs['data']['city']/*$_POST['city']*/;//收货地址市
		$area = $rs['data']['district']/*$_POST['area']*/;//收货地址区
		$address = $rs['data']['address']/*$_POST['address']*/; //详细地址
		$lx_mobile = $rs['data']['phone']/* $_POST['lx_mobile']*/; //联系电话
		$lx_name = $rs['data']['name']/*$_POST['lx_name']*/; //联系人
		$lx_code = '　'/*$_POST['lx_code']*/; //联系邮编
		$mes = $_POST['note'];//用户留言

		if ( empty($province) || empty($city) || empty($area) || empty($address) ) {
			$result = [
				'status' => 'error',
				'code' => -3,
				'message' => '地址信息不全',
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE));
		}

		if ( empty($lx_mobile) || empty($lx_name) || empty($lx_code) ) {
			$result = [
				'status' => 'error',
				'code' => -4,
				'message' => '联系人信息不全',
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE));
		}


		$sql = 'SELECT b.id, b.shopid, b.thumb, b.goods_name, b.shop_price,b.score_price, b.stock, a.id as cartid, a.goodsspecid, a.cartnum, c.specprice, c.specstock, c.specid, c.specids FROM zy_goodscarts a INNER JOIN zy_goods b ON a.goodsid = b.id and a.userid = '.$uid.' and a.id in('.$cids.') LEFT OUTER JOIN zy_goods_specs c ON a.goodsspecid = c.specid and c.shopid = b.shopid and c.goodsid = b.id';

		$sqls = 'SELECT b.shopid as id FROM zy_goodscarts a INNER JOIN zy_goods b ON a.goodsid = b.id and a.userid = '.$uid.' and a.id in('.$cids.') LEFT OUTER JOIN zy_goods_specs c ON a.goodsspecid = c.specid and c.shopid = '.$uid.' group by b.shopid ';

        $page = $_GET['page'] ? $_GET['page'] : '1';
        $info = $this->get_db->multi_listinfo($sql,$page,88888888);   
	    
        $narr = [];
        $total = 0;
		// $tnum = 0;
		$scoretotal = 0;
        foreach ($info as $k => $v) {
        	if(!isset($narr[$v['shopid']])){
        		$narr[$v['shopid']] = [
	        		'shopid' => $v['shopid'],
	        		'stprice'=>0,
					'stnum'=>0,
					'scoreprice'=>0,
        		];
        	}
        	
        	if ( $v['goodsspecid'] != 0 ) {
				$jg = $v['specprice'];
        	} else {
        		$jg = $v['shop_price'];
			}
			
			$sp = $v['score_price'];

        	$narr[$v['shopid']]['stprice'] += $jg*$v['cartnum'];
			$narr[$v['shopid']]['stnum'] += $v['cartnum'];
			$narr[$v['shopid']]['scoreprice'] += $sp*$v['cartnum'];
			$total += $jg*$v['cartnum'];
			$scoretotal += $sp*$v['cartnum'];
        	// $tnum += $v['cartnum'];
        	$narr[$v['shopid']]['cartinfo'][] = [
        		'cartid' => $v['cartid'],
        		'goodsid' => $v['id'],
        		'goodsname' => $v['goods_name'],
        		'goodsimg' => $v['thumb'],
        		'goodsspec' => $v['specid'],
        		'goodsspecs' => $v['specids'],
        		'goodsprice' => $jg,
        		'cartnum' => $v['cartnum'],
        	];
		}
		
		//获取用户的积分值
		$member=$this->member_db->get_one(array('userid'=>$userid));
		$userscore = $member['scoremoney'];
		if($userscore < $scoretotal){
			$result = [
				'status' => 'error',
				'code' => -300,
				'message' => '积分不足'
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE));
		}
		

        $token_url= APP_PATH.'index.php?m=zyorder&c=zyorderApi&a=addorder';
        
        $data = array (
        	'userid' => $uid,
        	'province' => $province,  
    		'city' => $city,      
    		'area' => $area,    
    		'address' => $address, 
    		'lx_mobile' => $lx_mobile,
    		'lx_name' => $lx_name,
    		'lx_code' => $lx_code,
			'usernote' => $mes,
			'scoretotal' => $scoretotal,
    		'shopdata' => $narr  
        );
        $content = http_build_query($data);
        $content_length = strlen($content);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' =>
                "Content-type: application/x-www-form-urlencoded\r\n" .
                "Content-length: $content_length\r\n",
                'content' => $content
            )
        );
		$token = json_decode(file_get_contents($token_url,false,stream_context_create($options)));
	    $rs =  json_decode(json_encode($token),true);

	    if ( $rs['data']['code'] != 1 ) {

	    }
	    if ($where) {
	    	$this->goodscarts_db->delete($where);
	    }
        $result = [
			'status' => 'success',
			'code' => 1,
			'message' => 'OK',
			'data' =>$rs['data']
		];
		
		exit(json_encode($result,JSON_UNESCAPED_UNICODE));
		
	}


	/**
     *订单支付完成计算销量库存
     */
	public function sales_balance (){

		$_userid = param::get_cookie('_userid');
		$userid = $_POST['uid'];
		$oids = $_POST['oids'];

		if($_userid){
			$uid = $_userid;
		}else{
			$uid = $userid;
		}

		if ( !$uid ) {
			$result = [
				'status' => 'error',
				'code' => 0,
				'message' => '请先登录！',
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE));
		}	

		if ( !$oids ) {
			$result = [
				'status' => 'error',
				'code' => -1,
				'message' => '缺少必要参数！',
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE));
		}
	}

}
?>