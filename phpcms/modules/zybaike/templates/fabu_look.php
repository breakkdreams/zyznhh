<?php
defined('IN_ADMIN') or exit('No permission resources.');
include $this->admin_tpl('header','admin');//包含头部（后台
?>

<div class="pad_10">
<form action="?m=zybaike&c=zybaike&a=look" method="post" name="myform" id="myform">

<table cellpadding="2" cellspacing="1" class="table_form" width="100%">
	<tr>
		<th width="20%">文章id</th>
		<td  size="30" class="input-text"><?php echo $id;?></td>
	</tr>
	<tr>
		<th width="20%">用户名称</th>
		<td  size="30" class="input-text"><?php echo $username;?></td>
	</tr>
	<tr>
		<th width="20%">用户头像</th>
		<td size="30" class="input-text"><?php echo $userhead;?></td>
	</tr>
	<tr>
		<th width="20%">文章标题</th>
		<td class="input-text"><?php echo $title;?></td>
	</tr> 
	<tr>
		<th width="20%">文章内容</th>
		<td   class="input-text"><?php echo $category;?></td>
	</tr>       
	

</table>
	
</form>
</div>


</body>
</html> 