<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/5/9
 * Time: 0:03
 */

namespace app\api\controller;


use wen\Request;

use myWechat\WechatMessage;

class Wechat
{
    public function message(Request $request)
    {
        // 当验证的时候是会存在此字段的,所以通过此字段判别是否在验证还是发消息
        if (!isset($_GET['echostr'])) {
            // 执行消息接收
            WechatMessage::responseMsg($request);
        }else{
            // 以通过WechatMessage进行封装成静态类,也把$_GET替换成了传入参数,所以需要将$_GET传入
            WechatMessage::valid($_GET);
            // 如果验证成功,会在valid函数里输出 $_GET['echostr'] 这个字段,返回给微信服务器
        }

    }
}