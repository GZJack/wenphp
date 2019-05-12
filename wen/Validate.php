<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/4/4
 * Time: 10:41
 */

namespace wen;


class Validate
{
    /***
     * 验证实例,说明一下,如果
     * @var null
     */
    private static $validateInstance = null;
    
    /***
     * @var array 将$rules真实的key值提取出来 name|姓名 => name
     */
    private static $realRulesKeyArray = [];
    /***
     * @var array 这是将$rules路由规则,把key提取出来
     */
    private static $rulesKeyArray = [];
    /***
     * @var array 这是将$rules路由规则,把value提取出来
     */
    private static $rulesValueArray = [];
    /****
     * @var array 再将key value 进行合拼起来
     */
    private static $rulesKeyValueArray = [];

    /***
     * 用于覆盖的 验证器规则
     * @var array
     * 用来实现子类继承使用的,当用户自定义了子类验证器,需要与控制器同名并且在相同模块下的validate目录下,会被自动调用
     */
    protected $rules = [];

    /***
     * 用于覆盖的 验证失败返回消息
     * @var array
     * 用来实现子类继承使用的,当用户自定义了子类验证器,需要与控制器同名并且在相同模块下的validate目录下,会被自动调用
     */
    protected $messages = [];

    /***
     * 用于覆盖的 验证场景
     * @var array
     * 用来实现子类继承使用的,当用户自定义了子类验证器,需要与控制器同名并且在相同模块下的validate目录下,会被自动调用
     */
    protected $scenes = [];

    /***
     * 验证规则 带下划线_的静态私有属性不能被覆盖
     * @var array
     */
    private static $_rules = [];
    /***
     * 验证失败返回的内容 带下划线_的静态私有属性不能被覆盖
     * @var array
     */
    private static $_messages = [];
    /***
     * 验证的场景数组 带下划线_的静态私有属性不能被覆盖
     * @var array
     */
    private static $_scenes = [];
    
    /***
     * 当前的验证场景
     * @var string
     */
    private static $scene = '';

    /***
     * 需要校验的数据
     * @var array
     */
    private static $data = [];

    /***
     * 错误信息
     * @var string
     */
    private static $errMsg = '';

    /***
     * 验证规则默认提示信息
     * @var array
     */
    private static $typeMsg = [
        'require'     => '%s为必须',
        'number'      => '%s必须是数字',
        'integer'     => '%s必须是整型数',
        'float'       => '%s必须是浮点数',
        'boolean'     => '%s必须是布尔值',
        'email'       => '%s不是一个有效的邮箱地址',
        'phone'       => '%s不是一个有效的手机号',
        'array'       => '%s必须是数组',
        'accepted'    => '%s只接受 yes 或 on 或 1',
        'date'        => '%s不是一个有效的时间',
        'file'        => '%s不是一个有效的文件',
        'image'       => '%s不是一个有效的图片',
        'alpha'       => '%s只允许字母',
        'alphaNum'    => '%s只允许字母和数字',
        'alphaDash'   => '%s只允许字母、数字和下划线、破折号',
        'activeUrl'   => '%s不是有效的网址',
        'chs'         => '%s只允许是汉字',
        'chsAlpha'    => '%s只允许汉字和字母',
        'chsAlphaNum' => '%s只允许汉字和字母、数字',
        'chsDash'     => '%s只允许汉字和下划线、破折号',
        'url'         => '%s不是一个有效的链接',
        'ip'          => '%s不是一个有效的IP',
        'dateFormat'  => '%s时间格式不对',
        'in'          => '%s只能是这里面的(:params)',
        'notIn'       => '%s不能是这里面的(:params)',
        'between'     => '%s只能是(:1至:2)范围之间',
        'notBetween'  => '%s不能是(:1至:2)范围之间',
        'length'      => '%s长度必须在(:1至:2)之间',
        'max'         => '%s最多不能超过:params个字符串',
        'min'         => '%s最少不能低于:params个字符串',
        'after'       => ':attribute cannot be less than :rule',
        'before'      => ':attribute cannot exceed :rule',
        'afterWith'   => ':attribute cannot be less than :rule',
        'beforeWith'  => ':attribute cannot exceed :rule',
        'expire'      => ':attribute not within :rule',
        'allowIp'     => 'access IP is not allowed',
        'denyIp'      => 'access IP denied',
        'confirm'     => ':attribute out of accord with :2',
        'different'   => ':attribute cannot be same with :2',
        'egt'         => ':attribute must greater than or equal :rule',
        'gt'          => ':attribute must greater than :rule',
        'elt'         => ':attribute must less than or equal :rule',
        'lt'          => ':attribute must less than :rule',
        'eq'          => ':attribute must equal :rule',
        'unique'      => ':attribute has exists',
        'regex'       => ':attribute not conform to the rules',
        'method'      => 'invalid Request method',
        'token'       => 'invalid token',
        'fileSize'    => 'file size not match',
        'fileExt'     => 'extensions to upload is not allowed',
        'fileMime'    => 'mime type to upload is not allowed',
    ];

