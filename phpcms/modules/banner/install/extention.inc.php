<?php
// +------------------------------------------------------------
// | distribution
// +------------------------------------------------------------
// | 卓远网络：CY QQ:185017580 http://www.300c.cn/
// +------------------------------------------------------------
// | 欢迎加入卓远网络-Team，和卓远一起，精通PHPCMS
// +------------------------------------------------------------
// | 版本号：20180208
// +------------------------------------------------------------
defined('IN_PHPCMS') or exit('Access Denied');
defined('INSTALL') or exit('Access Denied');

/**
 * 添加父级菜单:后台添加一个卓远商城菜单
 */



//先判断有没有卓远网络的大菜单
$zywldb = $menu_db->get_one(array('name'=>'hpshop','parentid'=>'0'));
if($zywldb){
	$parentid =$zywldb['id'];
}else{
	$parentid = $menu_db->insert(
	array(
		'name'=>'hpshop',
		'parentid'=>'0',
		'm'=>'hpshop',
		'c'=>'goods',
		'a'=>'init',
		'data'=>'',
		'listorder'=>9,
		'display'=>'1'
		),
	true
    );
}

/**
 * 添加菜单:订单管理
 */
$pid = $menu_db->insert(
	array(
		'name'=>'轮播图管理', //菜单名称
		'parentid'=>$parentid, //添加到后台的主菜单里
		'm'=>'banner', //模块
		'c'=>'banner', //文件
		'a'=>'init',//方法
		'data'=>'', //附加参数
		'listorder'=>0, //菜单排序
		'display'=>'1'), //显示菜单 1是显示 0是隐藏
		true //插入菜单之后，是否返回id
	);

/**
 * 添加子菜单  商品列表
 */
$menu_db->insert(
	array(
		'name'=>'轮播图列表', //菜单名称
		'parentid'=>$pid, //添加到积分商城。
		'm'=>'banner', //模块
		'c'=>'banner',//文件 
		'a'=>'bannerlist', //方法
		'data'=>'', //附加参数
		'listorder'=>1, //菜单排序
		'display'=>'1' //显示菜单 1是显示 0是隐藏
		)
	);
?>