<?php
include_once "excelclass/ExcelExport.php";


$limit = 10000; //一次查询一万条记录

$filename = 'login_log';

$title = ['id'=>'ID','user_id'=>'用户id','plat'=>'渠道','username'=>'用户名','sex'=>'性别','ip'=>'用户ip','register_time'=>'注册时间'];

$filter = ['sex'=>[1=>'男', 0=>'女'], 'register_time'=>'datetime'];

$con = mysqli_connect('127.0.0.1','root','pass','dbname') or die('数据库连接不上');

$countSql = "select count(*) from user";

$count = mysqli_fetch_assoc(mysqli_query($con,$countSql));

$total = $count['count(*)'];

$excelObj = (new ExcelExport())->filename($filename)->title($title)->filter($filter);

for ($i=0; $i < ceil($total/$limit); $i++) { //分段查询
	$offset = $i * $limit;
	$dataSql = "select * from user limit $limit offset $offset";
	$result = mysqli_query($con, $dataSql);
	$data = [];
	while ($row = mysqli_fetch_assoc($result)) {
		$data[] = $row;
	}
	$res = $excelObj->excel($data, $i+1); //生成多个文件时的文件名后面会标注'（$i+1）'
}
mysqli_close($con);


$excelObj->fileload();
