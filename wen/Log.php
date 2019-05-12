<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/4/2
 * Time: 0:31
 */

namespace wen;

/***
 * 日志记录及处理的类,抽象 静态类不可以new
 * Class Log
 * @package wen
 */
abstract class Log
{
    /**
     * 用来存当前的日志信息的
     * @var array
     */
    private static $logs = [];

    /***
     * 记录日志信息
     * @param $msg string|mixed
     * @param string $type
     */
    public static function record($msg,$type='log')
    {
        // 会将同一种类型的日志进行分组,而且以数组进行包裹起来
        self::$logs[$type][] = $msg;
    }

}