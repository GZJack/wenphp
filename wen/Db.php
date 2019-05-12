<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/3/30
 * Time: 13:32
 */

namespace wen;

/***
 * Class Db
 * @package wen
 */
class Db
{
    /***
     * 第一步,定义一个静态实例,来接收当前实例
     * @var null
     */
    private static $instance = null;

    /***
     * 数据库默认配置
     * @var array
     */
    private $dbConfig = array(
        // 数据库类型
        'type'            => 'mysql',
        // 服务器地址
        'host'        => '127.0.0.1',
        // 数据库名
        'dbname'        => 'bd',
        // 用户名
        'username'        => 'root',
        // 密码
        'password'        => 'root',
        // 端口
        'port'        => '3306',
        // 数据库编码默认采用utf8
        'charset'         => 'utf8',
    );
    /***
     * 数据库连接
     * @var null
     */
    private $conn = null;

    /***
     * 受影响的记录
     * @var int
     */
    public $count = 0;
    /***
     * 新增的主键Id
     * @var string
     */
    public $insertId = null;

    /***
     * 构造函数私有化,禁止外部new
     * Db constructor.
     * @param $config
     */
    private function __construct($config)
    {
        // 读取用户配置database
        $dbConfig = isset($config['database']) ? $config['database'] : $config;
        // 合并用户书写的配置
        $this -> dbConfig = array_merge($this->dbConfig,$dbConfig);
        // 执行数据库连接
        $this -> connect();
    }

    private function connect()
    {
        // 读取配置项里的数据库配置
        $dbConfig = $this -> dbConfig;
        // 拼接数据库连接
        $dsn = "{$dbConfig['type']}:host={$dbConfig['host']};port={$dbConfig['port']};
                dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";

        // 有可能会出现连接错误
        try{
            // 创建PDO对象
            $this -> conn = new \PDO($dsn,$dbConfig['username'],$dbConfig['password']);
            // 设置默认字符集
            $this -> conn -> query("SET NAMES {$dbConfig['charset']}");
        }catch (\PDOException $e){
            // 将错误抛出
            throw $e;
        }
    }


    /***
     * 私有化,禁止外部克隆
     */
    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    /***
     * 获得当前类实例
     * @param $config array
     * @return static
     */
    public static function getInstance($config)
    {
        // 判断当前实例,是否是其本身
        if(!self::$instance instanceof self){
            self::$instance = new self($config);
        }
        return self::$instance;
    }


    /***
     * 数据库写,增,删,改,会使用到的
     * @param $sql
     * @return mixed
     */
    public function exec($sql)
    {
        $count = $this -> conn -> exec($sql);
        // 如果大于0则表示有受影响的记录
        if($count>0){
            $insertId = $this -> conn -> lastInsertId();
            // 如果是新增操作就获取新增主键id
            if($insertId !== null){
                $this -> insertId = $insertId;
            }
             // 新增,删除,修改 都需要受影响的记录数
             $this -> count = $count;
        }else{
            // 错误的数组
            $errorArray = $this -> conn -> errorInfo();
            // 打印错误信息 , 这里不用管了,如果值不存在,你去修改,就会修改不到指定的值,删除也是一样的
            // 这里不做错误抛出了,请使用正确的sql模式
            // print_r($errorArray);
        }
        return $count;
    }


    /***
     * 单条查询
     * @param $sql string
     * @return mixed
     */
    public function fetch($sql)
    {
        return $this -> conn -> query($sql) -> fetch(\PDO::FETCH_ASSOC);
    }

    /***
     * 多条查询
     * @param $sql string
     * @return mixed
     */
    public function fetchAll($sql)
    {
        return $this -> conn -> query($sql) -> fetchAll(\PDO::FETCH_ASSOC);
    }
}