<?php
defined('IN_ADMIN') or exit('No permission resources.');
$show_dialog = 1;
include $this->admin_tpl('header', 'admin');
?>
<link rel="stylesheet" type="text/css" href="<?php echo APP_PATH?>statics/zybaike/layui/css/layui.css">
<script src="<?php echo APP_PATH?>statics/zybaike/layui/layui.all.js"></script>

<!-- 样式库 -->
<style type="text/css">
    .clear{ clear: both; }
    .btn:hover{text-decoration: none;}
    .btn {display: inline-block; height: 34px; line-height: 34px; padding: 0 14px; background-color: #009688; color: #fff; white-space: nowrap; text-align: center; font-size: 14px; border: none; border-radius: 2px; cursor: pointer; transition: all .3s; -webkit-transition: all .3s; box-sizing: border-box;}
    .btn:hover {opacity: .8;color: #fff;}
    .btn-primary {
        background-color: #fff;
        border: 1px solid #C9C9C9;
        color: #555;
    }
    .btn-warm {
        background-color: #FFB800;
    }
    .btn-danger {
        background-color: #FF5722;
    }
    .btn-info {
        background-color: #1E9FFF;
    }


    .btn-sm {
        height: 30px;
        line-height: 30px;
        padding: 0 10px;
        font-size: 12px;
    }
    .btn-xs {
        height: 22px;
        line-height: 22px;
        padding: 0 5px;
        font-size: 12px;
    }
</style>


<div class="pad-lr-10">
<form name="searchform" action="" method="get" >
<input type="hidden" value="zybaike" name="m">
<input type="hidden" value="zybaike" name="c">
<input type="hidden" value="wenzhang" name="a">

<div class="explain-col search-form">
<select name="type" >
    <option value="">请选择</option>
    <option value="1" <?php if ($_GET['type']==1) {?>selected<?php }?>>用户id</option>
    <option value="2" <?php if ($_GET['type']==2) {?>selected<?php }?>>用户名</option>
    <option value="3" <?php if ($_GET['type']==3) {?>selected<?php }?>>文章id</option>
    <option value="4" <?php if ($_GET['type']==4) {?>selected<?php }?>>文章标题</option>
    
    
</select>
<input type="text" value="<?php echo $_GET['q']?>" class="input-text" name="q">

发布日期  <?php echo form::date('start_addtime',$_GET['start_addtime'])?><?php echo L('to')?>   <?php echo form::date('end_addtime',$_GET['end_addtime'])?>

<select name="islock">
    <option value="" selected="">正常/禁发</option>
    <option value="1" <?php if ($_GET['islock']==1) {?>selected<?php }?>>正常</option>
    <option value="2" <?php if ($_GET['islock']==2) {?>selected<?php }?>>禁用</option>
</select>

<input type="submit" value="<?php echo L('search')?>" name="dosubmit" class="btn btn-sm" style="padding: 0 10px;">

</div>
</form>
	

<form name="myform" id="myform" action="?m=zybaike&c=zybaike&a=del_wenzhang" method="post" >
<div class="table-list">
<table width="100%" cellspacing="0" class="layui-table">
	<thead>
		<tr>
			<th width="25px" align="center"><input type="checkbox" value="" id="check_box" onclick="selectall('ID[]');"></th>
			<th align="center"><strong>文章ID</strong></th>
            <th align="center"><strong>用户ID</strong></th>
            <th align="left"><strong>用户昵称</strong></th>
            <th align="left"><strong>文章标题</strong></th>
            <th align="left"><strong>文章分类</strong></th>
            <th align="left"><strong>发布时间</strong></th>
            <th align="left"><strong>浏览量</strong></th>
            <th align="left"><strong>点赞量</strong></th>
            <th align="left"><strong>转发量</strong></th>
			<th align="left"><strong>评论量</strong></th>
            <th align="left"><strong>举报量</strong></th>
            <th align="center"><strong>状态</strong></th>
            <th width="200px" align="center"><strong>操作</strong></th>
		</tr>
	</thead>
<tbody>
	
<?php
if(is_array($info)){
	foreach($info as $info){
		?>
	<tr> 
		<td align="center" width="35">
			<input type="checkbox" name="id[]" value="<?php echo $info['id']?>">
		</td>
        <td align="center"><?php echo $info['id']?></td>
		<td align="center"><?php echo $info['userid']?></td>
        <td align="center">
         <img src="<?php echo $info['userhead']?>" height="25" width="25">
			<?php echo $info['username']?>		
 		 

        </td>
		
		
		<td><?php echo $info['title']?></td>
		<td><?php echo $info['category']?></td>
		<td><?php echo $info['time']?></td>
		<td><?php echo $info['browse-number']?></td>
		<td><?php echo $info['fabulous-number']?></td>
		<td><?php echo $info['forward-number']?></td>
		<td><?php echo $info['comment-number']?></td>
		<td><a href="javascript:jubao(0);" onclick="jubao('<?php echo $info['id']?>')" class="btn btn-danger btn-sm" style="padding-top: 0;padding-bottom: 0;"><?php echo $info['report-number']?></a></td>
		
		 <td align="center">
            <?php if($info['islock']==0){ ?>
                正常
            <?php }?>
            <?php if($info['islock']==1){ ?>
               <?php if($info['islock']) {?><img title="禁止" src="<?php echo IMG_PATH?>icon/icon_padlock.gif"><?php }?> 禁止<?php }?>
        </td>
       <td align="center">
            <?php if($info['islock']==0){ ?>
                <a href="?m=zybaike&c=zybaike&a=baike_islock&id=<?php echo $info['id']?>&islock=1" onClick="return confirm('确认禁止?')" class="btn btn-sm" style="padding-top: 0;padding-bottom: 0;">禁止</a>
            <?php }?>
            <?php if($info['islock']==1){ ?>
                <a href="?m=zybaike&c=zybaike&a=baike_islock&id=<?php echo $info['id']?>&islock=0" onClick="return confirm('确认开启?')" class="btn btn-sm" style="padding-top: 0;padding-bottom: 0;">开启</a>
            <?php }?>
	
		    <a href="javascript:void(0);" onclick="view('<?php echo $info['id']?>')" class="btn btn-sm btn-info" style="padding-top: 0;padding-bottom: 0;">查看</a>
            <a href="?m=zybaike&c=zybaike&a=del_wenzhang&id=<?php echo $info['id']?>" onClick="return confirm('确认删除?')" class="btn btn-danger btn-sm" style="padding-top: 0;padding-bottom: 0;">删除</a>

	
	 </td>
        </tr>
	<?php
	}
}
?>
	
</tbody>
</table>
</div>	
	<div class="btn"> <label for="check_box"><?php echo L('cancel')?></label>
<input type="submit" class="button" name="dosubmit" onClick="document.myform.action='?m=zybaike&c=zybaike&a=del_wenzhang'" value="批量删除"/></div>
	<div id="pages"><?php echo $pages?></div>
</form>
</div>	
	
<script>
function view(id) {
    window.top.art.dialog({
        id:'view',
        iframe:'?m=zybaike&c=zybaike&a=wenzhang_view&type=1&id='+id,
        title:'文章信息',
        width:'500',
        height:'500',
        lock:true
    },
    function(){
        var d = window.top.art.dialog({id:'view'}).data.iframe;
        var form = d.document.getElementById('dosubmit');
        form.click();
        return false;
    },
    function(){
        window.top.art.dialog({id:'view'}).close()
    });
    void(0);
}
	
function jubao(id) {
    window.top.art.dialog({
        id:'jubao',
        iframe:'?m=zybaike&c=zybaike&a=jubao_view&type=1&id='+id,
        title:'举报信息',
        width:'500',
        height:'500',
        lock:true
    },
    function(){
        var d = window.top.art.dialog({id:'jubao'}).data.iframe;
        var form = d.document.getElementById('dosubmit');
        form.click();
        return false;
    },
    function(){
        window.top.art.dialog({id:'jubao'}).close()
    });
    void(0);
}
</script>
</body>
</html>
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	




