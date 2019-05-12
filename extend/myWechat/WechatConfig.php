<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/3/29
 * Time: 14:13
 */

namespace myWechat;

/***
 * 存放一些,关于wechat的配置,和常量
 * Class WechatConfig
 * @package myWechat
 */
class WechatConfig
{
    // 设置版本号 便于日后维护
    const VERSION = '1.0.0';
    /***
     * 定义一个配置选项
     * @var array
     */
    private static $options = array(
        'appid' => 'wx71a33b5cc2f0cc8c',
        'appsecret' => '17918a6c0ffa75794949acac37bdc0aa',
        'token' => 'zhangsandalisi',
    );

    /***
     * 设置配置项
     * 当$key,为字符串的时候,就必须有$value
     * 如果$key,是一个数组的话,就直接替换掉 self::$options的配置
     * @param $key mixed
     * @param $value mixed
     */
    public static function set($key,$value)
    {
        // 修改某一项配置
        if (is_string($key) && $value !== null){
            self::$options[$key] = $value;
        }elseif (is_array($key)){
            // 合并配置
            array_merge(self::$options,$key);
        }
    }

    /***
     * 读取配置项
     * 当$key为空的时候,就读取整个配置项,当有字符串的时候,就读取某个配置项
     * @param $key string
     * @return mixed
     */
    public static function get($key="")
    {
        // 一个三目运算,获得结果
        return ($key!=="") ? (isset(self::$options[$key]) ? self::$options[$key] : '') : self::$options;
    }

}