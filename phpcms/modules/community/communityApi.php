<?php
declare(strict_types=1);
defined('IN_PHPCMS') or exit('No permission resources.');
pc_base::load_app_class('admin', 'admin', 0);
pc_base::load_sys_class('format', '', 0);
pc_base::load_sys_class('form', '', 0);
pc_base::load_app_func('global');

class communityApi {
	function __construct() {
		$this->get_db = pc_base::load_model('get_model');
		$this->admin = pc_base::load_model('admin_model');
		$this->member_db = pc_base::load_model('member_model');
		$this->member_detail_db = pc_base::load_model('member_detail_model');
		$this->community_db = pc_base::load_model('community_model');
		$this->likes_db = pc_base::load_model('likes_model');
		$this->follow_db = pc_base::load_model('follow_model');
		$this->report_db = pc_base::load_model('report_model');
		$this->zycommnet_db = pc_base::load_model('zycomment_model');
		$this->zyconfig_db = pc_base::load_model('zyconfig_model');
		$this->module_db = pc_base::load_model('module_model');
		$this->config = $this->zyconfig_db->get_one('','url');
	}


	/*
	*社区列表
	*/
	public function community_list(){
		$page = $_POST['page'];
		$keyword = $_POST['keyword'];
		$userid = $_POST['userid'];

		$wheres = ' status = 0 ';
		if($keyword!=null){
			$wheres .=' and content like %'.$keyword.'% ';
		}
		if($userid!=null){
			$wheres .=' and userid = '.$userid;
		}
		$community = $this->community_db->listinfo($wheres,$order = '',$page, $pages = '10');
		for($i=0;$i<sizeof($community);$i++){
			$likes=$this->likes_db->get_one(['beuserid'=>$community[$i]['userid'],'userid'=>$userid,'articleid'=>$community[$i]['id']]);
			if($likes!=null){
				$community[$i]['islike'] = '1';
			}else{
				$community[$i]['islike'] = '0';
			}
		}

		$result = [
			'status'=>'success',
			'code'=>200,
			'message'=>'获取成功',
			'data'=>[
				'community'=>$community,//社区列表
			]
		];
		
		exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));		
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

		$member=$this->member_db->get_one(['userid'=>$userid]);

		$data_detail = [
			'catid'=>$catid,
			'userid'=>$userid,
			'userhead'=>$member['headimgurl'],
			'username'=>$member['nickname'],
			'content'=>$content,
			'img'=>$img,
			'addtime'=>$addtime,
			'log'=>$log,
			'lat'=>$lat,
		];
		
		$res = $this->community_db->insert($data_detail);

        $result = [
            'status'=>'success',
            'code'=>200,
            'message'=>'发布成功',
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
			$member=$this->member_db->get_one(['userid'=>$userid]);
			
			$info = [
				'userid'=>$userid,//用户id
				'username'=>$member['nickname'],//用户名
				'userhead'=>$member['headimgurl'],//用户头像
				'articleid'=>$articleid,//文章id
				'content'=>$content,
				'img'=>$img,
				'addtime'=>$addtime,
				'pid'=>$pid, //父级评论id,
			];
			$this->zycommnet_db->insert($info);
			
			//通过文章id查询评论对应的文章评论量字段进行增加
			$community = $this->community_db->get_one(['id'=>$articleid]);
			$num = $community['comment']+1;//表中的评论量加1
			$this->community_db->update(['comment'=>$num],['id'=>$articleid]);
			
			$result = [
				'status'=>'success',
				'code'=>200,
				'message'=>'评论成功',
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));	
		}	
	}

	/*
	评论列表和社区详情
	*/
	public function comment_list(){
		require('classes/PHPTree.class.php');//加载树形结构类
		$page = $_POST['page'];
		$articleid = $_POST['articleid'];//文章id
        $wheres = ' articleid = '.$articleid;
		$pinglun = $this->zycommnet_db->listinfo($wheres,$order = '',$page, $pages = '1000');

		$data = catetree($pinglun);

		$r=PHPTree::makeTree($data, [

		]);


		$community=$this->community_db->get_one(['id'=>$articleid]);

		$result = [
			'status'=>'success',
			'code'=>200,
			'message'=>'获取成功',
			'data'=>[
				'pinglun'=>$r,//评论列表
				'community'=>$community,//社区详情
			]
		];
		
		exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));		
	}

	/*
	*点赞
	*/
	public function community_likes(){
		$type = $_POST['type'];//1.文章点赞 2.评论点赞
		//用户id
		$userid = $_POST['userid'];
		if($userid==null){
			 $this->empty_userid();
		}
		$articleid = $_POST['articleid'];
		$beuserid = $_POST['beuserid'];
		$addtime = time();


		if ( $articleid == null || $beuserid == null ) {
            $result = [
                'status' => 'error',
                'code' => 0,
                'message' => '参数错误！',
            ];
            exit(json_encode($result,JSON_UNESCAPED_UNICODE));
		}
		
		if($userid){
			
			$info = [
				'userid'=>$userid,//点赞的用户
				'beuserid'=>$beuserid,//被点赞的用户
				'articleid'=>$articleid,//文章id
				'addtime'=>$addtime,
			];
			$this->likes_db->insert($info);

			if($type == 1){
				//通过文章id查询评论对应的文章评论量字段进行增加
				$community = $this->community_db->get_one(['id'=>$articleid,'userid'=>$beuserid]);
				if($community!=null){
					$num = $community['likes']+1;//表中的点赞量加1
					$this->community_db->update(['likes'=>$num],['id'=>$articleid]);
				}
			}else{
				//通过文章id查询评论对应的文章评论量字段进行增加
				$zycommnet = $this->zycommnet_db->get_one(['articleid'=>$articleid,'userid'=>$beuserid]);
				if($zycommnet!=null){
					$num = $zycommnet['likes']+1;//表中的点赞量加1
					$this->zycommnet_db->update(['likes'=>$num],['id'=>$zycommnet['id']]);
				}
			}
		
			
			
			$result = [
				'status'=>'success',
				'code'=>200,
				'message'=>'点赞成功',
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));	
		}	
	}

	/*
	*举报
	*/
	public function community_report(){
		//用户id
		$userid = $_POST['userid'];
		if($userid==null){
			 $this->empty_userid();
		}
		$articleid = $_POST['articleid'];
		$content = $_POST['content'];
		$status = 0;
		$score = 0;
		$addtime = time();


		if ( $articleid == null || $content == null ) {
            $result = [
                'status' => 'error',
                'code' => 0,
                'message' => '参数错误！',
            ];
            exit(json_encode($result,JSON_UNESCAPED_UNICODE));
		}
		
		if($userid){
			$info = [
				'userid'=>$userid,//用户id
				'content'=>$content,//举报内容
				'status'=>$status,//举报状态(1举报成功 0审核 -1恶意举报)
				'addtime'=>$addtime,
				'score'=>$score,
				'articleid'=>$articleid,
			];
			$this->report_db->insert($info);
					
			$result = [
				'status'=>'success',
				'code'=>200,
				'message'=>'举报成功,请等待审核',
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));	
		}	
	}

	/*
	*关注
	*/
	public function community_follow(){
		//用户id
		$userid = $_POST['userid'];
		if($userid==null){
			 $this->empty_userid();
		}
		$befollowid = $_POST['befollowid'];

		$addtime = time();


		if ( $befollowid == null ) {
            $result = [
                'status' => 'error',
                'code' => 0,
                'message' => '参数错误！',
            ];
            exit(json_encode($result,JSON_UNESCAPED_UNICODE));
		}
		
		if($userid){
			$info = [
				'userid'=>$userid,//用户id
				'befollowid'=>$befollowid,//被关注人id
				'addtime'=>$addtime,
			];
			$this->follow_db->insert($info);

			//判断对方是否关注,是=>成为好友
			$guanzhu=$this->follow_db->get_one(['userid'=>$befollowid,'befollowid'=>$userid]);
			if($guanzhu != null){
				//成为好友
			}
					
			$result = [
				'status'=>'success',
				'code'=>200,
				'message'=>'关注成功',
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));	
		}	
	}

	/*
	*取消关注
	*/
	public function community_delete_follow(){
		//用户id
		$userid = $_POST['userid'];
		if($userid==null){
			 $this->empty_userid();
		}
		$befollowid = $_POST['befollowid'];

		if ( $befollowid == null ) {
            $result = [
                'status' => 'error',
                'code' => 0,
                'message' => '参数错误！',
            ];
            exit(json_encode($result,JSON_UNESCAPED_UNICODE));
		}
		
		if($userid){
			$follow = $this->follow_db->get_one(['userid'=>$userid,'befollowid'=>$befollowid]);
			if($follow != null){
				$this->follow_db->delete(['id'=>$follow['id']]);
				//删除好友

			}
					
			$result = [
				'status'=>'success',
				'code'=>200,
				'message'=>'取消关注成功',
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));	
		}	
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












