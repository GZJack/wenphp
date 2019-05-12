<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/3/31
 * Time: 10:26
 */

namespace wen;

/***
 * 负责处理请求的,不能被继承,是单例模式,Request::getInstance()
 * Class Request
 * @package wen
 */
final class Request
{
    /***
     * 用于接收当前类实例
     * @var null
     */
    private static $instance = null;

    /***
     * 是否构建过了,构建了,就无需再构建了
     * @var bool
     */
    private static $isBuild = false;

    /***
     * 挂载一份原始的 $_SERVER 的数据
     * @var null
     */
    private $server = [];

    /***
     * 新产生的结果
     * @var array
     */
    public $newResult = [];


    /***
     * uri 构建 MVC 的主要部分
     * @var string
     */
    public $request_uri = '';

    /***
     * /index.php 带斜杠的文件名,入口文件
     * @var string
     */
    public $script_name = '';

    /***
     * @var string 去掉文件名后剩下的uri
     */
    public $uri = '';

    /***
     * 完整的请求地址
     * @var null
     */
    public $url = null;

    /***
     * @var string 协议
     */
    public $scheme = 'http';

    /***
     * @var string 域名
     */
    public $domain = '';

    /***
     * @var int 端口号
     */
    public $port = 80;

    /***
     * 协议+域名+端口
     * @var string
     */
    public $http_host_port = '';

    /***
     * ?问号后所有的参数
     * @var string
     */
    public $query = '';


    /***
     * 请求方法
     * @var string
     */
    public $method = 'GET';

    /***
     * @var array 可执行的提交方法,及当前实例对应的方法集
     */
    private static $_method = ['GET','POST','PUT','DELETE','PATCH','OPTIONS','HEAD','REQUEST','SERVER'];

    /***
     * 这是解析后得到的path_info,并非是$_SERVER上的
     * @var array
     */
    public $path_info = [];


    /***
     * @var array 当前类的错误对象集合
     */
    private static $errMsg = [
        'GET' => "Get里缺少参数:",
        'POST' => "Post里缺少参数:",
        'ALL' => "当前提交里缺少参数:",
        'HEAD' => "请求头缺少参数:",
        'PUT' => "Put里缺少参数:",
        'DELETE' => "Delete里缺少参数:",
        'PATCH' => "Patch里缺少参数:",
        'OPTIONS' => "Options里缺少参数:",
        'SERVER' => "Server里缺少参数:",
        'REQUEST' => "Request里缺少参数:",
        'method' => "请求的提交方法不正确",
        'limit' => "提交存在多余的参数"
    ];

    /***
     * 请求参数
     */
    // 暴露的 php://input
    public $phpInput = '';
    // 所有的参数,包括GET POST PUT DELETE 路由
    private static $param = [];
    // 单一的 GET
    private static $get = [];
    // 单一  POST
    private static $post = [];
    // 单一 $_REQUEST
    private static $request = [];
    // 单一 路由
    private static $route = [];
    // 单一 PUT
    private static $put = [];
    // 单一 $_SESSION
    private static $session = [];
    // 单一 $_FILE
    private static $file = [];
    // 单一 $_COOKIE
    private static $cookie = [];
    // 单一 请求头参数
    private static $header = [];



    /***
     * 禁止外部new
     * Request constructor.
     */
    private function __construct()
    {
        // 获得 php://input 原始数据 这是一个字符串
        $this -> phpInput = file_get_contents('php://input');
    }

    /***
     * 禁止外部克隆
     */
    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    /***
     * 获得当前类实例
     * @return static
     */
    public static function getInstance()
    {
        if(!self::$instance instanceof self){
            self::$instance = new self();
        }
        return self::$instance;
    }


    /***
     * 生成基础 url
     * @param $server array
     */
    private function baseUrl(&$server)
    {
        // 拼接完整的url => 协议://主机名:端口/uri $server['request_scheme']
        $url = $this -> scheme().'://'.$server['http_host'];
        // 端口为 80 和 443 就不显示端口了
        $port = $server['server_port'];
        // 判断端口
        $url .= ($port === '80' || $port === '443') ? '' : ':'.$port;
        // 协议+域名+端口
        $this -> http_host_port = $url;
        // 再拼接uri
        $url .= $server['request_uri'];
        // 赋值到当前实例上
        $this -> url = $url;
    }

