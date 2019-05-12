<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/3/31
 * Time: 22:50
 */

namespace wen;


abstract class Model
{

    protected static $sqlConfigs = [];

    protected static $instance = null;

    /***
     * 数据库连接实例
     * @var null|Db
     */
    private $db = null;

    /***
     * 表名
     * @var string
     */
    private static $table_name = '';

    /***
     * 查询的字段名
     * @var string
     */
    private static $str_field = '*';

    /***
     * 需要执行的sql语句
     * @var string
     */
    //private $sql = '';

    /***
     * 要查询的where语句
     * @var string
     */
    private static $str_where = '';

    private static $str_order_by = '';

    /***
     * 显示多少条数据
     * @var string
     */
    private static $str_limit = '';
    // 这是多少页的时候使用
    private static $limit = 0;


    private static $str_keys = '';

    private static $str_values = '';

    /***
     * 新增的主键Id
     * @var string
     */
    public $insertId = null;



    /***
     * Model constructor.
     */
    public function __construct()
    {
        // 构建一个数据库实例
        $this -> db = Db::getInstance(Config::get('database'));
        // 通过new,拿到表名 table  得到的表明,必须是 app\home\model\User
        $strDefaultTable = get_class($this);
        // 转换成数组
        $defaultTableArray = explode('\\',$strDefaultTable);
        $table = array_pop($defaultTableArray); //拿到最后一个
        $isModel = array_pop($defaultTableArray); // 再拿最后一个
        // 如果是这个,$table就一定是模型类了
        if($isModel==='model'){
            self::$table_name = strtolower($table); // 将表明小写
        }
    }



    /***
     * 设置表名
     * @param $table
     * @return static
     */
    final public function table($table)
    {
        self::$table_name = $table;
        return $this;
    }

    private static function _where($and,$name,$exp,$value=null)
    {
        // 当值不是null的时候就进行对 表达式的符进行校验
        if(!is_null($value)){
            // 等于 大于 小于 大于等于 小于等于 不等于
            $arrayExp = ['=','>','<','>=','<=','<>'];
            // 如果用乱写,就报错,默认等于号
            if(!in_array($exp,$arrayExp)){
                $exp = '=';
            }
        }else{
            $value = $exp;
            $exp = '='; // 默认的
        }
        // 然后就是对值进行判断
        // 如果是字符串,需要在外面裹一个单引号
        if (is_string($value)) $value = "'$value'";
        if(self::$str_where === ''){
            self::$str_where = " WHERE $name $exp $value ";
        }else{
            self::$str_where .= " $and $name $exp $value ";
        }
    }

    final public function where($name,$exp,$value=null)
    {
        self::_where('AND',$name,$exp,$value);
        return $this;
    }

    final public function whereOr($name,$exp,$value=null)
    {
        self::_where('OR',$name,$exp,$value);
        return $this;
    }

    final public function field($fields=[])
    {
        $newField = implode(',',$fields);
        if (!empty($fields)){
            if(self::$str_field !== '*'){
                self::$str_field .= ','.$newField;
            }else{
                self::$str_field = $newField;
            }
        }
        return $this;
    }

    /***
     * @param integer $limit
     * @return Model
     */
    final public function limit($limit = 0)
    {
        if(self::$str_limit === '' && is_integer($limit))
        {
            self::$limit = $limit;
            self::$str_limit = " LIMIT $limit ";
            self::$str_order_by = ''; // 以limit 为重
        }
        return $this;
    }

    /***
     * 必须先设置了limit,才能设置页数
     * @param integer $page
     * @return Model
     */
    final public function pages($page=0)
    {
        if (self::$str_limit !== '' && is_integer($page)){
            $offSet = self::$limit * $page - 1;
            self::$str_limit .= " OFFSET $offSet ";
        }
        return $this;
    }