    /***
     * Validate constructor.
     * @param array $_rules 验证规则
     * @param array $_messages 验证失败的提示信息
     * @param array $_scenes 验证场景
     */
    public function __construct($_rules=[],$_messages=[],$_scenes=[])
    {
        self::$_rules = array_merge(self::$_rules,$_rules);
        self::$_messages = array_merge(self::$_messages,$_messages);
        self::$_scenes = array_merge(self::$_scenes,$_scenes);
    }

    /***
     * 获得当前实例
     * @return static
     */
    public static function getInstance()
    {
        if (is_null(self::$validateInstance))
        {
            self::$validateInstance = new static(); // new 子类(有子类的情况下),没有就new自身
        }
        return self::$validateInstance;
    }

    /***
     * 合并继承 规则,消息,验证场景
     * 这个在 Loader 类验证器里被调用,如果存在此子类,则会被调用
     */
    public function mergeExtends()
    {
        $this -> setRules($this -> rules);
        $this -> setMessages($this -> messages);
        $this -> setScenes($this -> scenes);
    }

    /***
     * 实例化后单独设置,验证规则
     * @param array $_rules
     */
    public function setRules($_rules=[])
    {
        self::$_rules = array_merge(self::$_rules,$_rules);
    }

    /***
     * 实例化后单独设置,验证返回消息
     * @param array $_messages
     */
    public function setMessages($_messages=[])
    {
        self::$_messages = array_merge(self::$_messages,$_messages);
    }

    /***
     * 实例化后单独设置,验证场景
     * @param array $_scenes
     */
    public function setScenes($_scenes=[])
    {
        self::$_scenes = array_merge(self::$_scenes,$_scenes);
    }

    /***
     * 实例化后单独设置,挂载数据
     * @param array $data
     */
    public function setData($data=[])
    {
        self::$data = array_merge(self::$data,$data);
    }



    /***
     * 校验数据
     * @param $key string 当前校验的key
     * @param $data array 校验的数据
     * @param $_rules array 当前的校验数组 -> 其包含着 keys values
     * @return null|string|bool
     */
    protected static function checkValue($key,&$data,$_rules=[])
    {
        // 第一步 取出 keys 和 values
        $keys = $_rules['keys'];
        $values = $_rules['values'];

        // 第二步, 遍历规则
        foreach ($values as $index => $rule){
            // 2.1 定义一个接收返回的结果
            $result = null; // 结果只要是一个非 true 的结果,就是无法通过的
            // 2.2 定义一个 单条规则
            $_rule = null;
            // 2.3 规则需要的参数
            $_param = '';
            // 2.4 看一看,单条规则中是否存在 : 需要带参数的
            if(stripos($rule,':',1) > 1){
                // 2.4.1 将规则和参数进行分开
                list($_rule, $_param) = explode(':',$rule);
            }else{
               $_rule = $rule;
            }
            // 2.5 执行is函数
            $result = self::is($_rule,$key,$data,$_param,$keys['name']);
            // 2.6 返回非 true 退出循环
            if ($result !== true) {
                return $result;
            }
        }
        // 第三步,所有数据都通过了
        return true;
    }

