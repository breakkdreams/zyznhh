<?php
defined('IN_PHPCMS') or exit('Access Denied');
defined('INSTALL') or exit('Access Denied');


/**
 * 添加父级菜单:后台添加一个卓远商城菜单
 */
//先判断有没有卓远网络的大菜单
$zywldb = $menu_db->get_one(array('name'=>'zymembermenu','parentid'=>'0'));
if($zywldb){
	$parentid =$zywldb['id'];
}else{
	$parentid = $menu_db->insert(
	array(
		'name'=>'zymembermenu', 
		'parentid'=>'0', 
		'm'=>'zybaike', 
		'c'=>'zybaike', 
		'a'=>'init', 
		'data'=>'', 
		'listorder'=>9,   
		'display'=>'1'
		),
	true
    );
}

/**
 * 添加菜单:花草百科
 */
$pid = $menu_db->insert(
	array(
		'name'=>'zybaike_manage', //菜单名称
		'parentid'=>$parentid, //添加到后台的主菜单里
		'm'=>'zybaike', //模块
		'c'=>'zybaike', //文件
		'a'=>'zybaike_manage',//方法
		'data'=>'', //附加参数
		'listorder'=>0, //菜单排序
		'display'=>'1'), //显示菜单 1是显示 0是隐藏
		true //插入菜单之后，是否返回id
	);

/**
 * 添加子菜单:文章管理
 */
$wenzhang = $menu_db->insert(
	array(
		'name'=>'zywenzhang_manage_menu', //菜单名称
		'parentid'=>$pid, //添加到花草百科。
		'm'=>'zybaike', //模块
		'c'=>'zybaike',//文件 
		'a'=>'wenzhang', //方法
		'data'=>'', //附加参数
		'listorder'=>1, //菜单排序
		'display'=>'1' //显示菜单 1是显示 0是隐藏
		),true//插入菜单之后，是否返回id
	);

/**
 * 添加子菜单:文章评论管理
 */
$pinglun = $menu_db->insert(
	array(
		'name'=>'zypinglun_manage_menu', //菜单名称
		'parentid'=>$pid, //添加到花草百科。
		'm'=>'zybaike', //模块
		'c'=>'zybaike',//文件
		'a'=>'pinglun', //方法
		'data'=>'', //附加参数
		'listorder'=>1, //菜单排序
		'display'=>'1' //显示菜单 1是显示 0是隐藏
	),true//插入菜单之后，是否返回id
);





/**
 * 菜单名称翻译
 */	
$language = array(
	
	'zybaike_manage' =>'花草百科',
	'zywenzhang_manage_menu'=>'文章管理',
	'zypinglun_manage_menu'=>'文章评论管理',


);


?>