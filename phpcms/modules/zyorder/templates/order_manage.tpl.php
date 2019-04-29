	<?php
defined('IN_ADMIN') or exit('No permission resources.');
$show_dialog = 1;
include $this->admin_tpl('header', 'admin');
?>
<div class="pad-lr-10">
    <form name="searchform" action="" method="get" >
        <input type="hidden" value="zyorder" name="m">
        <input type="hidden" value="order" name="c">
        <input type="hidden" value="order_manage" name="a">
        <input type="hidden" value="<?php echo $_GET['menuid']?>" name="menuid">
        <div class="explain-col search-form">
            <select name="type" >
                <option value=""><?php echo L('please_select')?></option>
                <option value="1" <?php if ($_GET['type']==1) {?>selected<?php }?>>订单编号</option>
                <option value="2" <?php if ($_GET['type']==2) {?>selected<?php }?>>用户ID</option>
                <option value="3" <?php if ($_GET['type']==3) {?>selected<?php }?>>用户帐号</option>
                <option value="3" <?php if ($_GET['type']==4) {?>selected<?php }?>>用户手机</option>
            </select> 
            <input type="text" value="<?php echo $_GET['q']?>" class="input-text" name="q"> 
            <?php echo 下单时间?>  <?php echo form::date('start_addtime',$_GET['start_addtime'])?><?php echo L('to')?>   <?php echo form::date('end_addtime',$_GET['end_addtime'])?> 
            <select name="status">
             <option value="" selected="">全部状态</option>
             <option value="1">待支付</option>
             <option value="2">待发货</option>
             <option value="3">待收货</option>
             <option value="4">待评价</option>
             <option value="5">已评价</option>
             <option value="6">已删除</option>
         </select>

         <input type="submit" value="<?php echo L('search')?>" class="button" name="dosubmit">
     </div>
 </form>
 <form name="myform" id="myform" action="?m=zyorder&c=order&a=order_manage_del" method="post" >
    <div class="table-list">
        <table width="100%" cellspacing="0">
         <thead>
          <tr>
           <th width="35" align="center"><input type="checkbox" value="" id="check_box" onclick="selectall('id[]');"></th>
           <th align="center"><strong>ID</strong></th>
           <th align="center"><strong>订单编号</strong></th>
           <th align="center"><strong>收货人</strong></th>
           <th align="center"><strong>收货地址(省)</strong></th>
           <th align="center"><strong>收货地址(市)</strong></th>
           <th align="center"><strong>收货地址(区)</strong></th>
           <th align="center"><strong>详细地址</strong></th>
           <th align="center"><strong>收货联系方式</strong></th>
           <th align="center"><strong>添加日期</strong></th>
           <th align="center"><strong>支付方式</strong></th>
           <th align="center"><strong>状态</strong></th>
		   <th align="center"><strong>售后</strong></th>
           <th align="center"><strong>操作</strong></th>
       </tr>
   </thead>
   <tbody>
    <?php
    if(is_array($info)){
     foreach($info as $info){
      ?>

      <?php 
      if($info['fh_time']){
       $start_time = strtotime("+2 week", $info['fh_time']);
   }else{
       $start_time = time();
   }

   $end_time= time();
   ?>    

   <tr <?php if($start_time<$end_time){?> style="background-color:#ffdfdf;"<?php }?>>
      <td align="center" width="35"><input type="checkbox" name="id[]" value="<?php echo $info['id']?>"></td>
      <td align="center"><?php echo $info['id']?></td>
      <td align="center"><?php echo $info['ordersn']?></td>
      <td align="center"><?php echo $info['lx_name']?></td>
      <td align="center"><?php echo $info['province']?></td>
      <td align="center"><?php echo $info['city']?></td>
      <td align="center"><?php echo $info['area']?></td>
      <td align="center"><?php echo $info['address']?></td>
      <td align="center"><?php echo $info['lx_mobile']?></td>
      <td align="center"><?php echo date('Y-m-d H:i:s',$info['addtime']);?></td>
      <td align="center"><?php switch($info['pay_type']){ case 1:echo '余额';break;case 2:echo '支付宝';break; case 3:echo '微信';break;default:echo '待支付';break;}?></td> 
      <td align="center">
	  <?php 
	  switch($info['status']){
		  case 1:echo '待支付';break;
		  case 2:echo '待发货';break;
		  case 3:echo '待收货';break;
		  case 4:echo '待评价';break;
		  case 5:echo '已完成';break;
		  case 6:echo '已取消';break;
		  case 7:echo '申请退款';break;
		  case 8:echo '退款成功';break;
          case 9:echo '客服介入';break;
          case 10:echo '申请退货';break;
          case 11:echo '申请退货成功';break;
          case 12:echo '等待商家收获';break;
          case 13:echo '退货成功';break;
	  }
	  ?>
	  <?php echo $info['remind'];?>
	  </td>
      <td align="center">
	  <?php 
	    switch($info['shstatus']){
		   case 1:echo '同意';break;	
		   case 2:echo '拒绝';break;	
		   case 3:echo '客服介入';break;	
		   case 4:echo '待处理';break;	
		   default:echo '暂无售后';break;
        }
      ?>
        <?php if($info['shstatus'] =='4' && $info['status'] ==7){?>
            <a href="javascript:void(0);" onclick="agreetk('<?php echo $info['id']?>')"><font color="red">同意退款</font></a>/
            <a href="javascript:void(0);" onclick="refusetk('<?php echo $info['id']?>')"><font color="red">拒绝退款</font></a>
        <?php }?>
        <?php if($info['shstatus'] =='4' && $info['status'] ==10){?>
            <a href="javascript:void(0);" onclick="agreeapplyth('<?php echo $info['id']?>')"><font color="red">同意退货申请</font></a>/
            <a href="javascript:void(0);" onclick="refuseapplyth('<?php echo $info['id']?>')"><font color="red">拒绝退货申请</font></a>
        <?php }?>
        <?php if($info['shstatus'] =='4' && $info['status'] ==12){?>
            <a href="javascript:void(0);" onclick="agreeth('<?php echo $info['id']?>')"><font color="red">同意退货</font></a>/
            <a href="javascript:void(0);" onclick="refuseth('<?php echo $info['id']?>')"><font color="red">拒绝退货</font></a>
        <?php }?>
	  </td>
      <td align="center">
        <?php if($info['status'] =='2'){?>
        <a href="javascript:void(0);" onclick="orderfh('<?php echo $info['id']?>')"><font color="#333">发货</font></a>/
        <?php }?>
        <?php if($info['status'] =='5'){?>
        <?php }?>
        <a href="javascript:void(0);" onclick="view2('<?php echo $info['id']?>')"><font color="blue">物流信息</font></a>
        /<a href="javascript:void(0);" onclick="view('<?php echo $info['id']?>')"><font color="#006600">商品详情</font></a>/
    
    </td>
</tr>
<?php
}
}
?>
</tbody>
</table>
</div>
<div class="btn"> <label for="check_box"><?php echo L('selected_all')?>/取消</label>
    <input type="submit" class="button" name="dosubmit" onClick="document.myform.action='?m=zyorder&c=order&a=order_manage_del'" value="批量删除"/></div>
    <div id="pages"><?php echo $pages?></div>
