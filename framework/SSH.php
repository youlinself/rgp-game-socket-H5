<?php
/*-----------------------------------------------------+
 * SSH封装
 * @author yeahoo2000@gmail.com
 +-----------------------------------------------------*/
class SSH{
    /**
     * 通过SSH在目标服务器上执行命令
     * @param array $cfg 服务器信息
     * @param string $cmd 命令字串
     * @return string | array
     */
    public static function exec($srv, $cmd, &$stdout, &$stderr){
        if(!$srv || !$srv->ip || !$srv->ssh_port || !$srv->ssh_user){
            throw new ErrorException("执行SSH命令[$cmd]时发生错误: 服务器'$srvId'的配置信息不完整");
        }
        $cmd = str_replace(array("\\", "\"", "`"), array("\\\\", "\\\"", "\`"), $cmd);
        $cmd = "/usr/bin/sudo /usr/bin/ssh -q -p".$srv->ssh_port." -o StrictHostKeyChecking=no ".$srv->ssh_user."@".$srv->ip." \"$cmd\"";
        return App::os()->exec($cmd, $stdout, $stderr);
    }

    /**
     * 通过SCP从本地复制文件到目标服务器
     * @param array $cfg 服务器信息
     * @param string | array $files 文件列表，需使用绝对路径
     * @param string $target 目标路径，需使用绝对路径
     * @param bool $full_output 是否返回完整输出，完整输出时返回的是数组
     * @return string | array
     */
    function scp_to($cfg, $files, $target, $full_output = false){
        $cfg->ip = get_ip($cfg);
        if(!$cfg->ip){
            App::error("复制文件到目标服务器[{$cfg['host']}:{$cfg->ip}]时出现异常: 无法取得有效IP，请检查物理机的配置");
        }
        if(is_array($files)){
            $files = implode(" ", $files);
        }
        $cmd = "/usr/bin/sudo /usr/bin/scp -P{$cfg['ssh_port']} $files {$cfg['ssh_user']}@{$cfg->ip}:$target";
        $rtn = exec($cmd, $output, $return_value);
        if(255 == $return_value){
            App::error("复制文件到目标服务器[{$cfg['host']}:{$cfg->ip}]时出现异常: 无法访问服务器");
        }
        else if(0 != $return_value){
            App::error("复制文件到目标服务器[{$cfg['host']}:{$cfg->ip}]时出现异常，未知错误[$return_value]:\n$cmd");
        }
        return $full_output? $output : $rtn;
    }

    /**
     * 通过SCP从目标服务器复制文件到本地
     * @param array $cfg 服务器信息
     * @param string | array $files 远程文件路径，需使用绝对路径
     * @param string $target 本地目标路径，需使用绝对路径
     * @param bool $full_output 是否返回完整输出，完整输出时返回的是数组
     * @return string | array
     */
    function scp_from($cfg, $files, $local_path, $full_output = false){
        if(!$cfg->host){
            App::error("从服务器复制文件到本地时出现异常: 无法取得有效IP，请检查物理机的配置");
        }
        if(is_array($files)){
            $files = implode(" ", $files);
        }
        $rtn = exec("/usr/bin/sudo /usr/bin/scp -P{$ssh->port} {$cfg->ssh_user}@{$cfg->ip}:\"$files\" $local_path", $output, $return_value);
        if(255 == $return_value){
            App::error("从服务器[{$cfg->host}]复制文件到本地时出现异常: 无法访问服务器");
        }
        else if(0 != $return_value){
            App::error("从服务器[{$cfg->host}}]复制文件本地到时出现异常: 未知错误[$return_value]");
        }
        return $full_output? $output : $rtn;
    }
}
