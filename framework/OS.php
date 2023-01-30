<?php
/*-----------------------------------------------------+
 * 执行一些操作系统相关的操作
 * @author yeahoo2000@gmail.com
 +-----------------------------------------------------*/
class OS{
    protected $cwd;
    protected $env;
    protected $descriptorspec = [
        0 => ["pipe", "r"],  // stdin
        1 => ["pipe", "w"],  // stdout
        2 => ["pipe", "w"],  // stderr
    ];

    public function __construct($cwd = '', $env = [], $descriptorspec = null){
        if(empty($cwd)){
            $this->cwd = ROOT.'/var';
        }

        $env = array_merge(['HOME' => $_SERVER['HOME']], $env);
        $this->env = new ObjectBaseArray($env);

        if(is_array($descriptorspec)){
            $this->descriptorspec = $descriptorspec;
        }
    }

    public function setCwd($cwd){
        $this->cwd = $cwd;
        return $this;
    }

    public function setEnv($env){
        $this->env = array_merge($this->env, $env);
        return $this;
    }

    /**
     * 执行一个操作系统命令，并返回退出状态码
     * 0: 表示正常退出
     * 其它值: 表示执行的命令发生了错误，具体意义要看被调用的命令
     *
     * # 支持设置环境变量
     * # 支持stdin输入设定
     * # stdout, stderr输出分离
     *
     * 使用示例:
     * 被调用的命令:test.sh
     * #!/bin/sh
     * read -p "输入循环次数:" i
     * while [ $i -gt 0 ]; do
     *     echo "stdout: $i"
     *     >&2 echo "stderr: $i"
     *     i=$(($i-1))
     *     sleep 1
     * done
     *
     * PHP调用代码:
     * $stdin = "3";
     * App::os()->exec("bash test.sh", $stdout, $stderr, $stdin);
     * echo "stdout:\n$stdout\n\nstderr:\n$stderr";
     */
    public function exec($cmd, &$stdout = '', &$stderr = '', $stdin = ''){
        App::debug("执行操作系统命令: $cmd");
        $proc = proc_open($cmd, $this->descriptorspec, $pipes, $this->cwd, $this->env->getArray());
        $txOff = 0; $txLen = strlen($stdin);
        $stdoutDone = false;
        $stderrDone = false;
        // 设置所有管道为非阻塞模式
        stream_set_blocking($pipes[0], 0);
        stream_set_blocking($pipes[1], 0);
        stream_set_blocking($pipes[2], 0);
        if($txLen == 0) fclose($pipes[0]);
        while(true){
            $rx = array();
            if(!$stdoutDone) $rx[] = $pipes[1];
            if(!$stderrDone) $rx[] = $pipes[2];

            $tx = array();
            if($txOff < $txLen) $tx[] = $pipes[0];

            $ex = null;
            // 查询管道状态
            stream_select($rx, $tx, $ex, null, null);

            // 将$stdin变量中的内容写入stdin管道
            if(!empty($tx)){
                $txRet = fwrite($pipes[0], substr($stdin, $txOff, 8192));
                if($txRet !== false) $txOff += $txRet;
                if($txOff >= $txLen) fclose($pipes[0]);
            }

            foreach($rx as $r){
                if($r == $pipes[1]){
                    // 读取stdout管道中的内容
                    $stdout .= fread($pipes[1], 8192);
                    if(feof($pipes[1])){
                        fclose($pipes[1]);
                        $stdoutDone = true;
                    }
                }else if($r == $pipes[2]){
                    // 读取stderr管道中的内容
                    $stderr .= fread($pipes[2], 8192);
                    if(feof($pipes[2])){
                        fclose($pipes[2]);
                        $stderrDone = true;
                    }
                }
            }

            if(!is_resource($proc)) break;
            if($txOff >= $txLen && $stdoutDone && $stderrDone) break;
        }
        return proc_close($proc);
    }

    /**
     * 递归删除指定目录下的所有文件和目录
     * @param string $name 目录路径或文件路径
     * @return bool 是否成功
     */
    public static function removeFile($name) {
        $path = dirname($name);
        if(is_file($name)){
            $result = @unlink($name)? true : false;
        }
        if(is_dir($name)){
            $handle = opendir($name);
            while(($file = readdir($handle)) !== false){
                if($file == '.' || $file == '..') continue;
                $dir= $name.DIRECTORY_SEPARATOR.$file;
                is_dir($dir) ? OS::removeFile($dir) : @unlink($dir);
            }
            closedir($handle);
            $result = @rmdir($name) ? true : false;
        }
        return $result;
    }

    /**
     * 写文件系统
     * @param string $file 文件名
     * @param string $content 内容
     */
    public static function writeFile($file, $content, $mode='wb'){
        $oldMask= umask(0);
        $fp= @fopen($file, $mode);
        fwrite($fp, $content);
        fclose($fp);
        umask($oldMask);
    }
}
