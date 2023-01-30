<?php
/*-----------------------------------------------------+
 * 工具包
 * @author yeahoo2000@gmail.com
 +-----------------------------------------------------*/
class Util{
    /**
     * 运行一组性能测试函数，并比较结果
     * @param int $times 执行次数
     * @param array $testers 测试项目组
     * @return null
     *
     * 使用方法示例:
     * function func($a, $b){
     *     return $a + $b;
     * }
     * $fname = 'func';
     * $params = array(4321, 1234);
     * Util::performance(1000000, array(
     *     array("直接调用函数", function($i) use($fname, $params){
     *         return func(4321, 1234);
     *     }),
     *     array("通过函数名调用函数", function($i) use($fname, $params){
     *         return $fname(4321, 1234);
     *     }),
     *     array("通过call_user_func_array调用", function($i) use($fname, $params){
     *         return call_user_func_array($fname, $params);
     *     }),
     * ));
     */
    public static function performance($times, $testers){
        $titleMaxLen = 40;
        if(self::inCLI()){
            $s = str_repeat(' ', $titleMaxLen - self::strSpace("测试项"));
            echo "测试项{$s}比值\t\t总用时(秒)\t平均用时(微秒)\t返回值\n";
        }else{
            echo "<table>\n";
            echo "<tr><th>项目</th><th>比值</th><th>总用时(秒)</th><th>平均用时(微秒)</th><th>返回值</th></tr>\n";
        }

        $base = 0;
        foreach($testers as $tester){
            list($title, $func) = $tester;
            $start = microtime(true);
            for($i = 0; $i < $times; $i++){
                $rtn = $func($i);
            }
            $total = microtime(true) - $start;
            if(!$base) $base = $total;
            $percent = $total / $base * 100;
            $avg = $total / $times * 1000 * 1000;

            ob_start();
            if(self::inCLI()){
                $s = str_repeat(' ', $titleMaxLen - self::strSpace($title));
                printf("%s%s%.2f%s\t\t%.4f sec\t%.2f us\t\t%s\n", $title, $s, $percent, '%', $total, $avg, $rtn);
            }else{
                printf("<tr><td>%s</td><td>%.2f%s</td><td>%.4f sec</td><td>%.4f us</td><td>%s</td></tr>\n", $title, $percent, '%', $total, $avg, $rtn);
            }
            ob_end_flush();

        }
        if(!self::inCLI()) echo "</table>";
    }

