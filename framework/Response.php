<?php
/*-----------------------------------------------------+
 * 响应处理类
 * @author yeahoo2000@gmail.com
 +-----------------------------------------------------*/
class Response{
    protected $status = 200;
    protected $headers = [];
    protected $body;
    public static $codes = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',

        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',

        226 => 'IM Used',

        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(Unused)',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',

        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',

        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',

        426 => 'Upgrade Required',

        428 => 'Precondition Required',
        429 => 'Too Many Requests',

        431 => 'Request Header Fields Too Large',

        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',

        510 => 'Not Extended',
        511 => 'Network Authentication Required'
    ];

    public function __construct(){
        // 载入默认的header输出配置，如果有的话
        if(App::cfg()->header){
            foreach(App::cfg()->header->getArray() as $k => $v){
                $this->headers[$k] = $v;
            }
        }
    }

    public function status($code = null){
        if(is_null($code)){
            return $this->status;
        }
        if(isset(self::$codes[$code])){
            $this->status = $code;
        }else{
            throw new Exception('无效的状态码');
        }
        return $this;
    }

    public function header($name, $value = null){
        $this->headers[$name] = $value;
        return $this;
    }

    public function sendHeaders(){
        $protocol = isset($_SERVER['SERVER_PROTOCOL'])? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
        sprintf('%s %d %s', $protocol, $this->status, self::$codes[$this->status]);
        foreach($this->headers as $k => $v){
            header($k.': '.$v);
        }

        if(($length = strlen($this->body)) > 0){
            header('Content-Length: '.$length);
        }
        return $this;
    }

    public function write($str) {
        $this->body .= $str;
        return $this;
    }

    public function send(){
        if (ob_get_length() > 0) ob_end_clean();
        if (!headers_sent()) $this->sendHeaders();
        exit($this->body);
    }

    public function cache($expires) {
        if ($expires === false) {
            $this->headers['Expires'] = 'Mon, 26 Jul 1997 05:00:00 GMT';
            $this->headers['Cache-Control'] = array('no-store, no-cache, must-revalidate', 'post-check=0, pre-check=0', 'max-age=0');
            $this->headers['Pragma'] = 'no-cache';
        }
        else {
            $expires = is_int($expires) ? $expires : strtotime($expires);
            $this->headers['Expires'] = gmdate('D, d M Y H:i:s', $expires) . ' GMT';
            $this->headers['Cache-Control'] = 'max-age='.($expires - time());
        }
        return $this;
    }

    public function clear(){
        $this->status = 200;
        $this->headers = [];
        $this->body = '';
        return $this;
    }
}
