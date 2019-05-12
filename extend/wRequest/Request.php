<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/3/25
 * Time: 22:01
 */

namespace wRequest;

class Request extends Error
{
    /***
     * @var string 缺少的参数名
     */
    private static $paramname = '';

    /***
     * @var string 请求提交方式
     */
    private static $requesttype = 'get';


    /***
     * 受保护方法,检测参数是否存在
     * @param $get object
     * @param $keys array
     * @return bool
     */
    private static function checkParam($get,$keys)
    {
        // 假设是通过的
        $isCheckOk = true;
        // 开始遍历
        foreach ($keys as $key => $value)
        {
            // 是否有建不存在
            if(!isset($get[$value]))
            {
                self::$paramname = $value;
                $isCheckOk = false;
                break;
            }
        }
        return $isCheckOk;
    }

    /***
     * 设置keys数组
     * @param $keys string|array
     * @return array|string
     */
    private static function setKeys($keys)
    {
        if (!is_array($keys)){
            if(is_string($keys)){
                $keys = array($keys);
            }else{
                $keys = array();
            }
        }
        return $keys;
    }

    /**
     * 暴露接口,检测$_GET参数是否有按要求传进来
     * @param $get object
     * @param $keys string|array
     * @return bool
     */
    public static function checkGetParam($get,$keys)
    {
        self::$requesttype = 'get';
        // 设置错误码
        self::setErrCode(20001);
        // 设置错误内容
        self::setErrMsg(' 缺少参数: ');
        // 开始校验
        return self::checkParam($get,self::setKeys($keys));
    }

    /***
     * 暴露接口,检测$_POST参数是否按要求提交进来
     * @param $post object
     * @param $keys string|array
     * @return bool
     */
    public static function checkPostParam($post,$keys)
    {
        self::$requesttype = 'post';
        // 设置错误码
        self::setErrCode(20001);
        // 设置错误内容
        self::setErrMsg(' 缺少参数: ');
        // 开始校验
        return self::checkParam($post,self::setKeys($keys));
    }



    /***
     * 错误字符串合拼 重写 Error上的方法
     * @return string
     */
    protected static function errMsgMerge()
    {
        if(self::getUseScene()!==''){
            return self::getUseScene().':fail,['.self::$requesttype.self::getErrMsg().self::$paramname.']';
        }else{
            return self::$requesttype.self::getErrMsg().self::$paramname;
        }
    }


}