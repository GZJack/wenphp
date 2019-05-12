<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/3/26
 * Time: 18:28
 */

namespace wRequest;
/***
 * 签名类,主要是用来生成签名和验证签名的类
 * 签名有带密钥类的,有没有带密钥的
 * Class Sign
 * @package myRequest
 */
class Sign
{
    /***
     * 签名错误字符
     * @var string
     */
    private static $errMsg = '';
    /***
     * 签名的种类,sign
     * (1) 按指定的顺序和参数进行md5加密,这种是最普通的
     */

    /***
     * 注册发送激活邮件和用户激活时,配合起来同时使用
     * @var array
     */
    public static $UserRegSignKeys = array('timeout','from','email','type','code');

    /***
     * 这是保证get上有key值,这个key就是对应上这里的key的值
     */
    public static $sortKeys = array(
        'k321' => array('from','type','code'),
        'k231' => array('from','code','type'),
        'k312' => array('type','from','code'),
    );


    /***
     * 检验是否超时了,如果超时了,都没有必要往下执行了,也不用请求数据库了
     * 是与系统时间进行比较
     * @param $get object
     * @return bool
     */
    private static function checkTimeOut($get)
    {
        // 三目运算  (表达式) ? (第二个三目) : (不满足的结果);
        $timeout = (int) (!isset($get['timeout'])) ? ((isset($get['timestamp']) ? $get['timestamp'] : 0)) : $get['timeout'];
        // 小于当前时间就是超时了
        if($timeout < time()){
            // 修改错误值
            self::$errMsg = '签名已超时';
            // 不通过
            return false;
        }
        // 否则就是通过
        return true;
    }

    /***
     * 获得时间戳,格式是,获得时效 将时间戳+超时时间,截取字符串 用 000 代替
     * @param $num int
     * @return string
     */
    final private static function getTime($num)
    {
        $newTime = '';
        switch ($num){
            case 1800:
                // 1800秒就是30分钟 +- 500秒/60 = 正负 22分钟-38分钟
                $newTime = (string) (time()+2300);
                $newTime = substr($newTime,0,-3).'000';
                break;
            case 20000:
                // 10000秒就是2个多,差不多3个小时 结果就是 2-6小时
                $newTime = (string) (time()+20000);
                $newTime = substr($newTime,0,-4).'0000';
                break;
            default:
                $newTime = (string) (time()+200); // 默认是1-3分钟
                $newTime = substr($newTime,0,-2).'00';
        }
        return $newTime;
    }

    /***
     * 简单签名,解签名,也需要保证$keys数组
     * @param $get object
     * @param $keys array
     * @return string
     */
    final public static function simpleSign($get,$keys)
    {
        $valueStringMerge = '';
        foreach ($keys as $key => $value){
            // 如果是数组,就存在特殊状况,如 time 动态时间戳
            if(is_array($value)){
                if($value['type']==='time'){
                    $valueStringMerge .= self::getTime($value['value']);
                }
            }else{
                $valueStringMerge .= (string) $get[$value];
            }
        }
        return md5($valueStringMerge);
    }

    /**
     * 检验简单签名是否正确,这里不做 $_GET 有没有缺少参数,以及 sign 是否存在,要求一开始 Request::checkGetParam()进行判断
     * @param $get object
     * @param $keys array
     * @return bool
     */
    final public static function checkSimpleSign($get, $keys)
    {
        // 先做timeout是否超时
        if(!self::checkTimeOut($get)){
            return false;
        }
        // 将生成的,和传过来的进行对比,如果相等,签名则是有效的
        if(self::simpleSign($get,$keys)===$get['sign']){
            return true; // 有效的
        }else{
            self::$errMsg = '签名不正确';
            return false; // 无效的
        }
    }


    /***
     * @param $key
     * @return array
     */
    private static function getKeys($key)
    {
        // 存在正确的key,则读取对应key的值
        if(isset(self::$sortKeys[$key])){
            return self::$sortKeys[$key];
        }
        // 读取第一个key $keys[0]
        $keys = array_keys(self::$sortKeys);
        // 返回第一个key
        return self::$sortKeys[$keys[0]];
    }

    /***
     * 按排序进行签名,和按排序进行校验
     * @param $get object|array
     * @param $key string
     * @return string
     */
    final public static function sortSign($get,$key)
    {
        // 获得key对应的数组
        return self::simpleSign($get,self::getKeys($key));
    }

    /***
     * 校验带key的签名
     * @param $get object|array
     * @return bool
     */
    final public static function checkSortSign($get)
    {
        // 获得key
        $key = isset($get['key']) ? $get['key'] : 'k3210';
        // 查看是否有时间戳,没有则添加一个临时时间戳
        if (isset($get['timeout']) || isset($get['timestamp'])){
        }else{
            $get['timeout'] = time() + 1000;
        }
        return self::checkSimpleSign($get,self::getKeys($key));
    }

    /***
     * 获取当前实例错误结果
     * @return array
     */
    final public static function getError()
    {
        return [
            'errCode' => 20010, // 签名错误
            'errMsg' => self::$errMsg
        ];
    }

}