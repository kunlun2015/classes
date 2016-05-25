<?php
/**
 * 解压缩包
 * @authors Kunlun (735767227@qq.com)
 * @date    2016-04-14 17:09:22
 * @copyright Kunlun
 */
$zip = new ZipArchive; 
$res = $zip->open('test.zip'); 
if ($res === true) {     
    //解压缩到test文件夹 
    $zip->extractTo('test'); 
    $zip->close(); 
    echo 'ok'; 
} else { 
    echo 'failed, code:' . $res; 
} 