<?php 
	defined('IN_ADMIN') or exit('No permission resources.');
	include $this->admin_tpl('header','admin');
?>

<style>
.btn { display: inline-block; padding: 0px 5px; margin-bottom: 0; font-size: 12px; font-weight: 400; line-height: 1.32857143; text-align: center; white-space: nowrap; vertical-align: middle; -ms-touch-action: manipulation; touch-action: manipulation; cursor: pointer; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; background-image: none; border: 1px solid transparent; border-radius: 4px; margin-left: 5px;}
.btn-info { background-image: -webkit-linear-gradient(top,#5bc0de 0,#2aabd2 100%); background-image: -o-linear-gradient(top,#5bc0de 0,#2aabd2 100%); background-image: -webkit-gradient(linear,left top,left bottom,from(#5bc0de),to(#2aabd2)); background-image: linear-gradient(to bottom,#5bc0de 0,#2aabd2 100%); filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ff5bc0de', endColorstr='#ff2aabd2', GradientType=0); filter: progid:DXImageTransform.Microsoft.gradient(enabled=false); background-repeat: repeat-x; border-color: #28a4c9;}
.btn-info { color: #fff; background-color: #5bc0de; border-color: #46b8da;}	
	
.btn-danger { background-image: -webkit-linear-gradient(top,#d9534f 0,#c12e2a 100%); background-image: -o-linear-gradient(top,#d9534f 0,#c12e2a 100%); background-image: -webkit-gradient(linear,left top,left bottom,from(#d9534f),to(#c12e2a)); background-image: linear-gradient(to bottom,#d9534f 0,#c12e2a 100%); filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffd9534f', endColorstr='#ffc12e2a', GradientType=0); filter: progid:DXImageTransform.Microsoft.gradient(enabled=false); background-repeat: repeat-x; border-color: #b92c28;}	
.btn-danger { color: #fff; background-color: #d9534f; border-color: #d43f3a;}

.btn-success { background-image: -webkit-linear-gradient(top,#5cb85c 0,#419641 100%); background-image: -o-linear-gradient(top,#5cb85c 0,#419641 100%); background-image: -webkit-gradient(linear,left top,left bottom,from(#5cb85c),to(#419641)); background-image: linear-gradient(to bottom,#5cb85c 0,#419641 100%); filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ff5cb85c', endColorstr='#ff419641', GradientType=0); filter: progid:DXImageTransform.Microsoft.gradient(enabled=false); background-repeat: repeat-x; border-color: #3e8f3e;}
.btn-success { color: #fff; background-color: #5cb85c; border-color: #4cae4c;}
a:hover{ text-decoration: none; }
</style>

<div class="pad_10">
<div class="table-list">


<form name="myform" id="myform" action="?m=banner&c=banner&a=bannerdel" method="post" onsubmit="checkuid();return false;" >
<div class="table-list">
    <table width="100%" cellspacing="0">
        <thead>
            <tr>
            <th width="20" align="center"><input type="checkbox" value="" id="check_box" onclick="selectall('id[]');"></th>
	
            <th ><?php echo 'ID'?></th>
            <th ><?php echo '举报的动态'?></th>
            <th ><?php echo '举报人'?></th>
            <th ><?php echo '举报理由'?></th>
            <th ><?php echo '状态'?></th>
            <th ><?php echo '管理操作'?></th>
            </tr>
        </thead>
    <tbody>
    	
<?php 
if(is_array($infos)){	
	foreach($infos as $info){
?>   
	<tr>
	<td  align="center" width="20"><input type="checkbox" name="id[]" value="<?php echo $info['id']?>"></td>

	<td  align="center"><?php echo $info['id']?></td>
	<td  align="center"><a href="javascript:void(0);" class="btn btn-info btn-sm" onclick="getinfo('<?php echo $info['articleid']?>')"><?php echo'查看详情'?></a></td>
    <td  align="center"><?php echo $info['username']?></td>
    <td  align="center"><?php echo $info['content']?></td>
    <td  align="center">
		<?php 
			if($info['status']==1){
                echo '举报成功';
			}elseif($info['status']==0){
                echo '审核中';
            }elseif($info['status']==-1){
                echo '恶意举报';
            }
		?>
    </td>
	<td align="center">
        <a href="javascript:void(0);" class="btn btn-info btn-sm" onclick="edit('<?php echo $info['id']?>')"><?php echo L('同意')?></a>
        <a href="javascript:void(0);" class="btn btn-danger btn-sm" onclick="refuse('<?php echo $info['id']?>')"><?php echo L('拒绝')?></a>
	</td>
	</tr>
<?php 
	}
}
?>
    </tbody>
    </table>
</div>


<div id="pages"> <?php echo $pages?></div>
</div>

</form>
</body>
</html>

<script>

function edit(id) {
    window.top.art.dialog({
        id:'edit',
        iframe:'?m=community&c=community&a=communityedit&id='+id+'&type=1',
        title:'编辑信息',
        width:'800',
        height:'500',
        lock:true
    },
    function(){
        var d = window.top.art.dialog({id:'edit'}).data.iframe;
        var form = d.document.getElementById('dosubmit');
        form.click();
        return false;
    },
    function(){
        window.top.art.dialog({id:'edit'}).close()
    });
    void(0);
}

function refuse(id) {
    window.top.art.dialog({
        id:'edit',
        iframe:'?m=community&c=community&a=communityedit&id='+id+'&type=2',
        title:'编辑信息',
        width:'800',
        height:'500',
        lock:true
    },
    function(){
        var d = window.top.art.dialog({id:'edit'}).data.iframe;
        var form = d.document.getElementById('dosubmit');
        form.click();
        return false;
    },
    function(){
        window.top.art.dialog({id:'edit'}).close()
    });
    void(0);
}

function getinfo(id) {
    window.top.art.dialog({
        id:'info',
        iframe:'?m=community&c=community&a=communityInfo&id='+id,
        title:'动态信息',
        width:'800',
        height:'500',
        lock:true
    },
    function(){
        window.top.art.dialog({id:'info'}).close()
    });
    void(0);
}



</script>