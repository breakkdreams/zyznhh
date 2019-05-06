
<?php

defined('IN_PHPCMS') or exit('No permission resources.');
pc_base::load_app_func('global');
pc_base::load_app_class('foreground');

class index {
	function __construct() {
		
		
		
		$this->zy_baike = pc_base::load_model('zybaike_model');
	}
	
	
	
	//发布文章
	public function fabu_add(){

	
		if($_POST['fabu']){
		
			$fabu=$_POST['fabu'];
			if(empty($fabu['title'])){
				showmessage('标题不能为空',HTTP_REFERER);
				die();
			}
			if(empty($fabu['content'])){
				showmessage('内容不能为空',HTTP_REFERER);
				die();
			}
			
			//$fabu=$_POST['fabu'];
			
			$result=$this->zy_baike->insert($fabu);
			
		//$_userid = param::get_cookie('_userid');
		//$_username = param::get_cookie('_username');
		//$memberinfo = $this->member_db->get_one(array('userid'=>$userid));
		
			if($result)
			{	
				showmessage(L('operation_success'),HTTP_REFERER);
			}else {
				showmessage(L("operation_failure"),HTTP_REFERER);
			}
			
			
			
		}else{
			showmessage(L("operation_failure"),HTTP_REFERER);
		 }
		
	}
	
	
}
?>