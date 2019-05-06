<?php

defined('IN_PHPCMS') or exit('No permission resources.');
	
class api{
	function __construct() {

		$this->get_db = pc_base::load_model('get_model');
		$this->baike_db = pc_base::load_model('zybaike_model');//百科
		$this->member_db = pc_base::load_model('member_model');
		
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
		//$_GET['id']='1';
		$id = $_GET['id'];
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
		$memberinfo = $this->member_db->get_one(array('userid'=>$userid));
		if($userid){
			$info = array(
				'userid'=>$userid,
				'username'=>$memberinfo['nickname'],
				'userhead'=>$memberinfo['headimgurl'],
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
	
	
	
	
}
	?>
