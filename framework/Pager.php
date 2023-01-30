<?php
/**
 * User: xiaoqing Email: liuxiaoqing437@gmail.com
 * Date: 2015/3/13
 * Time: 17:22
 * 分页
 */

class Pager {
    public $pageRows = 10; //每页显示行数

    public $url = '';
    public $file_rule = '';
    public $list_rule = '';

    public $page = 0; //指定当前页，不指定将取提交的page参数
    private $totalPage = 0; //总页数
    public $totalRows = 0; //记录总数
    public $left = 2; //左边显示个数
    public $right = 7; //显示右边列表的页的个数，如 1..3 4 5
    private $req = [];


    public function __construct($param = array()){
        if(isset($param['page'])){
            $page = (int)$param['page'];
        }else{
            isset($_GET['page']) ? $page = (int)$_GET['page'] : $page = $this->page;
        }

        //处理参数
        foreach ($param as $key => $val){
            $this->$key = $val;
        }
        unset($param);
        $this->page = $page;
        if(!is_numeric($this->page)){
            $this->page = 1;
        }
        if($this->page < 1){
            $this->page = 1;
        }

        if($this->pageRows < 1){
            return;
        }
    }

    /**
     * 设置总行数
     * @param $num
     */
    public function setTotalRows($num){
        $this->totalRows = $num;
    }

    /**
     * 提供数据库分页偏移，limit offset, pageRows
     * @return int
     */
    public function getOffset(){
        return ($this->page - 1) * $this->pageRows;
    }

    /**
     * 返回每页行数
     * @return int
     */
    public function getLimit(){
        return (int)$this->pageRows;
    }

    private function getUrl($page){
        if ($this->url && $this->list_rule && $this->file_rule) { //静态
            $url = $page == 1 ? $this->url . $this->file_rule : $this->url . $this->list_rule;
            $url = str_replace('{PAGE}', $page, $url);
        }elseif($this->url){ //动态
            $url = strpos($this->url, '?') === false ? $this->url . "?page=$page" : $this->url . "&page=$page";
        }else{

            if(!$this->req){
                $this->req = $_GET;
                foreach($_POST as $key => $val){
                    $this->req[$key] = $val;
                }
                unset($this->req['page']);
            }

            $url = '?' . http_build_query($this->req) . "&page=$page";
        }

        return $url;
    }

    /**
     * 获取 1 2 3 4 5 6 7 8 9 10 ..16 这样的列表的左页数，右页数
     * @return array (curPage, left, right)
     */
    private function get_page_list(){
        $curPage   = $this->page; //当前页
        $totalRows = $this->totalRows; //总行数
        $totalPage = $this->totalPage; //总页数
        if($curPage > $totalPage){
            $curPage = $totalPage;
            $this->page = 1;
        }
        $show_right_num = $this->right;
        $show_left_num  = $this->left; //左边页的个数
        $show_total_num = $show_left_num + $show_right_num + 1;
        if($show_total_num > $totalPage){
            $left  = 1;
            $right = $totalPage;
        }else{
            if($curPage - $show_left_num > 1){
                $left = $curPage - $show_left_num;
            }else{
                $left           = 1;
                $show_right_num = $show_left_num - $curPage + $show_right_num +
                    1;
            }
            $right = $curPage + $show_right_num;
            if($right >= $totalPage){
                //如果右页数大于总页数，右页数肯定为总页数
                $right = $totalPage;
                //保证要显示的页的个数
                $left = $totalPage - $show_total_num + 1 >
                    1 ? $totalPage - $show_total_num + 1 : 1;
            }
        } //end if
        return ['curPage' => $curPage, 'left' => $left,
                     'right'   => $right];
    }


    /**
     * @return int 返回总页数
     */
    public function getTotalPage(){
        $totalPage = ceil($this->totalRows / $this->pageRows);
        if($this->totalPage && $totalPage > $this->totalPage){
            $totalPage = $this->totalPage;
        }
        $this->totalPage = $totalPage;
        return $totalPage;
    }

    /**
     * @return string 返回分页html内容
     */
    public function render(){
        $this->totalPage = $this->getTotalPage();
        //根据左页数，右页数获取相关参数
        $params = $this->get_page_list();
        $left = $params['left'];
        $right = $params['right'];

        if($this->totalPage < 2)return '';

        $css = '<div class="column right aligned"><div class="ui small pagination menu">';
        if($this->page - 1){
            $css .= '<a class="item" href="' . $this->getUrl($this->page - 1) . '"><i class="icon left arrow"></i></a>';
        }
        for($i = $left; $i <= $right; $i++){
            if ($this->page == $i)
                $css .= '<a class="active item">' . $this->page . '</a>';
            else
                $css .= '<a class="item" href="' . $this->getUrl($i) . '">' . $i . '</a>';
        }

        if($this->page < $this->totalPage){
            $css .= '<a class="item" href="' . $this->getUrl($this->page + 1) . '"><i class="icon right arrow"></i></a>';
        }
        $css .= '</div></div>';

        return $css;
    }
}