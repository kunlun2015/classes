<?php
/**
 * 
 * @authors Amos
 * @date    2016-04-13 17:32:31
 * @copyright www.weipaidang.net
 */

require_once './Upload.class.php';
$upload = new Upload();
$rst = $upload->uploadOne($_FILES['file']);