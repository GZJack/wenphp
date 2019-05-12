<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/3/29
 * Time: 21:49
 */

namespace myWechat\message;

// 请求类引入进来
use wHttp\Http;

/***
 * 这是一个基础的模板类,用户直接复制过去,就能直接开发了
 * Class TemplateXxxx
 * @package myWechat\message
 */
class TemplateXxxx
{
    // 别名
    const alias = 'TmpXx';
    /***
     * 发送模板信息链接,无需替换,只需要拼接
     */
    const POST_SEND_TEMPLATE_MESSAGE_NO = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=';
    /***
     * 当前的token
     * @var string
     */
    private $access_token = '';

    private $json_data = null;

    // 为了保证信息不会错发,设置一次data,才可以发送一次
    private static $createData = false;

    /***
     * 构建实例前就获得token
     * TemplateMessage constructor.
     * @param $access_token
     */
    public function __construct($access_token)
    {
        $this -> access_token = $access_token;
    }

    /***
     * 构建数据
     */
    public function data()
    {
        $data = [
            'touser' => 'oG7gz5gno0ISjYNzrm5pJQELq65Q',
            'template_id' => 'IBqSc9rK36kVYqR2RnsNS_mv_6bnRIUd6k0QWcZ973A',
            'url' => 'http://weixin.qq.com/download',
            'data' => [
                'Time' => [
                    'value' => '2019-03-29 24:00',
                    'color' => '#173177'
                ]
            ]
        ];
        $this -> json_data = $data;
        // 可以发送消息了
        self::$createData = true;

    }

    /***
     * 发送方法,是需要受到data控制的,如果没有设置data,是不能够执行发送的
     * @return mixed|string
     */
    public function send()
    {
        if(!self::$createData) return 'Not used data,Cannot send';
        // 已经设置了data,就可以发送,下次想在发送,就得在设置一次data
        self::$createData = false;
        // 读取链接并拼接access_token
        $url = self::POST_SEND_TEMPLATE_MESSAGE_NO.$this -> access_token;
        return Http::post($url,$this -> json_data);
    }
}