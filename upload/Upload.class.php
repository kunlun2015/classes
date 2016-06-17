<?php
/**
 * 文件上传类
 * @authors Amos
 * @date    2016-03-11 16:40:21
 * @copyright www.weipaidang.net
 */

class Upload {

    private $maxSize  = 0; //上传的文件大小限制 (0-不做限制)
    private $exts     = array('jpeg', 'jpg', 'gif', 'xlsx', 'xls', 'zip'); //允许上传的文件后缀
    private $rootPath = './Uploads/'; //保存根路径
    private $savePath = ''; //保存路径
    private $saveExt; //文件保存后缀，空则使用原后缀
    private $error;   //上传错误信息
    private $mimes    = array();
    private $saveName =  array('uniqid', ''); //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
    private $callback = false; //检测文件是否存在回调，如果存在返回文件信息数组
    private $subName  = array('date', 'Y/m/d'); //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
    private $autoSub   = true; //自动子目录保存文件
    private $imagesExt = array('jpg', 'jpeg', 'gif');
    private $excelExt  = array('xlsx', 'xls');
    private $zipExt    = array('zip');
    
    function __construct(){        
        
    }

    /**
     * 单个文件上传
     * 
     *
     */
    public function uploadOne($file=''){
        $info = $this->upload(array($file));
        return $info ? $info[0] : $info;
    }


    public function upload($files){
        if($files === ''){
            $files = $_FILES;
        }

        if(empty($files)){
            $this->error('没有上传的文件！');
            return false;
        }

        //检测上传根目录
        $this->checkRootPath();        
        $files = $this->dealFiles($files);
        foreach ($files as $key => $file) {
            //检测上传文件后缀
            $file['ext'] = $this->getFileExt($file);            
            if(in_array($file['ext'], $this->imagesExt)){
                $this->savePath = $this->savePath.'images/';
            }else if(in_array($file['ext'], $this->excelExt)){
                $this->savePath = $this->savePath.'excel/';
            }else if(in_array($file['ext'], $this->zipExt)){
                $this->savePath = $this->savePath.'zip/';
            }
            //检测上传目录
            $this->checkSavePath($this->savePath);

            //上传文件检测
            if (!$this->check($file)){
                continue;
            }

            /* 获取文件hash */
            if($this->hash){
                $file['md5']  = md5_file($file['tmp_name']);
                $file['sha1'] = sha1_file($file['tmp_name']);
            }            
            
            /* 调用回调函数检测文件是否存在 */
            /*
            $data = call_user_func($this->callback, $file);
            if( $this->callback && $data ){
                if ( file_exists('.'.$data['path'])  ) {
                    $info[$key] = $data;
                    continue;
                }elseif($this->removeTrash){
                    call_user_func($this->removeTrash,$data);//删除垃圾据
                }
            }*/
            
            /* 生成保存文件名 */
            $savename = $this->getSaveName($file);
            if(false == $savename){
                continue;
            } else {
                $file['savename'] = $savename;
            }

            /* 检测并创建子目录 */
            $subpath = $this->getSubPath($file['name']);
            if(false === $subpath){
                continue;
            } else {
                $file['savepath'] = $this->savePath . $subpath;
            }            
            /* 对图像文件进行严格检测 */
            $ext = strtolower($file['ext']);
            if(in_array($ext, array('gif','jpg','jpeg','bmp','png','swf'))) {
                $imginfo = getimagesize($file['tmp_name']);
                if(empty($imginfo) || ($ext == 'gif' && empty($imginfo['bits']))){
                    $this->error = '非法图像文件！';
                    continue;
                }
            }            
            $this->save($file, $replace=true);
            $path[] = $file['savepath'].$file['savename'];
        }
        
        return $path;        
    }

    private function getFileExt($file){
        $file['name']  = strip_tags($file['name']);        
        /* 获取上传文件后缀，允许上传无后缀文件 */
        $file['ext'] = pathinfo($file['name'], PATHINFO_EXTENSION);        
        return $file['ext'];
    }

    /**
     * 检测上传根目录
     * @param string $rootpath   根目录
     * @return boolean true-检测通过，false-检测失败
     */
    private function checkRootPath(){
        if(!(is_dir($this->rootPath) && is_writable($this->rootPath))){
            $this->error('上传根目录不存在！请尝试手动创建:'.$this->rootPath);
        }
        return true;        
    }

    /**
     * 检测上传目录
     * @param  string $savepath 上传目录
     * @return boolean          检测结果，true-通过，false-失败
     */
    public function checkSavePath($savepath){
        /* 检测并创建目录 */        
        if (!$this->mkdir($savepath)) {
            return false;
        } else {
            /* 检测目录是否可写 */
            if (!is_writable($this->rootPath . $savepath)) {
                $this->error('上传目录 ' . $savepath . ' 不可写！');
                return false;
            } else {
                return $save_path;
            }
        }        
    }

    /**
     * 创建目录
     * @param  string $savepath 要创建的穆里
     * @return boolean          创建状态，true-成功，false-失败
     */
    public function mkdir($savepath){
        $dir = $this->rootPath . $savepath;
        if(is_dir($dir)){
            return true;
        }

        if(mkdir($dir, 0777, true)){
            return true;
        } else {
            $this->error("目录 {$savepath} 创建失败！");
            return false;
        }
    }

