<?php
defined('IN_ADMIN') or exit('No permission resources.');
include $this->admin_tpl('header', 'admin');
?>
<style type="text/css">
.table_form th{text-align: left;}
</style>

<form name="myform" id="myform" action="?m=zybaike&c=zybaike&a=wenzhang_view" method="post">
<div class="pad-10">
<div class="table-list">
<div class="common-form">
	<div id="div_setting_2" class="contentList">

    	<fieldset>
        <legend>文章信息</legend>
		<table width="100%" class="table_form">
			<tbody>
				<tr>
					<th width="120">用户ID</th>
					<td><?php echo $info['userid']?></td>
				</tr>
				<tr>
					<th width="120">用户名</th>
					<td><?php echo $info['username']?><?php if($info['islock']) {?><img title="锁定" src="<?php echo IMG_PATH?>icon/icon_padlock.gif"><?php }?></td>
				</tr>
                <tr>
					<th>头像</th>
					<td>
					<?php if(empty($info['userhead'])){?>
                        <img src="statics/images/member/nophoto.gif" height="90" width="90" onerror="this.src='statics/images/member/nophoto.gif'">
                    <?php }else{ ?>
                        <img src="<?php echo $info['userhead']?>" height="90" width="90">
                    <?php }?>
                    </td>
				</tr>
                <tr>
					<th>文章ID</th>
					<td><?php echo $info['id']?></td>
				</tr>
                <tr>
					<th>标题</th>
					<td><?php echo $info['title']?></td>
				</tr>
                <tr>
					<th>内容</th>
					<td><?php echo $info['content']?></td>
				</tr>
                <tr>
					<th>发布日期</th>
					<td><?php echo $info['time']?></td>
				</tr>
                <tr>
					<th>文章类型</th>
					<td><?php echo $info['category'];?></td>
				</tr>
               
                <tr>
					<th>状态</th>
					<td>
			            <?php if($info['islock']==0){ ?>
			                正常
			            <?php }?>
			            <?php if($info['islock']==1){ ?>
			                禁用
			            <?php }?>
					</td>
				</tr>
			</tbody>
		</table>
        </fieldset>
        <div class="bk15"></div>

	</div>
<input type="button" class="dialog" name="dosubmit" id="dosubmit" onclick="window.top.art.dialog({id:'view'}).close();"/>

</div>
</div>

</div>
</div>
</form>

</body>
</html>
