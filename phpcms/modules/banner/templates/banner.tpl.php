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
            <th ><?php echo '排序'?></th>		
            <th ><?php echo '轮播图ID'?></th>
            <th ><?php echo '类型'?></th>
            <th ><?php echo '图片'?></th>
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
    <td  align="center"><input style=" width: 50px;" name='listorders[<?php echo $info['id']?>]' type='text' size='5' value='<?php echo $info['sort']?>' class="input-text-c"></td>	
	<td  align="center"><?php echo $info['id']?></td>
	<td  align="center">
		<?php 
			if($info['type']==1){
                echo 'url';
				//echo '<img src="'.IMG_PATH.'wrong.png"/><font style=" color:#ff0000; margin-left:12px;">关闭</font>';
			}elseif($info['type']==2){
                echo '商品';
            }elseif($info['type']==3){
                echo '分类';
            }elseif($info['type']==4){
                echo '无';
            }
		?>
    </td>
    <td  align="center"><img style="width:30px;height:30px;" src="<?php echo $info['img']?>"></td>

	<td align="center">
        <a href="javascript:void(0);" class="btn btn-info btn-sm" onclick="edit('<?php echo $info['id']?>')"><?php echo L('编辑')?></a>
    	<a href="javascript:confirmurl('?m=banner&c=banner&a=bannerdel&id=<?php echo $info['id']?>', '<?php echo L('确定删除此轮播图')?>')" class="btn btn-danger btn-sm"><?php echo L('删除')?></a>
	</td>
	</tr>
<?php 
	}
}
?>
    </tbody>
    </table>
</div>

<div style="overflow: hidden;">
    <div class="btn" style=" width: 100%; text-align: left; margin-left: 0; "> 
        <label for="check_box"><?php echo L('selected_all')?>/取消</label>
        <input type="submit" class="btn btn-success btn-sm" name="dosubmit" onClick="document.myform.action='?m=banner&c=banner&a=bannerlistorder'" value="排序"/>
        <input type="submit" class="btn btn-danger btn-sm" name="dosubmit" value="批量删除" onclick="return confirm('<?php echo L('确定删除')?>')"/>
        
    </div>
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
        iframe:'?m=banner&c=banner&a=banneredit&id='+id,
        title:'修改轮播图信息',
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


function checkuid() {
    var url = "?m=banner&c=banner&a=bannerlistorder";  
    var z = $("#myform").attr("action");
    if(url==z){
        myform.submit();
    }else{
        var ids='';
        $("input[name='id[]']:checked").each(function(i, n){
            ids += $(n).val() + ',';
        });
        if(ids=='') {
            window.top.art.dialog({content:'<?php echo L('请先选择记录')?>',lock:true,width:'200',height:'50',time:1.5},function(){});
            return false;
        } else {
            myform.submit();
        }
    }       
	
}

</script>