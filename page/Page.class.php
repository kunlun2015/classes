<?php
/**
 * 分页类
 * 三种分页样式（不能ajax分页）
 */
class Page{
	private $pagesize;
	private $lastpage;
	private $totalpages;
	private $nums;
	private $numPage=1;

	function __construct($page_size,$total_nums){
		$this->pagesize=$page_size;		//每页显示的数据条数
		$this->nums=$total_nums;		//总的数据条数
		$this->lastpage=ceil($this->nums/$this->pagesize);		//最后一页
		$this->totalpages=ceil($this->nums/$this->pagesize);	//总得分页数
		if(!empty($_GET[page])){
			$this->numPage=$_GET[page];
			if(!is_int($this->numPage))	$this->numPage=(int)$this->numPage;
			if($this->numPage<1)	$this->numPage=1;
			if($this->numPage>$this->lastpage)	$this->numPage=$this->lastpage;
		}
	}

	public function show_page_way_1(){	//以"首页 上一页 下一页 尾页"形式显示		
		$url=$_SERVER["REQUEST_URI"];
		$url=parse_url($url);	//parse_url -- 解析 URL，返回其组成部分,注: 此函数对相对路径的 URL 不起作用。
		$url=$url[path];
		$str = '';
		if($this->nums > $this->pagesize){		//判断是否满足分页条件			
			$str = " 共 $this->totalpages 页 当前为第<font color=red><b>$this->numPage</b></font>页 共 $this->nums 条 每页显示 $this->pagesize 条";
			if($this->numPage==1){
				$str .= " 首页 ";
				$str .= "上一页 ";
			}
			if($this->numPage >= 2 && $this->numPage <= $this->lastpage){
				$str .= " <a href=$url?page=1>首页</a> " ;
				$str .= "<a href=$url?page=".($this->numPage-1).">上一页</a> " ;
			}

			if($this->numPage==$this->lastpage){
				$str .= "下一页 ";
				$str .= "尾页<br>";
			}

			if($this->numPage >= 1 && $this->numPage < $this->lastpage){
				$str .= "<a href=$url?page=".($this->numPage+1).">下一页</a> ";
				$str .= "<a href=$url?page=$this->lastpage>尾页</a><br> ";
			}
			return $str;
		}
		return;
	}

	public function show_page_way_2(){		//以数字形式显示"首页 1 2 3 4 尾页"		
		$url=$_SERVER["REQUEST_URI"];
		$url=parse_url($url);	//parse_url -- 解析 URL，返回其组成部分,注: 此函数对相对路径的 URL 不起作用。
		$url=$url[path];
		$str = '';
		if($this->nums > $this->pagesize){
			if($this->numPage==1)	$str.= "首页";
			else	$str.= "<a href=$url?page=1>首页</a>";
			for($i=1;$i<=$this->totalpages;$i++){
				if($this->numPage==$i){
					$str.= " ".$i." ";
				}else{
					$str.= " <a href=$url?page=$i>$i</a> ";
				}
			}
			if($this->numPage==$this->lastpage)		$str.= "尾页";
			else	$str.= "<a href=$url?page=$this->lastpage>尾页</a>";
			return $str;
		}
		return $str;
	}

	public function show_page_way_3(){
		global $c_id;
		$url=$_SERVER["REQUEST_URI"];
		$url=parse_url($url);	//parse_url -- 解析 URL，返回其组成部分,注: 此函数对相对路径的 URL 不起作用。
		$url=$url[path];
		$str = '';
		if($this->nums > $this->pagesize){		//判断是否满足分页条件			
			if($c_id){
				$str .= "到第<select name='select1' onChange=\"location.href='$url?c_id=$c_id&page='+this.value+'&pagesize=$this->pagesize'\">";
			}
			else	$str .= "到第<select name='select1' onChange=\"location.href='$url?page='+this.value+'&pagesize=$this->pagesize'\">";
			for($i = 1;$i <= $this->totalpages;$i++)
			$str .= "<option value='" . $i . "'" . (($this->numPage == $i) ? 'selected' : '') . ">" . $i . "</option>";
  			$str .= "</select>页, 每页显示";
  			if($c_id){
  				$str .= "<select name=select2 onChange=\"location.href='$url?c_id=$c_id&page=$this->numPage&pagesize='+this.value+''\">";
  			}
  			else	$str .= "<select name=select2 onChange=\"location.href='$url?page=$this->numPage&pagesize='+this.value+''\">";
			for($i = 0;$i < 5;$i++){ // 将个数定义为五种选择				
				$choice= ($i+1)*4;
				$str .= "<option value='" . $choice . "'" . (($this->pagesize == $choice) ? 'selected' : '') . ">" . $choice . "</option>";
			}
 		 	$str .= "</select>个";
 		 	return $str;
		}
		else	return;		//echo "没有下页了";
	}
}