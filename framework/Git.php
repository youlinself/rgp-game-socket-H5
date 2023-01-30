<?php
// 用法示例:
// App::git()->repo('client')->log('更新');
class Git{
    public $repo = null;

    public function repo($repo){
        if(!App::cfg()->project_root){
            throw new Exception("配置文件中没有定义项目根目录信息");
        }
        $repo = App::cfg()->project_root.'/'.$repo;
        if(!file_exists($repo)){
            throw new Exception("不存在git仓库: $repo");
        }
        $this->repo = $repo; 
        return $this;
    }

    public function cmd($args){
        if(is_null($this->repo)){
            throw new Exception("无法生成git命令，因为没有指定git仓库");
        }
        return "/usr/bin/sudo /usr/bin/git --work-tree={$this->repo} --git-dir={$this->repo}/.git ".implode(' ', $args);
    }

    public function exec($cmd, &$stdout, &$stderr){
        $cmd = $this->cmd([$cmd]);
        return App::os()->exec($cmd, $stdout, $stderr);
    }

    public function firstCommit(){
        $cmd = $this->cmd(['rev-list HEAD | tail -n 1']);
        if(0 != App::os()->exec($cmd, $stdout, $stderr)){
            throw new ErrorException("执行命令[$cmd]时发生错误: $stderr");
        }
        return trim($stdout);
    }

    public function versions($count = null){
        $args = [
            'for-each-ref',
            '--sort=-refname',
            "--format '%(objectname) %(refname)'",
            'refs/tags',
        ];
        if(is_int($count)){
            $args[] = "--count $count";
        }
        $cmd = $this->cmd($args);
        if(0 != App::os()->exec($cmd, $stdout, $stderr)){
            throw new ErrorException("执行命令[$cmd]时发生错误: $stderr");
        }
        $tags = [];
        $lines = explode("\n", trim($stdout));
        foreach($lines as $line){
            if(!preg_match("!refs/tags/(v[0-9]{6}_[0-9]{4}(_[0-9]{2}){0,1})!", $line, $matches)) continue;
            list($hash, $ref) = explode(' ', $line);
            $tag = $matches[1];
            $tags[$tag] = [
                'hash' => $hash,
                'ver' => $tag,
            ];
        }
        krsort($tags);
        return $tags;
    }

    public function log($keyword = '', $num = 0, $start = '', $end = ''){
        if(!is_numeric($num)){
            throw new ErrorException("参数num必须为整数");
        }
        $separator = '-------sparator--------';
        $args = [
            "log",
            "--format=%H%n%h%n%d%n%ce%n%at%n%s%n%b%n$separator",
        ];
        if($num){
            $args[] = "-$num";
        }
        if(!empty($keyword)){
            $args[] = "-g";
            $args[] = "--grep=\"$keyword\"";
        }
        if(!empty($start) && !empty($end)){
            $args[] = "$start...$end";
        }

        $cmd = $this->cmd($args);
        if(0 != App::os()->exec($cmd, $stdout, $stderr)){
            throw new ErrorException("执行命令[$cmd]时发生错误: $stderr");
        }

        $logs = [];
        $msgs = explode($separator, $stdout);
        foreach($msgs as $msg){
            $msg = trim($msg);
            if(empty($msg)) continue;

            $log = [];
            $lines = explode("\n", $msg);
            $hash = array_shift($lines);
            $log['files']= $this->files($hash);
            $log['h'] = array_shift($lines);
            $log['hash'] = $hash;
            $log['ref'] = array_shift($lines);
            $log['author'] = array_shift($lines);
            $log['date'] = date('Y/m/d H:i', array_shift($lines));

            // 解析git日志内容
            $msg = [];
            $tag = '';
            $idx = [];
            foreach($lines as $v){
                if(preg_match("!(^\[.*\])(.*)!", $v, $matches)){
                    $tag = str_replace(['[', ']'], ['', ''], $matches[1]);
                    if(!isset($msg[$tag])){
                        $msg[$tag] = [];
                        $idx[$tag] = 0;
                    }else{
                        $idx[$tag] += 1;
                    }
                    $msg[$tag][$idx[$tag]] = trim($matches[2]);
                }else{
                    if(empty($tag)) $tag = '其它'; // 没有包含有效标签
                    if(!isset($msg[$tag])){
                        $idx[$tag] = 0;
                        $msg[$tag] = [];
                        $msg[$tag][$idx[$tag]] = '';
                    }
                    $msg[$tag][$idx[$tag]] .= "\n".trim($v);
                }
            }
            foreach($msg as $t => $m){
                foreach($m as $k => $v){
                    $msg[$t][$k] = trim($v);
                }
            }
            $log['msg'] = $msg;

            $logs[$hash] = $log;
        }

        return $logs;
    }

    public function files($hash){
        $cmd = $this->cmd([
            "show",
            "--pretty='format:'",
            "--name-status",
            $hash,
        ]);
        if(0 != App::os()->exec($cmd, $stdout, $stderr)){
            throw new ErrorException("执行命令[$cmd]时发生错误: $stderr");
        }
        $files = array_values(array_filter(explode("\n", $stdout)));
        return $files;
    }
}
