<?php
/*-----------------------------------------------------+
 * 数据库驱动，基于PDO的实现
 * @author yeahoo2000@gmail.com
 +-----------------------------------------------------*/
class DB extends PDO{
    private static $instance;
    public $debug = false;

    /**
     * 事务计数器
     * @var int
     */
    private static $tCouter = 0;

    /**
     * 事务开始
     */
    public function beginTransaction(){
        if(self::$tCouter<0){
            throw new Exception("beginTransaction 没有严格配对");
        }else if(self::$tCouter == 0){
            parent::beginTransaction(); 
        }
        self::$tCouter++;
    }

    /**
     * 事务提交
     */
    public function commit(){
        if(self::$tCouter > 1){
            self::$tCouter--;
        }else if(self::$tCouter == 1){
            self::$tCouter--;
            parent::commit(); 
        }else{
            throw new PDOException("commit 没有严格配对");
        }
    }

    /**
     * 事务回滚
     */
    public function rollback(){
        if(self::$tCouter > 1){
            self::$tCouter--;
            throw new PDOException("rollback 内嵌回滚");
        }else if(self::$tCouter == 1){
            self::$tCouter--;
            parent::rollback(); 
        }else{
            throw new PDOException("rollback 没有严格配对");
        }
    }

    /**
     * 构造函数
     * @param string $dsn 数据库地址
     * @param string $user 用户名
     * @param string $pass 密码
     */
    public function __construct($dsn, $user, $pass) {
        parent::__construct($dsn, $user, $pass, [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'']);
        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
    }

    /**
     * 执行一个查询
     * @param string $sql SQL语句
     * @return Object 返回一个结果集句柄
     */
    public function query($sql){
        if($this->debug) $this->printSql($sql);
        $rs = parent::query($sql);
        $rs->setFetchMode(PDO::FETCH_ASSOC);
        return $rs;
    }

    /**
     * 执行一个SQL语句
     * @param string $sql SQL语句
     * @return bool 返回执行结果
     */
    public function exec($sql){
        if($this->debug) $this->printSql($sql);
        return parent::exec($sql);
    }

    /**
     * 执行一个Limit查询
     * 用法示例:
     * $sql = 'select * from user';
     * $rs = $dbh->selectLimit($sql, 0, 10); //取出第0行开始的10条数据
     * while($row = $rs->fetch()){
     *     print_r($row);
     * }
     *
     * @param string $sql SQL语句
     * @param int $offset 偏移量
     * @param int $num 要求返回的记录数
     * @return Object 返回一个结果集句柄
     */
    public function selectLimit($sql, $offset, $num){
        $sql .= " limit $offset, $num";
        return $this->query($sql);
    }

    /**
     * 返回一个查询结果集中的第一行第一列
     * @param string $sql SQL语句
     * @return string|int
     */
    public function getOne($sql){
        return $this->query($sql)->fetchColumn();
    }

    /**
     * 返回一个查询结果集中的第一行
     * @param string $sql SQL语句
     * @return array 
     */
    public function getRow($sql){
        return $this->query($sql)->fetch();
    }

    /**
     * 得到一个SQL查询的所有结果集
     * @param string $sql SQL语句
     * @return array
     */
    public function getAll($sql){
        return $this->query($sql)->fetchAll();
    }

    /**
     * 跟据数组中的数据返回一条插入语句
     * (目前只考虑Mysql支持)
     * @param string $table 表名
     * @param array $data 数据
     * @return string SQL语句
     */
    public static function getInsertSql($table, $data){
        $col=array();
        $val=array();
        foreach($data as $k=>$v){
            if(null === $v) continue;
            $col[] = $k;
            $val[] = $v;
        }
        return "insert into `{$table}`(`".implode($col, '`, `')."`) values('".implode($val, "', '")."')";
    }

    /**
     * 跟据数组中的数据返回一条更新语句
     * (目前只考虑Mysql支持)
     *
     * @param string $table 表名
     * @param string|array $primaryKey 主键名，多主键时用数组传递
     * @param array $data 数据
     * @return false|string false或SQL语句
     */
    public static function getUpdateSql($table, $primaryKey, $data){
        $w = array();
        if(is_array($primaryKey)){
            foreach($primaryKey as $v){
                $w[] = "`$v`='{$data[$v]}'";
                if(isset($data[$v])){
                    unset($data[$v]);
                }else{
                    return false;
                }
            }
        }else if(isset($data[$primaryKey])){
            $w[] = "`$primaryKey`='{$data[$primaryKey]}'";
            unset($data[$primaryKey]);
        }else{
            return false;
        }

        if(!$data) return false;

        $u = array();
        foreach($data as $k=>$v){
            if(null === $v) continue;
            $u[] = "`{$k}`='{$v}'";
        }
        return "update `{$table}` set ".implode($u, ', ')." where ".implode($w, ' and ');
    }

    /**
     * 输出SQL调试信息
     */
    private function printSql($sql){
        echo $sql."<hr />";
    }
}