    /***
     * 使用环境进行校验数据
     * @param $data array
     * @param $scene string
     * @return bool|string
     */
    protected static function useSceneCheckRules(&$data,$scene)
    {
        // 初始化校验环境是否存在
        if(!isset(self::$_scenes[$scene])){
            // 提示一条错误
            self::$errMsg = "scene $scene no exists scenes in";
            return false;
        }
        // 第一步,需要获得字段名
        $fields = self::$_scenes[$scene];
        // 第二步,遍历需要检测的字段
        foreach ($fields as $field){
            // 2.1 小步 -> 有可能环境字段乱写,也是不行的
            if (!isset(self::$rulesKeyValueArray[$field])){
                // 提示一条错误
                self::$errMsg = "field $field no exists rules in";
                return false;
            }
            // 2.2 小步 把规则取出来
            $rule = self::$rulesKeyValueArray[$field];
            // 2.3 小步 执行到 校验值
            $result = self::checkValue($field,$data,$rule);
            // 2.4 小步 返回非true,则退出
            if($result !== true){
                return $result;
            }
        }
        // 执行完了,没有错误的,是不是就通过了呀
        return true;
    }

    /***
     * 使用正常模式校验规则
     * @param $data array
     * @return bool|null|string
     */
    protected static function useNormalCheckRules(&$data)
    {
        // 第一步,遍历当前所有数据,当规则中有存在此key的时候,我才校验,不存在,我校验它干啥?
        foreach ($data as $key => $value){
            // 1.1 如果当前key不存在规则中,则无需校验
            if(isset(self::$rulesKeyValueArray[$key])){
                // 1.1.1 执行校验数据 并返回结果
                $result = self::checkValue($key,$data,self::$rulesKeyValueArray[$key]);
                // 1.1.2 如果返回来的是一个 非 true 则退出
                if($result !== true){
                    return $result;
                }
            }
        }
        // 第二步,执行完了,就是都通过了
        return true;
    }

    /***
     * 拆解路由规则,将规则以键值对的当方式进行排列
     * @param $_rules array
     */
    protected static function splitRule($_rules)
    {
        // 对key 进行排列
        $keys = array_keys($_rules);
        // 将 key|name 进行区分
        self::$rulesKeyArray = array_map(function ($key){
            // 如果有带|线的,就将其分开
            if (stripos($key,'|',1) > 1){
                list($_key,$_name) = explode('|',$key);
                return ['key' => $_key, 'name' => $_name];
            }else{
                return ['key' => $key, 'name' => $key];
            }
        },$keys);
        // 提取真实的key  name|姓名
        self::$realRulesKeyArray = array_map(function ($item){
            return $item['key'];
        },self::$rulesKeyArray);
        // 对value进行排列
        $values = array_values($_rules);
        // 将每个值进行分开 string|length:6,50|max:30
        self::$rulesValueArray = array_map(function ($value){
            // 直接返回数组
            return explode('|',$value);
        },$values);
        // 再将其合并起来
        foreach (self::$realRulesKeyArray as $index => $key){
            self::$rulesKeyValueArray[$key] = array(
                 'keys' => self::$rulesKeyArray[$index],
                 'values' => self::$rulesValueArray[$index]
            );
        }
    }

