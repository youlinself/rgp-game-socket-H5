<?php
/*-----------------------------------------------------+
 * 视图(模板)处理
 * @author yeahoo2000@gmail.com
 +-----------------------------------------------------*/
class View{
    private $tplFile;
    private $layoutFile;
    private $blocks = [];
    private $blockStack = [];
    private $path;
    private $baseUrl;
    public $vars;
    // 缓存时间，0:不生成缓存文件 -1:永久缓存
    public $cacheTime;
    // 缓存文件名，相对于VAR_DIR路径
    public $cacheFile;

    public function __construct(){
        $this->path = ROOT."/tpl";
        $this->vars = new ObjectBaseArray();
        $this->baseUrl = App::getBaseUrl();
    }

    public function setTpl($name){
        $file = "{$this->path}/{$name}.tpl.php";
        if(!file_exists($file)){
            throw new ErrorException("找不到模板文件:$file");
        }
        $this->tplFile = $file;
    }

    public function compile(){
        if(empty($this->tplFile)){
            throw new ErrorException("尝试编译一个未设置模板文件的视图");
        }

        $eReport = error_reporting();
        error_reporting(E_ALL ^E_NOTICE);
        extract($this->vars->getArray());
        ob_start();
        @include $this->tplFile;
        $content = ob_get_clean();
        error_reporting($eReport);

        if($this->layoutFile){
            ob_start();
            include $this->layoutFile;
            $content = ob_get_clean();
        }

        if(0 != $this->cacheTime && strlen($this->cacheFile)){
            OS::writeFile($this->cacheFile, $contents);
        }
        return $content;
    }

    public function render($name = null){
        if(($this->cacheTime < 0 && file_exists($this->cacheFile)) || (time() - @filemtime($this->cacheFile)) <= $this->cacheTime){
            include $this->cacheFile;
            return;
        }
        if(!is_null($name)) $this->setTpl($name);
        echo $this->compile();
    }

    public function cleanCache(){
        OS::removeFile($this->cacheFile);
    }

    public function layout($name){
        $file = "{$this->path}/{$name}.layout.php";
        if(!file_exists($file)){
            throw new ErrorException("找不到模板布局文件:$file");
        }
        $this->layoutFile = $file;
    }

    public function block($name){
        if(isset($this->blocks[$name])){
            echo $this->blocks[$name];
            return;
        }
        // 如果未指定区块内容，则自动查找相应的区块定义文件
        $file = "{$this->path}/{$name}.block.php";
        if(!file_exists($file)) return;
        include $file;
    }

    public function blockStart($name){
        array_push($this->blockStack, $name);
        ob_start();
    }

    public function blockEnd($name){
        $n = array_pop($this->blockStack);
        if($n != $name){
            throw new ErrorException('区块定义有误，未配对或有交叉');
        }
        $this->blocks[$name] = ob_get_clean();
    }
}