    private function create()
    {
        // 挂载 既有get 又有 post
        $this -> setParam($_REQUEST,null,'request');
        // 把GET上的参数全部拿下来
        $this -> setParam($_GET,null,'get');
        // 把POST的参数也挂载起来
        $this -> setParam($_POST,null,'post');

        // 将所有的key转换为小写
        $server = array_change_key_case($_SERVER,CASE_LOWER);
        // 挂载原始数据
        $this -> server = $server;
        // 挂载 文件名 /index.php 隐藏和显示 都会有的
        $this -> script_name = $server['script_name'];
        // 挂载 uri (有可能带文件名)
        $this -> request_uri = $server['request_uri'];
        // 请求的方式,方法 转成大写,确保是大写的
        $this -> method = strtoupper($server['request_method']);
        // 挂载协议
        $this -> scheme = isset($server['request_scheme']) ? $server['request_scheme'] : 'http';
        // 主机名
        $this -> domain = $server['http_host'];
        // 挂载 端口
        $this -> port = $server['server_port'];
        // ? 剩余部分的get请求参数
        $this -> query = $server['query_string'];
        // 生成基础 url
        $this -> baseUrl($server);
    }

    /***
     * 请求的构建,暴露接口让外部调用
     */
    public function build()
    {
        // 如果构建了,就不用再构建了
        if (self::$isBuild) return null;
        self::$isBuild = true; // 开始构建
        // 构建
        $this -> create();
    }

    /***
     * 例子:url => http://www.xxxx.com/index.php/home/index/index?name=张三&age=18
     * uri => /index.php/home/index/index?name=张三&age=18
     * name => /index.php
     * @param $requestUri string $_SERVER里的uri
     * @param $scriptName string $_SERVER里的带文件名部分
     * @return array
     */
    public static function getPathInfo($requestUri,$scriptName)
    {
        // 第一步,首先要保证 uri 要大于 文件名 的长度, 否则就不用截取 文件名
        if(strlen($requestUri) >= strlen($scriptName)){
            // 使用替换 文件名是不靠谱的,只能假设左边的uri是带文件名
            $leftFileName = substr($requestUri,0,strlen($scriptName));
            // 判断是否有文件名存在,如果相等则存在,存在我们才需要处理,不相等,就不用管了
            if(substr_compare($leftFileName,$scriptName,0) === 0){
                // 从文件名的长度开始截取,直到最后
                $requestUri = substr($requestUri,strlen($scriptName));
            }
        }
        // 将去掉文件的部分的uri,进行转成数组,例子 /home/index/index?name=张三&age=18
        $uriArray = parse_url($requestUri);
        // 转成小写并将左边的'/'去掉,最后转换成数组
        return explode('/',ltrim(strtolower($uriArray['path']),'/'));
    }


    /***
     * 设置挂载参数 (路由规则里设置了参数,也是可以被挂载到这里面来的)
     * @param $key string|array 参数key值
     * @param $value mixed 需要挂载的参数值
     * @param string $from
     */
    public function setParam($key,$value=null,$from=null)
    {
        // 如果key数组,value是null,则认为是合并数组 多个值挂载
        if (is_array($key)){
            self::$param = array_merge(self::$param,$key);
        }
        // 单个值挂载
        if(is_string($key)){
            self::$param[$key] = $value;
        }
        // 如果是字符串,则进行给指定的元素上赋值
        if(is_string($from)){
            // 将 from 小写
            $from = strtolower($from);
            // 大写的
            $_from = strtoupper($from);
            // 如果存在这个数组中的方法,则可以执行
            if(in_array($_from,self::$_method) && method_exists($this,$from)){
                // 执行对应实例上的方法
                $this -> $from($key,$value);
            }
        }
    }


    /***
     * 获取参数
     * @param null|string $name
     * @param null|mixed $default
     * @param null|string $to
     * @return array|string|null
     */
    public function getParam($name=null,$default=null,$to=null)
    {
        // 获得全部的param参数
        if(is_null($name) && is_null($to)){
            return self::$param;
        }else{
            if (is_null($to)){
                if(is_string($name)){
                    return isset(self::$param[$name]) ? self::$param[$name] : $default;
                }else{
                    return $default;
                }
            }else{
                // 小写 to
                $to = strtolower($to);
                // 大写 to
                $_to = strtoupper($to);
                // 方法存不存在
                if(in_array($_to,self::$_method) && method_exists($this,$to)){
                    // 执行对应当前实例上的方法
                    return $this -> $to($name,$default);
                }else{
                    return $default;
                }
            }
        }
    }