    /***
     * 开始校验数据,成功则返回true,否则 false
     * @param null|string $scene
     * @return bool
     */
    public function check($scene=null)
    {
        // 第一步 拆分规则
        self::splitRule(self::$_rules);
        // 1.1 定义一个返回结果,此结果 要么 bool 要么 string 就是一个
        $result = false; // true 表示验证通过
        // 第二步,如果使用了环境校验,则执行环境校验,否则就是正常校验
        if(is_string($scene)){
            // 2.1 设置了校验环境
            self::$scene = $scene;
            // 2.2 执行使用环境进行校验并返回一个结果
            $result = self::useSceneCheckRules(self::$data,self::$scene);
        }else{
            // 2.3 执行使用正常进行校验并返回结果
            $result = self::useNormalCheckRules(self::$data);
        }

        // 第三步,返回的结果是一个非 true 就是发生了错误,并无法通过
        if($result !== true){
            // 3.1 要是非布尔值,就是错误信息
            if(!is_bool($result)){
                self::$errMsg = $result;
            }
            $result = false;
        }
        // 最后一步,结果就是true,表示通过
        return $result;
    }

    /***
     * 获取错误信息
     */
    public function getError()
    {
        // 返回错误内容
        return self::$errMsg;
    }

    /***
     * 获取默认消息
     * @param $rule string 验证规则
     * @param $params string 验证传入的参数
     * @param $alias string 别名
     * @return string
     */
    protected static function getDefaultMessage($rule,$params,$alias)
    {
        // 第一步,通过rule验证规则,获得默认的错误提示
        $msg = isset(self::$typeMsg[$rule]) ? self::$typeMsg[$rule] : '%s验证失败';
        // 第二步,对msg逐步分析,(%s) -> 必须是有的,而且只有一个 , (:params 或 :1 或 :2) 三种情况
        if(stripos($msg,':params',1) > 1){
            $msg = str_replace(':params',$params,$msg);
        }
        // 第三步,继续对msg分析 :1 :2 成对出现的
        if(stripos($msg,':1',1) > 1){
            // 将其参数打成数组
            list($one,$two) = explode(',',$params);
            $msg = str_replace(':1',$one,$msg);
            $msg = str_replace(':2',$two,$msg);
        }
        // 第四步,替换别名,%s
        return sprintf($msg,$alias);
    }

    /***
     * 设置错误消息
     * @param $rule string 校验规则
     * @param $key string 校验的key
     * @param $params string 规则参数
     * @param $alias string 别名
     */
    protected static function setError($rule,$key,$params,$alias)
    {
        // 1.1 先以name.require对应结果去找,找不到,再以key(name)形式去找
        $msg = self::getMessages($key.'.'.$rule);
        // 1.2 是不是没有找到 得到null
        if(is_null($msg)){
            $msg = self::getMessages($key);
        }
        // 1.3 如果还是null,那就只能输出默认得错误了
        if (is_null($msg)){
            // 1.3.1 默认输出的错误提示信息
            self::$errMsg = self::getDefaultMessage($rule,$params,$alias);
        }else{
            // 1.3.2 输出用户自定义的错误消息
            self::$errMsg = $msg;
        }
    }

    /***
     * 获取错误消息
     * @param $key string
     * @return mixed|null
     */
    protected static function getMessages($key)
    {
        // 三目运算进行判断,并返回结果
        return isset(self::$_messages[$key]) ? self::$_messages[$key] : null;
    }

