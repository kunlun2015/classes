<?php
/**
 * 日志类
 * @authors Amos
 * @date    2016-02-17 18:11:14
 * @copyright www.weipaidang.net
 */

class Log {

    //日志存储路径
    private $log_path;
    //单个日志文件大小
    private $log_file_size;
    //单个文件夹存储容量（滚动存储删除最早的）
    private $log_folder_size;
    //日志存储时间
    private $max_log_date;
    //分文件夹存储
    private $folder_name;
    //日志存储文件名
    private $log_file_name;
    //日志级别 1:debug,2:msg
    private $log_level;

    public function __construct($folder_name, $file_name){
        $this->folder_name = $folder_name;
        $this->log_path = dirname(__FILE__).'/log/'.$this->folder_name;
        $this->log_file_name = $file_name;

        $this->log_file_size = 1*1024*1024; //100K
        $this->log_folder_size = 30;//文件夹容量
        $this->log_level = 2;
    }

    //开发模式下记录调试日志信息
    public function debug($msg, $line_num=false){
        if($this->log_level !== 1){
            return;
        }     
        $log_file = $this->logInit('debug');
        $this->write($log_file, $msg, $line_num);
    }


    //生产模式下记录必要信息
    public function msg($msg, $line_num=false){
        $log_file = $this->logInit('msg');
        $this->write($log_file, $msg, $line_num);
    }

    //写入日志
    private function write($log_file, $msg, $line_num = false){
        if($line_num){
            $source_file_name = explode('/', $_SERVER['PHP_SELF']);
            $source_file_name = array_pop($source_file_name).":{$line_num}";
        }
        file_put_contents($log_file, date('Y-m-d H:i:s').'---'.$source_file_name.'---'.var_export($msg, true)."\r\n", FILE_APPEND);
    }

    //日志容量及存储设置
    private function logInit($save_cate){
        $save_path = $this->log_path."/{$save_cate}/".date('Y').'/'.date('m').'/'.date('d');
        //创建日志存储目录
        if(!is_dir($save_path)){
            $rst = mkdir($save_path, 0777, true);
            if(!$rst){
                exit('create folder failed!');
            }
        } 
        $file_list = $this->getFile($save_path, $save_path);
        if(!$file_list[0]){
            $log_file = $save_path.'/'.$this->log_file_name.'_1.log';
        }else{
            $log_file_count = count($file_list);
            $last_log_file = array_pop($file_list);
            preg_match('/\d+/',$last_log_file,$match_arr);
            $last_th = $match_arr[0];
            $file_size = filesize($save_path.'/'.$last_log_file);
            if($file_size > $this->log_file_size){
                if($log_file_count > $this->log_folder_size){
                    $first_file = $save_path.'/'.$file_list[0];
                    unlink($first_file);
                    $file_th = $last_th+1;                    
                    $log_file = $save_path.'/'.$this->log_file_name."_{$file_th}.log";
                }else{
                    $file_th = $last_th+1;              
                    $log_file = $save_path.'/'.$this->log_file_name."_{$file_th}.log";
                }
            }else{
                $log_file = $save_path.'/'.$this->log_file_name."_{$last_th}.log";

            }
        }
        return $log_file;
    }

    //获取文件列表
    private function getFile($dir, $path) {
        $file_name[]=NULL;
        if (false != ($handle = opendir ( $dir ))) {
            $i=0;
            while ( false !== ($file = readdir ( $handle )) ) {
                //去掉"“.”、“..”以及带“.xxx”后缀的文件
                if ($file != "." && $file != ".."&&strpos($file,".")) {
                    $file_name[$i] = $file;
                    $file_time[$i] = date("Y-m-d H:i:s",filemtime($path.'/'.$file_name[$i]));
                    if($i==100){
                        break;
                    }
                    $i++;
                }
            }
            //关闭句柄
            closedir ( $handle );
        }
        //arsort($file_time);
        if($file_name[0]){
            array_multisort($file_name,SORT_ASC,SORT_NUMERIC, $file_time);
        }        
        return $file_name;
    }
}