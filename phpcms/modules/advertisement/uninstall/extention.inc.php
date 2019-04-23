<?php 
defined('IN_PHPCMS') or exit('Access Denied');
defined('UNINSTALL') or exit('Access Denied');
$advertisement_stat = pc_base::load_model('advertisement_stat_model');
$diff1 = date('Y', SYS_TIME);		//当前年份
$diff2 = date('m', SYS_TIME);		//当前月份
$diff = ($diff1-2019)*12+$diff2;

for($y=$diff;$y>0;$y--) {
	$value = date('Ym', mktime(0, 0, 0, $y, 1, 2019));
	if($value<'201902' || !$this->db->table_exists($this->db_tablepre.'advertisement_'.$value)) break;
	$advertisement_stat->query("DROP TABLE IF EXISTS `".$this->db_tablepre."advertisement_".$value."`");
}
?>