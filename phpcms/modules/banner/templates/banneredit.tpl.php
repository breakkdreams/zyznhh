<?php
defined('IN_ADMIN') or exit('No permission resources.');
include $this->admin_tpl('header', 'admin');
?>

<style type="text/css">
	/*隐藏radio按钮*/
input[type="radio"] { opacity: 0;}
.myradio { display: inline-block; vertical-align: middle; margin: 0; padding: 0; width: 70px; height: 24px; border-radius: 20px; position: relative; overflow: hidden;}
.mrclose { background-color: #f40;/*#e8e8e8;*/}
.mropen { background-color: #67e66c;}
.myradio .open, .myradio .close { width: 22px; height: 22px; font-size: 13px; border-radius: 50%; background: #fff; color: #fff; position: absolute; top: 0; left: 0; border: 1px solid #e8e8e8;}
.myradio .open { color: #fff; background-color: #fff;}
.hidden { display: none}
.disabled { pointer-events: none; cursor: default;}
.myradio .close { left: auto; right: 0; }
.myradio .open:after { content: '开启'; position: absolute; top: 0; left: 30px; width: 28px; height: 24px; line-height: 22px; }
.myradio .close:before { content: '关闭'; position: absolute; top: 0; left: -35px; width: 28px; height: 24px; line-height: 22px;}

input[type="radio"] + label::before { content: "\a0"; /*不换行空格*/ display: inline-block; vertical-align: middle; font-size: 16px; width: 1em; height: 1em; margin-right: .4em; border-radius: 50%; border: 1px solid #01cd78; text-indent: .15em; line-height: 1; margin-left: 10px; margin-top: 5px; -moz-box-sizing: border-box;  /*Firefox3.5+*/-webkit-box-sizing: border-box; /*Safari3.2+*/-o-box-sizing: border-box; /*Opera9.6*/-ms-box-sizing: border-box; /*IE8*/box-sizing: border-box; margin-top: -2px;}
input[type="radio"]:checked + label::before { background-color: #01cd78; background-clip: content-box; padding: .18em; font-size: 16px;}
input[type="radio"] { position: absolute; clip: rect(0, 0, 0, 0);}

</style>

<script language="javascript" type="text/javascript" src="<?php echo JS_PATH?>member_common.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo JS_PATH?>formvalidator.js" charset="UTF-8"></script>
<script language="javascript" type="text/javascript" src="<?php echo JS_PATH?>formvalidatorregex.js" charset="UTF-8"></script>

<script type="text/javascript">
  $(document).ready(function() {
	$.formValidator.initConfig({autotip:true,formid:"myform",onerror:function(msg){}});	
  });

</script>


<style type="text/css">
.table_form th{text-align: left;}
</style>

<form name="myform" id="myform" action="" method="post" >
<input type="hidden" name="id" value="<?php echo $info['id'];?>">	
<div class="pad-10">
<div class="common-form">
	<div id="div_setting_2" class="contentList">
    
    	<fieldset>
        <legend>基本信息</legend>
		<table width="100%" class="table_form">
			<tbody>
     
				<tr>
					<th width="125">类型</th>  
					<td>
						<select id="type" name="type">

						<?php 
							if($info['type'] == 4){
						?>
							<option value="4" selected="true">不跳转</option>
							<option value="2">商品</option>
						<?php
							}
						?>

						<?php 
							if($info['type'] == 2){
						?>
							<option value="4">不跳转</option>
							<option value="2" selected="true">商品</option>	
						<?php
							}
						?>

						</select>
					</td>
				</tr>


				<tr>
					<th width="125">类型值</th>  
					<td>
					<select id="goodsid" name="goodsid" hidden="true">
						<?php
						if(is_array($goodslist)){	
							foreach($goodslist as $goodsinfo){
								if($goodsinfo['id'] == $info['option']){
						?>
									<option selected="true" value="<?php echo $goodsinfo['id'] ?>"><?php echo $goodsinfo['goods_name'] ?></option>
						<?php
								}else{
						?>
									<option value="<?php echo $goodsinfo['id'] ?>"><?php echo $goodsinfo['goods_name'] ?></option>
						<?php
								}
							}
						}
						?>
						</select>
						
					</td>
				</tr>



				<tr>
					<th>品牌LOGO</th>  
					<td>
						<div class='upload-pic img-wrap'><input type='hidden' name='thumb' id='thumb' value='<?php echo $info['img'];?>'>
							<a href='javascript:void(0);' onclick="flashupload('thumb_images', '附件上传','thumb',thumb_images,'1,jpg|jpeg|gif|png|bmp,1,,,0','content','6','<?php echo $authkey;?>');return false;">
			<img src='<?php if($info['img']) { echo $info['img']; } else { echo "statics/images/icon/upload-pic.png"; } ?>' id='thumb_preview' width='135' height='113' style='cursor:hand' /></a><input type="button" style="width: 66px;" class="button" onclick="crop_cut_thumb($('#thumb').val());return false;" value="裁切图片"><input type="button" style="width: 66px;" class="button" onclick="$('#thumb_preview').attr('src','statics/images/icon/upload-pic.png');$('#thumb').val(' ');return false;" value="取消图片"><script type="text/javascript">function crop_cut_thumb(id){
	if (id=='') { alert('请先上传缩略图');return false;}
	window.top.art.dialog({title:'裁切图片', id:'crop', iframe:'index.php?m=content&c=content&a=public_crop&module=content&catid='+0+'&picurl='+encodeURIComponent(id)+'&input=thumb&preview=thumb_preview', width:'680px', height:'480px'}, 	function(){var d = window.top.art.dialog({id:'crop'}).data.iframe;
	d.uploadfile();return false;}, function(){window.top.art.dialog({id:'crop'}).close()});
};</script>
						</div>
					</td>
				</tr>

                
			</tbody>
		</table>
        </fieldset>
        <div class="bk15"></div>
        
	</div>
<input class="dialog" name="dosubmit" id="dosubmit" type="submit" value="确认"/>

</div>

</div>
</div>
</form>

</body>
</html>

<script language="javascript" type="text/javascript" src="<?php echo JS_PATH?>swfupload/swf2ckeditor.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo JS_PATH?>content_addtop.js"></script>
<script>
$(document).ready(function(){
	var typeid = $("#type").val(); 
		if(typeid == 2){
			$("#goodsid").show();
		}else{
			$("#goodsid").hide();
		}
});

$("#type").change(function(){
		var typeid = $("#type").val(); 
		if(typeid == 2){
			$("#goodsid").show();
		}else{
			$("#goodsid").hide();
		}
	});

</script>
