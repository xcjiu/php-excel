# php-excel
数据转换成Excel导出应用

## 非常简洁的一个 excel 导出封装，只要查询速度快，一百万数据量几十秒可导出并下载，生成多个excel文件并打包成zip通过浏览器下载
## 服务器临时生成的文件和目录会在下载后全部清除 
#### 具体使用说明：把excel文件放入你的项目扩展目录，请确保excel目录有读写权限，并已安装ZipArchive压缩扩展，PHP>= 5.2.0
```
use excel\excelclsss\ExcelExport;

//初始化并配置文件名，标题，字段值过滤器, 这些方法调用顺序随意
$excelObj = (new ExcelExport())->filename($filename)->title($title)->filter($filter);

//你的数据查询
......

//生成excel文件
$excelObj->excel($data, $i=1);

//打包zip并下载
$excelObj->fileload();
```
#### 具体方法参数说明
filename($filename)

$filename string 为字符串类型，配置这个文件名时不要加具体日期拼接，因为会自动生成 2018_08_08filename 模式的文件名
#
title($title)

$title array 为字段名对应标题的键值对数组, 如果标题字段数据中不存在则会忽略，如$title = ['user_id'=>'用户id','username'=>'用户名']
#
filter($filter)

$filter array 这个是用来做字段值过滤的，支持时间截转换成 datetime（Y-m-d H:i:s） 或 date（Y-m-d） 格式的输出,如：
```
$filter = ['sex'=>[1=>'男', 0=>'女'], 'login_time'=>'datetime'];
sex 该字段值为数字要转化成不同的中文，字段值会根据过滤器中的配置来显示男或女
login_time 该字段值查询出来是int类型的时间截，配置了 datetime 则会转换成具体时间格式来输出
```
#
excel($data, $i=1)

$data array 要导出的数据，为一个二唯数组，如果是一唯数组不做处理，因为只有一条数据不需要做文件导出

$i int 这个默认值是 1，用来分隔文件用的。如果只有一个文件输出，不需要传此参数，如果是生成多个文件则需要传入该参数，并且每个文件名数字参数都不同
#
fileload()  最后一步，不需要参数，自动打包zip下载并清理临时文件
#### 下面是一个demo
```
<?php
include_once "excelclass/ExcelExport.php";

$limit = 10000; //一次查询一万条记录

$filename = 'login_log';

$title = ['id'=>'ID','user_id'=>'用户id','plat'=>'渠道','username'=>'用户名','sex'=>'性别','ip'=>'用户ip','register_time'=>'注册时间'];

$filter = ['register_time'=>'datetime'];

$con = mysqli_connect('127.0.0.1','root','pass','dbname') or die('数据库连接不上');

$countSql = "select count(*) from user";

$count = mysqli_fetch_assoc(mysqli_query($con,$countSql));

$total = $count['count(*)'];

$excelObj = (new ExcelExport())->filename($filename)->title($title)->filter($filter);

for ($i=0; $i < ceil($total/$limit); $i++) { //分段查询, 一次$limit=10000条
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
```
