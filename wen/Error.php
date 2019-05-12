<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/3/31
 * Time: 22:13
 */

namespace wen;

/***
 * 负责错误类的注册
 * Class Error
 * @package wen
 */
class Error
{

    /**
     * 用于保存错误级别
     * @var integer
     */
    protected $severity;

    /***
     * 错误内容
     * @var array
     * 错误格式说明 ->
     */
    protected static $errMsgArray = [];


    /***
     * 将错误/异常处理 按自定义的方式进行注册
     */
    public static function register()
    {
        error_reporting(E_ALL);
        set_error_handler([__CLASS__, 'appError']);
        set_exception_handler([__CLASS__, 'appException']);
        register_shutdown_function([__CLASS__, 'appShutdown']);
    }

    /**
     * 错误处理
     * @access public
     * @param  integer $errNum      错误编号
     * @param  integer $errStr     详细错误信息
     * @param  string  $errFile    出错的文件
     * @param  integer $errLine    出错行号
     * @return void
     */
    public static function appError($errNum, $errStr, $errFile = '', $errLine = 0)
    {
//        echo '666';
//        $exception = new ErrorException($errNum, $errStr, $errFile, $errLine);
//        // 符合异常处理的则将错误信息托管至 wen\ErrorException
//        if (error_reporting() & $errNum) {
//            throw $exception;
//        }
        echo 'appError';
        echo '<pre>';
        echo '<br>';
        var_dump($errNum);
        echo '<br>';
        var_dump($errStr);
        echo '<br>';
        var_dump($errFile);
        echo '<br>';
        var_dump($errLine);
    }

    /**
     * 异常处理
     * @param $e
     * @throws \Exception
     */
    public static function appException($e)
    {
//        if (!$e instanceof \Exception) {
//            throw new \Exception($e);
//        }

//        $a = new \Exception();
//        $a -> getTraceAsString();

        echo get_class($e);

        echo '<br>appException<br>异常<br>';
        echo '<br>getFile()<br>';
        var_dump($e -> getFile());
        echo '<br>getCode()<br>';
        var_dump($e -> getCode());
        echo '<br>getPrevious()<br>';
        var_dump($e -> getPrevious());
        echo '<br>getLine()<br>';
        var_dump($e -> getLine());
        echo '<br>getTraceAsString()<br>';
        var_dump($e -> getTraceAsString());
        echo '<br>getMessage()<br>';
        var_dump($e -> getMessage());
        echo '<br>';
    }

    /**
     * php应用程序,将结果及错误全部输出后,最后才会执行到这句话
     * 可以利用这一原理,将错误信息,或者日志,放在这里处理
     * 每一个用户过来请求,就将一条记录保存一份,通过Log::save()
     */
    public static function appShutdown()
    {
        // 将错误信息托管至 wen\ErrorException
//        if (!is_null($error = error_get_last()) && self::isFatal($error['type'])) {
//            self::appException(new ErrorException(
//                $error['type'], $error['message'], $error['file'], $error['line']
//            ));
//        }


        // echo '<br>appShutdown<br>日志输出';



        // 写入日志
        // Log::save();
    }

    /**
     * 确定错误类型是否致命
     * @access protected
     * @param  int $type 错误类型
     * @return bool
     */
    protected static function isFatal($type)
    {
        return in_array($type, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE]);
    }


    /***
     * 设置错误消息
     * @param $errTitle string 错误标题
     * @param array $errContent 错误内容
     */
    final public static function setErrMsg($errTitle,array $errContent)
    {
        self::$errMsgArray[$errTitle] = $errContent;
    }

    /***
     * 获得所有错误的对象
     * @return array
     */
    final public static function getErrMsgArray()
    {
        return self::$errMsgArray;
    }


}