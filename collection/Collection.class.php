<?php
/**
 * 采集网站内容
 * @authors Amos
 * @date    2016-02-29 14:57:08
 * @copyright www.weipaidang.net
 */

class Collection {
    //保存路径
    public $save_path;
    //存储扩展名
    public $file_ext;
    //开始元素
    public $begin_elem;
    //结束元素
    public $end_elem;
    //目标网址
    public $target_site; 
    //每个文件夹存储的文件数量
    public $folder_size;   
    
    function __construct(){  
        header("Content-type: text/html; charset=utf-8");   
        session_start();   
    }

    public function begin(){         
        $file_save_path = $this->filePath();
        $content        = $this->getSiteContent(); 
        $this->save($file_save_path, $content);

    }

    //文件存储
    private function filePath(){
        //存储文件夹名
        if(!$_SESSION['folder_name']){
            $_SESSION['folder_name'] = 1;
        }
        //存储文件名
        if(!$_SESSION['file_name']){
            $_SESSION['file_name'] = 1;
        }else{
            $_SESSION['file_name'] += 1;
        }
        //分文件夹存储
        if($_SESSION['file_name'] > $this->folder_size){
            $_SESSION['file_name'] = 1;
            $_SESSION['folder_name'] += 1;
        }
        //存储路径
        $file_save_path = $this->save_path.$_SESSION['folder_name'].'/';
        //创建存储路径
        if(!is_dir($file_save_path)){
            mkdir($file_save_path, 0777 ,true);
        }
        $file_save_path .= $_SESSION['file_name'].'.'.$this->file_ext;
        return $file_save_path;
    }   

    //获取站点内容
    private function getSiteContent(){
        $content_all = file_get_contents($this->target_site);
        $content = $this->contentOperation($content_all);
        return $content;
    }

    //内容处理
    private function contentOperation($content_all){
        $rst = explode($this->begin_elem, $content_all);
        $rst = array_pop($rst);
        $rst = explode($this->end_elem, $rst);
        $content = strip_tags($rst[0]);
        $content = trim($content);
        return $content;
    }

    //内容写入文件
    private function save($file_save_path, $content){
        file_put_contents($file_save_path, $content);
    }
}


//session_unset();exit();
$collection = new Collection();
$collection->save_path   = './ebook/';
$collection->file_ext    = 'txt';
$collection->folder_size = 100;
$collection->target_site = 'http://news.sina.com.cn/c/nd/2016-02-28/doc-ifxpvysv4980301.shtml';
$collection->begin_elem  = '<div class="article article_16" id="artibody">';
$collection->end_elem    = '<!-- 吸顶导航结束定位标记 -->';
for($i = 0; $i< 1000; $i++){
    $rst = $collection->begin();
}
