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
	 * 微信APP_快捷登录
	 * @status [状态] -1数据不能为空/-2请开启微信登录模式,填写配置/-3开启微信开放平台,填写配置/-4帐号已锁定,无法操作
	 * @param  [type] $sex [*微信性别]
	 * @param  [type] $nickname [*微信昵称]
	 * @param  [type] $unionid [*unionid]
	 * @param  [type] $openid [*openid]
	 * @param  [type] $headimgurl [*微信头像]
	 * @return [json] [json数组]
	 */
	public function public_wechatapp_login()
	{
		
		$rs = array();
		$rs['sex'] = $_POST['sex'];
		$rs['nickname'] = $_POST['nickname'];
		$rs['unionid'] = $_POST['unionid'];
		$rs['openid'] = $_POST['openid'];
		$rs['headimgurl'] = $_POST['headimgurl'];

		//微信密钥
		$this->_wechatapp_appid = pc_base::load_config('zysystem', 'wechatapp_appid');	//微信PE appid
		$this->_wechatapp_appsecret = pc_base::load_config('zysystem', 'wechatapp_appsecret');	//微信PE appsecret
		$this->_wechat_kaifang = pc_base::load_config('zysystem', 'wechat_kaifang');	//是否开启微信开放平台（0开启、1未开启）
		$this->_wechat_off = pc_base::load_config('zysystem', 'wechat_off');	//是否开启微信登录（0开启、1未开启）


		//==================	操作失败-验证 START
			//数据不能为空
			if (!$rs['nickname'] ||!$rs['unionid'] || !$rs['openid'] || !$rs['headimgurl']) {
				$result = [
					'status'=>'error',
					'code'=>-1,
					'message'=>'数据不能为空',
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}
			//请开启微信登录模式,填写配置
			if ($this->_wechat_off==1) {
				$result = [
					'status'=>'error',
					'code'=>-2,
					'message'=>'请开启微信登录模式,填写配置',
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}
			//请开启微信登录模式,填写配置
			if ($this->_wechat_kaifang==1) {
				$result = [
					'status'=>'error',
					'code'=>-3,
					'message'=>'开启微信开放平台,填写配置',
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}

		//==================	操作失败-验证 END


		
		//==================	新建微信登录临时表，判断是否已经有手机号码 START
		//根据微信号来查用户信息。看是否存在这个用户（判断是否开启了微信开放平台进行电脑微信进行绑定）
        //如果不存在，就把当前用户的信息,进行注册，在登录
		//如果存在，那就直接登录
		$memberinfo = $this->member_db->get_one(array('wechat_unionid'=>$rs['unionid']));

		//帐号已锁定,无法操作
		if($memberinfo['islock']==1) {
			$result = [
				'status'=>'error',
				'code'=>-4,
				'message'=>'帐号已锁定,无法操作',	//帐号已锁定,无法操作
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
		}


		if ($memberinfo) {	//登陆
			$result = [
				'status'=>'success',
				'code'=>200,
				'message'=>'登录成功',
				'data'=>[
					'userid'=>$memberinfo['userid'],
					'groupid'=>$memberinfo['groupid']
				]
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
		}else{			//注册

			//==================	操作成功-注册数据 START
				if($rs['sex']==0 ||  $rs['sex']==null){
					$rs['sex'] = '保密';
				}elseif($rs['sex']==1){
					$rs['sex'] = '男';
				}elseif($rs['sex']==2){
					$rs['sex'] = '女';
				}
				/* $temporary_name = str_replace(' ','',$rs['nickname']);
				if(empty($temporary_name)){
					$rs['nickname']='???';
				} */

				//获取会员基本设置的配置
				$member_setting = $this->module_db->get_one(array('module'=>'member'), 'setting');
				$member_setting = string2array($member_setting['setting']);
				$password = 123456;
				$mobile = '';

				$userinfo = array();
				//用户基本信息
				$userinfo['username'] = create_randomstr(8);
				$userinfo['password'] = $password;
				$userinfo['encrypt'] = create_randomstr(6);
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

				$userinfo['headimgurl'] = $rs['headimgurl'];
				$userinfo['sex'] = $rs['sex'];
				$userinfo['nickname'] = $rs['nickname'];

				//记录微信信息
				$userinfo['wechat_unionid'] = $rs['unionid'];
				$userinfo['wechat_name'] = $rs['nickname'];
				$userinfo['wechat_headimg'] = $rs['headimgurl'];
				$userinfo['wechat_sex'] = $rs['sex'];
				$userinfo['wechatapp_openid'] = $rs['openid'];
				//记录微信信息

				
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
				//==================	操作成功-注册数据 END			

			//登陆
			$memberinfo = $this->member_db->get_one(array('userid'=>$userid));
			
			$result = [
				'status'=>'success',
				'code'=>200,
				'message'=>'登录成功',
				'data'=>[
					'userid'=>$memberinfo['userid'],
					'groupid'=>$memberinfo['groupid']
				]
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
		}

		//==================	新建微信登录临时表，判断是否已经有手机号码 END
	}


	/**
	 * 店铺会员首页
	 */
	public function shop_init()
	{

	}

	/**
	 * 店铺会员首页
	 */
	public function apptoweb_login()
	{
		$userid = $_POST['userid'];
		//用手机号码查出用户账号
		$memberinfo = $this->member_db->get_one(array('userid'=>$userid));

			//帐号密码类型不能为空
			if (!$memberinfo) {
				$result = [
					'status'=>'error',
					'code'=>-1,
					'message'=>'用户不存在',
					
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}


			$cookietime = SYS_TIME + 7200;	//系统时间+两个小时
			$phpcms_auth = sys_auth($memberinfo['userid']."\t".$memberinfo['password'], 'ENCODE', get_auth_key('login'));
			param::set_cookie('auth', $phpcms_auth, $cookietime);
			param::set_cookie('_userid', $memberinfo['userid'], $cookietime);
			param::set_cookie('_username', $memberinfo['username'], $cookietime);
			param::set_cookie('_nickname', $memberinfo['nickname'], $cookietime);
			param::set_cookie('_groupid', $memberinfo['groupid'], $cookietime);
			param::set_cookie('cookietime', $_cookietime, $cookietime);


			//帐号密码类型不能为空
			if ($memberinfo) {
				$result = [
					'status'=>'success',
					'code'=>200,
					'message'=>'操作成功',
				];
				exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			}
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



	public function ceshi(){

		$result = [
			'status'=>'success',
			'code'=>200,
			'message'=>'登录成功',
			'data'=>$_POST
		];
		exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
	}






//====================================	商品收藏 START
	/**
	 * 前台——收藏-添加
	 * @param  [type] $id     [商品id]
	 * @param  [type] $userid [用户id]
	 */
	public function collect_add($id,$userid)
	{
		$id = $_POST['id'];	//商品id
		$_userid = param::get_cookie('_userid');
		//用户id，APP端必须传
		//非APP端直接用$_userid
		if($_userid){
			$userid = $_userid;
		}else{
			$userid = $_POST['userid'];
		}

		if(!isset($userid))
		{
			$result = [
				'status'=>'error',
				'code'=>1004,
				'message'=>'没有登陆',
				'data'=>'',
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
		}
		//调用商品接口
		$commodity_info=@file_get_contents("http://pub.300c.cn/index.php?m=hpshop&c=goods_api&a=goodsinfo&gid=".$id);
		$cinfo = json_decode($commodity_info,true);
		$exist = $this->member_collect_db->get_one(array('pid'=>$id,'userid'=>$userid));

		//判断该用户是否收藏改商品
		$exist_result = $exist['pid'] != $id || $exist['userid'] != $userid;

		if($cinfo['code'] == 1 && $exist_result)
		{
			//生成商品链接
			$url = "http://pub.300c.cn/index.php?m=zymember&c=index&a=collect&gid=".$id;
		
			$data = array(
				'pid'=>$_POST['id'],
				'catid'=>$cinfo['data']['id'],
				'url'=>$url,
				'thumb'=>$cinfo['data']['thumb'],
				'title'=>$cinfo['data']['goods_name'],
				'price'=>$cinfo['data']['market_price'],
				'userid'=>$userid,
			);
			$state = $this->member_collect_db->insert($data,true);

			//==================	操作失败-验证 START

			if(0<$state){
				$result = [
				'status'=>'success',
				'code'=>200,
				'message'=>'收藏成功',
				'data'=>[
					'pid'=>$_POST['id'],
					'catid'=>$cinfo['data']['id'],
					'url'=>$url,
					'thumb'=>$cinfo['data']['thumb'],
					'title'=>$cinfo['data']['goods_name'],
					'price'=>$cinfo['data']['market_price'],
					'userid'=>$userid,
					'id' => $state,
				]
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			//==================	操作成功-插入数据 END
			}else{
				$result = [
				'status'=>'error',
				'code'=>1001,
				'message'=>'收藏失败',
				'data'=>'',
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			//==================	操作失败-插入数据 END
			}
		}
		if($cinfo['code'] == -1){
			$result = [
				'status'=>'error',
				'code'=>1002,
				'message'=>'该商品不存在',
				'data'=>'',
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
		}
		if($exist['pid'] = $id && $exist['userid'] = $userid)
		{
			$result = [
				'status'=>'error',
				'code'=>1003,
				'message'=>'该商品已收藏',
				'data'=>'',
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
		}
		
	}


	/**
	 * 前台——收藏-删除
	 * @param  [type] $id     [商品id]
	 * @param  [type] $userid [用户id]
	 */
	public function collect_del($id,$userid)
	{
		$id = $_POST['id'];	//商品id
		$_userid = param::get_cookie('_userid');
		//用户id，APP端必须传
		//非APP端直接用$_userid
		if($_userid){
			$userid = $_userid;
		}else{
			$userid = $_POST['uid'];
		}

		if(!isset($userid))
		{
			$result = [
				'status'=>'error',
				'code'=>1004,
				'message'=>'没有登陆',
				'data'=>'',
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
		}

		$info = $this->member_collect_db->get_one(array('pid'=>$id,'userid'=>$userid));
		if(isset($info))
		{
			$state = $this->member_collect_db->delete(array('pid'=>$id,'userid'=>$userid));

			if($state){
				$result = [
				'status'=>'success',
				'code'=>200,
				'message'=>'删除成功',
				'data'=>'',
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			//==================	操作成功-插入数据 END
			}else{
				$result = [
				'status'=>'error',
				'code'=>1001,
				'message'=>'删除失败',
				'data'=>'',
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			//==================	操作失败-插入数据 END
		}
		}else{
			$result = [
				'status'=>'error',
				'code'=>1002,
				'message'=>'商品,用户 不存在',
				'data'=>'',
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			//==================	操作失败-插入数据 END
		}
	}


	/**
	 * 前台——收藏-列表
	 * @param  [type] $userid [用户id]
	 */
	public function collect_list($userid)
	{
		$_userid = param::get_cookie('_userid');
		//用户id，APP端必须传
		//非APP端直接用$_userid
		if($_userid){
			$userid = $_userid;
		}else{
			$userid = $_POST['uid'];
		}

		if(!isset($userid))
		{
			$result = [
				'status'=>'error',
				'code'=>1004,
				'message'=>'没有登陆',
				'data'=>'',
			];
			exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
		}

		$cinfo = $this->member_collect_db->select(array('userid'=>$userid));

		//==================	操作失败-验证 START
		if($cinfo){
			$result = [
			'status'=>'success',
			'code'=>200,
			'message'=>'获取成功',
			'data'=>$cinfo,
		];
		exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
		//==================	操作成功-插入数据 END
		}else{
			$result = [
			'status'=>'error',
			'code'=>1001,
			'message'=>'获取失败',
			'data'=>'',
		];
		exit(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
		//==================	操作失败-插入数据 END
		}
	}



//====================================	商品收藏 END








//====================================	商品足迹 START


	/**
	 * 前台——足迹-添加
	 * @status [状态] -103请先登录/-101账号不存在/-102帐号已锁定,无法登录/-1gid参数空或异常/-2商品不存在或已经下架
	 * @param  [type] $id     [*商品id]
	 * @param  [type] $userid [*用户id]
	 * @param  [type] $type [*类型：1web端、2APP端]
	 */
	public function footprint_add($id,$userid)
	{
		$id = $_POST['id'];	//商品id
		$type = $_POST['type'] ? $_POST['type'] : 1;	//类型：1web端、2APP端
		$userid = $type==1 ? param::get_cookie('_userid') : $_POST['userid'];	//用户id

		//用手机号码查出用户账号
		$memberinfo = $this->member_db->get_one(array('userid'=>$userid));

		//==================	操作失败-验证 START
			//请先登录
			if (!$userid) {
				$this->_return_status(-103);
			}
			//账号不存在
			if (!$memberinfo) {
				$this->_return_status(-101);
			}
			//帐号已锁定,无法登录
			if($memberinfo['islock']==1) {
				$this->_return_status(-102);
			}
		//==================	操作失败-验证 END


		//==================	操作成功-插入数据 START
		//逻辑
		//如果当天同样的商品已存在，那么就删除；不然就添加进去。

			//==================	获取其他接口-接口 START
				$config = $this->zyconfig_db->get_one(array('key'=>'zyshop15'),"url");
				$curl = [
					'gid'=>$id,
				];
				$shop_data = _crul_get($config['url'],$curl);
				$shop_data=json_decode($shop_data,true);

				//==================	操作失败-验证 START
					//gid参数空或异常
					if ($shop_data['code']==0) {
						$result = [
							'status'=>'error',
							'code'=>-1,
							'message'=>'gid参数空或异常',
						];
					}
					//商品不存在或已经下架
					if ($shop_data['code']==-1) {
						$result = [
							'status'=>'error',
							'code'=>-2,
							'message'=>'商品不存在或已经下架',
						];
					}
				//==================	操作失败-验证 END

			//==================	获取其他接口-接口 END		


			$time = time();
			//删除的是当天的
			$del_where = "pid = ".$id." AND userid=".$userid;
			$start_addtime = strtotime(date("Y-m-d 00:00:00"));
			$end_addtime = strtotime(date("Y-m-d 23:59:59"));
			$del_where .= " and addtime >= '".$start_addtime."'";
			$del_where .= " and addtime <= '".$end_addtime."'";
			$this->member_footprint_db->delete($del_where);

			$footprint_time = strtotime(date('y-m-d 01:00:00',$time));

			$arr = [
				'pid'=>$shop_data['data']['id'],
				'catid'=>$shop_data['data']['id'],
				'url'=>APP_PATH."index.php?m=hpshop&c=index&a=goodsinfo&id=".$shop_data['data']['id'],
				'thumb'=>$shop_data['data']['thumb'],
				'title'=>$shop_data['data']['goods_name'],
				'price'=>$shop_data['data']['market_price'],
				'userid'=>$memberinfo['userid'],
				'addtime'=>$time,
				'footprint_time'=>$footprint_time,
			];

			$result = $this->member_footprint_db->insert($arr,true);

			$this->_return_status(200);

		//==================	操作成功-插入数据 END		
		
	}


	/**
	 * 前台——足迹-列表
	 * @status [状态] -103请先登录/-101账号不存在/-102帐号已锁定,无法登录
	 * @param  [type] $userid [*用户id]
	 * @param  [type] $type [*类型：1web端、2APP端]
	 */
	public function footprint_list()
	{
		$type = $_POST['type'] ? $_POST['type'] : 1;	//类型：1web端、2APP端
		$userid = $type==1 ? param::get_cookie('_userid') : $_POST['userid'];	//用户id


		//用手机号码查出用户账号
		$memberinfo = $this->member_db->get_one(array('userid'=>$userid));

		//==================	操作失败-验证 START
			//请先登录
			if (!$userid) {
				$this->_return_status(-103);
			}
			//账号不存在
			if (!$memberinfo) {
				$this->_return_status(-101);
			}
			//帐号已锁定,无法登录
			if($memberinfo['islock']==1) {
				$this->_return_status(-102);
			}
		//==================	操作失败-验证 END

		//==================	操作成功-显示数据 START
			//如果日期是一样的，那么就不获取过来
			//如果是不一样的，那么就获取过来存起来

			$date= strtotime("-1 months",time());		//前个月的时间戳
			$where = 'userid='.$userid.' AND addtime>='.$date;
			$order = 'id DESC';
			$info = $this->member_footprint_db->select($where,'`id`,`footprint_time`,`addtime`','',$order);

			$item=array();
			$info_unique =assoc_unique($info ,'footprint_time');

			foreach ($info_unique as $key => $value) {
				$item[$key]['addtime']=date('Y-m-d',$value['addtime']);
				$where1 = 'userid='.$userid.' AND addtime>='.$date.' and footprint_time='.$value['footprint_time'];
				$item[$key]['data'] = $this->member_footprint_db->select($where1,'`id`,`url`,`thumb`,`title`,`price`','',$order);
			}


			$this->_return_status(200,$item);

		//==================	操作成功-显示数据 END
		
	}

//====================================	商品足迹 END




//====================================	店铺管理 START

	/**
	 * [store_audit 提交店铺审核资料]
	 * @status [状态] -104参数不能为空/-103请先登录/-101账号不存在/-102帐号已锁定,无法登录
	 * @param  [type] $store_logo [*店铺logo]
	 * @param  [type] $store_name [*店铺名称]
	 * @param  [type] $store_zmidcard [*店铺身份证正面]
	 * @param  [type] $store_fmidcard [*店铺身份证反面]
	 * @param  [type] $userid [*用户id]
	 * @param  [type] $type [*类型：1web端、2APP端]
	 * @return [type] [description]
	 */
	public function store_audit(){
		$store_logo = $_POST['store_logo'];
		$store_name = $_POST['store_name'];
		$store_zmidcard = $_POST['store_zmidcard'];
		$store_fmidcard = $_POST['store_fmidcard'];
		$type = $_POST['type'] ? $_POST['type'] : 1;	//类型：1web端、2APP端
		$userid = $type==1 ? param::get_cookie('_userid') : $_POST['userid'];	//用户id

		//用手机号码查出用户账号
		$memberinfo = $this->member_db->get_one(array('userid'=>$userid));

		//==================	操作失败-验证 START
			//参数不能为空
			if (!$store_logo || !$store_name || !$store_zmidcard || !$store_fmidcard || !$type || !$userid) {
				$this->_return_status(-104);
			}
			//请先登录
			if (!$userid) {
				$this->_return_status(-103);
			}
			//账号不存在
			if (!$memberinfo) {
				$this->_return_status(-101);
			}
			//帐号已锁定,无法登录
			if($memberinfo['islock']==1) {
				$this->_return_status(-102);
			}
		//==================	操作失败-验证 END




		//==================	操作成功 END
			$this->member_db->update(['shopname'=>$store_name,'store_logo'=>$store_logo,'store_audit'=>1],['userid'=>$userid]);

			$this->_return_status(200);
		//==================	操作成功 END

	}



//====================================	店铺管理 END





}
?>
