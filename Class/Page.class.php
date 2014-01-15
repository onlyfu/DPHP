<?php
/**
 * DPhp
 * Page Class
 * Author: only.fu <fuwy@foxmail.com>
 * Update: 14-01-14
 */

class Page {
    //数据总条数
    public $_dataNum;
    //每页数据条数
    public $_pageDataNum=10;
    //当前页
    public $_page=1;
    //链接地址
    public $_pageUrl;
    //分页风格
    public $_style=1;
    //链接字符串
    public $_link='&page=';

    public function set($dataNum, $pageUrl, $page, $pageDataNum=10, $style=1, $link=''){
    	$this->_dataNum=$dataNum;
        $this->_pageUrl=$pageUrl;
        $this->_page=$page?$page:$this->_page;
        $this->_pageDataNum=$pageDataNum?$pageDataNum:$this->_pageDataNum;
        $this->_style=$style?$style:$this->_style;
        $this->_link=$link?$link:$this->_link;
        $pageNum=ceil($this->_dataNum/$this->_pageDataNum);
        $prePage=$this->_page==1?1:$this->_page-1;
        $nextPage=$this->_page==$pageNum?$pageNum:$this->_page+1;
        switch($this->_style){
            case 1:
                if($pageNum==1||$pageNum==0){
                    $template='';
                }else{
                    if($this->_page==1){
                        $pageStep1="<span class='pagetext'></span>";
                    }else{
                        $pageStep1="<a href='".$this->_pageUrl.$this->_link.$prePage.
                            "' class='pagetext'></a>";
                    }
                    if($this->_page==$pageNum){
                        $pageStep2=' <span class="pagenext"></span>';
                    }else{
                        $pageStep2=" <a href='".$this->_pageUrl.$this->_link.$nextPage.
                            "' class='pagenext'></a>";
                    }
                    $pagesNum='';
                    if($pageNum<10){
                        for($i=1;$i<=$pageNum;$i++){
                            $class=$i==$this->_page?'page_focus':'page_num';
                            $pagesNum.='<a href="'.$this->_pageUrl.$this->_link.$i.
                                '" class="'.$class.'">'.$i.'</a>';
                        }
                    }else{
                        $a=(ceil(is_int($this->_page/10)?$this->_page/10+1:$this->_page/10)-1);
                        if($a==0){
                            $start_i=1;
                            $end_i=$pageNum>=9?9:$pageNum;
                        }else{
                            $start_i=$a*10;
                            $end_i=$pageNum>=($a*10+9)?$a*10+9:$pageNum;
                            $morePreNum=$start_i-1;
                            $morePre='<a href="'.$this->_pageUrl.$this->_link.$morePreNum.
                                '" class="page">...</a>';
                        }
                        for($i=$start_i;$i<=$end_i;$i++){
                            $class=$i==$this->_page?'page_focus':'page_num';
                            $pagesNum.='<a href="'.$this->_pageUrl.$this->_link.$i.
                                '" class="'.$class.'">'.$i.'</a>';
                        }
                        if($pageNum>=($a*10+9)){
                            $moreNum=$end_i+1;
                            $more='<a href="'.$this->_pageUrl.$this->_link.$moreNum.
                                '" class="page">...</a>';
                        }
                    }
                    $template='<div class="page7">'.$pageStep1.' '.$morePre.' '.$pagesNum.' '.$more.
                        ' '.$pageStep2.'</div>';
                }
                break;
        }
        return $template;
    }
}
?>