    /**
     * 检查当前脚本是否在命令行环境中执行
     * @return bool
     */
    public static function inCLI(){
        if(php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR'])){
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取客户端IP
     * @return string
     */
    public static function clientIp(){
        return getenv('HTTP_CLIENT_IP')?:
            getenv('HTTP_X_FORWARDED_FOR')?:
            getenv('HTTP_X_FORWARDED')?:
            getenv('HTTP_FORWARDED_FOR')?:
            getenv('HTTP_FORWARDED')?:
            getenv('REMOTE_ADDR');
    }

    /**
     * 产生一个随机字串
     * @param int $len 指定随机字串的长度
     * @param string $scope 随机字符的取值范围
     * @return string
     */
    public static function randString($len, $scope = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890") {
        $strLen = strlen($scope) - 1;
        $string = '';
        for($i = 0; $i < $len; $i ++){
            $string .= substr($scope, mt_rand(0, $strLen), 1);
        }
        return $string;
    }

    /**
     * 字串长度计算(可计算utf8)
     * 一个汉字(或是其它非ascii)的长度计为1
     * 注意：此函数性能不高，不要用于大量调用的情况
     * @param string $str 待计算的字串
     * @return int 长度
     */
    public static function utf8strlen($str) {
        $i = 0;
        $count = 0;
        $len = strlen($str);
        while($i < $len){
            $chr = ord($str[$i]);
            $count++;
            $i++;
            if($i >= $len) break;
            if($chr & 0x80){
                $chr <<= 1;
                while($chr & 0x80){
                    $i++;
                    $chr <<= 1;
                }
            }
        }
        return $count;
    }

    /**
     * 返回一个中英文混合字符串的占位长度
     * 一个汉字(或是其它非ascii)占两个ascii字符的位置
     * 注意：此函数性能不高，不要用于大量调用的情况
     * @param string $str 待计算的字串
     * @return int 占位长度
     */
    public static function strSpace($str){
        return (strlen($str) + self::utf8strlen($str)) / 2;
    }

    /*
     * 截取中文字符
     * @param string $str 字串
     * @param int $len 截取长度
     * @return string 截取后的字串
     */
    public static function cnSubstr($str, $len){
        for($i = 0; $i < $len; $i ++){
            $temp_str = substr($str, 0, 1);
            if (ord($temp_str) > 127) {
                $i ++;
                if($i < $len){
                    $new_str[] = substr($str, 0, 3);
                    $str = substr($str, 3);
                }
            }else{
                $new_str [] = substr($str, 0, 1);
                $str = substr($str, 1);
            }
        }
        return join($new_str);
    }

    /**
     * 将Y-m-d H:i:s格式的时间转成unixtime
     * @param string $datatime 日期时间，格式: Y-m-d 或 Y-m-d H:i:s
     * @return int
     */
    public static function unixtime($datetime){
        $d = explode(' ', $datetime);
        $date = explode('-', $d[0]);
        if(isset($d[1])){
            list($h, $i, $s) = explode(':', $d[1]);
            return mktime($h, $i, $s, $date [1], $date [2], $date [0]);
        }
        return mktime(0, 0, 0, $date [1], $date [2], 0 + $date [0]);
    }

    /**
     * 判断某个字串是否Y-m-d格式的时间字串
     * @param string $date 日期
     * @return bool
     */
    public static function isDate($date){
        $dPat = '([1-9])|((0[1-9])|([1-2][0-9])|(3[0-2]))';
        $mPat = '([1-9])|((0[1-9])|(1[0-2]))';
        $yPat = '(19|20)[0-9]{2}';
        $pattern = "!^($yPat)-($mPat)-($dPat)$!";
        return preg_match($pattern, $date);
    }

    /**
     * 判断某个字串是否Y-m-d H:i:s格式的时间字串
     * @param string $datetime 日期时间
     * @return bool
     */
    public static function isDatetime($datetime){
        $dPat = '([1-9])|((0[1-9])|([1-2][0-9])|(3[0-2]))';
        $mPat = '([1-9])|((0[1-9])|(1[0-2]))';
        $yPat = '(19|20)[0-9]{2}';
        $hPat = '([1-9])|(([0-1][0-9])|(2[0-3]))';
        $mPat = '([1-9])|([0-5][0-9])';
        return preg_match("!^($yPat)-($mPat)-($dPat) ($hPat):($mPat):($mPat)$!", $datetime);
    }

    public static function captcha($code, $height = 35){
        $colorMin = 0;
        $colorMax = 230;
        $fonts = ['fleck.ttf', 'molten.ttf', 'oneway.ttf', 'sixty.ttf'];
        $len = strlen($code);
        $width = $height * $len;
        $size = round($height * 0.70);
        $offset = round($size / $len);
        $image = imagecreate($width, $height);
        imagecolorallocate($image,  233, 233, 233);

        // 生成随机线条
        // for($i=0; $i<mt_rand(20, 30); $i++){ 
        //     $x1 = mt_rand(0, $width - 12); $y1 = mt_rand(0, $height - 12); 
        //     $x2 = mt_rand(0, $width - 12); $y2 = mt_rand(0, $height - 12); 
        //     $x1 = $x1; $y1 = $y1; 
        //     $x2 = $x1 + mt_rand(-12, 12); $y2 = $y1 + mt_rand(-12, 12); 
        //     $color = imagecolorallocate($image, mt_rand(0, 230), mt_rand(0, 230), mt_rand(0, 230)); 
        //     imageline($image, $x1, $y1, $x2, $y2, $color); 
        // } 

        // 生成字母
        $x = 3;
        for($i = 0; $i < strlen($code); $i++){
            $char = $code[$i];
            $font = FRAMEWORK_ROOT.'/fonts/'.$fonts[array_rand($fonts)];
            $y = $height - mt_rand(0, $offset);
            $c = imagecolorallocate($image, mt_rand($colorMin, $colorMax), mt_rand($colorMin, $colorMax), mt_rand($colorMin, $colorMax));
            imagettftext($image, $size, 0, $x, $y, $c, $font , $char);
            $x += $size + mt_rand(-$offset, $offset);
        }

        // 输出图片
        header("Expires: -1");
        header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", false);
        header("Pragma: no-cache");
        header('Content-Type: image/png');
        imagepng($image);
        imagedestroy($image);
    }
    /**
     * 获取文件尾部内容
     * @param string $file 文件名
     * @return int $len 长度，单位byte
     */
    public static function tail($file, $len = 20000){
        if(!file_exists($file)) return;
        clearstatcache();
        $size = filesize($file) - $len;
        $size = $size < 0? 0 : $size;
        $f = fopen($file, "r");
        fseek($f, $size);
        $text = '';
        while($s = fgets($f)){
            $text .= $s;
        }
        fclose($f);
        return $text;
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

    /**
     * 对明文密码进行加密
     * @param string $pwd 明文密码
     * @return string 加密后的密码
     */
    public static function password($pwd){
        return md5(sha1($pwd.'tool+*+#@').'toolPassword*+!#!~$@#');
    }

    /**
     * 递归创建所有不存在的目录
     * @param string $path 目标路径
     * @param int $mode 权限，安全方案：注意:文件夹必须带有x权限才能有读取权限,文件带有x权限则有执行权限
     * @return bool
     */
    public static function _mkdir($path, $mode = 0755){
        if (is_dir($path)) return true;
        return mkdir($path, $mode, true);
    }

    /**
     * 获得文件大小为8kb这样的格式
     * @param int $bytes 文件大小整数
     * @return string 返回格式如: 1 Kb or 1 MB..
     */
    public static  function formatBytes($bytes){
        //注：使用if是最快速的
        if ($bytes < 1024) return $bytes . ' B';
        elseif ($bytes < 1048576) return round($bytes / 1024, 2) . ' KB';
        elseif ($bytes < 1073741824) return round($bytes / 1048576, 2) . ' MB';
        elseif ($bytes < 1099511627776) return round($bytes / 1073741824, 2) . ' GB';
        else return round($bytes / 1099511627776, 2) . ' TB';
    }
}
