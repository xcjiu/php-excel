<?php
namespace app\common\tools; //如果是在框架里面引用最好还是通过命名空间来使用，如果不用请注释或删除这一行
use \ZipArchive;
// +-----------------------------------------------------
// | 数据转换成excel文件导出类，生成多文件打包成zip导出
// +-----------------------------------------------------
// | author: xcjiu
// +-----------------------------------------------------
// | github: https://github.com/xcjiu/php-excel
// +-----------------------------------------------------
class ExcelExport
{
  //字段对应的标题
  private $title = [];
  //文件名
  private $filename = '';
  //字段值过滤器
  private $filter = []; 
  //存储文件的临时目录
  private $stodir = '/tmp/';

  /**
   * 指定临时存储路径
   * 请确定这个路径有读写权限
   */
  public function tmpdir($dir)
  {
    if(substr($dir, -1) != '/'){
      $dir .= '/';
    }
    $this->stodir = $dir;
    if(!is_dir($this->stodir)){
      mkdir($this->stodir);
    }
    return $this;
  }

  /**
   * 生成 excel 数据表文件
   * @param  array  $data 要导出的数据
   * @return bool
   */
  public function excel(array $data=[], $i=1) 
  {  
    set_time_limit(0);
    header("Content-type: text/html; charset=utf-8");
    if($data && is_array($data)){
      $filename = $this->filename ? $this->filename : date('Y_m_d');
      $filter = $this->filter;
      $current = (array)current($data);
      if(is_array($current)){
        $filePath = $this->stodir . $filename . "($i)" . '.csv';
        $fp = fopen($filePath, 'a');
        $columns = $this->titleColumn(array_keys($current));
        fputcsv($fp, $columns);
        foreach ($data as &$row) {
          foreach ($row as $k => &$v) {
            if(!isset($columns[$k])){
              unset($row[$k]);
            }
            if(isset($filter[$k])){
              if($filter[$k]=='datetime'){
                $v = date("Y-m-d H:i:s",$v);
              }
              if($filter[$k]=='date'){
                $v = date("Y-m-d",$v);
              }
              if(is_array($filter[$k])){
                $v = isset($filter[$k][$v]) ? $filter[$k][$v] : $v;
              }
            }
          }
          fputcsv($fp, $row);
        }
        fclose($fp);
        unset($data);
        return true;
      }
    }
    return false;
  }

  /**
   * 打包好zip文件并导出
   * @param  [type] $filename [description]
   * @return [type]           [description]
   */
  public function fileload()
  {
    $zipname = $this->stodir . '.zip';
    $zipObj = new ZipArchive();
    if($zipObj->open($zipname, ZipArchive::CREATE) === true){
      $res = false;
      foreach(glob($this->stodir . "*") as $file){ 
        $res = $zipObj->addFile($file, substr($file, strrpos($file, '/') + 1));
      }
      $zipObj->close();
      if($res){
        header ("Cache-Control: max-age=0");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment;filename =" . $zipname);
        header('Content-Type: application/zip');
        header('Content-Transfer-Encoding: binary');
        header ('Content-Length: ' . filesize($zipname));

        @readfile($zipname);//输出文件;
        //清理临时目录和文件
        $this->deldir($this->stodir); 
        @unlink($zipname);
        ob_flush();
        flush();
      }else{
        $this->deldir($this->stodir); 
        ob_flush();
        flush();
        die('暂无文件可下载！');
      }
    }else{
      $this->deldir($this->stodir); 
      ob_flush();
      flush();
      die('文件压缩失败！');
    }
    exit();
  }

  /**
   * 清理目录，删除指定目录下所有内容及自身文件夹
   * @param  [type] $dir [description]
   * @return [type]       [description]
   */
  private function deldir($dir)
  {
      if(is_dir($dir)){
          foreach(glob($dir . '*') as $file){ 
              if(is_dir($file)) { 
                  $this->deldir($file); 
                  @rmdir($file);
              } else {
                  @unlink($file);
              } 
          }
         @rmdir($dir); 
      }
  }

  /**
   * 设置标题
   * @param array $title 标题参数为字段名对应标题名称的键值对数组
   * @return obj this 
   */
  public function title($title)
  {
    if($title && is_array($title)){
      $this->title = $title;
    }
    return $this;
  }

  /**
   * 设置导出的文件名
   * @param string $filename 文件名
   * @return obj this 
   */
  public function filename($filename)
  {
    $this->filename = date('Y_m_d') . (string)$filename;
    /*if(!is_dir($this->stodir . $this->filename)){
      mkdir($this->stodir . $this->filename);
    }*/
    //$this->stodir .= $this->filename . '/';
    return $this;
  }

  /**
   * 设置字段过滤器
   * @param array $filter 文件名
   * @return obj this 
   */
  public function filter($filter)
  {
    $this->filter = (array)$filter;
    return $this;
  }

  /**
   * 确保标题字段名和数据字段名一致,并且排序也一致
   * @param  array $keys  要显示的字段名数组
   * @return array 包含所有要显示的字段名的标题数组
   */
  protected function titleColumn(array $keys)
  {
    $title = $this->title;
    if($title && is_array($title)){
      $titleData = [];
      foreach ($keys as $v) {
        if(isset($title[$v])){
          $titleData[$v] = $title[$v];
          unset($title[$v]);
        }
      }
      unset($keys);
      return $titleData;
    }
    return $keys;
  }
}
