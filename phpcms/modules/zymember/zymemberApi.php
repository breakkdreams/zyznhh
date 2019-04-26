<?php
defined('IN_PHPCMS') or exit('No permission resources.');
pc_base::load_app_func('global');

class zymemberApi{
	function __construct() {

		$this->get_db = pc_base::load_model('get_model');
		$this->member_db = pc_base::load_model('member_model');
		$this->members_db = pc_base::load_model('member_model');
		$this->member_detail_db = pc_base::load_model('member_detail_model');
		$this->sso_members_db = pc_base::load_model('sso_members_model');
		//会员组表
		$this->member_group_db = pc_base::load_model('member_group_model');
		//配置模块表
		$this->zyconfig_db = pc_base::load_model('zyconfig_model');
		$this->module_db = pc_base::load_model('module_model');
		$this->member_collect_db = pc_base::load_model('member_collect_model');
		$this->member_footprint_db = pc_base::load_model('member_footprint_model');
	}
 
	/**
	* 会员注册协议
	* @return [json] [json数组]
	*/
	public function registration_agreement()
	{
        $member_setting = $this->module_db->get_one(array('module'=>'member'), 'setting');
		$member_setting = string2array($member_setting['setting']);

		//==================	操作成功-更新数据 START
			$result = [
				'status'=>'success',
				'code'=>200,
				'message'=>'操作成功',
				'data'=>[
					'regprotocol'=>$member_setting['regprotocol']
				]
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
		//==================	操作成功-更新数据 END
	}

	/**
	 * 注册_手机短信注册
	 * @status [状态] -1帐号、密码、验证码不能为空/-2用户名格式错误/-3密码格式错误/-4验证码错误/-5帐号已存在
	 * @param  [type] $mobile [*用户帐号]
	 * @param  [type] $verify_code [*手机验证码]
	 * @param  [type] $password [*用户密码]
	 * @param  [type] $type [*类型：1web端、2APP端]
	 * @param  [type] $forward [接下来该跳转的页面链接]
	 * @return [json] [json数组]
	 */
	public function sms_registered()
	{
		$mobile = $_POST['mobile'];	//手机号
		$verify_code = $_POST['verify_code'];	//短信验证码
		$password = $_POST['password'];	//密码
		$type = $_POST['type'];	//类型：1web端、2APP端
		$forward = $_POST['forward'] ? urldecode($_POST['forward']) : APP_PATH.'index.php?m=member&c=index';	//接下来该跳转的页面链接

		//用手机号码查出用户账号
		$memberinfo = $this->member_db->get_one(array('mobile'=>$mobile));

		//==================	操作失败-验证 START
		//帐号密码类型不能为空
		if (!$mobile || !$verify_code || !$password) {
			$result = [
				'status'=>'error',
				'code'=>-1,
				'message'=>'帐号、验证码、密码不能为空',

			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
		}
		//用户名格式验证（手机号码格式验证）
		if (!$this->_verify_ismobile($mobile)) {
			$result = [
				'status'=>'error',
				'code'=>-2,
				'message'=>'用户名格式错误',	//只允许 13，14，15，16，17，18，19的号码,11位

			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
		}
		//密码格式错误
		if (!$this->_verify_ispassword($password)) {
			$result = [
				'status'=>'error',
				'code'=>-3,
				'message'=>'密码格式错误',	//6-16位之间,只允许数字、大小写英文、下划线

			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
		}
		//短信验证码错误
		//调用通讯模块-短信接口-查询此账号的短信验证码是否匹配上了
		//$sms_verify = true;
		//==================	获取其他接口-接口 START
		$config = $this->zyconfig_db->get_one(array('key'=>'zymessagesys4'),"url");
		$curl = [
			'mobile'=>$mobile,
			'verify_code'=>$verify_code,
			'clear'=>1,
		];
		$sms_verify = _crul_post($config['url'],$curl);
		$sms_verify=json_decode($sms_verify,true);
		//==================	获取其他接口-接口 END


		if($sms_verify['status']=='error') {	//false,进入
			$result = [
				'status'=>'error',
				'code'=>-4,
				'message'=>$sms_verify['message'],	//短信验证码错误

			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
		}
		//帐号已存在
		if ($memberinfo) {
			$result = [
				'status'=>'error',
				'code'=>-5,
				'message'=>'帐号已存在',	//帐号已存在

			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
		}
		//==================	操作失败-验证 END

		//==================	操作成功-插入数据 START

		//获取会员基本设置的配置
		$member_setting = $this->module_db->get_one(array('module'=>'member'), 'setting');
		$member_setting = string2array($member_setting['setting']);

		$userinfo = array();
		//用户基本信息
		$userinfo['username'] = create_randomstr(8);
		$userinfo['password'] = $password;
		$userinfo['encrypt'] = create_randomstr(6);
		$userinfo['nickname'] = $mobile;
		$userinfo['regdate'] = time();
		$userinfo['regip'] = ip();
		$userinfo['email'] = time().'@300c.cn';
		$userinfo['groupid'] = 2;
		$userinfo['amount'] = 0;
		$userinfo['point'] = $member_setting['defualtpoint'];
		$userinfo['modelid'] = 10;
		//$userinfo['islock'] = $_POST['info']['islock']==1 ? 0 : 1;
		//$userinfo['vip'] = $_POST['info']['vip']==1 ? 1 : 0;
		//$userinfo['overduedate'] = strtotime($_POST['info']['overduedate']);
		$userinfo['mobile'] = $mobile;


		//传入phpsso为明文密码，加密后存入phpcms_v9
		$password = $userinfo['password'];
		$userinfo['password'] = password($userinfo['password'], $userinfo['encrypt']);


		//主表
		$userid=$this->member_db->insert($userinfo,true);
		$this->member_db->update(array('phpssouid'=>$userid),'userid='.$userid);

		//sso表
		$sso_members_db = pc_base::load_model('sso_members_model');
		$data_member_sso = array(
			'username'=>$userinfo['username'],
			'password'=>$userinfo['password'],
			'random'=>$userinfo['encrypt'],
			'email'=>$userinfo['email'],
			'regdate'=>$userinfo['regdate'],
			'lastdate'=>$userinfo['regdate'],
			'regip'=>$userinfo['regip'],
			'lastip'=>$userinfo['lastip'],
			'appname'=>'phpcmsv9',
			'type'=>'app',
		);
		$sso_members_db->insert($data_member_sso);

		//附表
		$data_member_detail = array(
			'userid'=>$userid,
		);
		$this->member_detail_db->insert($data_member_detail);

		$memberinfo = $this->member_db->get_one(array('mobile'=>$mobile));
		//如果是网页的话，要存缓存。如果是APP的话，我就直接传值就行了
		if ($type==1) {
			$cookietime = SYS_TIME + 7200;	//系统时间+两个小时
			$phpcms_auth = sys_auth($memberinfo['userid']."\t".$memberinfo['password'], 'ENCODE', get_auth_key('login'));
			param::set_cookie('auth', $phpcms_auth, $cookietime);
			param::set_cookie('_userid', $memberinfo['userid'], $cookietime);
			param::set_cookie('_username', $memberinfo['username'], $cookietime);
			param::set_cookie('_nickname', $memberinfo['nickname'], $cookietime);
			param::set_cookie('_groupid', $memberinfo['groupid'], $cookietime);
			param::set_cookie('cookietime', $_cookietime, $cookietime);
		}

		//调用通讯模块-短信接口-清空此账号的短信验证码
		//操作成功之后删除遗留的短信验证码
		//==================	获取其他接口-接口 START
		$config = $this->zyconfig_db->get_one(array('key'=>'zymessagesys5'),"url");
		$curl = [
			'mobile'=>$memberinfo['mobile']
		];
		_crul_post($config['url'],$curl);
		//==================	获取其他接口-接口 END


		$result = [
			'status'=>'success',
			'code'=>200,
			'message'=>'注册成功',
			'data'=>[
				'userid'=>$memberinfo['userid'],
				'groupid'=>$memberinfo['groupid'],
				'forward'=>$forward,	//给web端用的，接下来跳转到哪里
			]
		];
		exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
		//==================	操作成功-插入数据 END

	}

	/**
	* 登录_帐号密码登录
	* @status [状态] -1帐号、密码不能为空/-2用户名格式错误/-3密码格式错误/-4帐号不存在/-5密码错误/-6帐号已锁定
	* @param  [type] $mobile [*用户帐号]
	* @param  [type] $password [*用户密码]
	* @param  [type] $type [*类型：1web端、2APP端]
	* @param  [type] $forward [接下来该跳转的页面链接]
	* @return [json] [json数组]
	*/
	public function account_login($mobile,$password,$type,$forward)
	{
		$mobile = $_POST['mobile'];	//帐号（暂定为手机号）
		$password = $_POST['password'];	//密码
		$type = $_POST['type'] ? $_POST['type'] : 1;	//类型：1web端、2APP端
		$forward = $_POST['forward'] ? urldecode($_POST['forward']) : APP_PATH.'index.php?m=member&c=index';	//接下来该跳转的页面链接
		
		//用手机号码查出用户账号
		$memberinfo = $this->member_db->get_one(array('mobile'=>$mobile));
		//==================	操作失败-验证 START
			//帐号密码类型不能为空
			if (!$mobile || !$password) {
				$result = [
					'status'=>'error',
					'code'=>-1,
					'message'=>'帐号、密码不能为空',
					
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}
			//用户名格式验证（手机号码格式验证）
			if (!$this->_verify_ismobile($mobile)) {
				$result = [
					'status'=>'error',
					'code'=>-2,
					'message'=>'用户名格式错误',	//只允许 13，14，15，16，17，18，19的号码,11位
					
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}
			//密码格式错误
			if (!$this->_verify_ispassword($password)) {
				$result = [
					'status'=>'error',
					'code'=>-3,
					'message'=>'密码格式错误',	//6-16位之间,只允许数字、大小写英文、下划线
					
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}
			//账号不存在
			if (!$memberinfo) {
				$result = [
					'status'=>'error',
					'code'=>-4,
					'message'=>'账号不存在',	//账号不存在
					
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}
			//密码错误
			if($memberinfo['password'] != password($password, $memberinfo['encrypt'])) {
				$result = [
					'status'=>'error',
					'code'=>-5,
					'message'=>'密码错误',	//密码错误
					
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}
			//帐号已锁定,无法登录
			if($memberinfo['islock']==1) {
				$result = [
					'status'=>'error',
					'code'=>-6,
					'message'=>'帐号已锁定,无法登录',	//帐号已锁定,无法登录
					
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}
		//==================	操作失败-验证 END

		//==================	操作成功-返回数据 START
			//更新会员数据
			$member_data = [
				'loginnum'=>'+=1',
				'lastip'=>ip(),
				'lastdate'=>time(),
			];
			$this->member_db->update($member_data,array('userid'=>$memberinfo['userid']));

			//如果是网页的话，要存缓存。如果是APP的话，我就直接传值就行了
			if ($type==1) {
				$cookietime = SYS_TIME + 7200;	//系统时间+两个小时
				$phpcms_auth = sys_auth($memberinfo['userid']."\t".$memberinfo['password'], 'ENCODE', get_auth_key('login'));
				param::set_cookie('auth', $phpcms_auth, $cookietime);
				param::set_cookie('_userid', $memberinfo['userid'], $cookietime);
				param::set_cookie('_username', $memberinfo['username'], $cookietime);
				param::set_cookie('_nickname', $memberinfo['nickname'], $cookietime);
				param::set_cookie('_groupid', $memberinfo['groupid'], $cookietime);
				param::set_cookie('cookietime', $_cookietime, $cookietime);
			}
			$result = [
				'status'=>'success',
				'code'=>200,
				'message'=>'登录成功',
				'data'=>[
					'userid'=>$memberinfo['userid'],
					'groupid'=>$memberinfo['groupid'],
					'forward'=>$forward,	//给web端用的，接下来跳转到哪里
				]
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
		//==================	操作成功-返回数据 END
	}


	/**
	* 登录_手机短信登录
	* @status [状态] -1帐号、验证码不能为空/-2用户名格式错误/-4帐号不存在/-5短信验证码错误/-6帐号已锁定
	* @param  [type] $mobile [*手机号码]
	* @param  [type] $verify_code [*手机验证码]
	* @param  [type] $type [*类型：1web端、2APP端]
	* @param  [type] $forward [接下来该跳转的页面链接]
	* @return [json] [json数组]
	*/
	public function sms_login($mobile,$verify_code,$type,$forward)
	{
		$mobile = $_POST['mobile'];	//手机号码
		$verify_code = $_POST['verify_code'];	//验证码
		$type = $_POST['type'] ? $_POST['type'] : 1;	//类型：1web端、2APP端
		$forward = $_POST['forward'] ? urldecode($_POST['forward']) : APP_PATH.'index.php?m=member&c=index';	//接下来该跳转的页面链接

		//用手机号码查出用户账号
		$memberinfo = $this->member_db->get_one(array('mobile'=>$mobile));
		//==================	操作失败-验证 START
			//帐号密码类型不能为空
			if (!$mobile || !$verify_code) {
				$result = [
					'status'=>'error',
					'code'=>-1,
					'message'=>'帐号、验证码不能为空',
					
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}
			//用户名格式验证（手机号码格式验证）
			if (!$this->_verify_ismobile($mobile)) {
				$result = [
					'status'=>'error',
					'code'=>-2,
					'message'=>'用户名格式错误',	//只允许 13，14，15，16，17，18，19的号码,11位
					
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}
			//账号不存在
			if (!$memberinfo) {
				$result = [
					'status'=>'error',
					'code'=>-4,
					'message'=>'账号不存在',	//账号不存在
					
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}
			//短信验证码错误
			//调用通讯模块-短信接口-查询此账号的短信验证码是否匹配上了
			//$sms_verify = true;
			//==================	获取其他接口-接口 START
				$config = $this->zyconfig_db->get_one(array('key'=>'zymessagesys4'),"url");
				$curl = [
					'mobile'=>$mobile,
					'verify_code'=>$verify_code,
					'clear'=>1,
				];
				$sms_verify = _crul_post($config['url'],$curl);
				$sms_verify=json_decode($sms_verify,true);
			//==================	获取其他接口-接口 END		


			if($sms_verify['status']=='error') {	//false,进入
				$result = [
					'status'=>'error',
					'code'=>-5,
					'message'=>$sms_verify['message'],	//短信验证码错误
					
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}
			//帐号已锁定,无法登录
			if($memberinfo['islock']==1) {
				$result = [
					'status'=>'error',
					'code'=>-6,
					'message'=>'帐号已锁定,无法登录',	//帐号已锁定,无法登录
					
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}
		//==================	操作失败-验证 END

		//==================	操作成功-返回数据 START
			//更新会员数据
			$member_data = [
				'loginnum'=>'+=1',
				'lastip'=>ip(),
				'lastdate'=>time(),
			];
			$this->member_db->update($member_data,array('userid'=>$memberinfo['userid']));

			//如果是网页的话，要存缓存。如果是APP的话，我就直接传值就行了
			if ($type==1) {
				$cookietime = SYS_TIME + 7200;	//系统时间+两个小时
				$phpcms_auth = sys_auth($memberinfo['userid']."\t".$memberinfo['password'], 'ENCODE', get_auth_key('login'));
				param::set_cookie('auth', $phpcms_auth, $cookietime);
				param::set_cookie('_userid', $memberinfo['userid'], $cookietime);
				param::set_cookie('_username', $memberinfo['username'], $cookietime);
				param::set_cookie('_nickname', $memberinfo['nickname'], $cookietime);
				param::set_cookie('_groupid', $memberinfo['groupid'], $cookietime);
				param::set_cookie('cookietime', $_cookietime, $cookietime);
			}
			
			//调用通讯模块-短信接口-清空此账号的短信验证码
			//操作成功之后删除遗留的短信验证码
			//==================	获取其他接口-接口 START
				$config = $this->zyconfig_db->get_one(array('key'=>'zymessagesys5'),"url");
				$curl = [
					'mobile'=>$memberinfo['mobile']
				];
				_crul_post($config['url'],$curl);
			//==================	获取其他接口-接口 END		


			$result = [
				'status'=>'success',
				'code'=>200,
				'message'=>'登录成功',
				'data'=>[
					'userid'=>$memberinfo['userid'],
					'groupid'=>$memberinfo['groupid'],
					'forward'=>$forward,	//给web端用的，接下来跳转到哪里
				]
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
		//==================	操作成功-返回数据 END

	}


	/**
	* 头像上传_ajax
	*/
	public function portrait_ajax() {
		$userid = $_POST['userid'];	//用户id
		$member=$this->member_db->get_one(array('userid'=>$userid));
		$str=stripslashes($_POST['imgurl1']);
		if($member){
			$data= array(
				'headimgurl'=>$str,
			);
			$state = $this->member_db->update($data,array('userid'=>$userid));

			$result = [
				'status'=>'success',
				'code'=>200,
				'message'=>'修改成功',
				'data'=>[
					'userid'=>$member['userid'],
				]
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
		}else{
			$result = [
					'status'=>'error',
					'code'=>'-101',
					'message'=>'未找到用户',
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
		}
		
	}

	/**
	* 个人资料_修改基本会员资料
	* @status [状态] -1用户id不能为空/-2修改数据不能为空/-3账号不存在/-4帐号已锁定,无法操作/-11用户昵称格式错误
	* @param  [type] $userid [*用户id]
	* @param  [type] $type [*类型：1web端、2APP端]
	* @param  [type] $forward [接下来该跳转的页面链接]
	* @param  [type] $nickname [用户昵称]
	* @param  [type] $sex [性别（男、女、保密）]
	* @param  [type] $shopname [店铺名称]
	* @return [json] [json数组]
	*/
	public function edit_memberdata()
	{
		$userid = $_POST['userid'];	//用户id
		$type = $_POST['type'] ? $_POST['type'] : 1;	//类型：1web端、2APP端
		$forward = $_POST['forward'] ? urldecode($_POST['forward']) : APP_PATH.'index.php?m=member&c=index';	//接下来该跳转的页面链接
		
		//如果要修改，则被修改；不然获取原来的数据
		//下面就是要更新的数据组
		$data = array();
		if ($_POST['nickname']) $data['nickname'] = $_POST['nickname'];
		if ($_POST['sex']) $data['sex'] = $_POST['sex'];
		if ($_POST['shopname']) $data['shopname'] = $_POST['shopname'];
		
		//用用户id查出用户账号
		$memberinfo = $this->member_db->get_one(array('userid'=>$userid));
		
		//==================	操作失败-验证 START
			//帐号密码类型不能为空
			if (!$userid) {
				$result = [
					'status'=>'error',
					'code'=>-1,
					'message'=>'用户id不能为空',
					
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}
			//帐号密码类型不能为空
			if (!$data) {
				$result = [
					'status'=>'error',
					'code'=>-2,
					'message'=>'修改数据不能为空',
					
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}
			//昵称的基本验证，判断不能为空
			if ($data['nickname']) {
				if(empty($data['nickname'])){
					$result = [
						'status'=>'error',
						'code'=>-11,
						'message'=>'用户昵称格式错误',
						
					];
					exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
				}
			}
			//账号不存在
			if (!$memberinfo) {
				$result = [
					'status'=>'error',
					'code'=>-3,
					'message'=>'账号不存在',	//账号不存在
					
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}
			//帐号已锁定,无法登录
			if($memberinfo['islock']==1) {
				$result = [
					'status'=>'error',
					'code'=>-4,
					'message'=>'帐号已锁定,无法操作',	//帐号已锁定,无法登录
					
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}
		//==================	操作失败-验证 END

		//==================	操作成功-更新数据 START
			//更新会员数据
			$this->member_db->update($data,array('userid'=>$memberinfo['userid']));

			$result = [
				'status'=>'success',
				'code'=>200,
				'message'=>'修改成功',
				'data'=>[
					'userid'=>$memberinfo['userid'],
					'groupid'=>$memberinfo['groupid'],
					'forward'=>$forward,	//给web端用的，接下来跳转到哪里
				]
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
		//==================	操作成功-更新数据 END

	}



	/**
	* 安全中心_密码修改
	* @status [状态] -1帐号、密码、验证码不能为空/-2用户名格式错误/-3密码格式错误/-4验证码错误/-5帐号不存在/-11 密码输入不一致/-100操作错误，进度错误
	* @param  [type] $userid [*用户id]
	* @param  [type] $mobile [*手机号码]
	* @param  [type] $verify_code [*手机验证码]
	* @param  [type] $password [2*用户密码]
	* @param  [type] $repassword [2*重复密码]
	* @param  [type] $progress [*进度：1密码找回_手机验证；2密码找回_设置密码]
	* @param  [type] $type [*类型：1web端、2APP端]
	* @param  [type] $forward [接下来该跳转的页面链接]
	* @return [json] [json数组]
	*/
	public function psd_edit()
	{
		$userid = $_POST['userid'];	//用户id
		$type = $_POST['type'] ? $_POST['type'] : 1;	//类型：1web端、2APP端
		$progress = $_POST['progress'] ? $_POST['progress'] : 1;	//进度：1密码找回_手机验证；2密码找回_设置密码
		$forward = $_POST['forward'] ? urldecode($_POST['forward']) : APP_PATH.'index.php?m=member&c=index';	//接下来该跳转的页面链接

		$mobile = $_POST['mobile'];	//手机号
		$verify_code = $_POST['verify_code'];	//短信验证码
		$password = $_POST['password'];	//密码
		$repassword = $_POST['repassword'];	//重复密码

		//用手机号码查出用户账号
		$memberinfo = $this->member_db->get_one(array('mobile'=>$mobile,'userid'=>$userid));
		
		//==================	操作失败-验证 START
			//帐号密码类型不能为空
			if (!$mobile || !$verify_code || !$userid) {
				$result = [
					'status'=>'error',
					'code'=>-1,
					'message'=>'帐号、验证码、用户id不能为空',
					
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}
			//用户名格式验证（手机号码格式验证）
			if (!$this->_verify_ismobile($mobile)) {
				$result = [
					'status'=>'error',
					'code'=>-2,
					'message'=>'用户名格式错误',	//只允许 13，14，15，16，17，18，19的号码,11位
					
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}
			//短信验证码错误
			//调用通讯模块-短信接口-查询此账号的短信验证码是否匹配上了
			//$sms_verify = true;
			//==================	获取其他接口-接口 START
				$config = $this->zyconfig_db->get_one(array('key'=>'zymessagesys4'),"url");
				$curl = [
					'mobile'=>$mobile,
					'verify_code'=>$verify_code,
					'clear'=>1,
				];
				$sms_verify = _crul_post($config['url'],$curl);
				$sms_verify=json_decode($sms_verify,true);
			//==================	获取其他接口-接口 END		


			if($sms_verify['status']=='error') {	//false,进入
				$result = [
					'status'=>'error',
					'code'=>-4,
					'message'=>$sms_verify['message'],	//短信验证码错误
					
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}
			//帐号不存在
			if (!$memberinfo) {
				$result = [
					'status'=>'error',
					'code'=>-5,
					'message'=>'帐号不存在',	//帐号不存在
					
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}
		
		//==================	操作失败-验证 END


		switch ($progress) {
			case 1:		//手机号码、验证码

				//==================	操作成功-插入数据 START
				$result = [
					'status'=>'success',
					'code'=>200,
					'message'=>'操作成功',
					'data'=>[
						'userid'=>$memberinfo['userid'],
						'groupid'=>$memberinfo['groupid'],
						'forward'=>$forward,	//给web端用的，接下来跳转到哪里
					]
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
				//==================	操作成功-插入数据 END

				break;
			
			case 2:		//手机号码、验证码、输入登录密码、重复登录密码

				//==================	操作失败-验证 START
				//密码格式错误
				if (!$this->_verify_ispassword($password)) {
					$result = [
						'status'=>'error',
						'code'=>-3,
						'message'=>'密码格式错误',	//6-16位之间,只允许数字、大小写英文、下划线
						
					];
					exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
				}
				//验证密码重复的是否一样
				if($password != $repassword){
					$result = [
						'status'=>'error',
						'code'=>-11,
						'message'=>'密码输入不一致',
						
					];
					exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
				}
				//==================	操作失败-验证 END		

				
				//==================	操作成功-修改数据 START
				//调用通讯模块-短信接口-清空此账号的短信验证码
				//操作成功之后删除遗留的短信验证码
				//==================	获取其他接口-接口 START
					$config = $this->zyconfig_db->get_one(array('key'=>'zymessagesys5'),"url");
					$curl = [
						'mobile'=>$memberinfo['mobile']
					];
					_crul_post($config['url'],$curl);
				//==================	获取其他接口-接口 END		



				//更改数据库密码
				$newpassword = password($password, $memberinfo['encrypt']);
				$this->member_db->update(array('password'=>$newpassword),array('userid'=>$memberinfo['userid']));

				$result = [
					'status'=>'success',
					'code'=>200,
					'message'=>'操作成功',
					'data'=>[
						'userid'=>$memberinfo['userid'],
						'groupid'=>$memberinfo['groupid'],
						'forward'=>$forward,	//给web端用的，接下来跳转到哪里
					]
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
				//==================	操作成功-修改数据 END

				break;
			
			default:	//progress	进度错误
				$result = [
					'status'=>'error',
					'code'=>-100,
					'message'=>'操作错误',	//progress	进度错误
					
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
				break;
		}

	}


	/**
	* 安全中心_交易密码修改-记得密码
	* @status [状态] -1密码不能为空/-2密码格式错误/-3帐号不存在/-4原密码错误/-5帐号已锁定，无法操作/-6 密码输入不一致
	* @param  [type] $userid [*用户id]
	* @param  [type] $rawpassword [*原密码]
	* @param  [type] $password [*新密码]
	* @param  [type] $repassword [*重复密码]
	* @param  [type] $type [*类型：1web端、2APP端]
	* @param  [type] $forward [接下来该跳转的页面链接]
	*/
	public function trapsd_edit_jd()
	{
		$userid = $_POST['userid'];	//用户id
		$rawpassword = $_POST['rawpassword'];	//原密码
		$password = $_POST['password'];	//新密码
		$repassword = $_POST['repassword'];	//重复密码
		$type = $_POST['type'] ? $_POST['type'] : 1;	//类型：1web端、2APP端
		$forward = $_POST['forward'] ? urldecode($_POST['forward']) : APP_PATH.'index.php?m=member&c=index';	//接下来该跳转的页面链接

		//用手机号码查出用户账号
		$memberinfo = $this->member_db->get_one(array('userid'=>$userid));

		//==================	操作失败-验证 START
			//帐号密码类型不能为空
			if (!$userid || !$rawpassword || !$password || !$repassword) {
				$result = [
					'status'=>'error',
					'code'=>-1,
					'message'=>'密码不能为空',
					
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}
			//密码格式错误
			if (!$this->_verify_ispassword($rawpassword) || !$this->_verify_ispassword($password) || !$this->_verify_ispassword($repassword)) {
				$result = [
					'status'=>'error',
					'code'=>-2,
					'message'=>'密码格式错误',	//6-16位之间,只允许数字、大小写英文、下划线
					
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}
			//帐号不存在
			if (!$memberinfo) {
				$result = [
					'status'=>'error',
					'code'=>-3,
					'message'=>'帐号不存在',	//帐号不存在
					
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}
			//原密码错误
			if ($memberinfo['trade_password'] != password($rawpassword, $memberinfo['trade_encrypt'])) {
				
				$result = [
					'status'=>'error',
					'code'=>-4,
					'message'=>'原密码错误',
					
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}
			//帐号已锁定，无法操作
			if (!$memberinfo) {
				$result = [
					'status'=>'error',
					'code'=>-5,
					'message'=>'帐号已锁定，无法操作',	//帐号已锁定，无法操作
					
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}
			//验证密码重复的是否一样
			if($password != $repassword){
				$result = [
					'status'=>'error',
					'code'=>-6,
					'message'=>'密码输入不一致',
					
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}

		//==================	操作失败-验证 END

		//==================	操作成功-修改数据 START
			//更改数据库密码
			$newpassword = password($password, $memberinfo['trade_encrypt']);
			$this->member_db->update(array('trade_password'=>$newpassword),array('userid'=>$memberinfo['userid']));
			
			$result = [
				'status'=>'success',
				'code'=>200,
				'message'=>'操作成功',
				'data'=>[
					'userid'=>$memberinfo['userid'],
					'groupid'=>$memberinfo['groupid'],
					'forward'=>$forward,	//给web端用的，接下来跳转到哪里
				]
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
		//==================	操作成功-修改数据 END

	}


	/**
	* 安全中心_交易密码修改-不记得密码
	* @status [状态] -1帐号、密码、验证码不能为空/-2用户名格式错误/-3密码格式错误/-4验证码错误/-5帐号不存在/-11 密码输入不一致/-100操作错误，进度错误
	* @param  [type] $userid [*用户id]
	* @param  [type] $mobile [*手机号码]
	* @param  [type] $verify_code [*手机验证码]
	* @param  [type] $password [2*用户密码]
	* @param  [type] $repassword [2*重复密码]
	* @param  [type] $progress [*进度：1密码找回_手机验证；2密码找回_设置密码]
	* @param  [type] $type [*类型：1web端、2APP端]
	* @param  [type] $forward [接下来该跳转的页面链接]
	* @return [json] [json数组]
	*/
	public function trapsd_edit()
	{
		$userid = $_POST['userid'];	//用户id
		$type = $_POST['type'] ? $_POST['type'] : 1;	//类型：1web端、2APP端
		$progress = $_POST['progress'] ? $_POST['progress'] : 1;	//进度：1密码找回_手机验证；2密码找回_设置密码
		$forward = $_POST['forward'] ? urldecode($_POST['forward']) : APP_PATH.'index.php?m=member&c=index';	//接下来该跳转的页面链接

		$mobile = $_POST['mobile'];	//手机号
		$verify_code = $_POST['verify_code'];	//短信验证码
		$password = $_POST['password'];	//密码
		$repassword = $_POST['repassword'];	//重复密码

		//用手机号码查出用户账号
		$memberinfo = $this->member_db->get_one(array('mobile'=>$mobile,'userid'=>$userid));
		
		//==================	操作失败-验证 START
			//帐号密码类型不能为空
			if (!$mobile || !$verify_code || !$userid) {
				$result = [
					'status'=>'error',
					'code'=>-1,
					'message'=>'帐号、验证码、用户id不能为空',
					
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}
			//用户名格式验证（手机号码格式验证）
			if (!$this->_verify_ismobile($mobile)) {
				$result = [
					'status'=>'error',
					'code'=>-2,
					'message'=>'用户名格式错误',	//只允许 13，14，15，16，17，18，19的号码,11位
					
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}
			//短信验证码错误
			//调用通讯模块-短信接口-查询此账号的短信验证码是否匹配上了
			//$sms_verify = true;
			//==================	获取其他接口-接口 START
				$config = $this->zyconfig_db->get_one(array('key'=>'zymessagesys4'),"url");
				$curl = [
					'mobile'=>$mobile,
					'verify_code'=>$verify_code,
					'clear'=>1,
				];
				$sms_verify = _crul_post($config['url'],$curl);
				$sms_verify=json_decode($sms_verify,true);
			//==================	获取其他接口-接口 END		


			if($sms_verify['status']=='error') {	//false,进入
				$result = [
					'status'=>'error',
					'code'=>-4,
					'message'=>$sms_verify['message'],	//短信验证码错误
					
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}
			//帐号不存在
			if (!$memberinfo) {
				$result = [
					'status'=>'error',
					'code'=>-5,
					'message'=>'帐号不存在',	//帐号不存在
					
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}
		
		//==================	操作失败-验证 END


		switch ($progress) {
			case 1:		//手机号码、验证码

				//==================	操作成功-插入数据 START
				$result = [
					'status'=>'success',
					'code'=>200,
					'message'=>'操作成功',
					'data'=>[
						'userid'=>$memberinfo['userid'],
						'groupid'=>$memberinfo['groupid'],
						'forward'=>$forward,	//给web端用的，接下来跳转到哪里
					]
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
				//==================	操作成功-插入数据 END

				break;
			
			case 2:		//手机号码、验证码、输入登录密码、重复登录密码

				//==================	操作失败-验证 START
				//密码格式错误
				if (!$this->_verify_ispassword($password)) {
					$result = [
						'status'=>'error',
						'code'=>-3,
						'message'=>'密码格式错误',	//6-16位之间,只允许数字、大小写英文、下划线
						
					];
					exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
				}
				//验证密码重复的是否一样
				if($password != $repassword){
					$result = [
						'status'=>'error',
						'code'=>-11,
						'message'=>'密码输入不一致',
						
					];
					exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
				}
				//==================	操作失败-验证 END		

				
				//==================	操作成功-修改数据 START
				//调用通讯模块-短信接口-清空此账号的短信验证码
				//操作成功之后删除遗留的短信验证码
				//==================	获取其他接口-接口 START
					$config = $this->zyconfig_db->get_one(array('key'=>'zymessagesys5'),"url");
					$curl = [
						'mobile'=>$memberinfo['mobile']
					];
					_crul_post($config['url'],$curl);
				//==================	获取其他接口-接口 END		



				//更改数据库密码
				$newpassword = password($password, $memberinfo['trade_encrypt']);
				$this->member_db->update(array('trade_password'=>$newpassword['password'],'trade_encrypt'=>$newpassword['encrypt']),array('userid'=>$memberinfo['userid']));

				$result = [
					'status'=>'success',
					'code'=>200,
					'message'=>'操作成功',
					'data'=>[
						'userid'=>$memberinfo['userid'],
						'groupid'=>$memberinfo['groupid'],
						'forward'=>$forward,	//给web端用的，接下来跳转到哪里
					]
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
				//==================	操作成功-修改数据 END

				break;
			
			default:	//progress	进度错误
				$result = [
					'status'=>'error',
					'code'=>-100,
					'message'=>'操作错误',	//progress	进度错误
					
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
				break;
		}

	}




	/**
	 * 安全中心_密码找回
	 * @status [状态] -1手机号码不能为空/-2用户名格式错误/-3帐号不存在/-4短信验证码错误/-5密码格式错误/-11 密码输入不一致/-100操作错误，进度错误
	 * @param  [type] $mobile [*手机号码]
	 * @param  [type] $verify_code [*手机验证码]
	 * @param  [type] $password [2*用户密码]
	 * @param  [type] $repassword [2*重复密码]
	 * @param  [type] $progress [*进度：1输入手机号码；2发送短信验证码；3设置密码]
	 * @param  [type] $type [*类型：1web端、2APP端]
	 * @param  [type] $forward [接下来该跳转的页面链接]
	 * @return [json] [json数组]
	 */
	public function psd_back()
	{
		$type = $_POST['type'] ? $_POST['type'] : 1;	//类型：1web端、2APP端
		$progress = $_POST['progress'] ? $_POST['progress'] : 1;	//进度：1密码找回_手机验证；2密码找回_设置密码
		$forward = $_POST['forward'] ? urldecode($_POST['forward']) : APP_PATH.'index.php?m=member&c=index';	//接下来该跳转的页面链接

		$mobile = $_POST['mobile'];	//手机号
		$verify_code = $_POST['verify_code'];	//短信验证码
		$password = $_POST['password'];	//密码
		$repassword = $_POST['repassword'];	//重复密码
		
		//用手机号码查出用户账号
		$memberinfo = $this->member_db->get_one(array('mobile'=>$mobile));

		//==================	操作失败-验证 START
			//帐号密码类型不能为空
			if (!$mobile) {
				$result = [
					'status'=>'error',
					'code'=>-1,
					'message'=>'手机号码不能为空',
					
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}
			//用户名格式验证（手机号码格式验证）
			if (!$this->_verify_ismobile($mobile)) {
				$result = [
					'status'=>'error',
					'code'=>-2,
					'message'=>'用户名格式错误',	//只允许 13，14，15，16，17，18，19的号码,11位
					
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}
			//帐号不存在
			if (!$memberinfo) {
				$result = [
					'status'=>'error',
					'code'=>-3,
					'message'=>'帐号不存在',	//帐号不存在
					
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}
		
		//==================	操作失败-验证 END


		switch ($progress) {
			case 1:		//手机号码

				//==================	操作成功-插入数据 START
				$result = [
					'status'=>'success',
					'code'=>200,
					'message'=>'操作成功',
					'data'=>[
						'userid'=>$memberinfo['userid'],
						'groupid'=>$memberinfo['groupid'],
						'forward'=>$forward,	//给web端用的，接下来跳转到哪里
					]
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
				//==================	操作成功-插入数据 END

				break;
			
			case 2:		//验证码

				//==================	操作失败-验证 START
				//短信验证码错误
				//调用通讯模块-短信接口-查询此账号的短信验证码是否匹配上了
				//$sms_verify = true;
				//==================	获取其他接口-接口 START
					$config = $this->zyconfig_db->get_one(array('key'=>'zymessagesys4'),"url");
					$curl = [
						'mobile'=>$mobile,
						'verify_code'=>$verify_code,
						'clear'=>1,
					];
					$sms_verify = _crul_post($config['url'],$curl);
					$sms_verify=json_decode($sms_verify,true);
				//==================	获取其他接口-接口 END		


				if($sms_verify['status']=='error') {	//false,进入
					$result = [
						'status'=>'error',
						'code'=>-4,
						'message'=>$sms_verify['message'],	//短信验证码错误
						
					];
					exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
				}
				//==================	操作失败-验证 END	

				//==================	操作成功-插入数据 START
				$result = [
					'status'=>'success',
					'code'=>200,
					'message'=>'操作成功',
					'data'=>[
						'userid'=>$memberinfo['userid'],
						'groupid'=>$memberinfo['groupid'],
						'forward'=>$forward,	//给web端用的，接下来跳转到哪里
					]
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
				//==================	操作成功-插入数据 END

				break;
			
			case 3:		//手机号码、验证码、输入登录密码、重复登录密码

				//==================	操作失败-验证 START
				//短信验证码错误
				//调用通讯模块-短信接口-查询此账号的短信验证码是否匹配上了
				//$sms_verify = true;
				//==================	获取其他接口-接口 START
					$config = $this->zyconfig_db->get_one(array('key'=>'zymessagesys4'),"url");
					$curl = [
						'mobile'=>$mobile,
						'verify_code'=>$verify_code,
						'clear'=>1,
					];
					$sms_verify = _crul_post($config['url'],$curl);
					$sms_verify=json_decode($sms_verify,true);
				//==================	获取其他接口-接口 END		


				if($sms_verify['status']=='error') {	//false,进入
					$result = [
						'status'=>'error',
						'code'=>-4,
						'message'=>$sms_verify['message'],	//短信验证码错误
						
					];
					exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
				}
				//密码格式错误
				if (!$this->_verify_ispassword($password)) {
					$result = [
						'status'=>'error',
						'code'=>-5,
						'message'=>'密码格式错误',	//6-16位之间,只允许数字、大小写英文、下划线
						
					];
					exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
				}
				//验证密码重复的是否一样
				if($password != $repassword){
					$result = [
						'status'=>'error',
						'code'=>-11,
						'message'=>'密码输入不一致',
						
					];
					exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
				}
				//==================	操作失败-验证 END		

				
				//==================	操作成功-修改数据 START
				//调用通讯模块-短信接口-清空此账号的短信验证码
				//操作成功之后删除遗留的短信验证码
				//==================	获取其他接口-接口 START
					$config = $this->zyconfig_db->get_one(array('key'=>'zymessagesys5'),"url");
					$curl = [
						'mobile'=>$memberinfo['mobile']
					];
					_crul_post($config['url'],$curl);
				//==================	获取其他接口-接口 END		


				//更改数据库密码
				$newpassword = password($password, $memberinfo['encrypt']);
				$this->member_db->update(array('password'=>$newpassword),array('userid'=>$memberinfo['userid']));

				$result = [
					'status'=>'success',
					'code'=>200,
					'message'=>'操作成功',
					'data'=>[
						'userid'=>$memberinfo['userid'],
						'groupid'=>$memberinfo['groupid'],
						'forward'=>$forward,	//给web端用的，接下来跳转到哪里
					]
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
				//==================	操作成功-修改数据 END

				break;
			
			default:	//progress	进度错误
				$result = [
					'status'=>'error',
					'code'=>-100,
					'message'=>'操作错误',	//progress	进度错误
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
				break;
		}		


	}


	/**
	 * 绑定手机
	 * @status [状态] -1手机号、验证码、新手机不能为空/-2请先登录/-3手机号码格式错误/-4账号已存在，无法绑定/-5该账号已经绑定，请勿重复操作/-6短信验证码错误/-7帐号已锁定
	 * @param  [type] $userid [*用户id]
	 * @param  [type] $mobile [*手机号码]
	 * @param  [type] $verify_code [*手机验证码]
	 * @param  [type] $type [*类型：1web端、2APP端]
	 * @param  [type] $forward [接下来该跳转的页面链接]
	 * @return [json] [json数组]
	 */
	public function binding_mobile()
	{
		$type = $_POST['type'] ? $_POST['type'] : 1;	//类型：1web端、2APP端
		$forward = $_POST['forward'] ? urldecode($_POST['forward']) : APP_PATH.'index.php?m=member&c=index';	//接下来该跳转的页面链接

		$userid = $_POST['userid'];	//用户id
		$mobile = $_POST['mobile'];	//手机号
		$verify_code = $_POST['verify_code'];	//短信验证码
		
		//用手机号码查出用户账号
		$memberinfo = $this->member_db->get_one(array('userid'=>$userid));
		$member_mobile = $this->member_db->get_one(array('mobile'=>$mobile));

		//==================	操作失败-验证 START
			//帐号密码类型不能为空
			if (!$mobile || !$verify_code ||!$userid) {
				$result = [
					'status'=>'error',
					'code'=>-1,
					'message'=>'手机号、验证码不能为空',
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}
			//请先登录
			if (!$memberinfo) {
				$result = [
					'status'=>'error',
					'code'=>-2,
					'message'=>'请先登录',	//请先登录
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}
			//用户名格式验证（手机号码格式验证）
			if (!$this->_verify_ismobile($mobile)) {
				$result = [
					'status'=>'error',
					'code'=>-3,
					'message'=>'手机号码格式错误',	//只允许 13，14，15，16，17，18，19的号码,11位
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}
			//账号已存在，无法绑定
			if ($member_mobile) {
				$result = [
					'status'=>'error',
					'code'=>-4,
					'message'=>'账号已存在，无法绑定',	//账号已存在，无法绑定
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}
			//该账号已经绑定，请勿重复操作
			if ($memberinfo['mobile']) {
				$result = [
					'status'=>'error',
					'code'=>-5,
					'message'=>'该账号已经绑定，请勿重复操作',	//该账号已经绑定，请勿重复操作
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}
			//短信验证码错误
			//调用通讯模块-短信接口-查询此账号的短信验证码是否匹配上了
			//$sms_verify = true;
			//==================	获取其他接口-接口 START
				$config = $this->zyconfig_db->get_one(array('key'=>'zymessagesys4'),"url");
				$curl = [
					'mobile'=>$mobile,
					'verify_code'=>$verify_code,
					'clear'=>1,
				];
				$sms_verify = _crul_post($config['url'],$curl);
				$sms_verify=json_decode($sms_verify,true);
			//==================	获取其他接口-接口 END		


			if($sms_verify['status']=='error') {	//false,进入
				$result = [
					'status'=>'error',
					'code'=>-6,
					'message'=>$sms_verify['message'],	//短信验证码错误
					
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}
			//帐号已锁定,无法操作
			if($memberinfo['islock']==1) {
				$result = [
					'status'=>'error',
					'code'=>-7,
					'message'=>'帐号已锁定,无法操作',	//帐号已锁定,无法操作
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}
		//==================	操作失败-验证 END

		//==================	操作成功-返回数据 START
			//更新会员数据
			$member_data = [
				'mobile'=>$mobile,
			];
			$this->member_db->update($member_data,array('userid'=>$memberinfo['userid']));

			
			//调用通讯模块-短信接口-清空此账号的短信验证码
			//操作成功之后删除遗留的短信验证码
			//==================	获取其他接口-接口 START
				$config = $this->zyconfig_db->get_one(array('key'=>'zymessagesys5'),"url");
				$curl = [
					'mobile'=>$memberinfo['mobile']
				];
				_crul_post($config['url'],$curl);
			//==================	获取其他接口-接口 END		


			$result = [
				'status'=>'success',
				'code'=>200,
				'message'=>'绑定成功',
				'data'=>[
					'userid'=>$memberinfo['userid'],
					'groupid'=>$memberinfo['groupid'],
					'forward'=>$forward,	//给web端用的，接下来跳转到哪里
				]
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
		//==================	操作成功-返回数据 END

	}


	/**
	 * 修改手机号
	 * @status [状态] -1帐号、验证码不能为空/-2请先登录/-3手机号码格式错误/-4账号已存在，无法绑定/-6短信验证码错误/-7帐号已锁定
	 * @param  [type] $userid [*用户id]
	 * @param  [type] $mobile [*手机号码]
	 * @param  [type] $verify_code [*手机验证码]
	 * @param  [type] $newmobile [*新手机号码]
	 * @param  [type] $type [*类型：1web端、2APP端]
	 * @param  [type] $forward [接下来该跳转的页面链接]
	 * @return [json] [json数组]
	 */
	public function update_mobile()
	{
		$type = $_POST['type'] ? $_POST['type'] : 1;	//类型：1web端、2APP端
		$forward = $_POST['forward'] ? urldecode($_POST['forward']) : APP_PATH.'index.php?m=member&c=index';	//接下来该跳转的页面链接

		$userid = $_POST['userid'];	//用户id
		$mobile = $_POST['mobile'];	//手机号
		$verify_code = $_POST['verify_code'];	//短信验证码
		$newmobile = $_POST['newmobile'];	//新手机号码
		
		//用手机号码查出用户账号
		$memberinfo = $this->member_db->get_one(array('userid'=>$userid));
		$member_mobile = $this->member_db->get_one(array('mobile'=>$newmobile));

		//==================	操作失败-验证 START
			//帐号密码类型不能为空
			if (!$mobile || !$verify_code ||!$userid || !$newmobile) {
				$result = [
					'status'=>'error',
					'code'=>-1,
					'message'=>'手机号、验证码、新手机不能为空',
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}
			//请先登录
			if (!$memberinfo) {
				$result = [
					'status'=>'error',
					'code'=>-2,
					'message'=>'请先登录',	//请先登录
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}
			//用户名格式验证（手机号码格式验证）
			if (!$this->_verify_ismobile($newmobile)) {
				$result = [
					'status'=>'error',
					'code'=>-3,
					'message'=>'手机号码格式错误',	//只允许 13，14，15，16，17，18，19的号码,11位
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}
			//账号已存在，无法绑定
			if ($member_mobile) {
				$result = [
					'status'=>'error',
					'code'=>-4,
					'message'=>'账号已存在，无法修改',	//账号已存在，无法绑定
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}
			//短信验证码错误
			//调用通讯模块-短信接口-查询此账号的短信验证码是否匹配上了
			//$sms_verify = true;
			//==================	获取其他接口-接口 START
				$config = $this->zyconfig_db->get_one(array('key'=>'zymessagesys4'),"url");
				$curl = [
					'mobile'=>$mobile,
					'verify_code'=>$verify_code,
					'clear'=>1,
				];
				$sms_verify = _crul_post($config['url'],$curl);
				$sms_verify=json_decode($sms_verify,true);
			//==================	获取其他接口-接口 END		


			if($sms_verify['status']=='error') {	//false,进入
				$result = [
					'status'=>'error',
					'code'=>-6,
					'message'=>$sms_verify['message'],	//短信验证码错误
					
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}
			//帐号已锁定,无法操作
			if($memberinfo['islock']==1) {
				$result = [
					'status'=>'error',
					'code'=>-7,
					'message'=>'帐号已锁定,无法操作',	//帐号已锁定,无法操作
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}
		//==================	操作失败-验证 END

		//==================	操作成功-返回数据 START
			//更新会员数据
			$member_data = [
				'mobile'=>$newmobile,
			];
			$this->member_db->update($member_data,array('userid'=>$memberinfo['userid']));

			
			//调用通讯模块-短信接口-清空此账号的短信验证码
			//操作成功之后删除遗留的短信验证码
			//==================	获取其他接口-接口 START
				$config = $this->zyconfig_db->get_one(array('key'=>'zymessagesys5'),"url");
				$curl = [
					'mobile'=>$memberinfo['mobile']
				];
				_crul_post($config['url'],$curl);
			//==================	获取其他接口-接口 END		


			$result = [
				'status'=>'success',
				'code'=>200,
				'message'=>'修改成功',
				'data'=>[
					'userid'=>$memberinfo['userid'],
					'groupid'=>$memberinfo['groupid'],
					'forward'=>$forward,	//给web端用的，接下来跳转到哪里
				]
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
		//==================	操作成功-返回数据 END

	}


	/**
	 * 公共模块_会员详细信息
	 * @param  [type] $userid [*用户id]
	 * @param  [type] $field [需要查询的字段，已逗号隔开]
	 * @return [json]         [数据组]
	 */
	public function pub_memberinfo($userid=NULL,$field)
	{
		if($_POST['userid']){
			$field = $_POST['field'] ? $_POST['field'] : '';
			$userid = $_POST['userid'];
		}else{
			$field = $_GET['field'] ? $_GET['field'] : '';
			$userid = $_GET['userid'];
		}
		//==================	操作失败-验证 START
		if(!$userid){
			$result = [
				'status'=>'error',
				'code'=>-1,
				'message'=>'请输入用户id',
				
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
		}

		$memberinfo = $this->members_db->get_one(['userid'=>$userid]);
		if(!$memberinfo){
			$result = [
				'status'=>'error',
				'code'=>-2,
				'message'=>'用户不存在',
				
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
		}
		//==================	操作失败-验证 END

		//==================	操作成功-查询数据 START
		//如果有字段数组传值过来，那么就只显示传值过来的字段值(利用逗号进行打散操作)
		//如果没有字段数组传值过来，那么就显示当前用户的全部信息
		if($field){
			$field = explode(",", $field);		//打散成数组，到时候进行重新组装
			foreach ($field as $key => $value) {
				$data[$value] = $memberinfo[$value];
			}
		}else{
			$data = $memberinfo;
		}
		//加上域名
		if($data['headimgurl']=='statics/images/member/nophoto.gif'){
			$data['headimgurl'] = APP_PATH.'statics/images/member/nophoto.gif';
		}		
		$result = [
			'status'=>'success',
			'code'=>200,
			'message'=>'操作成功',
			'data'=>$data
		];
		exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
		//==================	操作成功-查询数据 END
	}

	
//====================================	私有验证函数 START

	/*
	 * 私有验证_手机号
	 * 只允许 13，14，15，16，17，18，19的号码
	 * 长度进行了验证、手机号码前两位进行了验证
	 */
	private function _verify_ismobile($mobile) 
	{
		if (preg_match('/^(?:13\d{9}|14[5|7]\d{8}|15[0|1|2|3|5|6|7|8|9]\d{8}|16\d{9}|17\d{9}|18[0|2|3|5|6|7|8|9]\d{8}|19\d{9}|)$/',$mobile)){
			return true;
		} else {
			return false;
		}
	}

	/*
	 * 私有验证_用户帐号
	 * 4-20位之间,只允许数字、大小写英文
	 */
	private function _verify_isusername($username) 
	{
		if (preg_match('/^[0-9a-zA-Z]{4,20}$/i',$username)){
			return true;
		}else {
			return false;
		}
	}
	/*
	 * 私有验证_密码
	 * 6-16位之间,只允许数字、大小写英文、下划线
	 */
	private function _verify_ispassword($password) 
	{
		if (preg_match('/^[_0-9a-zA-Z]{6,16}$/i',$password)){
			return true;
		}else {
			return false;
		}
	}

	/*
	 * 私有返回状态_返回状态
	 * @status [状态] 200操作成功/-100状态码不能为空，操作失败/-101账号不存在/-102帐号已锁定,无法登录/-103请先登录
	 * @param  [type] $status [*状态]
	 * @param  [type] $data [*数据组]
	 * @param  [type] $page [*翻页数据]
	 */
	private function _return_status($status,$data,$pages)
	{
		$status = $status;	//状态
		$data = $data;	//成功：返回数据组
		$pages = $pages;	//成功：返回数据组
		$data = $data;	//成功：返回数据组
		//==================	操作失败-验证 START
			switch ($status) {
				case 200:	//操作成功
					$result = [
						'status'=>'success',
						'code'=>200,
						'message'=>'操作成功',
					];
					if($pages){
						$result['page']=$pages;
					}
					if($data){
						$result['data']=$data;
					}
					exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
					break;
				
				case -101:	//账号不存在
					$result = [
						'status'=>'error',
						'code'=>-101,
						'message'=>'账号不存在',
					];
					exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
					break;
				
				case -102:	//帐号已锁定,无法登录
					$result = [
						'status'=>'error',
						'code'=>-102,
						'message'=>'帐号已锁定,无法登录',
					];
					exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
					break;
				
				case -103:	//请先登录
					$result = [
						'status'=>'error',
						'code'=>-103,
						'message'=>'请先登录',
					];
					exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
					break;
				
				case -104:	//参数不能为空
					$result = [
						'status'=>'error',
						'code'=>-104,
						'message'=>'参数不能为空',
					];
					exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
					break;
				
				default:
					$result = [
						'status'=>'error',
						'code'=>-100,
						'message'=>'操作失败',	//帐号已锁定,无法登录
					];
					exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
					break;
			}
		//==================	操作失败-验证 END
	}
//====================================	私有验证函数 END

}
?>