    /***
     * 校验 GET 和 POST PUT DELETE
     * @param $params array
     * @param $requireParam string|array
     * @param $method string
     */
    private static function checkParam(&$params,$requireParam,$method)
    {
        // 判断是否通过,默认是通过的
        $isOk = true;
        // 缺少的key
        $lackKey = '';
        // 如果是字符串,则直接校验
        if(is_string($requireParam)){
            // 记录下当前缺少的key
            $lackKey = $requireParam;
            // 校验是否存在
            $isOk = isset($params[$requireParam]) ? true : false;
        }elseif (is_array($requireParam)){
            // 多个进行校验
            foreach ($requireParam as $key){
                // 只要一有不存在的,就不会对其他参数继续校验了,就是一个一个地校验
                if($isOk === true){
                    // 记录下key
                    $lackKey = $key;
                    // 判断
                    $isOk = isset($params[$key]) ? true : false;
                }
            }
        }else{
            // 开发人员设置得不得当,是允许通过得,并没有做什么校验
            Response::debug("[ WARNING ] param require is string or array",-2,'Request.checkGetParam');
            // 允许通过
            $isOk = true;
        }
        // 不通过,输出结果,并停终止进程
        if($isOk === false){
            // 输出错误结果
            Response::send(self::$errMsg[$method].$lackKey,20002,'Request.checkGetParam');
            // 输出结果,结束当前运行
            exit;
        }
    }

    /***
     * 校验针对的提交方法的,只是校验无返回值,当出现不存在的参数时,会自动中断运行,输出结果的
     * @param $requireParam string|array
     * @param string|null $method
     */
    public function has($requireParam,$method=null)
    {
        if(is_null($method)){
            // 在所有的参数里面进行校验
            self::checkParam(self::$param,$requireParam,'ALL');
        }else{
            // 将提交方式进行大写
            $method = strtoupper($method);
            // 校验是否合法
            if(!in_array($method,self::$_method)){
                Response::debug("[ WARNING ] has() set method is error",-2,'Request.has');
            }
            // 通过将 $method 小写 并执行当前类上对应的方法,并返回真实的结果
            $type = strtolower($method);
            // 获得对应的数据
            $data = $this -> $type(null,null);
            // 再对其校验
            self::checkParam($data,$requireParam,$method);
        }
    }

    /***
     * 限定参数(包括GET POST PUT DELETE ...)
     * 限定参数,无提交方法限制,就是确保,当前提交里所提交的参数
     * 意思->也就是只能提交规定里的参数,多一个就报错,或404
     * @param $limitParam string|array
     */
    public function limit($limitParam)
    {
        // 限定的参数
        $limit = [];
        // 这种限制的,就是用已经存在的参数来循环,对比当前限制的,如果超出了限制的,就是false
        if(is_string($limitParam)){
            array_push($limit,$limitParam);
        }elseif (is_array($limitParam)){
            $limit = array_merge($limit,$limitParam);
        }
        // 得到参数所有的keys
        $keys = array_keys(self::$param);
        // 找差集
        if(!empty(array_diff_key($limit,$keys))){
            // 输出测试结果
            Response::debug(self::$errMsg['limit'],404,'Request.limit');
            // 输出结果,结束当前运行
            exit;
        }
    }

    /***
     * 获得$_SERVER的参数
     * @param null|string|array $name
     * @param null|mixed $default
     * @return null|mixed
     */
    public function server($name=null,$default=null)
    {
        // 如果为空,则重新加载
        if(empty($this -> server)){
            $this -> server = array_change_key_case($_SERVER,CASE_LOWER);
        }
        // 如果是字符串,就是想获取某一项值
        if(is_string($name)){
            $name = strtolower($name);
            return isset($this -> server[$name]) ? $this -> server[$name] : $default;
        }elseif (is_array($name)){
            return $this -> server = array_merge($this -> server,array_change_key_case($name,CASE_LOWER));
        }else{
            return $this -> server;
        }
    }