    /***
     * @param $rule string  指定校验的规则
     * @param $key string  指定校验 字段
     * @param $data array  指定校验的数据
     * @param $params string 校验规则 传入的参数 => 20,30
     * @param $alias string  别名
     * @return bool|string 返回非true则失败
     */
    final protected static function is($rule,$key,&$data,$params,$alias)
    {
        // 第一步,定义一个返回结果,一个校验值
        $result = false;
        $value = null;
        // 第二步,需要确保所对应的key,必须存在data中,否则接下来均会报错的
        if(!isset($data[$key])){
            // 2.1 要保证需要验证的key,必须存在data里
            $result = "$key is no exists data in";
            // 2.2 停止往下执行
            return $result;
        }else{
            // 2.3 获得校验值
            $value = $data[$key];
        }
        // 第三步,执行 switch 语句
        switch ($rule){
            case 'require':
                // 3.1 为必须存在,且不能为空
                $result = !empty($value);
                break;
            case 'accepted':
                // 3.2 接受
                $result = in_array($value, ['1', 'on', 'yes']);
                break;
            case 'date':
                // 3.3 是否是一个有效日期
                $result = false !== strtotime($value);
                break;
            case 'alpha':
                // 3.4 只允许字母
                $result = self::regex($value, '/^[A-Za-z]+$/');
                break;
            case 'alphaNum':
                // 3.5 只允许字母和数字
                $result = self::regex($value, '/^[A-Za-z0-9]+$/');
                break;
            case 'alphaDash':
                // 只允许字母、数字和下划线 破折号
                $result = self::regex($value, '/^[A-Za-z0-9\-\_]+$/');
                break;
            case 'chs':
                // 只允许汉字
                $result = self::regex($value, '/^[\x{4e00}-\x{9fa5}]+$/u');
                break;
            case 'chsAlpha':
                // 只允许汉字、字母
                $result = self::regex($value, '/^[\x{4e00}-\x{9fa5}a-zA-Z]+$/u');
                break;
            case 'chsAlphaNum':
                // 只允许汉字、字母和数字
                $result = self::regex($value, '/^[\x{4e00}-\x{9fa5}a-zA-Z0-9]+$/u');
                break;
            case 'chsDash':
                // 只允许汉字、字母、数字和下划线_及破折号-
                $result = self::regex($value, '/^[\x{4e00}-\x{9fa5}a-zA-Z0-9\_\-]+$/u');
                break;
            case 'phone':
                // 是否为有效的手机
                $result = self::regex($value, '/^1[34578]\d{9}$/ims');
                break;
            case 'activeUrl':
                // 是否为有效的网址
                $result = checkdnsrr($value);
                break;
            case 'ip':
                // 是否为IP地址
                $result = self::filter($value, [FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6]);
                break;
            case 'url':
                // 是否为一个URL地址
                $result = self::filter($value, FILTER_VALIDATE_URL);
                break;
            case 'float':
                // 是否为float
                $result = self::filter($value, FILTER_VALIDATE_FLOAT);
                break;
            case 'number':
                $result = is_numeric($value);
                break;
            case 'integer':
                // 是否为整型
                $result = self::filter($value, FILTER_VALIDATE_INT);
                break;
            case 'email':
                // 是否为邮箱地址
                $result = self::filter($value, FILTER_VALIDATE_EMAIL);
                break;
            case 'boolean':
                // 是否为布尔值
                $result = in_array($value, [true, false, 0, 1, '0', '1'], true);
                break;
            case 'array':
                // 是否为数组
                $result = is_array($value);
                break;
//            case 'file':
//                $result = $value instanceof File;
//                break;
//            case 'image':
//                $result = $value instanceof File && in_array($this->getImageType($value->getRealPath()), [1, 2, 3, 6]);
//                break;
//            case 'token':
//                $result = $this->token($value, '__token__', $data);
//                break;
            default:
                // 第三小步,剩下的不是方法,就是正则运算了
                if(method_exists(self::getInstance(),$rule)){ // 判断方法是否存在
                    // 固定参数传递过去,并执行此方法
                    $result = call_user_func_array(array(self::getInstance(),$rule),[$rule,$key,$value,$params,$alias]);
                }else{
                    // 不存在的方法,就当作是正则
                    $result = self::regex($value, $rule);
                }
        }

        // 第四步, 当结果是false的时候,就去获取对应的错误消息
        if($result===false){
            // 4.1 执行设置错误消息
            self::setError($rule,$key,$params,$alias);
        }
        // 第五步,将执行得到的结果返回给校验函数,获得结果
        return $result;
    }

