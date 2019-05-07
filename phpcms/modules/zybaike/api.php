<?php

defined('IN_PHPCMS') or exit('No permission resources.');
	
class api{
	function __construct() {

		$this->get_db = pc_base::load_model('get_model');
		$this->baike_db = pc_base::load_model('zybaike_model');
		$this->pinglun_db = pc_base::load_model('zypinglun_model');//百科
		$this->haoyou_db = pc_base::load_model('zyhaoyou_model');
	}
	/*									
	* 百科文章列表
	* @return [json] [json数组]
	*/
	public function baike_list()
	{

		$zybaike = $this->baike_db->listinfo(array());
	
//var_dump($zybaike);
		//==================	加载百科表数据 
		//$this->zy_baike->listinfo($data);
	
			$result = [
				'status'=>'success',
				'code'=>200,
				'message'=>'操作成功',
				'data'=>
				[ 
					'zybaike'=>$zybaike,
				]
				
			];
		
			exit(json_encode($result ,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
		//==================	加载百科表数据 
	}



		
		/*
		*百科文章内容
		*/
		public function baike_content(){
		
		$id = $_POST['id'];
			//获取对应文章id
		$wzid = $this->baike_db->get_one(array('id'=>$id));
		if(!$wzid){
		  $result = [
			'status'=>'error',
			'code'=>-1,
			'message'=>'该文章不存在',
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
		}
			//记录文章浏览量
			$num = $wzid['browse-number']+1;
			$this->baike_db->update(array('browse-number'=>$num),array('id'=>$id));
			$result = [
				'status'=>'success',
				'code'=>200,
				'message'=>'操作成功',
				'data'=>[
					'userid'=>$wzid['userid'],
					'username'=>$wzid['username'],
					'userhead'=>$wzid['userhead'],
					'title'=>$wzid['title'],
					'time'=>$wzid['time'],
					'content'=>$wzid['content'],
					'image'=>$wzid['image'],
					'browse-number'=>$wzid['browse-number'],
				]
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));	
		
	}
	 
	 /**
	  *我要发布
	  */

		public function baike_fabu(){
		$userid = $_POST['userid'];

		if(!$userid){
			$result = [
					'status'=>'error',
					'code'=>-1,
					'message'=>'未登入',
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
		}
		if($userid){
			$info = array(
				'userid'=>$_POST['userid'],
				'username'=>$_POST['username'],
				'userhead'=>$_POST['userhead'],
				'title'=>$_POST['title'],
				'content'=>$_POST['content'],
				'image'=>$_POST['image'],
				'category'=>$_POST['category'],
			);	
			$this->baike_db->insert($info);
			$result = [
			'status'=>'success',
			'code'=>200,
			'message'=>'发布完成',
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));	
		}	
			
		}
	
	/*
	*发评论
	*/
	public function baike_fapinglun(){
		
		$userid = $_POST['userid'];//用户id
		
		if(!$userid){
			$result = [
					'status'=>'error',
					'code'=>-1,
					'message'=>'需用户id',
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
		}
		
		if($userid){
			$articleid=$_POST['articleid'];
			$type=$articleid+'.1';
			
			//
			$info = array(
				
				'userid'=>$_POST['userid'],//用户id
				'username'=>$_POST['username'],//用户名
				'userhead'=>$_POST['userhead'],//用户头像
				'articleid'=>$_POST['articleid'],//文章id
				'content'=>$_POST['content'],
				'type'=>$type,
				'pid'=>$_POST['pid'], //父级评论id
			);	
			$this->pinglun_db->insert($info);
		
			//通过文章id查询评论对应的文章评论量字段进行增加
			$id = $info['articleid'];
			$wzid = $this->baike_db->get_one(array('id'=>$id));
			$num = $wzid['comment-number']+1;//baike表中的评论量加1
			$this->baike_db->update(array('comment-number'=>$num),array('id'=>$id));
			
			
			
			$result = [
			'status'=>'success',
			'code'=>200,
			'message'=>'评论完成',
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));	
		}	
	}
	/*
	评论列表
	*/
	public function baike_pinglunlist(){
		// $_POST['articleid']
		$id =15;
		$pinglun = $this->pinglun_db->listinfo(array('articleid'=>$id));
		//$pinglunquan = '';
		for($i=0;$i<count($pinglun);$i++){
			$pinglun[$i]['fu'] = $this->pinglun_db->listinfo(array('pid'=>$pinglun[$i]['id']));
		} 
		//$huifu = $this->pinglun_db->listinfo(array('pid'=>$pinglun['id']));
		if(!$pinglun){
			$result = [
				'status'=>'error',
				'code'=>-1,
				'message'=>'未找到该文章',
				
			];
		
			exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
		};
		$result = [
				'status'=>'success',
				'code'=>200,
				'message'=>'评论获取成功',
				'data'=>[
		
					'pinglun'=>$pinglun,
				
				]
			];
		
			exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));	
		
		
		
		
	}
	/*
	*文章点赞
	*/
	public function baike_zan(){
		$id = $_POST['id'];//文章id
		//$this->baike_db->insert($views = $r['views'] + 1;);fabulous-number/browse-number
		$wz = $this->baike_db->get_one(array('id'=> $_POST['id']));
		
		$zan = $wz['fabulous-number'] + 1;//每浏览一次，点击量+1
		$this->baike_db->update(array('fabulous-number'=>$zan),array('id'=>$id));
			
		$result = [
				'status'=>'success',
				'code'=>200,
				'message'=>'已为该文章点赞',
			
			];
		
			exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));	
		
	}
	
	/*
	*评论点赞
	*/
	public function baike_praise(){
		$id=$_POST['id'];
	
			$pl = $this->pinglun_db->get_one(array('id'=>$id));
			$zan =$pl['praise']+1;
			$this->pinglun_db->update(array('praise'=>$zan),array('id'=>$id));
			$result = [
				'status'=>'success',
				'code'=>200,
				'message'=>'已为该评论点赞',
			
			];
		exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));	
	
	}
	
	/*
	*文章举报
	*/
	public function baike_report(){
		$reportid=$_POST['id'];
		$content=$_POST['content'];
		
		$info = array(
				
				'userid'=>$_POST['userid'],//用户id
				'username'=>$_POST['username'],//用户名
				'userhead'=>$_POST['userhead'],//用户头像
				'reportid'=>$_POST['id'],//文章id
				'content'=>$_POST['content'],
				'type'=>'举报',
			
			);	
			$this->pinglun_db->insert($info);
		
			$pl = $this->baike_db->get_one(array('id'=>$reportid));
			$num =$pl['report-number']+1;
			$this->baike_db->update(array('report-number'=>$num),array('id'=>$reportid));
			$result = [
				'status'=>'success',
				'code'=>200,
				'message'=>'已举报该文章',
			
			];
		exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));	
	
	}
	
	
	/*
	*关注
	*/
	public function baike_guanzhu(){
		$id=$_POST['id'];
		
			$in=$this->baike_db->get_one(array('userid'=>$id));
			$info = array(
			'userid'=>$_POST['userid'],
			'username'=>$_POST['username'],
			'userhead'=>$_POST['userhead'],
			'friendid'=>$id,
			'friendname'=>$in['username'],
			'friendhead'=>$in['userhead'],
			);
		$guanzhu=$this->haoyou_db->get_one(array('userid'=>$info['userid'],'friendid'=>$info['friendid']));
	if(!$guanzhu){
			$this->haoyou_db->insert($info);
			$result = [
				'status'=>'success',
				'code'=>200,
				'message'=>'关注成功',
			];
		exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));	
	}
	
	if($guanzhu){
		$this->haoyou_db->delete(array('userid'=>$info['userid'],'friendid'=>$info['friendid']));
		$result = [
				'status'=>'error',
				'code'=>-1,
				'message'=>'取消关注',
			];
		exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));	
	}
		
		
	}
	
	
	/*
	*好友列表
	*/
	public function baike_haoyou(){
		$userid=1;//$_POST['userid'];
		$haoyou = $this->haoyou_db->listinfo(array('userid'=>$userid));
			for($i=1;$i<count($haoyou);$i++){
		$haoyoulist[$i] =array('friendid'=>$haoyou[$i]['friendid'],'friendname'=>$haoyou[$i]['friendname'],'friendhead'=>$haoyou[$i]['friendhead']);
		}
		$result = [
				'status'=>'success',
				'code'=>200,
				'message'=>'获取好友列表',
				'data'=>[
		
					'haoyoulist'=>$haoyoulist,
				
				]
			];
	
		
		exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));	
	
	
	
	
	}
	
	
	
	
}
	?>
