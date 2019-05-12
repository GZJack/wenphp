<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/3/31
 * Time: 22:50
 */

namespace wen;


class ErrorException extends \Exception
{
    /**
     * 用于保存错误级别
     * @var integer
     */
    protected $severity;

    /***
     * 错误内容
     * @var array
     */
    protected $errMsgArray = [];

    /**
     * 错误异常构造函数
     * @param integer $severity 错误级别
     * @param string  $message  错误详细信息
     * @param string  $file     出错文件路径
     * @param integer $line     出错行号
     * @param array   $context  错误上下文，会包含错误触发处作用域内所有变量的数组
     */
    public function __construct($severity, $message, $file, $line, array $context = [])
    {
        $this -> severity = $severity;
        $this -> message  = $message;
        $this -> file     = $file;
        $this -> line     = $line;
        $this -> code     = 0;

        echo '级别'.$severity;

        empty($context) || $this -> setErrMsg('Error Context', $context);

        var_dump($context);

        var_dump($this->errMsgArray);
    }



    /**
     * 获取错误级别
     * @return integer 错误级别
     */
    final public function getSeverity()
    {
        return $this->severity;
    }

    /***
     * 设置错误消息
     * @param $errTitle string 错误标题
     * @param array $errContent 错误内容
     */
    final public function setErrMsg($errTitle,array $errContent)
    {
        $this -> errMsgArray[$errTitle] = $errContent;
    }

    /***
     * 获得所有错误的对象
     * @return array
     */
    final public function getErrMsgArray()
    {
        return $this -> errMsgArray;
    }
}