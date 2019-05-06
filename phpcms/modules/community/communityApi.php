<?php
defined('IN_PHPCMS') or exit('No permission resources.');
pc_base::load_app_class('admin', 'admin', 0);
pc_base::load_sys_class('format', '', 0);
pc_base::load_sys_class('form', '', 0);
pc_base::load_app_func('global');

class community extends admin {
	function __construct() {
		parent::__construct();
		$this->get_db = pc_base::load_model('get_model');
		$this->admin = pc_base::load_model('admin_model');
		$this->member_db = pc_base::load_model('member_model');
		$this->member_detail_db = pc_base::load_model('member_detail_model');
		$this->community_db = pc_base::load_model('community_model');
		$this->zycommnet_db = pc_base::load_model('zycommnet_model');
		$this->zyconfig_db = pc_base::load_model('zyconfig_model');
		$this->module_db = pc_base::load_model('module_model');
		$this->config = $this->zyconfig_db->get_one('','url');
	}

 	/**
     * 发布动态
     */
    public function public_community(){
		//用户id
		$userid = $_POST['userid'];
		if($userid==null){
			 $this->empty_userid();
		}
		//分类id
		$catid = $_POST['catid'];
		//内容
		$content = $_POST['content'];
		//图片
		$img = $_POST['img'];
		//添加时间
		$addtime = time();
		//经度
		$log = $_POST['log'];
		//纬度
		$lat = $_POST['lat'];
		if($log == null){
			$log = 0;
		}
		if($lat == null){
			$lat = 0;
		}

        if ( $catid == null || $content == null || $img == null  ) {
            $result = [
                'status' => 'error',
                'code' => 0,
                'message' => '参数错误！',
            ];
            exit(json_encode($result,JSON_UNESCAPED_UNICODE));
		}

		$member=$this->member_db->get_one(array('userid'=>$userid));

		$data_detail = array(
			'catid'=>$catid,
			'userid'=>$userid,
			'userhead'=>$member['headimgurl'],
			'username'=>$member['nickname'],
			'content'=>$content,
			'img'=>$img,
			'addtime'=>$addtime,
			'catid'=>$catid,
			'log'=>$log,
			'lat'=>$lat,
		);
		
		$this->community_db->insert($data_detail);

        $result = [
            'status'=>'success',
            'code'=>200,
            'message'=>'成功',
        ];
        exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
	}

	/*
	*发评论
	*/
	public function community_comment(){
		//用户id
		$userid = $_POST['userid'];
		if($userid==null){
			 $this->empty_userid();
		}

		$articleid = $_POST['articleid'];
		$content = $_POST['content'];
		$pid = $_POST['pid'];
		$addtime = time();
		$img = $_POST['img'];

		if ( $articleid == null || $content == null || $pid == null  ) {
            $result = [
                'status' => 'error',
                'code' => 0,
                'message' => '参数错误！',
            ];
            exit(json_encode($result,JSON_UNESCAPED_UNICODE));
		}
		
		if($userid){
			$member=$this->member_db->get_one(array('userid'=>$userid));
			
			$info = array(
				'userid'=>$userid,//用户id
				'username'=>$member['nickname'],//用户名
				'userhead'=>$member['headimgurl'],//用户头像
				'articleid'=>$articleid,//文章id
				'content'=>$content,
				'img'=>$img,
				'addtime'=>$addtime,
				'pid'=>$pid, //父级评论id
			);	
			$this->zycommnet_db->insert($info);
		
			//通过文章id查询评论对应的文章评论量字段进行增加
			$community = $this->community_db->get_one(array('id'=>$articleid));
			$num = $community['comment']+1;//baike表中的评论量加1
			$this->community_db->update(array('comment'=>$num,'id'=>$articleid));
			
			$result = [
				'status'=>'success',
				'code'=>200,
				'message'=>'评论完成',
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));	
		}	
	}

	/*
	评论列表和社区详情
	*/
	public function comment_list(){
		$page = $_POST['page'];
		$articleid = $_POST['articleid'];//文章id
        $wheres = ' articleid = '.$articleid;
		$pinglun = $this->zycommnet_db->listinfo($wheres,$page, $pages = '10');
		
		for($i=0;$i<count($pinglun);$i++){
			$where = ' pid = '.$pinglun[$i]['id'];
			$pinglun[$i]['fu'] = $this->zycommnet_db->listinfo($where,$order = '',$page, $pages = '10');
		} 

		if(!$pinglun){
			$result = [
				'status'=>'error',
				'code'=>-1,
				'message'=>'未找到该文章',
				
			];
		
			exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
		};

		$community=$this->community_db->get_one(array('id'=>$articleid));

		$result = [
			'status'=>'success',
			'code'=>200,
			'message'=>'获取成功',
			'data'=>[
				'pinglun'=>$pinglun,
				'community'=>$community
			]
		];
		
		exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));		
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

	

}
?>












