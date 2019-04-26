<?php

$types = $_GET['types'];

switch ($types) {
	case '1':	//用户头像
		$imgurl2 = uploadfile_user('userheadimg');
		break;

	case '2':	//评价
		$imgurl2 = uploadfile_user('evaluation');
		break;

	case '3':	//售后
		$imgurl2 = uploadfile_user('server');
		break;

	default:
		//返回
		$result = [
			'status'=>'error',
			'code'=>-400,
			'message'=>'操作失败',
			'data'=>[
				'url'=>'',
				'tmp'=>''
			],
		];
		$result = json_encode($result , JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
		exit($result);
		break;
}



/**
 * 图片上传
 * @param  string $file_url [文件夹]
 */
function uploadfile_user($file_url){
	//设置允许上传文件后缀
	$agreetype = ['jpg','png','jpeg'];
	$count = count($_FILES["file"]["name"]);


	for ($i = 0; $i < $count; $i++) {
   		 //获取文件名称
		 //$filename = $_FILES["file"]["name"][$i];
		 $filename = $_FILES["file"]["name"];
		 //获取文件类型
		 $filetype = explode(".",$filename);
		 $type = $filetype[1];

		 //获取文件大小
		 $filesize = $_FILES["file"]["size"][$i];

		 //获取临时文件
		 //$tempfile = $_FILES["file"]["tmp_name"][$i];
		 $tempfile = $_FILES["file"]["tmp_name"];

		 //重组文件名称
		 $newsname = time().rand(10,1000).$i.'.'.$type;

		 //设置上传路径
		 $basepath = str_replace( '\\' , '/',dirname(dirname(__FILE__)));
		 $time = date('Ymd',time());
		 $savePath = $basepath.'/uploadfile/'.$file_url.'/'.$time.'/';

		 $savePath2 = APP_PATH.'uploadfile/'.$file_url.'/'.$time.'/';
		 $tmp = 'uploadfile/'.$file_url.'/'.$time.'/';




		//判断有没有这个文件夹名,然后创建文件夹
		MkFolder($savePath);

		//组装文件网络路径
		$imgurl=$savePath.$newsname;
		$imgurl2=$savePath2.$newsname;
		$tmp = $tmp.$newsname;
   		 //var_dump($imgurl);

		//允许文件类型判断
		$typecount = count($agreetype);
		for ($j = 0; $j < $typecount; $j++){
			if ($type == $agreetype[$j]){
				$temptype = $agreetype[$j];
			}
		}
		if (empty($temptype)){

			//不允许上传文件的处理方法
			//echo '不允许上传文件的处理方法';
			//exit;
			$result = [
				'status'=>'error',
				'code'=>-1,
				'message'=>'不允许上传文件的处理方法',
				'data'=>[
					'url'=>'',
					'tmp'=>''
				],
			];
			$result = json_encode($result , JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
			exit($result);
		}


		 //存储文件
		if(!copy($tempfile, $imgurl)){
			//文件上传失败的处理方法
			//echo $filename.'上传失败';
			$result = [
				'status'=>'error',
				'code'=>-2,
				'message'=>'上传失败',
				'data'=>[
					'url'=>'',
					'tmp'=>''
				],
			];
			$result = json_encode($result , JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
			exit($result);
		}

	};


	//返回
	$result = [
		'status'=>'success',
		'code'=>200,
		'message'=>'上传成功',
		'data'=>[
			'url'=>$imgurl2,
			'tmp'=>$tmp
		],
	];
	$result = json_encode($result , JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
	exit($result);
}




/**
 * 创建文件夹
 * PHP判断文件夹是否存在和创建文件夹的方法（递归创建多级目录）
 * @param  string $path [请求文件路径]
 */
function MkFolder($path){
	if(!is_readable($path)){
		MkFolder( dirname($path) );
		if(!is_file($path)) mkdir($path,0777);
	}
}







?>
