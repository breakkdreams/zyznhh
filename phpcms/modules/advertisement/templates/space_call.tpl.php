<?php 
defined('IN_ADMIN') or exit('No permission resources.');
$show_header = $show_validator = $show_scroll = 1; 
include $this->admin_tpl('header', 'admin');
?>
<div class="pad-10">
<h2 class="title-1 f14 lh28">(<?php echo $r['name'];?>)<?php echo L('get_code_space')?></h2>
<div class="bk10"></div>
<div class="explain-col">
<strong><?php echo L('explain')?>：</strong><br />
<?php echo L('notice')?>
</div>
<div class="bk10"></div>
<?php if($r['type']=='code') {?>
<fieldset>
	<legend><?php echo L('one_way')?></legend>
    <?php echo L('js_code')?><font color='red'><?php echo L('this_way_stat_show')?></font><br />
<input name="jscode1" id="jscode1" value='<?php echo $r['path']?>' style="width:416px"> <input type="button" onclick="$('#jscode1').select();document.execCommand('Copy');" value="<?php echo L('copy_code')?>" class="button" style="width:114px">
</fieldset>
<?php } else {?>
<fieldset>
	<legend><?php echo L('one_way')?></legend>
    <?php echo L('js_code')?><font color='red'><?php echo L('this_way_stat_show')?></font><br />
<input name="jscode1" id="jscode1" value='<script language="javascript" src="{APP_PATH}index.php?m=advertisement&c=index&a=show_advertisement&id=<?php echo $r['spaceid']?>"></script>' style="width:410px"> <input type="button" onclick="$('#jscode1').select();document.execCommand('Copy');" value="<?php echo L('copy_code')?>" class="button" style="width:114px">
</fieldset>
<div class="bk10"></div>
<fieldset>
	<legend><?php echo L('second_code')?></legend>
    <?php echo L('js_code_html')?><br />
<input name="jscode2" id="jscode2" value='<script language="javascript" src="{APP_PATH}caches/<?php echo $r['path']?>"></script>' style="width:410px">
 <input type="button" onclick="$('#jscode2').select();$('#jscode2').val().execCommand('Copy');" class="button"  style="width:114px" value="<?php echo L('copy_code')?>">
</fieldset>
<!--api调用-->
<fieldset>
    <legend><?php echo L('调用api')?></legend>
    <?php echo L('api调用代码')?><br />
    <input name="apicode" id="apicode" value='<?php echo APP_PATH.'index.php?m=advertisement&c=api&a=create_api&id='.$r['spaceid'].'&type='.$r['type']?>' style="width:416px"> <input type="button" onclick="$('#apicode').select();document.execCommand('Copy');" value="<?php echo L('copy_code')?>" class="button" style="width:114px">
</fieldset>
<!--api调用-->
<!--点击数api调用-->
<fieldset>
    <legend><?php echo L('调用点击数api')?></legend>
    <?php echo L('点击数api调用代码')?><font color='red'><?php echo '  此方式需要添加id的参数方可生效';?></font><br />
    <input name="click_apicode" id="click_apicode" value='<?php echo APP_PATH.'index.php?m=advertisement&c=api&a=advertisement_click&id='.$r['id']?>' style="width:416px"> <input type="button" onclick="$('#click_apicode').select();document.execCommand('Copy');" value="<?php echo L('copy_code')?>" class="button" style="width:114px">
</fieldset>
<!--点击数api调用-->
<?php } ?>
</div>
</body>
</html>