    /**
     * 转换上传文件数组变量为正确的方式
     * @access private
     * @param array $files  上传的文件变量
     * @return array
     */
    private function dealFiles($files) {
        $fileArray  = array();
        $n          = 0;
        foreach ($files as $key=>$file){
            if(is_array($file['name'])) {
                $keys       =   array_keys($file);
                $count      =   count($file['name']);
                for ($i=0; $i<$count; $i++) {
                    $fileArray[$n]['key'] = $key;
                    foreach ($keys as $_key){
                        $fileArray[$n][$_key] = $file[$_key][$i];
                    }
                    $n++;
                }
            }else{
               $fileArray = $files;
               break;
            }
        }
       return $fileArray;
    }

    /**
     * 检查上传的文件
     * @param array $file 文件信息
     */
    private function check($file) {
        /* 文件上传失败，捕获错误代码 */
        if ($file['error']) {
            $this->error($file['error']);
            return false;
        }

        /* 无效上传 */
        if (empty($file['name'])){
            $this->error('未知上传错误！');
        }

        /* 检查是否合法上传 */
        if (!is_uploaded_file($file['tmp_name'])) {
            $this->error('非法上传文件！');
            return false;
        }

        /* 检查文件大小 */
        if (!$this->checkSize($file['size'])) {
            $this->error('上传文件大小不符！');
            return false;
        }

        /* 检查文件Mime类型 */
        //TODO:FLASH上传的文件获取到的mime类型都为application/octet-stream
        if (!$this->checkMime($file['type'])) {
            $this->error('上传文件MIME类型不允许！');
            return false;
        }

        /* 检查文件后缀 */
        if (!$this->checkExt($file['ext'])) {
            $this->error('上传文件后缀不允许');
            return false;
        }

        /* 通过检测 */
        return true;
    }

    /**
     * 检查文件大小是否合法
     * @param integer $size 数据
     */
    private function checkSize($size) {
        if($this->maxSize === 0){
            return true;
        }
        return !($size > $this->maxSize) || (0 == $this->maxSize);
    }

    /**
     * 检查上传的文件MIME类型是否合法
     * @param string $mime 数据
     */
    private function checkMime($mime) {
        return empty($this->mimes) ? true : in_array(strtolower($mime), $this->mimes);
    }

    /**
     * 检查上传的文件后缀是否合法
     * @param string $ext 后缀
     */
    private function checkExt($ext) {
        return empty($this->exts) ? true : in_array(strtolower($ext), $this->exts);
    }

    /**
     * 根据上传文件命名规则取得保存文件名
     * @param string $file 文件信息
     */
    private function getSaveName($file) {
        $rule = $this->saveName;
        if (empty($rule)) { //保持文件名不变
            /* 解决pathinfo中文文件名BUG */
            $filename = substr(pathinfo("_{$file['name']}", PATHINFO_FILENAME), 1);
            $savename = $filename;
        } else {
            $savename = $this->getName($rule, $file['name']);
            if(empty($savename)){
                $this->error('文件命名规则错误！');
                return false;
            }
        }

        /* 文件保存后缀，支持强制更改文件后缀 */
        $ext = empty($this->config['saveExt']) ? $file['ext'] : $this->saveExt;

        return $savename . '.' . $ext;
    }

    /**
     * 获取子目录的名称
     * @param array $file  上传的文件信息
     */
    private function getSubPath($filename) {
        $subpath = '';
        $rule    = $this->subName;
        if ($this->autoSub && !empty($rule)) {
            $subpath = $this->getName($rule, $filename) . '/';

            if(!empty($subpath) && !$this->mkdir($this->savePath . $subpath)){
                
                return false;
            }
        }
        return $subpath;
    }

    /**
     * 根据指定的规则获取文件或目录名称
     * @param  array  $rule     规则
     * @param  string $filename 原文件名
     * @return string           文件或目录名称
     */
    private function getName($rule, $filename){
        $name = '';
        if(is_array($rule)){ //数组规则
            $func     = $rule[0];
            $param    = (array)$rule[1];
            foreach ($param as &$value) {
               $value = str_replace('__FILE__', $filename, $value);
            }
            $name = call_user_func_array($func, $param);
        } elseif (is_string($rule)){ //字符串规则
            if(function_exists($rule)){
                $name = call_user_func($rule);
            } else {
                $name = $rule;
            }
        }
        return $name;
    }
    
    /**
     * 保存指定文件
     * @param  array   $file    保存的文件信息
     * @param  boolean $replace 同名文件是否覆盖
     * @return boolean          保存状态，true-成功，false-失败
     */
    public function save($file, $replace=true) {
        $filename = $this->rootPath . $file['savepath'] . $file['savename'];

        /* 不覆盖同名文件 */ 
        if (!$replace && is_file($filename)) {
            $this->error('存在同名文件' . $file['savename']);
            return false;
        }

        /* 移动文件 */
        if (!move_uploaded_file($file['tmp_name'], $filename)) {
            $this->error('文件上传保存错误！');
            return false;
        }
        
        return true;
    }

    /**
     * 返回错误信息
     * json 格式
     */
    private function error($msg){
        $return = array('status' => 'fail', 'msg' => $msg);
        exit(json_encode($return));
    }

}