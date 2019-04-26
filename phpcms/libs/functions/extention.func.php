<?php
/**
 * [将Base64图片转换为本地图片并保存]
 * @E-mial wuliqiang_aa@163.com
 * @TIME   2017-04-07
 * @WEB    http://blog.iinu.com.cn
 * @param  [Base64] $base64_image_content [要保存的Base64]
 * @param  [目录] $path [要保存的路径]
 */
function base64_image_content($base64_image_content,$path){
    //匹配出图片的格式
    //return preg_match ('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result);
    var_dump($path);
    if (preg_match ('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)){
        $type = $result[2];
        $new_file = $path.date('Ymd',time())."/";
        //$new_file = $path;
        if(!file_exists($new_file)){
            //检查是否有该文件夹，如果没有就创建，并给予最高权限
            mkdir($new_file, 0700);
        }
        $new_file = $new_file.time().".{$type}";
        //var_dump($result,$new_file,!file_exists($new_file),str_replace($result[1], '',$aaa));
        //var_dump(base64_decode(str_replace($result[1], '',$base64_image_content)));
        //$new_tmp_name = getcwd()."\\Work_IMG\\".$new_name;
        //$portrait_url = "http://".$_SERVER['HTTP_HOST']."/wlyl/ccc/";
        //exit;
        if (file_put_contents($new_file, base64_decode(str_replace($result[1], '',$base64_image_content)))){
            return '/'.$new_file;
        }else{
            return false;
        }
    }else{
        return false;
    }
}





?>