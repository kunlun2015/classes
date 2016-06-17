<?php
/**
 * pdo操作方法封装
 * @authors Amos
 * @date    2016-06-15 17:35:45
 * @copyright 735767227@qq.com
 */

class PdoMsql {
    private $pdo;
    private $host = '127.0.0.1';
    private $db = 'amos';
    private $username = 'root';
    private $psd = '123@wepartner';
    private $table_prefix;

    /**
     * [__construct 初始化pdo连接]
     */
    function __construct(){
        $this->pdo = new PDO("mysql:host=$this->host;dbname=$this->db", $this->username, $this->psd) or die('database connect failed');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec('set names utf8');
    }

    /**
     * [fetch 返回一条查询结果]
     * @param  [string] $sql    [sql语句]
     * @param  array  $params [参数列表]
     * @return [array]         [以一维数组形式返回查询结果]
     */
    public function fetch($sql, $params = array()){       
        $stmt = $this->pdo->prepare($sql);             
        $stmt->execute($params);
        $rst = $stmt->fetch(PDO::FETCH_ASSOC);        
        return $rst;
    }

    /**
     * [fetchAll 返回多条查询结果]
     * @param  [string] $sql    [sql语句]
     * @param  array  $params [参数列表]
     * @return [array]         [以二维数组形式返回查询结果]
     */
    public function fetchAll($sql, $params = array()){
        $stmt = $this->pdo->prepare($sql);             
        $stmt->execute($params);
        $rst = $stmt->fetchAll(PDO::FETCH_ASSOC);       
        return $rst;
    }

    /**
     * [rowCount 返回记录总条数]
     * @param  [string] $sql    [sql语句]
     * @param  array  $params [参数列表]
     * @return [int]         [返回记录总条数]
     */
    public function rowCount($sql, $params = array()){
        $stmt = $this->pdo->prepare($sql);             
        $stmt->execute($params);
        $rst = $stmt->rowCount();       
        return $rst;
    }

    /**
     * [add 插入一条数据]
     * @param [string] $table [表名称]
     * @param array  $data  [参数列表]
     * @return [int] [返回插入id]
     */
    public function add($table, $data = array()){
        $conditions = $this->implode($data);
        $sql        = 'insert into '.$this->table_prefix.$table." SET {$conditions['fields']}";
        $params     = $conditions['params'];
        $rst        = $this->query($sql, $params);
        $insert_id  = $this->pdo->lastInsertId();
        return $insert_id;
    }

    /**
     * [save 更新一条数据]
     * @param  [string] $table      [表名称]
     * @param  array  $data       [待更新的参数列表]
     * @param  array  $conditions [where条件参数]
     * @param  string $gule       [条件参数链接符]
     * @return [int]             [返回受影响的行数]
     */
    public function save($table, $data = array(), $conditions = array(), $gule = 'AND'){
        $fields = $this->implode($data, ',');
        $conditions = $this->implode($conditions, $gule);
        $params = array_merge($fields['params'], $conditions['params']);
        $sql = 'update '.$this->table_prefix.$table." SET {$fields['fields']}";
        $sql .= $conditions['fields'] ? ' WHERE '.$conditions['fields'] : '';
        return $this->query($sql, $params);
    }

    /**
     * [delete 删除一条记录]
     * @param  [string] $table  [表名称]
     * @param  array  $params [where参数列表]
     * @param  string $gule   [条件参数链接符]
     * @return [int]         [返回受影响的行数]
     */
    public function delete($table, $params = array(), $gule = 'AND'){
        $condition = $this->implode($params, $gule);
        $sql = 'DELETE FROM '.$this->table_prefix.$table;
        $sql .= $condition['fields'] ? ' WHERE '.$condition['fields'] : '';
        return $this->query($sql, $condition['params']);
    }

    /**
     * [transaction 事务执行多条sql语句]
     * @param  array  $sqls [待执行的sql语句]
     * @return [boolen]       [执行的最终结果]
     */
    public function transaction($sqls = array()){
        try{
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
            $this->pdo->beginTransaction();//开启事务处理
            foreach($sqls as $sql){
                $this->query($sql);
            }            
            $this->pdo->commit();
            return true;
        }catch(PDOException $e){
            $this->pdo->rollback();
            return false;
        }
    }

    /**
     * [beginTransaction 开启事务执行]
     * @return [type] [description]
     */
    public function beginTransaction(){
        $this->pdo->beginTransaction();//开启事务处理
    }

    /**
     * [transactionRollBack 事务回滚]
     * @return [type] [description]
     */
    public function transactionRollBack(){
        $this->pdo->rollback();
    }

    /**
     * [submitTransaction 提交事务]
     * @return [type] [description]
     */
    public function submitTransaction(){
        $this->pdo->commit();
    }

    /**
     * [query 执行一条非查询语句]
     * @param  [string] $sql    [sql语句]
     * @param  array  $params [参数列表]
     * @return [int]         [返回受影响的行数]
     */
    public function query($sql, $params = array()){
        if(!$params){
            $rst = $this->pdo->exec($sql);
            return $rst;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rst = $stmt->rowCount();
        return $rst;
    }

    /**
     * [implode 格式化pdo数据绑定]
     * @param  [mix] $params [如果是数组则格式化绑定参数，如果是字符串则以数组形式返回]
     * @param  string $glue   [参数连接符]
     * @return [array]         [返回格式化参数]
     */
    private function implode($params, $glue = ',') {
        $result = array('fields' => ' 1 ', 'params' => array());
        $split = '';
        if (!is_array($params)) {
            $result['fields'] = $params;
            return $result;
        }
        if (is_array($params)) {
            $result['fields'] = '';
            foreach ($params as $fields => $value) {
                $result['fields'] .= $split . "`$fields` =  :$fields";
                $split = ' ' . $glue . ' ';
                $result['params'][":$fields"] = is_null($value) ? '' : $value;
            }
        }
        return $result;
    }

    /**
     * [fields 获取表的字段]
     * @param  [string] $table [表名称]
     * @return [array]        [返回字段名称]
     */
    public function fields($table){       
        $stmt = $this->pdo->prepare("DESC {$this->table_fields}{$table}");  
        $stmt->execute();  
        $table_fields = $stmt->fetchAll(PDO::FETCH_COLUMN);  
        return $table_fields;      
    }

    /**
     * [__destruct 析构函数 释放资源]
     */
    public function __destruct(){       
        $this->pdo = null;       
    }
}

$pdo = new PdoMsql();
$rst = $pdo->fields('sessioninfo');
var_dump($rst);