<?php
defined('IN_ADMIN') or exit('No permission resources.');
$show_dialog = 1;
include $this->admin_tpl('header', 'admin');
?>
<div class="pad-lr-10">
<form name="myform" id="myform" action="?m=zyshop&c=zyshop&a=order_manage_view" method="post" >
<div class="table-list">
<table width="100%" cellspacing="0">
	<thead>
		<tr>
            <th align="center"><strong>产品图</strong></th>
            <th align="center"><strong>产品名称</strong></th>
            <th align="center"><strong>尺寸</strong></th>
            <th align="center"><strong>颜色</strong></th>
            <th align="center"><strong>单价</strong></th>
            <th align="center"><strong>数量</strong></th>
            <th align="center"><strong>总价</strong></th>
		</tr>
	</thead>
<tbody>
<?php
if(is_array($info)){
	foreach($info as $info){
		?>
	<tr>
        <td align="center"><img src="<?php echo thumb($info['thumb'],200,200)?>" width="30"></td>
         <td align="center"><a target="_blank" href="<?php echo $info['url']?>"><?php echo $info['title']?></a></td>
         <td align="center"><?php echo $info['size']?></td>
        <td align="center"><?php echo $info['color']?></td>
        <td align="center"><?php echo $info['bw_price']?></td>
         <td align="center"><?php echo $info['quantity']?></td>
        <td align="center"><?php echo $info['total']?></td>
        
	</tr>
	<?php
	}
}
?>
</tbody>
</table>
</div>
<input name="dosubmit" type="submit" id="dosubmit" value="<?php echo L('submit')?>" class="dialog">
</form>
</div>




</body>
</html>
