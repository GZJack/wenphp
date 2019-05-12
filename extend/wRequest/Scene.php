<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/3/26
 * Time: 12:20
 */

namespace wRequest;

class Scene extends Error
{
    /***
     * @var array
     * 场景说明
     * webapp 网页场景,包括手机和电脑端
     * pcweb 电脑端浏览器
     * pcapp 电脑端app
     * miniProgram 小程序端
     * android 安卓客户端
     * ios 苹果客户端
     * wxweb 微信浏览器,主要来至公众号
     */
    private static $fromArray = array('webapp','pcweb','pcapp','miniProgram','android','ios','wxweb');

    /***
     * @var string
     * 行为 -> 注册 登录 更新
     */
    private static $action = '';

    /***
     * @param $from string
     * @return bool
     */
    private static function checkScene($from)
    {
        // 用映射获得一个新的数组
        $newFromArray = array_map(function ($item){
            // 一个回调函数,拼接一个行为
            return $item.'.'.self::$action;
        },self::$fromArray);
        // 然后遍历新数组
        foreach ($newFromArray as $key => $value)
        {
            // 如果提交的from和定义里存在的,则可以通过
            if($from===$value){
                // 回收内存
                $newFromArray = null;
                // 通过
                return true;
            }
        }
        // 回收内存
        $newFromArray = null;
        // 无法通过
        return false;
    }

    /**
     * 验证注册场景
     * @param $from string
     * @return bool
     */
    public static function checkRegScene($from)
    {
        // 修改为验证
        self::$action = 'reg';
        // 设置错误码
        self::setErrCode(20002);
        // 设置错误
        self::setErrMsg('url get 中 from 的参数值不正确');
        // 执行并返回验证结果
        return self::checkScene($from);
    }


    /***
     * 合成拼接错误字符,替换Error里的errMsgMerge函数
     * @return string
     */
    protected static function errMsgMerge()
    {
        if(self::getUseScene()!==''){
            return self::getUseScene().':fail,['.self::getErrMsg().']';
        }else{
            return self::getErrMsg();
        }
    }



}