<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/3/29
 * Time: 16:38
 */

namespace myWechat\message;

// 请求类引入进来
use wHttp\Http;
// 引入单例基类
use myWechat\SingleBase;

/***
 * 发送模板消息的
 * Class TemplateMessage
 * @package myWechat\message
 */

class TemplateMessage extends SingleBase
{
    // 别名
    const alias = 'TmpMsg';
    /***
     * 发送模板信息链接,无需替换,只需要拼接
     */
    const POST_SEND_TEMPLATE_MESSAGE_NO = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=';

    // 提交的jsons数据
    private $json_data = null;
    // 选中的 template_id
    private $template_id = '';
    // 需要跳转的链接
    private $url = '';
    // 发送的数据
    private $data = array();

    private static $TemplateIdArray = array(
        // 订单 变化  通知  微信
        'ORDER_CHANGE_CALL_WECHAT' => array(
            'template_id' => 'IBqSc9rK36kVYqR2RnsNS_mv_6bnRIUd6k0QWcZ973A',
            'description' => '订单变动提醒之模板消息',
            'url' => 'http://t.5156580.com/#/list/27818860',
            'data' => array(
                'Time' => array(
                    'value' => '',
                    'color' => '#173177'
                ),

            )
        ),

    );


    // 为了保证信息不会错发,设置一次data,才可以发送一次
    private static $createDataOk = false;



    /***
     * 暴露当前,所支持的模板Id
     * @return array
     */
    public function getTemplateAllIdArray()
    {
        return self::$TemplateIdArray;
    }

    /***
     * 校验提交的data与被定义的是否相同,并且,不能多,也不能少
     * @param $serverData array
     * @param $clientData array
     * @return bool
     */
    private function checkData($serverData,$clientData)
    {
        // 遍历
        foreach ($serverData as $key => $value){
            // 保证每个每个key 都必须存在 而且值必须是字符串类型  否则无法通过
            if(!isset($clientData[$key]) || !is_string($clientData[$key])) return false;
            // 赋上新值
            $serverData[$key]['value'] = $clientData[$key];
        }
        // 获取数据
        $this -> data = $serverData;
        // 通过了
        return true;
    }

    /***
     * 创建数据
     * @param $data array
     * @param $tmpId string
     * @return bool
     */
    private function createData($data,$tmpId)
    {
        // $tmpId保证是字符串 且不存在则不能通过
        if(!is_string($tmpId) || !isset(self::$TemplateIdArray[$tmpId])) return false;
        // 得到当前发送的模板
        $template = self::$TemplateIdArray[$tmpId];
        // 赋值template_id
        $this -> template_id = $template['template_id'];
        // 赋值 url
        $this -> url = $template['url'];
        // 开始数据校验
        return $this -> checkData($template['data'],$data);
    }

    /***
     * 暴露接口,用于构建提交数据的
     * @param $data array
     * @param $touser string
     * @param $tmpId string
     * @return bool
     */
    public function data($data,$touser,$tmpId)
    {
        // 创建数据
        if($this -> createData($data,$tmpId) === false){
            return false;
        }
        // 通过就,开始配置好数据 'oG7gz5gno0ISjYNzrm5pJQELq65Q'
        $this -> json_data = [
            'touser' => $touser,
            'template_id' => $this -> template_id,
            'url' => $this -> url,
            'data' => $this -> data
        ];

        // 数据已经构建好了 可以发送消息了
        self::$createDataOk = true;
        // 构建完成
        return true;
    }

    /***
     * 暴露接口,用于发送
     * @return mixed|string
     */
    public function send()
    {
        if(!self::$createDataOk) return 'Not used data,Cannot send';
        // 已经设置了data,就可以发送,下次想在发送,就得在设置一次data
        self::$createDataOk = false;
        // 读取链接并拼接access_token
        $url = self::POST_SEND_TEMPLATE_MESSAGE_NO.$this -> access_token;
        return Http::post($url,$this -> json_data);
    }

    public function getTemplatelist()
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/template/get_all_private_template?access_token='.$this -> access_token;
        return Http::get($url);
    }



}