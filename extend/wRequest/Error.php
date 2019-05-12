<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/3/26
 * Time: 12:54
 */

namespace wRequest;

class Error
{
    /***
     * 使用返回场景
     * @var string 使用场景,errMsg:'user.reg:ok' / 'user.reg:fail,[错误的原因]'
     */
    private static $usescene = '';

    /***
     * 错误内容
     * @var string
     */
    private static $errMsg = '';

    /***
     * 返回的错误码
     * @var int
     */
    private static $errCode = 20000;

    /***
     * 暴露接口,设置使用场景
     * @param $scene string
     */
    final public static function setUseScene($scene)
    {
        self::$usescene = is_string($scene) ? $scene : '';
    }

    /***
     * @return string
     */
    protected static function getUseScene()
    {
        return self::$usescene;
    }

    /***
     * 设置错误字符
     * @param $errMsg string
     */
    protected static function setErrMsg($errMsg)
    {
        self::$errMsg = is_string($errMsg) ? $errMsg : '';
    }

    /***
     * 获得错误字符
     * @return string
     */
    protected static function getErrMsg()
    {
        return self::$errMsg;
    }


    /***
     * 错误字符串合拼,被用户继承过去,但是可以在自己的类里重新定义返回结果的拼接
     * @return string
     */
    protected static function errMsgMerge()
    {
        return self::$errMsg;
    }

    /***
     * 设置错误码
     * @param $errCode int
     */
    protected static function setErrCode($errCode)
    {
        self::$errCode = $errCode;
    }


    /***
     * 获得当前类的错误
     * @return array
     */
    final public static function getError()
    {
        return [
            'errCode' => self::$errCode,
            // static 关键字,访问子类的 errMsgMerge 的方法
            'errMsg' => static::errMsgMerge()
        ];
    }

    /***
     * 动态设置错误,即设置,即返回
     * @param $errCode int
     * @param $errMsg string
     * @return array
     */
    final public static function setError($errCode,$errMsg)
    {
        return [
            'errCode' => $errCode,
            'errMsg' => $errMsg
        ];
    }

    /**
     * 设置一个返回的失败对象
     * @param $errCode int
     * @param $errMsg string
     * @return array
     */
    final public static function setFailReturn($errCode, $errMsg)
    {
        // 如果不为空,则返回
        if(self::getUseScene()!==''){
            return [
                'errCode' => $errCode,
                'errMsg' => self::$usescene.':fail,['.$errMsg.']'
            ];
        }else{
            return self::setError($errCode,$errMsg);
        }
    }

    /**
     * 设置一个正确的返回对象,带正确提示的
     * @param $okMsg string
     * @return array
     */
    final public static function setOkReturn($okMsg)
    {
        if(self::getUseScene()!==''){
            return [
                'errCode' => 0,
                'errMsg' => self::getUseScene().':ok,['.$okMsg.']'
            ];
        }else{
            return self::setError(0,$okMsg);
        }
    }

    /***
     * 获取一个成功返回
     * @return array
     */
    final public static function getOkReturn()
    {
        if(self::getUseScene()!==''){
            return [
                'errCode' => 0,
                'errMsg' => self::getUseScene().':ok'
            ];
        }else{
            return self::setError(0,'ok');
        }
    }
}