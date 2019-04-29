<?php
return array(

//========================================新_接口配置
'wechat_off'=>0,	//是否开启微信登录（0开启、1未开启）
'wechat_kaifang'=>1,	//是否开启微信开放平台（0开启、1未开启）

'wechat_appid' => 'wx5b74a5c289a4b2a5',						//微信PE appid
'wechat_appsecret' => '8c73b91c2c927df3a02795d74e04264f',	//微信PE appsecret
'wechatxcx_appid' => 'wxcf3b4e31a2876493',						//微信PC appid
'wechatxcx_appsecret' => '36e09345ee70952c112d4444a7282ff7',	//微信PC appsecret


'wechatpay_off' => 0,	//是否开启微信微信支付（0开启、1未开启）
'wechatpay_appid' => 'wxcf3b4e31a2876493',				//微信PE appid
'wechatpay_appsecret' => '36e09345ee70952c112d4444a7282ff7',			//微信PE appsecret
'wechatpay_mchid' => '1527766571',	//微信支付_商户号
'wechatpay_mchidkey' => 'gjkghjgGFD55dffgg559jjkhi7777778',	//微信支付_支付安全密钥签名




//******阿里云短信接口密钥-通过300c进行获取*******
'alisms_off'=>'0',	//是否开启阿里云短信接口（0开启、1未开启）
'alisms_uid'=>'19040281473371',	
'alisms_pid'=>'LTAI6rnR5gc7Dqmv',	
'alisms_passwd'=>'18o5uJ9qktZn06hlGF2RcSHXnXQ7Ft',	

//******邮箱接口密钥-通过300c进行获取*******
'mail_off'=>'0',	//是否开启邮箱接口（0开启、1未开启）
'mail_host'=>'smtp.exmail.qq.com',	//邮件服务器
'mail_port'=>'465',	    //邮件发送端口
'mail_secure'=>'ssl',	   //协议 ssl
'mail_username'=>'admin@300c.cn',	    //验证用户名
'mail_pwd'=>'ZHUOyuan123',	        //验证密码
'mail_set_from'=>'admin@300c.cn',	   //发件人邮箱


);
?>