    /***
     * 限定提交方法,当提交的方法不一致的时候,就会中断,并输出404
     * @param null|string $method
     */
    public function method($method=null)
    {
        // 如果不是null,就是执行限定方法
        if(!is_null($method)){
            // 将限定的方法大写
            $method = strtoupper($method);
            // 比较 不相等
            if($this -> method !== $method){
                // 输出测试结果
                Response::debug(self::$errMsg['method'],404,'Request.method');
                // 输出结果,结束当前运行
                exit;
            }
        }
    }

    /***
     * post put delete 中获得对应的值
     * @param $data array
     * @param $name string|array
     * @param $default mixed
     * @return mixed
     */
    private static function getValue(&$data,$name,$default)
    {
        // 如果 $name 是数组的话,则往请求头里添加内容
        if(is_array($name)){
            $data = array_merge($data,$name);
        }
        // 如果是字符串则返回对应的内容
        if(is_string($name)){
            return isset($data[$name]) ? $data[$name] : $default;
        }else{
            if(is_null($name)){
                return $data;
            }
            return $default;
        }
    }

    /***
     * 设置和获取 请求头
     * @param $name string|array|null
     * @param null|mixed $default
     * @return mixed|null
     */
    public function header($name=null,$default=null)
    {
        // 如果为空
        if(empty(self::$header)){
            $header = function_exists('apache_request_headers') ? array_change_key_case(apache_request_headers(),CASE_LOWER) : [];
            // 如果上面无法获得请求头,就只能从$_SERVER上获取
            if(empty($header)){
                $server = $this -> server ?: array_change_key_case($_SERVER,CASE_LOWER);
                // 遍历一遍
                foreach ($server as $key => $val) {
                    // 将带 http_ 的全部读取出来
                    if (0 === strpos($key, 'http_')) {
                        // 去掉 http_ 并把下划线换成短线-
                        $key = str_replace('_', '-', strtolower(substr($key, 5)));
                        // 赋值
                        $header[$key] = $val;
                    }
                }
                // 赋值请求头的 提交内容格式
                $header['content-type'] = isset($server['content_type']) ? $server['content_type'] : '';
                // 提交内容长度
                $header['content-length'] = isset($server['content_length']) ? (int) $server['content_length'] : 0 ;
            }
            // 合并起来
            self::$header = array_merge(self::$header,$header);
        }

        // 返回对应的值
        return self::getValue(self::$header,$name,$default);
    }

    /***
     * 通过请求头 content-type 的类型 来解析 php://input 的内容
     * @return array|bool|mixed|null|string
     */
    private function getInput()
    {
        // 定义返回的结果集
        $result = null;
        // 从请求上获取 内容类型
        $contentType = $this -> header('content-type','');
        // 从 php://input 上获取内容
        $phpInput = $this -> phpInput;
        // 再去看请求头有没有 content-type 类型不为空,则进行比较
        if (!empty($contentType)){
            // 如果存在Json的标准格式
            if(stripos($contentType,'application/json') !== false){
                $result = (array) json_decode($phpInput,true);
            }else if (stripos($contentType,'application/xml') !== false){
                // 将其转成json字符串
                $xml = simplexml_load_string($phpInput,'SimpleXMLElement', LIBXML_NOCDATA);
                $result = json_decode(json_encode($xml),TRUE);
            }else{
                // 默认的处理方法
                parse_str($phpInput, $result);
            }
        }else{
            if (empty($phpInput)){
                $result = $phpInput;
            }else{
                // 默认的处理方法
                parse_str($phpInput, $result);
            }
        }
        // 返回结果集
        return $result;
    }

    /***
     * 获取获取get或设置get的方法
     * @param null|string|array $name
     * @param null|mixed $default
     * @return array|mixed
     */
    public function get($name=null,$default=null)
    {
        // 如果为空
        if(empty(self::$get)){
            self::$get = $_GET;
        }
        // 返回对应的值
        return self::getValue(self::$get,$name,$default);
    }