    /***
     * 验证值得长度,是否在必须大于几,小于几之间
     * @param $value mixed 需要校验得值
     * @param $params string 函数所需要的参数
     * @return bool
     */
    final protected  function  length($value,$params)
    {
        // 获得所有参数中的value和params
        list(,,$value,$params,) = func_get_args();
        //  强制转成字符串
        $value = (string) $value;
        // 获得规定的长度
        list($min,$max) = explode(',',$params);
        // 判断长度是否符合
        if (strlen($value) >= (int) $min && strlen($value) <= (int) $max){
            return true;
        }else{
            return false;
        }
    }

    /***
     * 校验一个数值,是否在多少至多少之间
     * @param $value mixed 校验的数字
     * @param $params string 校验的参数
     * @return bool
     */
    final protected function between($value,$params)
    {
        // 获得所有参数中的value和params
        list(,,$value,$params,) = func_get_args();
        //  强制转成数字
        $value = (int) $value;
        // 获得规定的长度
        list($min,$max) = explode(',',$params);
        // 判断数值是否符合
        if ($value >= (int) $min && $value <= (int) $max){
            return true;
        }else{
            return false;
        }
    }

    /***
     * 校验一个数值,是不能在多少至多少之间的
     * @param $value mixed 校验的数字
     * @param $params string 校验的参数
     * @return bool
     */
    final protected function notBetween($value,$params)
    {
        // 获得所有参数中的value和params
        list(,,$value,$params,) = func_get_args();
        //  强制转成数字
        $value = (int) $value;
        // 获得规定的长度
        list($min,$max) = explode(',',$params);
        // 判断数值是否符合
        if ($value <= (int) $min || $value >= (int) $max){
            return true;
        }else{
            return false;
        }
    }

    /***
     * 判断一个字符串长度,最大值不能为多少
     * @param $value mixed
     * @param $params string
     * @return bool
     */
    final protected function max($value,$params)
    {
        // 获得所有参数中的value和params
        list(,,$value,$params,) = func_get_args();
        //  强制转成字符串
        $value = (string) $value;
        // 读取参数
        $max = (int) $params;
        // 判断长度
        if(strlen($value) <= $max){
            return true;
        }else{
            return false;
        }
    }

    /***
     * 判断一个字符串长度,最小值不能为多少
     * @param $value mixed
     * @param $params string
     * @return bool
     */
    final protected function min($value,$params)
    {
        // 获得所有参数中的value和params
        list(,,$value,$params,) = func_get_args();
        //  强制转成字符串
        $value = (string) $value;
        // 读取参数
        $min = (int) $params;
        // 判断长度
        if(strlen($value) >= $min){
            return true;
        }else{
            return false;
        }
    }


    /**
     * 使用正则验证数据
     * @access protected
     * @param mixed     $value  字段值
     * @param mixed     $rule  验证规则 正则规则或者预定义正则名
     * @return mixed
     */
    protected static function regex($value, $rule)
    {
        if (0 !== strpos($rule, '/') && !preg_match('/\/[imsU]{0,4}$/', $rule)) {
            // 不是正则表达式则两端补上/
            $rule = '/^' . $rule . '$/';
        }
        return is_scalar($value) && 1 === preg_match($rule, (string) $value);
    }

    /**
     * 使用filter_var方式验证
     * @access protected
     * @param mixed     $value  字段值
     * @param mixed     $rule  验证规则
     * @return bool
     */
    protected static function filter($value, $rule)
    {
        $param = null;
        if (is_string($rule) && strpos($rule, ',')) {
            list($rule, $param) = explode(',', $rule);
        } elseif (is_array($rule)) {
            $param = isset($rule[1]) ? $rule[1] : null;
            $rule  = $rule[0];
        } else {
            $param = null;
        }
        return false !== filter_var($value, is_int($rule) ? $rule : filter_id($rule), $param);
    }



}