<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/4/24
 * Time: 12:39
 */

namespace wen;

class Sqlite
{
    /***
     * 所有的SQL,均写在这里,方便增加和修改
     */
    // 创建数据表
    const CREATE_TABLE_SQL = "CREATE TABLE USER_DATA (
                Id CHAR(50) PRIMARY KEY NOT NULL,
                name CHAR(50) NOT NULL, 
                type CHAR(20) NOT NULL, 
                data TEXT NOT NULL, 
                status INT DEFAULT 1, 
                create_time INT DEFAULT 0, 
                update_time INT DEFAULT 0);";
    // 插入新数据
    const INSERT_SQL = "INSERT INTO USER_DATA 
              (Id,name,data,type,status,create_time,update_time) 
              VALUES ('%s','%s','%s','%s',%d,%d,%d);";
    // 修改数据,只对data 和
    const UPDATE_SQL = "UPDATE USER_DATA 
              SET data = '%s' , type = '%s' , update_time = %d 
              WHERE Id='%s' AND name='%s' AND status=1";
    // 软删除
    const DELETE_SQL = "UPDATE USER_DATA 
              SET status=0 , update_time = %d 
              WHERE Id='%s' AND name='%s' AND status=1";
    // 查询多条数据
    const SELECT_ALL_SQL = "SELECT Id AS msgId , data, type, create_time, update_time from USER_DATA 
                 WHERE name='%s' AND status=1 ORDER BY update_time ASC;";
    // 单条查询
    const SELECT_ONE_SQL = "SELECT Id AS msgId , data, type, create_time, update_time from USER_DATA 
                 WHERE Id='%s' AND name='%s' AND status=1 LIMIT 1 OFFSET 0";
    /***
     * @var null 当前实例
     */
    private static $instance = null;
    /***
     * @var string 数据库文件的地址
     */
    private static $sqlitePath = RUNTIME_PATH.'sqlite'.DS;
    /***
     * @var null 这是sqlite3的实例
     */
    private $sqlite = null;

    public $lastId = null;

    /***
     * @var int 当前的时间戳
     */
    private $timestamp = 0;

    /***
     * @var string 数据类型
     */
    private $dataType = 'string';

    /***
     * @var null 当前需要添加或修改的数据
     */
    private $currentData = null;


    private function __construct($name)
    {
        // 判断当前文件是不是初次创建
        $isFirstCreate = false;
        // 设置并得到真实的路径
        $dbFile = self::setSqliteDbFile($name);
        // 判断文件是否存在
        if(file_exists($dbFile)){
            $isFirstCreate = true;
        }
        // 构建当前实例
        $this -> sqlite = new \SQLite3($dbFile);
        // 设置超时时间
        $this -> sqlite -> busyTimeout(5000);
        // 如果是新增的,我们则需要将数据库初始化
        if($isFirstCreate===false){
            // 初始化数据库
            $this -> sqliteInit();
        }
    }

    private function sqliteInit()
    {
        // 默认就创建一个数据表
        $sql = self::CREATE_TABLE_SQL;
        // 初始化创建表
        $this -> sqlite -> exec($sql);
    }    

    private static function setSqliteDbFile($name)
    {
        $name = md5($name);
        return self::$sqlitePath.$name.'.db';
    }

    public static function getInstance($name=null)
    {
        if(!self::$instance instanceof self){
            // 空的名字是无法new的
            if(!is_null($name)){
                // new 当前类
                self::$instance = new self($name);
            }
        }
        return self::$instance;
    }

    private function setData($data)
    {
        // 获得数据类型
        $type = gettype($data);
        // 设置了类型
        $this -> dataType = $type;
        // 类型转换
        switch ($type){
            case 'array':
                $data = json_encode($data);
                break;
            case 'object':
                $data = json_encode($data);
                break;
            default:
                $data = (string) $data;
        }
        return $data;
    }

    private function setSql($name,$from,$Id)
    {
        // 给标签添加一个md5
        $name = md5($name);
        // 时间戳
        $this -> timestamp = $_SERVER['REQUEST_TIME'];
        // 需要返回的SQL语句
        $sql = "";
        // 拼接SQL
        switch ($from){
            // 新增
            case 'add':
                $sql = sprintf(self::INSERT_SQL,
                    $Id,
                    $name,
                    $this -> setData($this -> currentData),
                    $this -> dataType,
                    1,
                    $this -> timestamp,
                    $this -> timestamp
                );
                break;
            case 'find':
                $sql = sprintf(self::SELECT_ONE_SQL,
                        $Id,
                        $name
                    );
                break;
            case 'select':
                $sql = sprintf(self::SELECT_ALL_SQL,
                        $name
                    );
                break;
            case 'update':
                $sql = sprintf(self::UPDATE_SQL,
                        $this -> setData($this -> currentData),
                        $this -> dataType,
                        $this -> timestamp,
                        $Id,
                        $name
                    );
                break;
            case 'delete':
                $sql = sprintf(self::DELETE_SQL,
                        $this -> timestamp,
                        $Id,
                        $name
                    );
                break;
        }
        // 返回拼接好的SQL
        return $sql;
    }

    private function setError($from)
    {
        $errorCode = $this -> sqlite -> lastErrorCode();
        $errorMsg = $this -> sqlite -> lastErrorMsg();
        Response::debug("[ SQLITE ] Exec Error code : $errorCode => msg : $errorMsg",404,$from);
        // 关闭当前实例
        $this -> close();
    }


    private static function changeData($type,$data)
    {
        switch ($type){
            case 'array':
                $data = json_decode($data,true);
                break;
            case 'object':
                $data = json_decode($data,true);
                break;
            case 'boolean':
                $data = (boolean) $data;
                break;
            case 'integer':
                $data = (integer) $data;
                break;
            case 'double':
                $data = (double) $data;
                break;
        }
        return $data;
    }

    private static function setValue($row)
    {
        $value = [];
        $value['msgId'] = $row['msgId'];
        // 如果是数组或者对象
        if($row['type'] === 'array' || $row['type'] === 'object'){
            $newData = self::changeData($row['type'],$row['data']);
            $value = array_merge($value,$newData);
        }else{
            $value['data'] = self::changeData($row['type'],$row['data']);
        }
        return $value;
    }

    private static function getData($query)
    {
        // 用于接收结果集
        $_result = [];
         // 取出所有结果集
         while ($row = $query -> fetchArray(SQLITE3_ASSOC)){
             $value = self::setValue($row);
             array_push($_result,$value);
         }
         // 返回结果集
        return $_result;
    }


     public function add($name,$data)
     {
         // 生成一个ID
         $lastId =  uniqid('msgId_');
         // 设置当前值
         $this -> currentData = $data;
         // 执行新增的数据库语句
         $sql = $this -> setSql($name,'add',$lastId);
         // 然后执行添加
         $exec = $this -> sqlite -> exec($sql);
         // 如果不存在错误的
         if($exec){
             // 挂载当前添加的lastId
             $this -> lastId = $lastId;
             // 返回受影响的记录数
             return $this -> sqlite -> changes();
         }else{
            $this -> setError('Sqlite.add');
            return 0;
         }
     }



     public function find($name,$msgId)
     {
         // 生成SQL
         $sql = $this -> setSql($name,'find',$msgId);
         // 执行查询
         $query = $this -> sqlite -> query($sql);
         // 有结果的情况,则查询结果
         if($query){
             // 返回结果
             return self::getData($query);
         }else{
             $this -> setError('Sqlite.find');
             return [];
         }
     }

     public function select($name)
     {
         // 生成SQL
         $sql = $this -> setSql($name,'select','');
         // 执行查询
         $query = $this -> sqlite -> query($sql);
         // 有结果的情况,则查询结果
         if($query){
            return self::getData($query);
         }else{
            $this -> setError('Sqlite.select');
            return [];
         }
     }

     public function update($name,$msgId,$data)
     {
         // 设置当前值
         $this -> currentData = $data;
         // 生成SQL
         $sql = $this -> setSql($name,'update',$msgId);
         // 执行SQL
         $exec = $this -> sqlite -> exec($sql);
         // 如果没有错误
         if($exec){
             // 返回受影响的记录数
             return $this -> sqlite -> changes();
         }else{
             // 执行错误
             $this -> setError('Sqlite.update');
             return 0;
         }
     }

     public function delete($name,$msgId)
     {
         // 生成SQL
         $sql = $this -> setSql($name,'delete',$msgId);
         // 执行SQL
         $exec = $this -> sqlite -> exec($sql);
         // 没有错误
         if($exec){
             // 返回受影响的记录条数
             return $this -> sqlite -> changes();
         }else{
             // 生成错误
             $this -> setError('Sqlite.delete');
             return 0;
         }
     }

     public function close()
     {
         // 保证当前实例是存在的,才能实现去关闭
         if(!is_null($this -> sqlite)){
             // 关闭数据库连接
             $this -> sqlite -> close();
         }
         // 将数据库实例置为空
         $this -> sqlite = null;
         // 将当前实例置为空
         self::$instance = null;
     }

}