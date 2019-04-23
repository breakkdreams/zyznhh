<?php
defined('IN_PHPCMS') or exit('Access Denied');
defined('INSTALL') or exit('Access Denied');

//添加父级菜单：后台添加一个广告菜单

//判断有没有大菜单
$zywldbs = $menu_db->get_one(array('name'=>'zymarketing','parentid'=>'0'));
if($zywldbs){
	$parentids =$zywldbs['id'];
}else{
	$parentids = $menu_db->insert(
		array(
			'name'=>'zymarketing',
			'parentid'=>'0',
			'm'=>'zymarketing',
			'c'=>'marketing',
			'a'=>'init',
			'data'=>'',
			'listorder'=>9,
			'display'=>'1'
		),
		true
	);
}
//一级
$parentidp=$menu_db->insert(
    array(
        'name'=>'ad_module',
        'parentid'=>$parentids,
        'm'=>'advertisement',
        'c'=>'space',
        'a'=>'init',
        'data'=>'',
        'listorder'=>'0',
        'display'=>'1',
    ),true
);
//二级
$parentid=$menu_db->insert(
    array(
        'name'=>'ad_manage',
        'parentid'=>$parentidp,
        'm'=>'advertisement',
        'c'=>'space',
        'a'=>'init',
        'data'=>'',
        'listorder'=>'0',
        'display'=>'1',
    ),true
);


//$parentid = $menu_db->insert(array('name'=>'advertisement', 'parentid'=>29, 'm'=>'advertisement', 'c'=>'space', 'a'=>'init', 'data'=>'', 'listorder'=>0, 'display'=>'1'), true);
$menu_db->insert(array('name'=>'add_space', 'parentid'=>$parentid, 'm'=>'advertisement', 'c'=>'space', 'a'=>'add', 'data'=>'', 'listorder'=>0, 'display'=>'0'));
$menu_db->insert(array('name'=>'edit_space', 'parentid'=>$parentid, 'm'=>'advertisement', 'c'=>'space', 'a'=>'edit', 'data'=>'', 'listorder'=>0, 'display'=>'0'));
$menu_db->insert(array('name'=>'del_space', 'parentid'=>$parentid, 'm'=>'advertisement', 'c'=>'space', 'a'=>'delete', 'data'=>'', 'listorder'=>0, 'display'=>'0'));
$menu_db->insert(array('name'=>'advertisement_list', 'parentid'=>$parentid, 'm'=>'advertisement', 'c'=>'advertisement', 'a'=>'init', 'data'=>'', 'listorder'=>0, 'display'=>'0'));
$menu_db->insert(array('name'=>'add_advertisement', 'parentid'=>$parentid, 'm'=>'advertisement', 'c'=>'advertisement', 'a'=>'add', 'data'=>'', 'listorder'=>0, 'display'=>'0'));
$menu_db->insert(array('name'=>'edit_advertisement', 'parentid'=>$parentid, 'm'=>'advertisement', 'c'=>'advertisement', 'a'=>'edit', 'data'=>'', 'listorder'=>0, 'display'=>'0'));
$menu_db->insert(array('name'=>'del_advertisement', 'parentid'=>$parentid, 'm'=>'advertisement', 'c'=>'advertisement', 'a'=>'delete', 'data'=>'', 'listorder'=>0, 'display'=>'0'));
$menu_db->insert(array('name'=>'advertisement_stat', 'parentid'=>$parentid, 'm'=>'advertisement', 'c'=>'advertisement', 'a'=>'stat', 'data'=>'', 'listorder'=>0, 'display'=>'0'));
$menu_db->insert(array('name'=>'advertisement_setting', 'parentid'=>$parentid, 'm'=>'advertisement', 'c'=>'space', 'a'=>'setting', 'data'=>'', 'listorder'=>0, 'display'=>'0'));
$menu_db->insert(array('name'=>'create_advertisement_js', 'parentid'=>$parentid, 'm'=>'advertisement', 'c'=>'space', 'a'=>'create_js', 'data'=>'', 'listorder'=>0, 'display'=>'1'));
$menu_db->insert(array('name'=>'advertisement_template', 'parentid'=>$parentid, 'm'=>'advertisement', 'c'=>'space', 'a'=>'poster_template', 'data'=>'', 'listorder'=>0, 'display'=>'1'));

$language = array('advertisement'=>'广告', 'add_space'=>'添加版位', 'edit_space'=>'编辑版位', 'del_space'=>'删除版位', 'advertisement_list'=>'广告列表', 'add_advertisement'=>'添加广告', 'edit_advertisement'=>'编辑广告', 'del_advertisement'=>'删除广告', 'advertisement_stat'=>'广告统计', 'advertisement_setting'=>'模块配置', 'create_advertisement_js'=>'重新生成js', 'advertisement_template'=>'广告模板设置','zymarketing'=>'营销模块','ad_module'=>'广告系统','ad_manage'=>'广告管理');
?>