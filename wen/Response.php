<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/3/31
 * Time: 10:27
 */

namespace wen;

/***
 * 负责请求后返回的数据
 * Class Response
 * @package wen
 */
class Response
{
    /***
     * 用于接收当前类实例
     * @var null
     */
    private static $instance = null;

    /***
     * @var null 挂载Request类在当前类上
     */
    private $request = null;

    /****
     * @var array 正在等待发送的消息
     */
    protected static $waitMessageArray = [];


    /***
     * 禁止外部new
     * Request constructor.
     */
    private function __construct()
    {
        // 将Request实例挂载在当前实例上
        $this -> request = Request::getInstance();
        // 设置字符集
        $this -> setHeader();
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

    public function setHeader()
    {
        header('Content-type:text/html;charset=utf-8');
    }

    public static function send404($msg='',$form='')
    {
        echo $msg.'404错误页面'.$form;
    }

    /***
     * 等待发送,主要是用于返回不同的显示结果,如果是开发模式,和上线模式
     * 这个接口是用提供给所有类提交的
     * @param string|mixed $msg
     * @param string $from
     */
    public static function waitSend($msg='',$from='')
    {
        /***
         * msg 参数详解 固定格式
         * [ 类型 ] 原因 及结果 。。。
         * from 参数详解 固定格式
         * File.method  (文件名.方法名)
         */
        array_push(self::$waitMessageArray,['msg' => $msg , 'from' => $from]);
    }

    /***
     * 最用发出信息,显示到页面上
     * @param $msg string 显示的内容
     * @param $code int  错误码
     * @param $from string 调用的来源
     */
    public static function send($msg='',$code=0,$from='')
    {
        // 如果两个都不为空的话,则把这个消息挂载到等待上去
        if(!empty($msg) && !empty($from)){
            // 挂载到等待上去
            self::waitSend($msg,$from);
        }
        // 接下来就是需要根据,开发模式下,和上线模式输出不同的结果
        /***
         * 第一种,开发模式
         * 循环遍历输出,等待消息
         */
        if(App::$isAppDebug === true){
            self::appDebugEcho(self::$waitMessageArray);
        }else{
            self::appUpperLineEcho($code);
        }
    }

    /***
     * 直接输出显示的,需要在开发模式下输出
     * @param string $msg
     * @param int $code
     * @param string $from
     */
    public static function debug($msg='',$code=0,$from='')
    {
        if(App::$isAppDebug){
            // 挂载到等待上去
            self::waitSend($msg,$from);
            self::appDebugEcho(self::$waitMessageArray);
            exit(-1);
        }
    }

    /***
     * 直接发送控制器里方法 return 返回来的值,无需挂载
     * @param null|mixed $result action方法返回的结果
     */
    public static function sendActionReturn($result=null)
    {
        if(is_string($result)){
            echo $result;
        }elseif (is_array($result) || is_object($result)){
            print_r($result);
        }else{
            echo $result;
        }
    }

    /***
     * @param int $code 错误码
     */
    private static function appUpperLineEcho($code=0)
    {
        echo '<br>错误码 => '.$code;
    }

    /***
     * @param $waitMessages array 开发模式下的输出
     */
    private static function appDebugEcho($waitMessages)
    {
        // 遍历等待消息
        foreach ($waitMessages as $item){
            echo '<br>MSG => '.$item['msg'].'<br>';
            echo 'FROM => '.$item['from'].'<br>';
        }
    }



    /***
     * 关闭程序了,当程序关闭了,就会在这里执行输出
     */
    public static function appClose()
    {
        echo '系统已经关闭';
        return true;
    }

    /***
     * 设置session
     * @param $name string
     * @param $value string
     */
    public function setSession($name,$value)
    {
        if(is_string($name) && is_string($value)){
            //session_start();
            $_SESSION[$name] = $value;
        }else{
            self::debug("[ WARNING ] setSession() param is error",404,'Response.setSession');
        }
    }

    /***
     * 销毁session
     */
    public function destroySession()
    {
        session_destroy();
    }

    /***
     * @param $name string
     * @param $value mixed
     * @param int $expire
     */
    public function setCookie($name,$value,$expire=0)
    {
        if(is_string($name) && is_string($value) && is_int($expire)){
            if($expire === 0){
                // 十年后超时
                //$expire = $_SERVER["REQUEST_TIME"] + 60*60*24*365*10;
                // 设置Cookie
                setcookie($name, $value);
            }else{
                $expire = $_SERVER["REQUEST_TIME"] + $expire;
                // 设置Cookie
                setcookie($name, $value, $expire);
            }
        }else{
            self::debug("[ WARNING ] setCookie() param is error",404,'Response.setCookie');
        }
    }
}