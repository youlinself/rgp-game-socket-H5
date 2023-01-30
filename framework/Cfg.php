<?php
/*-----------------------------------------------------+
 * 配置文件加载器
 * @author yeahoo2000@gmail.com
 +-----------------------------------------------------*/
class Cfg extends ObjectBaseArray{
    const file = '/env.php';
    const app_cfg = '/lib/app.cfg.php';

    public function __construct(){
        parent::__construct();
        if(file_exists(ROOT.self::file)) $this->load();
    }

    // 加载配置文件
    public function load(){
        $cfg = require ROOT.self::file;

        // 调用ini_set设置相关php.ini项目
        if(isset($cfg['ini_set'])){
            foreach($cfg['ini_set'] as $group => $items){
                if(is_array($items)){
                    foreach($items as $key => $val){
                        ini_set($group.'.'.$key, "$val");
                    }
                }else{
                    ini_set($group, "$items");
                }
            }
        }

        $this->setArray($cfg, true);
    }

    //加载应用配置
    public function loadAppCfg(){
        $app_cfg = [];
        $app_file = ROOT.self::app_cfg;
        if(file_exists($app_file)){
            $app_cfg = require $app_file;

        }
        return $app_cfg;
    }
}
