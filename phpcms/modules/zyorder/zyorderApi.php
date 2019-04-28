<?php
defined('IN_PHPCMS') or exit('No permission resources.');
pc_base::load_app_func('global');
pc_base::load_sys_class('form', '', 0);
pc_base::load_sys_class('format', '', 0);

class zyorder_api{
	function __construct() {
		$this->get_db = pc_base::load_model('get_model');
		$this->order_db = pc_base::load_model('zy_order_model');
		$this->logistics_db = pc_base::load_model('zy_logistics_model');
		$this->evaluate_set_db = pc_base::load_model('zy_evaluate_set_model');
		$this->evaluate_db = pc_base::load_model('zy_evaluate_model');
		//订单商品表
		$this->ordergoods_db = pc_base::load_model('zy_order_goods_model');
		//商品表
		$this->goods_db = pc_base::load_model('goods_model');
		$this->member_db = pc_base::load_model('member_model');
		
	}
    /**
     * CURL方式的GET传值
     * @param  [type] $url  [GET传值的URL]
     * @return [type]       [description]
     */
     function _crul_get($url){
	    $oCurl = curl_init();  
	    if(stripos($url,"https://")!==FALSE){  
	    	 curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);  
		     curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);  
		     curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1  
	   }  
	   curl_setopt($oCurl, CURLOPT_URL, $url);  
	   curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );  
	   $sContent = curl_exec($oCurl);  
	   $aStatus = curl_getinfo($oCurl);  
	   curl_close($oCurl);  
	   if(intval($aStatus["http_code"])==200){  
		   return $sContent;
	   }else{  
	       return false;  
	   }  
}  





     /**
       * CURL方式的POST传值
       * @param  [type] $url  [POST传值的URL]
       * @param  [type] $data [POST传值的参数]
       * @return [type]       [description]
     */
     function _crul_post($url,$data){

       //初始化curl		
       $curl = curl_init();
       curl_setopt($curl, CURLOPT_URL, $url); 
       curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
       curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        //post提交方式
       curl_setopt($curl, CURLOPT_POST, 1);
       curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
       //要求结果为字符串且输出到屏幕上
       curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
       //运行curl
       $result = curl_exec($curl);

       //返回结果	    
       if (curl_errno($curl)) {
          return 'Errno'.curl_error($curl);
       }
       curl_close($curl);
       return $result;
      }
	
	/**
     *单个订单商品详情(用户版，此处用户与商家相同)
     */
	public function uordgoodsinfo($ischeck = 1,$orderid = 0){

		if ( $_POST['oid'] ) {
			$oid = $_POST['oid'];//订单id
		} else {
			$oid = $orderid;//订单id
		}
		//$info = $this->ordergoods_db->select(1);
		if ( empty($oid) ) {
			$result = [
				'status' => 'error',
				'code' => -1,
				'message' => '访问受限，缺少参数',
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE));
		}

		if ( $ischeck == 1 ) {

			$_userid = param::get_cookie('_userid');
			$userid = $_POST['uid'];//用户id，APP端必须传
			//$oid = $_POST['oid'];
			//非APP端直接用$_userid
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

			$count = $this->order_db->count(['id'=>$oid,'userid'=>$uid]);
			if ( $count == 0 ) {
				$result = [
					'status' => 'error',
					'code' => -2,
					'message' => '非法访问',
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE));
			}
		}

		$info = $this->ordergoods_db->select(['order_id'=>$oid],'id,goods_id,goods_name,goods_num,final_price,goods_price,specid,specid_name,is_comment,goods_img');
		if ( $ischeck != 1 ) {
			return $info;
			exit(0);
		}

		if (isset($_POST['eva'])) {
			
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

    public function getevaluateset(){
		$evaluate_sets = $this->evaluate_set_db->select(1);
		$this->caozuo_success($evaluate_sets);
	}
	
	/********************************************** /
	*        用户订单api                               * /
	*/                                            /**/

	
	/**
	* 获取订单列表（店铺用户通用）
	*/
	public function order_list(){
		//用户id和用户名
		//$_userid = $_POST['userid'];
		$pageindex = $_POST['pageindex'];
		$pagesize = $_POST['pagesize'];

		$_userid = param::get_cookie('_userid');
		$userid = $_POST['userid'];//用户id，APP端必须传
		//非APP端直接用$_userid
		if($_userid){
			$uid = $_userid;
		}else{
			$uid = $userid;
		}
		if($pageindex == null){
			$pageindex = 1;
		}
		if($pagesize == null){
			$pagesize = 10;
		}
		if($uid==null){
			$this->empty_userid();
		}
		$where = ' userid = '.$uid;
		if($_POST['status']==1){
			$where.= ' AND status=1';
		}else if($_POST['status']==2){
			$where.= ' AND status=2';
		}else if($_POST['status']==3){
			$where.= ' AND status=3';
		}else if($_POST['status']==4){
			$where.= ' AND status=4';
		}else{
			$where.= ' AND 1';
		}

        $where.=' AND status < 6 ';
		$order = ' id DESC ';
		$page = $pageindex ? $pageindex : '1';
		$orders = $this->order_db->listinfo($where,$order,$page,$pagesize); //读取数据库里的字段
		$totalcount = $this->order_db->count($where);
		foreach ($orders as $k => $v) {
			$goodsinfo = $this->uordgoodsinfo(0,$v['id']);
			$orders[$k]['goodsinfo'] = $goodsinfo;
			$orders[$k]['storename'] = '';
 		}

		// $orderss = $this->order_db->select($where);
		// $totalcount = count($orderss);
		//查询商品信息
		$totalpage = ceil($totalcount/$pagesize);
		$data = [
			"status"=> 'success',
			"code"=>1,
			"message"=>'操作成功',
			"data"=>$orders,
			'page' => [
				'pagesize'=>$pagesize,
				'totalpage'=>$totalpage,
				'totalnum' => $totalcount
			]
		];
		echo json_encode($data);
	}
	
	 


		
	//订单详情
	public function order_info(){
		//用户id和用户名
		//$_userid = $_POST['userid'];
		$id = $_POST['id'];
		$_userid = param::get_cookie('_userid');
		$userid = $_POST['userid'];

		if($_userid){
			$uid = $_userid;
		}else{
			$uid = $userid;
		}


		if($uid==null){
			exit($this->empty_userid());
		}
		if($this->check_uid($id,$uid)){
			$order = $this->order_db->get_one(array('id'=>$_POST['id'],'userid'=>$uid));
			if($this->check_uid_status($id,$uid,3)){
			  $KdApi = pc_base::load_app_class('KdApiSearch');
			  $KdApi = new KdApiSearch();
			  $order = $this->order_db->get_one(array('id'=>$id,'userid'=>$uid));
			  $logisticResult=$KdApi->getOrderTracesByJson($order['shipper_code'],$order['logistics_order']);
			
		    }
		
			$goods = $this->uordgoodsinfo(0,$order['id']);
			$order['goodsinfo'] = $goods;
			$order['totalprice'] = 0;
			foreach($goods as $good){
				$order['totalprice']  += $good['final_price'];
			}
			$order['goodsnum'] = count($goods);
			$order['storename'] ='' ;
			$order['wuliu'] = $logisticResult;
			$this->caozuo_success($order);
		}else{
			$this->error_check_uid();
		}	
	}
	
	/**
	* 提醒发货
	*/
	public function order_txfh(){
		$id = $_POST['id'];
		$_userid = $_POST['userid'];
		if($_userid ==null){
			$this->empty_userid();
		}
		if($this->check_uid_status($id,$_userid,2)){	
		    $result = $this->order_db->update(array('remind'=>'提醒发货'),array('id'=>$id));
			if($result){
			  $this->caozuo_success("操作成功");
			}
		}else{	
			$this->error_check_uid_status();
		}
	}

	
	/**
	* 订单中心_待支付_取消订单
	*/
	public function order_cancel(){	

		$id = $_POST['id'];
		//$_userid = $_POST['userid'];

		$_userid = param::get_cookie('_userid');
		$userid = $_POST['userid'];

		if($_userid){
			$uid = $_userid;
		}else{
			$uid = $userid;
		}

		if($uid ==null){
			$this->empty_userid();
		}
		if($this->check_uid_status($id,$uid,2)||$this->check_uid_status($id,$uid,1)){	
			$result = $this->order_db->update(array('status'=>6),array('id'=>$id));
			if($result){
				$info = $this->order_db->get_one(array('id'=>$id));
				$score = $info['scoremoney'];
				$data=[
					'userid'=>$uid,
					'scoremoney'=>$score,
					'describe'=>'积分返回',
					'module'=>'zyorder'
			 ];
				$url = APP_PATH."index.php?m=zymember&c=zymemberApi&a=pub_addscoremoney&userid=$uid&scoremoney=$score&describe=积分返回&module=zyorder";
				$return = json_decode($this->_crul_get($url,$data),true);
				if ( $return['code']=='200' ) {
					$this->caozuo_success("取消成功");
				}			
			}else{
				$this->caozuo_fail();
			}
		}else{	
			$this->error_check_uid_status();
		}
	}

    //删除订单
	public function order_delete(){	
		$id = $_POST['id'];
		//$_userid = $_POST['userid'];

		$_userid = param::get_cookie('_userid');
		$userid = $_POST['userid'];

		if($_userid){
			$uid = $_userid;
		}else{
			$uid = $userid;
		}
		if($uid==null){
			$this->empty_userid();
		}
		if($this->check_uid_status($id,$uid,5)){	
			$result = $this->order_db->delete(array('id'=>$id));
			if($result){
				$this->caozuo_success("删除成功");
			}else{
				$this->caozuo_fail();
			}
		}else{	
			 $this->error_check_uid_status();
		}
	}
  
 
	/**
	* 订单中心_支付
	*/
	// public function order_pay(){
	// 	$id = $_POST['id'];
	// 	$_userid = $_POST['userid'];
	// 	$password = $_POST['password'];
	// 	$totalprice = $_POST['totalprice'];

	// 	if($_userid==null){
	// 		$this->empty_userid();
	// 	}
	// 	$data=[
	// 	   "userid"=>$_userid,
	// 	   "pay_password"=>$password
	// 	];
	// 	$url = APP_PATH.'index.php?m=zymember&c=zymemberApi&a=zyorder_offpaypas';
	// 	$return = json_decode($this->_crul_post($url,$data),true);
		
	// 	if($return['code']=='200'){
	// 	  if($this->check_uid_status($id,$_userid,1)){
	// 	    $order = $this->order_db->get_one(array('id'=>$id));
	// 		if($order['totalprice']!=$totalprice){
	// 			$this->caozuo_fail("totalprice !=");
	// 		}
	// 		$paytime = time();
	// 		$result =  $this->order_db->update(array('status'=>2,'paytime'=>$paytime),array('id'=>$id));
	// 		if($result){
	// 			$this->caozuo_success("支付成功");
	// 		}
	// 		else{
	// 			$this->caozuo_fail();
	// 		}
	// 	}else{
	// 		$this->error_check_uid();
	// 	}
	// 	}else{
	// 		exit(json_encode($return));
	// 	}
		
		
	// }

	/**
	* 订单管理_快递鸟ajax传值
	*/
	public function kuaidi_ajx() {
		$id = $_POST['id'];
		$_userid = $_POST['userid'];
		if($this->check_uid_status($id,$_userid,3)){
			$KdApi = pc_base::load_app_class('KdApiSearch');
			$KdApi = new KdApiSearch();
			$order = $this->order_db->get_one(array('id'=>$id,'userid'=>$_userid));
			$logisticResult=$KdApi->getOrderTracesByJson($order['shipper_code'],$order['logistics_order']);
			$logisticResult = json_decode($logisticResult);
			echo  $logisticResult;
		}
	}

	public function kuaidi_ajx2(){
		$shipper_code = $_POST['shipper_code'];
		$logistics_order = $_POST['logistics_order'];
		$KdApi = pc_base::load_app_class('KdApiSearch');
		$KdApi = new KdApiSearch();
		$logisticResult=$KdApi->getOrderTracesByJson($shipper_code,$logistics_order);
		return $logisticResult;
	}


	//确认收货
	public function order_qrsh(){
		$id = $_POST['id'];
		$_userid = $_POST['userid'];
		if($_userid == null){
			$this->empty_userid();
		}
		if($this->check_uid_status($id,$_userid,3)){
			$result = $this->order_db->update(array('status'=>4),array('id'=>$id));
			if($result){
				$this->caozuo_success("确认收货");
			}else{
			    $this->caozuo_fail();
			}
		}else{
			 $this->error_check_uid_status();
		}
	}
	
	
	//评价
	public function evaluate(){
		$_userid = $_POST['userid'];
		$id = $_POST['id'];
		$evalute_arr = $_POST['evalute_arr'];
		if($_userid==null){
			 $this->empty_userid();
		}
		if($this->check_uid_status($id,$_userid,4)){
		    foreach($evalute_arr as $val){
				$shopid = $val['shopid'];
				$img = $val['img'];
		     	$content = $val['content'];
		        $star = [];
				$setarr = [];
				$evaluate_set = $this->evaluate_set_db->select(1);
				foreach($evaluate_set as $v){
				    array_push($setarr,$v['value']);
			    }

			    foreach($_POST as $k=>$v){
				   if(in_array($k,$setarr)){
				    	$star[$k] = $v;
				   }
			    }
				//$result = $this->uploadimg($_FILES,$_userid);
				$data =[
				  'orderid'=>$id,
				  'shopid'=>$shopid,
				  'content'=>$content,
				  'star'=>$star,
				  'userid'=>$_userid,
				  'img'=>$img,
				  'addtime'=>time()
			     ];
			$evaluateid = $this->evaluate_db->insert($data,true);
			$result = $this->order_db->update(array('status'=>5),array('id'=>$_POST['id']));
			if($result){
			    $this->caozuo_success($result);
			}else{
				$this->caozuo_fail();
			}
	        } 
		}else{
			$this->error_check_uid_status();
		}
	}
	
	
	//申请退款
	function apply_tk(){
		$_userid = $_POST['userid'];
		$orderid = $_POST['id'];
		$tk_reason = $_POST['tk_reason'];
		$tk_explain = $_POST['tk_explain'];
		if($_userid==null){
			$this->empty_userid();
		}
		if($this->check_uid($orderid,$_userid)){
		$order = $this->order_db->get_one(array('id'=>$orderid));

		if($order['status']>2){
		   $result = $this->order_db->update(array('status'=>7,'prestatus'=>$order['status'],'tk_reason'=>$tk_reason,'tk_explain'=>$tk_explain,'shstatus'=>4),array('id'=>$orderid));
		   if($result){
		      $this->caozuo_success("退款成功");
		   }else{
		      $this->caozuo_fail("退款失败");
	       }
		}
		}else{
		   $this->error_check_uid();
		}
	}
	
	/**
     *订单余额支付
     */
	public function prepay_for_balance (){
		//dump($_POST,true);
		$_userid = param::get_cookie('_userid');
		$userid = $_POST['uid'];
		$oids = $_POST['oids'];
		$pass = $_POST['paycode'];


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

		if (!$oids || !$pass) {
			$result = [
				'status' => 'error',
				'code' => -1,
				'message' => '访问受限，缺少必要参数',
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE));
		}
		$oid = '';
		foreach ($oids as $key => $value) {
			if ( isset($oid) ) {
				$oid .= ','.$value;
			} else {
				$oid = $value;
			}
		}

		$where = ' id in ('.$oid.') and status = 1 and userid = '.$uid;
		$count = $this->order_db->count($where);
		if ( $count != count($oids) ) {
			$result = [
				'status' => 'error',
				'code' => -2,
				'message' => '非法访问',
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE));	
		}
		$data=[
		   "userid"=>$uid,
		   "pay_password"=>$pass
		];
		$url = APP_PATH.'index.php?m=zymember&c=zymember_api&a=zyorder_offpaypas';	//yyy
		$return = json_decode($this->_crul_post($url,$data),true);

		if($return['code']=='200'){
			//$where = ' id in ('.$oid.') and userid = '.$uid;
			$sql = ' SELECT SUM(totalprice) as tprcie from zy_order where '.$where;
			$rs = $this->order_db->query($sql);
			$res = $this->order_db->fetch_array($rs);
			$tprice = $res[0]['tprcie'];
			$data=[
			   'userid'=>$uid,
			   'amount'=>$tprice,
			   'describe'=>'余额支付',
			   'module'=>'zyorder'
			];
			$url = APP_PATH."index.php?m=zymember&c=zymemberApi&a=pub_reduceamount&userid=$uid&amount=$tprice&describe=余额支付&module=zyorder";
			$return = json_decode($this->_crul_get($url,$data),true);

			if ( $return['code']=='200' ) {
				$result =  $this->order_db->update(array('status'=>2),$where);
				$result = [
					'status' => 'success',
					'code' => 1,
					'message' => 'OK',
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE));
			} else {
				$result = [
					'status' => 'error',
					'code' => -5,
					'message' => $return['message'],
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE));	
			} 

			
		}else{
			if($return['code']=='-3'){
				$code = -3;
			}else{
				$code = -4;
			}
			$result = [
				'status' => 'error',
				'code' => $code,
				'message' => $return['message'],
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE));	
		}

	}


	/**
	* 添加订单
	*/
	public function addorder(){
		$_userid = $_POST['userid'];
		if($_userid==null){
			$result = [
				'status' => 'error',
				'code' => -1,
				'message' => '访问受限，缺少参数',
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE));
		}

		//$shopid = $_POST['shopid'];  //店铺id
		$ordersn = time() + mt_rand(100,999);
		//$buycarid = $_POST['buycarid']; //购物车id
		// $province = $_POST['province'];  //收货地址省 
		// $city = $_POST['city'];//收货地址市
		// $area = $_POST['area'];//收货地址区
		// $address = $_POST['address']; //详细地址
		// $lx_mobile = $_POST['lx_mobile']; //联系电话
		// $lx_name = $_POST['lx_name']; //联系人
		// $lx_code = $_POST['lx_code']; //联系邮编
		// $shopdata = $_POST['shopdata'];  //商品数据
		//$usernote = $_POST['usernote']; //备注
		$addtime = time();//生成下单时间
		$data = [
			"userid"=>$_userid,
			//"shopid"=>$shopid,
			//"ordersn"=>$ordersn,
			//"buycarid"=>$buycarid,
			"province"=>$_POST['province'],  //收货地址省 
			"city"=>$_POST['city'],//收货地址市
			"area"=>$_POST['area'],//收货地址区
			"status"=>1,
			"address"=>$_POST['address'], //详细地址
			"lx_mobile"=>$_POST['lx_mobile'], //联系电话
			"lx_name"=>$_POST['lx_name'], //联系人
			"lx_code"=>$_POST['lx_code'], //联系邮编
			//"totalprice"=>$totalprice,
			"usernote"=>$_POST['usernote'],
			"addtime"=>$addtime,
		];

		foreach($data as $k => $v){
			if(/*$v==null*/empty($v) && $k != "usernote"){
				// $re = ['code'=>0];
				// echo json_encode($re);
				// return ;
				$result = [
					'status' => 'error',
					'code' => -1,
					'message' => '访问受限，缺少参数',
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE)); 
			}

		}
		
		
		$idarr = [];
		foreach ($_POST['shopdata'] as $ks => $vs) {
			$newdata = $data;
			$newdata['storeid'] = $vs['shopid'];
			$newdata['ordersn'] = time() + mt_rand(100,999);
			$newdata['usernote'] = $vs['usernote'];
			$newdata['totalprice'] = $vs['stprice'];
			$newdata['scoreprice'] = $vs['scoreprice'];
			$id = $this->order_db->insert($newdata,true);

			$idarr[] = [
				'oid' => $id,
				'order_sn' => $newdata['ordersn']
			];
		
			$len = count($vs['cartinfo']);
    		$sql= "insert into phpcms_order_goods ( `order_id`, `goods_id`, `goods_name`, `goods_num`, `goods_img`, `goods_price`, `specid`, `specid_name`) values";
			foreach ($vs['cartinfo'] as $key => $val) {
				$sql.="(".$id.", ".$val['goodsid'].", '".$val['goodsname']."', '".$val['cartnum']."', '".$val['goodsimg']."', '".$val['goodsprice']."', '".$val['goodsspec']."', '".$val['goodsspecs']."'),";
			};
			$sql = substr($sql,0,strlen($sql)-1);
			$this->ordergoods_db->query($sql);
		}
		//减去用户积分
		$scoretotal = $_POST['scoretotal'];
		$member=$this->member_db->get_one(array('userid'=>$_userid));
		$userscore = $member['scoremoney'];
		$cutscore = $scoretotal - $userscore;
		if($cutscore>0){
			$data= array(
				'scoremoney'=>$cutscore,
			);
			$state = $this->member_db->update($data,array('userid'=>$_userid));
		}

		$result = [
			'status' => 'success',
			'code' => 1,
			'message' => 'OK',
			'data' => $idarr
		];
		exit(json_encode($result,JSON_UNESCAPED_UNICODE));

		//exit($this->show_success($result));
	}
	
	
	
	
	
	 /********************************************** /
	*        统一判断状态方法                     * /
	*/                                            /**/
  
    //订单与用户相关
	public function check_uid($id,$_userid){	
		if($_userid!=null){	
			$order = $this->order_db->get_one(array('id'=>$id));
			if($order['userid'] != $_userid){	
				return false;
			}
			return true;
		}
	}
	
	//订单与商户相关  
    public function check_storeid($id,$storeid){
		if($storeid!=null){
		   $order = $this->order_db->get_one(array('storeid'=>$storeid));
		   if($order['storeid'] != $storeid){
		     return false;
		   }
		   return true;
		}
	}	
	
    //订单与用户，订单状态相关  
	public function check_uid_status($id,$_userid,$status){	
		if($_userid!=null){	
			$order = $this->order_db->get_one(array('id'=>$id));
			if($order['status'] != $status||$order['userid']!= $_userid){
				return false;
			}
			return true;
		}	
	}        
		 
	//订单与商户，订单状态相关  
    public function check_storeid_status($id,$storeid,$status){
		if($storeid!=null){
			$order = $this->order_db->get_one(array('storeid'=>$storeid));
			if($order['status'] != $status || $order['storeid']!=$storeid){
			     return false;
			}
			return true;
		}
	}

	
	
	/********************************************** /
	*        统一输出格式                          * /
	*/                                            /**/

	
	//check_uid
	function error_check_uid(){
		$data = [
					"status"=>'error',
					"code"=>203,
					"message"=>'订单与用户不匹配',
					"data"=>''
		];
		exit( json_encode($data));
		
	}
	
	//check_uid_status
    function error_check_uid_status(){
		$data = [
					"status"=>'error',
					"code"=>204,
					"message"=>'订单与商户，订单状态不匹配',
					"data"=>''
		];
		exit( json_encode($data));
	}
	//check_storeid
	function error_check_storeid(){
		$data = [
					"status"=>'error',
					"code"=>205,
					"message"=>'订单与商户不匹配',
					"data"=>''
		];
		exit( json_encode($data));
	}
	//check_storeid_status
    function error_check_storeid_status(){
		$data = [
					"status"=>'error',
					"code"=>206,
					"message"=>'订单与商户，订单状态不匹配',
					"data"=>''
		];
		exit( json_encode($data));
	}
	
	
	function caozuo_success($e){
		$data = [
					"status"=>'success',
					"code"=>200,
					"message"=>'操作成功',
					"data"=>$e
		];
		exit( json_encode($data));
	}
	function caozuo_fail(){
		$data = [
					"status"=>'error',
					"code"=>201,
					"message"=>'操作失败',
					"data"=>''
		];
		exit( json_encode($data));
	}
	
	function empty_userid(){
		$data = [
					"status"=>'error',
					"code"=>207,
					"message"=>'用户id为空',
					"data"=>''
		];
		exit( json_encode($data));
	}
	function empty_storeid(){
		$data = [
					"status"=>'error',
					"code"=>208,
					"message"=>'商户id为空',
					"data"=>''
		];
		exit( json_encode($data));
	}
		
	
	
	
	
	

}
