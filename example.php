<?php
include_once "excelclass/ExcelExport.php";


$limit = 10000; //一次查询一万条记录

$filename = 'login_log';

$title = ['id'=>'ID','user_id'=>'用户id','main_plat'=>'主渠道','sub_plat'=>'二级渠道','sdk_plat'=>'SDKID','device_code'=>'设备号','ip'=>'用户ip','login_time'=>'登录时间'];

$filter = ['login_time'=>'datetime'];

$con = mysqli_connect('127.0.0.1','root','pass','dbname') or die('数据库连接不上');

$countSql = "select count(*) from user_login_log";

$count = mysqli_fetch_assoc(mysqli_query($con,$countSql));

$total = $count['count(*)'];

$excelObj = (new ExcelExport())->filename($filename)->title($title)->filter($filter);

for ($i=0; $i < ceil($total/$limit); $i++) { //分段查询
	$offset = $i * $limit;
	$dataSql = "select id,user_id,main_plat,sub_plat,sdk_plat,device_code,ip,login_time from user_login_log limit $limit offset $offset";
	$result = mysqli_query($con, $dataSql);
	$data = [];
	while ($row = mysqli_fetch_assoc($result)) {
		$data[] = $row;
	}
	$res = $excelObj->excel($data, $i+1); //生成多个文件时的文件名后面会标注'（$i+1）'
}
mysqli_close($con);


$excelObj->fileload();
