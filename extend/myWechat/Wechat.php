<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/3/28
 * Time: 21:57
 */

namespace myWechat;

// 模板信息发送类
use myWechat\message\TemplateMessage;

/***
 * 实现微信公众号的接口类,全部实现静态方法书写
 * Class Wechat
 * @package myWachat
 */
class Wechat
{

    // 错误就是获取access_token,有可能总是或不到,但是又不能每次都在那里不停的循环
    private static $get_access_token_fail_count = 0;
    // 允许最大次数为五次
    private static $get_access_token_fail_max_count = 5;
    // 一个时间戳,记录access_token是否,有效的
    private static $token_timeout = 0;
    // access_token
    private static $access_token = '';

    // 容器池,挂载所有公众号的所有方法,name 全称,alias 别名,value 对应的实例
    private static $instances = array();


    /***
     * 挂载实例
     * @param $name string 实例名称
     * @param $object object 挂载的实例
     * @param $alias string  实例别名
     */
    public static function set($name,$object,$alias='')
    {
        // 不符合的直接pass
        if (!is_string($name) || !is_object($object)) return;
        // 往对象池里添加实例
        array_push(self::$instances,self::createinstance($name,$object,$alias));
    }

    /***
     * 校验是否超时
     * @return bool
     */
    private static function checkTimeout()
    {
        // 获取时间戳的差
        $poor = (int) self::$token_timeout - time();
        // 超时时间,小于当前时间,就是超时了
        if($poor > 0){
            return false; // 没超时
        }else{
            return true; // 超时了
        }
    }

    private static function getNewAccessToken()
    {
        // 通过封装的 WechatGetAccessToken 里获取
        $tokenArray = WechatGetAccessToken::getAccessToken();
        // 赋值到当前实例上
        self::$access_token = isset($tokenArray['access_token']) ? $tokenArray['access_token'] : "";
        self::$token_timeout = isset($tokenArray['tiemout']) ? $tokenArray['tiemout'] : 0;
    }

    /***
     * 创建实例对象
     * @param $name string
     * @param $object object
     * @param string $alias
     * @return array
     */
    private static function createinstance($name,$object,$alias='')
    {
        return array(
            'name' => $name,
            'alias' => $alias,
            'value' => $object
        );
    }

    private static function init()
    {
        // 初始化掉这个对象池
        self::$instances = array();
        // 挂载了 发送模板信息 模块
//        array_push(self::$instances,
//            self::createinstance('TemplateMessage',
//                new TemplateMessage(self::$access_token),
//                TemplateMessage::alias));
    }


    /***
     * 从对象池里获取实例
     * @param $key string
     * @return array|object
     */
    public static function get($key)
    {
        // 每次需要实例的时候就会,进行检测是否超时
        if(self::checkTimeout()){
            // 执行获取token
            self::getNewAccessToken();
            // 重新挂载对象池
            self::init();
            // 如果超过错误最大次数,则退出
            if(self::$get_access_token_fail_count <= self::$get_access_token_fail_max_count){
                // 加1
                ++self::$get_access_token_fail_count;
                // 返回 执行自身
                return self::get($key);
            }
        }
        // 如果通过了,就重置为0
        self::$get_access_token_fail_count = 0;
        // 定义一个接收对象
        $object = [];
        // 遍历对象池
        foreach (self::$instances as $item => $value){
            // 别名或名称一致,就返回对象实例
            if($value['name'] === $key || $value['alias'] === $key){
                // 赋值对象实例
                $object = $value['value'];
                break;
            }
        }
        // 返回对象实例
        return $object;
    }

    /***
     * 少使用这个接口,主要是 access_token,在其他系统(比如线下版和上线版)被使用了,就会造成
     * access_token 就会无效,这时就暴露一个接口,重载
     */
    public static function reload()
    {
        // 强制重新获取access_token
        WechatGetAccessToken::switchAccessToken();
        // 再重新获得,并修改当前类上的timeout和access_token
        self::getNewAccessToken();
        // 重新初始化 对象池
        self::init();
    }

    public static function getToken()
    {
        // 执行获取token
        self::getNewAccessToken();
        return self::$access_token;
    }


//    public static function get()
//    {
//        return  WechatGetAccessToken::getAccessToken();
//    }





}