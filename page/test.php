<?php
/**
 * 
 * @authors Amos
 * @date    2015-08-03 09:37:54
 * @copyright www.weipaidang.net
 */

require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'Page.class.php';

$page = new Page(2, 5);
$page_nav = $page->show_page_way_3();
echo $page_nav;