</form>
</div>





<script>
    function orderfh(id) {
      window.top.art.dialog({
        id:'view',
        iframe:'?m=zyorder&c=order&a=order_manage_ddfh&id='+id, 
        title:'订单发货', 
        width:'1000', 
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
  function view(id) {
    window.top.art.dialog({
        id:'view',
        iframe:'?m=zyorder&c=order&a=order_manage_shopinfo&id='+id, 
        title:'商品详情', 
        width:'1000', 
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
function view2(id) {
    window.top.art.dialog({
        id:'view',
        iframe:'?m=zyorder&c=order&a=order_manage_wlxx&id='+id, 
        title:'物流信息', 
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
function view3(id) {
    window.top.art.dialog({
        id:'view',
        iframe:'?m=zyorder&c=order&a=order_manage_userinfo&id='+id, 
        title:'订单详细信息', 
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
function view4(id) {
    window.top.art.dialog({
        id:'view',
        iframe:'?m=zyorder&c=order&a=order_manage_view4&id='+id, 
        title:'评价信息', 
        width:'1000', 
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

//同意退款
function agreetk(id){
    $.ajax({
        url:'?m=zyorder&c=order&a=agree_refuse_tk&pc_hash=<?php echo $_SESSION['pc_hash'];?>',
        data:{
            'id':id,
            'isagree':1
        },
        dataType:'json',
        type:'post',
        success:function(res){
            alert(res.message);
            location.reload();
        },
    });
}
//拒绝退款
function refusetk(id){
    $.ajax({
        url:'?m=zyorder&c=order&a=agree_refuse_tk&pc_hash=<?php echo $_SESSION['pc_hash'];?>',
        data:{
            'id':id,
            'isagree':2
        },
        dataType:'json',
        type:'post',
        success:function(res){
            alert(res.message);
            location.reload();
        },
    });
}
//同意申请的退货
function refusetk(id){
    $.ajax({
        url:'?m=zyorder&c=order&a=agree_refuse_apply_th&pc_hash=<?php echo $_SESSION['pc_hash'];?>',
        data:{
            'id':id,
            'isagree':1
        },
        dataType:'json',
        type:'post',
        success:function(res){
            alert(res.message);
            location.reload();
        },
    });
}
//拒绝申请的退货
function refusetk(id){
    $.ajax({
        url:'?m=zyorder&c=order&a=agree_refuse_apply_th&pc_hash=<?php echo $_SESSION['pc_hash'];?>',
        data:{
            'id':id,
            'isagree':2
        },
        dataType:'json',
        type:'post',
        success:function(res){
            alert(res.message);
            location.reload();
        },
    });
}
//同意退货
function agreeth(id){
    $.ajax({
        url:'?m=zyorder&c=order&a=agree_refuse_th&pc_hash=<?php echo $_SESSION['pc_hash'];?>',
        data:{
            'id':id,
            'isagree':1
        },
        dataType:'json',
        type:'post',
        success:function(res){
            alert(res.message);
            location.reload();
        },
    });
}
//拒绝退货
function refuseth(id){
    $.ajax({
        url:'?m=zyorder&c=order&a=agree_refuse_th&pc_hash=<?php echo $_SESSION['pc_hash'];?>',
        data:{
            'id':id,
            'isagree':2
        },
        dataType:'json',
        type:'post',
        success:function(res){
            alert(res.message);
            location.reload();
        },
    });
}
</script>


<script>
    function edit(id) {
        window.top.art.dialog({
            id:'edit',
            iframe:'?m=zyorder&c=order&a=order_manage_ddfh&id='+id, 
            title:'物流单号信息', 
            width:'400', 
            height:'200', 
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
</script>


</body>
</html>
