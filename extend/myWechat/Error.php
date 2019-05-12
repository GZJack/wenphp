<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/3/29
 * Time: 14:26
 */

namespace myWechat;


class Error
{
    /***
     * @var string 错误提示
     */
    protected static $errMsg = '';

    /***
     * @var bool 是否开启开发模式
     */
    private static $debug = true;

    /***
     * @param $errMsg string|array 设置错误
     * @param bool $isWechatError
     */
    public static function set($errMsg,$isWechatError=false)
    {
        // 如果是微信错误,就要通过微信的提供的错误码去找对应的错误信息
        if($isWechatError===true){
            // 这个是从错误文件上获得对应的错误内容
            $errMsg = WechatError::get($errMsg['errcode']);
        }
        // 将内容写在属性上
        self::$errMsg = $errMsg;
        // 是否开启开发模式
        if (self::$debug){
            // 会打印出这条错误信息
            var_dump($errMsg);
        }
    }

    /***
     * 获取错误
     * @return string
     */
    public static function getError()
    {
        return self::$errMsg;
    }
}