    /***
     * 获取获取post或设置post的方法
     * @param null|string|array $name
     * @param null|mixed $default
     * @return array|mixed
     */
    public function post($name=null,$default=null)
    {
        // 如果为空,则则进行挂载
        if(empty(self::$post)){
            // 为空的话,就有可能 content-type 是json 或者 xml
            if(empty($_POST)){
                // 从php://input上获取,如果是字符串,就不用理会了
                $phpInput = $this -> getInput();
                // 如果不存在值,即返回来的是字符串
                self::$post = is_array($phpInput) ? $phpInput : [];
            }else{
                self::$post = $_POST;
            }
        }
        // 返回对应的值
        return self::getValue(self::$post,$name,$default);
    }

    /***
     * 即获得GET 又可以获得 POST 的数据
     * @param null|string|array $name
     * @param null|mixed $default
     * @return mixed
     */
    public function request($name=null,$default=null)
    {
        // 如果为空
        if(empty(self::$request)){
            self::$request = $_REQUEST;
        }
        // 返回对应的值
        return self::getValue(self::$request,$name,$default);
    }

    /***
     * 设置和获取route的数据
     * @param null|string|array $name
     * @param null|mixed $default
     * @return mixed
     */
    public static function route($name=null,$default=null)
    {
        // 返回对应的值
        return self::getValue(self::$route,$name,$default);
    }

    /***
     * 设置和获取put的数据
     * @param null|string|array $name
     * @param null|mixed $default
     * @return mixed
     */
    public function put($name=null,$default=null)
    {
        // 判断是否为空
        if(empty(self::$put)){
            self::$put = $this -> getInput();
        }
        // 返回对应的值
        return self::getValue(self::$put,$name,$default);
    }

    /***
     * 设置和获取delete的数据
     * @param null|string|array $name
     * @param null|mixed $default
     * @return mixed
     */
    public function delete($name=null,$default=null)
    {
        return $this -> put($name,$default);
    }

    /***
     * 设置和获取patch的数据
     * @param null|string|array $name
     * @param null|mixed $default
     * @return mixed
     */
    public function patch($name=null,$default=null)
    {
        return $this -> put($name,$default);
    }



    /***
     * 设置和获取options的数据
     * @param null|string|array $name
     * @param null|mixed $default
     * @return mixed
     */
    public function options($name=null,$default=null)
    {
        return $this -> put($name,$default);
    }


    /***
     * 获得session
     * 设置在 Response类上
     * @param null|string $name
     * @param null|mixed $default
     * @return null|string
     */
    public function getSession($name=null,$default=null)
    {
        // 先开启session
        session_start();
        // 如果为空,则需要读取
        if(empty(self::$session)){
            // 挂载 session
            self::$session = $_SESSION;
        }
        // 返回对应的值
        return self::getValue(self::$session,$name,$default);
    }

    /***
     * 获得Cookie
     * 设置在 Response类上
     * @param null $name
     * @param null|mixed $default
     * @return null
     */
    public function getCookie($name=null,$default=null)
    {
        // 如果为空,则需要挂载
        if(empty(self::$cookie)){
            // 挂载 cookie
            self::$cookie = $_COOKIE;
        }
        // 返回对应的值
        return self::getValue(self::$session,$name,$default);
    }

    public function file()
    {
        if(empty(self::$file)){
            self::$file = $_FILES;
        }
    }


    /**
     * 判断是否使用了 https 协议
     * @return bool
     */
    public function isSsl()
    {
        $server = $this -> server;
        // 校验$_SERVER里是否存在 HTTPS
        if (isset($server['https']) && ('1' == $server['https'] || 'on' == strtolower($server['https']))) {
            return true;
        } elseif (isset($server['request_scheme']) && 'https' == $server['request_scheme']) {
            // 校验$_SERVER里是否存在 REQUEST_SCHEME
            return true;
        } elseif (isset($server['server_port']) && ('443' == $server['server_port'])) {
            // 校验$_SERVER里是否存在 SERVER_PORT
            return true;
        } elseif (isset($server['http_x_forwarded_proto']) && 'https' == $server['http_x_forwarded_proto']) {
            // 校验$_SERVER里是否存在 HTTP_X_FORWARDED_PROTO
            return true;
        }
        // 没有,默认就是 http
        return false;
    }


    /**
     * 当前URL地址中的scheme参数
     * @access public
     * @return string
     */
    public function scheme()
    {
        return $this -> isSsl() ? 'https' : 'http';
    }



}