    final public function orderBy(array $args)
    {
        // 转成字符串
        $str_args = implode(',',$args);
        // 如果设置了 limit 这个 ORDER BY 就失效了,会有冲突
        if(self::$str_order_by === '' && self::$str_limit === '' && !empty($args))
        {
            self::$str_order_by = " ORDER BY $str_args ASC ";
        }
        return $this;
    }
    final public function asc()
    {
        self::$str_order_by = str_replace('DESC','ASC',self::$str_order_by);
        return $this;
    }
    final public function desc()
    {
        self::$str_order_by = str_replace('ASC','DESC',self::$str_order_by);
        return $this;
    }



    /***
     * @param array $data
     * @return Model
     */
    final public function data(array $data=[])
    {
        $keys = array_keys($data);
        $values = array_values($data);
        self::$str_keys = implode(',',$keys); // 不用单引号
        $newValues = [];
        foreach ($values as $item){
            $newItem = (string) "'$item'"; // 加单引号
            array_push($newValues,$newItem);
        }
        self::$str_values = implode(',',$newValues);
        return $this;
    }


    final public function insert()
    {
        if(self::$str_keys === '' || self::$str_values === ''){
            //throw new \Exception('[ INSERT ] data() is empty',-1);
            Response::debug('[ SQL ] data() is empty',-1,'Model.insert');
        }
        // 'INSERT INTO user (column1,column2,column3,...) VALUES (value1,value2,value3,...)’
        $insertSql = "INSERT INTO %s (%s) VALUE (%s)";
        $sql = sprintf($insertSql,
            self::$table_name,
            self::$str_keys,
            self::$str_values);
        $count = $this -> db -> exec($sql);
        $this -> insertId = $this -> db -> insertId;
        return $count;
    }

    final public function delete()
    {
        if(self::$str_where===""){
            //throw new \Exception('[ DELETE ] where require',-1);
            Response::debug('[ SQL ] where require',-1,'Model.delete');
        }
        // 'DELETE FROM user WHERE Id = 200 AND status = 0'
        $deleteSql = "DELETE FROM %s %s ";
        $sql = sprintf($deleteSql,
            self::$table_name,
            self::$str_where);
        return $this -> db -> exec($sql);
    }

    private static function _updateData()
    {
        $keys = explode(',',self::$str_keys);
        $values = explode(',',self::$str_values);
        $newValues = [];
        foreach ($keys as $index => $value){
            $newValue = " $value = $values[$index] ";
            array_push($newValues,$newValue);
        }
        self::$str_values = implode(',',$newValues);
    }


    final public function update()
    {
        self::_updateData(); // 执行 保证保证data,已经设置了
        if(self::$str_values === '' || self::$str_where === ''){
            //throw new \Exception('[ UPDATE ] data() is empty OR where is empty');
            Response::debug('[ SQL ] data() is empty OR where is empty',-1,'Model.update');
        }
        // "UPDATE user SET name = 'jack' , sex = '男’ WHERE Id = 1234 AND status = 1"
        $updateSql = "UPDATE %s SET %s %s ";
        $sql = sprintf($updateSql,
               self::$table_name,
               self::$str_values,
               self::$str_where);
        return $this -> db -> exec($sql);
    }


    /***
     * 单条查询
     * @return mixed
     */
    final public function find()
    {
        // 'SELECT name,age,sex FROM user WHERE id = 123 ANd status = 1
        $selectSql = 'SELECT %s FROM %s %s ';
        $sql = sprintf($selectSql,
            self::$str_field,
            self::$table_name,
            self::$str_where);
        return $this -> db -> fetch($sql);
    }

    /**
     * 多条查询
     * @return mixed
     */
    final public function select()
    {
        // 'SELECT name,age,sex FROM user WHERE id = 123 ANd status = 1 (LIMIT 100 OFFSET 200) 或 (ORDER BY age ASC)
        $selectSql = 'SELECT %s FROM %s %s %s %s ';
        $sql = sprintf($selectSql,
            self::$str_field,
            self::$table_name,
            self::$str_where,
            self::$str_limit,
            self::$str_order_by);
        return $this -> db -> fetchAll($sql);
    }




    /***
     * 模型下的验证器函数,继承后就可以使用
     * @param array $data
     * @param array $validate
     * @return Validate
     */
    public function validate($data=[], $validate=[])
    {
        return Loader::validate($data,$validate);
    }

}