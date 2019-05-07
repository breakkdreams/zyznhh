<?php
defined('IN_ADMIN') or exit('No permission resources.');
include $this->admin_tpl('header', 'admin');
?>
<style type="text/css">
.table_form th{text-align: left;}
</style>

<form name="myform" id="myform" action="?m=zybaike&c=zybaike&a=jubao_view" method="post">
<div class="pad-10">
<div class="table-list">
<div class="common-form">
	<div id="div_setting_2" class="contentList">

    	<fieldset>
        <legend>举报信息</legend>
		<table width="100%" class="table_form">
			<tbody>
			
                <tr>
					<th>文章ID</th>
					<th>用户ID</th>
					<th>举报原由</th>
					<th>日期</th>
				</tr>
     
<?php
if(is_array($info)){
	foreach($info as $info){
		?>
		<tr> 
		
			<td><?php echo $info['reportid']?></td>
			<td><?php echo $info['userid']?></td>
              
			<td><?php echo $info['content']?></td>
		
			<td><?php echo $info['time']?></td>
		</tr>
				
	<?php
	}
}
?>				
		</tbody>
		</table>
        </fieldset>
        <div class="bk15"></div>
	
	</div>
<input type="button" class="dialog" name="dosubmit" id="dosubmit" onclick="window.top.art.dialog({id:'jubao'}).close();"/>

</div>
</div>

</div>
</div>
</form>

</body>
</html>
