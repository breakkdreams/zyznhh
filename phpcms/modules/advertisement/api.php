<?php 
defined('IN_PHPCMS') or exit('No permission resources.');
class api {
	function __construct() {
        $this->db = pc_base::load_model('advertisement_model');
        $this->db_s = pc_base::load_model('advertisement_space_model');
        $this->s_db = pc_base::load_model('advertisement_stat_model');
	}
	
	public function init() {
		
	}

    /**
     * 创建api
     */
    public function create_api() {
        $json=array();
        if(!$_GET['id']){
            $json['status']='failure';
            $json['code']='-1';
            $json['message']='广告位ID为空';
            echo "<pre>";
            exit(json_encode($json,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
            echo  "</pre>";
        }else{
            $id = intval($_GET['id']);
        }
        if(!$_GET['type']){
            $json['status']='failure';
            $json['code']='-2';
            $json['message']='广告类型为空';
            echo "<pre>";
            exit(json_encode($json,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
            echo  "</pre>";
        }else{
            $type =$_GET['type'];
        }

        $siteid = get_siteid();
        $now = SYS_TIME;
        $where = " `spaceid`='".$id."' AND `siteid`='".$siteid."' AND `disabled`=0 AND `startdate`<='".$now."' AND (`enddate`>='".$now."' OR `enddate`=0)";
        if($type=='index'||$type=='banner'or$type=='couplet'or$type=='fixure'or$type=='float'){
            $pinfo = $this->db->get_one($where, '*', '`listorder` ASC, `id` DESC');
            if($pinfo) {
                $pinfo['setting'] = string2array($pinfo['setting']);
            }
            $_GET['siteid']=get_siteid();
            $_GET['spaceid']=$id;
            $_GET['id'] = $pinfo['id'];
            $this->show();
        }else {
            $pinfo = $this->db->select($where, '*', '', '`listorder` ASC, `id` DESC');
            if($pinfo) {
                foreach ($pinfo as &$value) {
                    $value['setting'] = string2array($value['setting']);
                }
            }
            $_GET['siteid']=get_siteid();
            $_GET['spaceid']=$id;
            foreach ($pinfo as $k=>$v) {
                $_GET['id'] = $pinfo[$k]['id'];
                $this->show();
            }
        }

        if($pinfo){
            //http://localhost/pc/index.php?m=advertisement&c=index&a=show&siteid=1&spaceid=12&id=12

            $json['status']='success';
            $json['code']='200';
            $json['message']='操作成功';
            $json['data']=$pinfo;
        }else{
            $json['status']='failure';
            $json['code']='-200';
            $json['message']='广告数据为空或已禁用';
        }

        echo "<pre>";
        exit(json_encode($json,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
        echo  "</pre>";
    }

    /**
     * 统计广告点击次数
     *
     */
    public function advertisement_click() {
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
		if(!$id){
			$json['status']='failure';
            $json['code']='-1';
            $json['message']='广告ID为空';
			echo "<pre>";
			exit(json_encode($json,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			echo  "</pre>";
		}
        $r = $this->db->get_one(array('id'=>$id));
        if (!is_array($r) && empty($r)){
			$json['status']='failure';
            $json['code']='-2';
            $json['message']='广告ID不存在';
			echo "<pre>";
			exit(json_encode($json,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
			echo  "</pre>";
		}
        $ip_area = pc_base::load_sys_class('ip_area');
        $ip = ip();
        $area = $ip_area->get($ip);
        $username = param::get_cookie('username') ? param::get_cookie('username') : '';
        if($id) {
            $siteid = get_siteid();
            $this->s_db->insert(array('siteid'=>$siteid, 'pid'=>$id, 'username'=>$username, 'area'=>$area, 'ip'=>$ip, 'referer'=>safe_replace(HTTP_REFERER), 'clicktime'=>SYS_TIME, 'type'=> 1));
        }
        $res=$this->db->update(array('clicks'=>'+=1'), array('id'=>$id));
		if($res){
			$json['status']='success';
            $json['code']='200';
            $json['message']='操作成功';
		}else{
			$json['status']='failure';
            $json['code']='-200';
            $json['message']='操作失败';
		}
		echo "<pre>";
		exit(json_encode($json,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
		echo  "</pre>";
    }

    /**
     * js传值，统计展示次数
     */
    public function show() {
        $siteid = $_GET['siteid'] ? intval($_GET['siteid']) : get_siteid();
        $spaceid = $_GET['spaceid'] ? intval($_GET['spaceid']) : 0;
        $id = $_GET['id'] ? intval($_GET['id']) : 0;
        if (!$spaceid || !$id) {
            exit(0);
        } else {
            $this->show_stat($siteid, $spaceid, $id);
        }
    }

    /**
     * 统计广告展示次数
     * @param intval $siteid 站点ID
     * @param intval $spaceid 广告版位ID
     * @param intval $id 广告ID
     * @return boolen
     */
    protected function show_stat($siteid = 0, $spaceid = 0, $id = 0) {
        $M = new_html_special_chars(getcache('advertisement', 'commons'));
        if(isset($M[$siteid]['enablehits']) && $M[$siteid]['enablehits']==0) return true;
        //$siteid = intval($siteid);
        $spaceid = intval($spaceid);
        $id = intval($id);
        if(!$id) return false;
        if(!$siteid || !$spaceid) {
            $r = $this->db->get_one(array('id'=>$id), 'siteid, spaceid');
            $siteid = $r['id'];
            $spaceid = $r['spaceid'];
        }
        $ip = ip();
        $ip_area = pc_base::load_sys_class('ip_area');
        $area = $ip_area->get($ip);
        $username = param::get_cookie('username') ? param::get_cookie('username') : '';
        $this->db->update(array('hits'=>'+=1'), array('id'=>$id));
        $this->s_db->insert(array('pid'=>$id, 'siteid'=>$siteid, 'spaceid'=>$spaceid, 'username'=>$username, 'area'=>$area, 'ip'=>$ip, 'referer'=>safe_replace(HTTP_REFERER), 'clicktime'=>SYS_TIME, 'type'=>0));
        return true;
    }
}
?>