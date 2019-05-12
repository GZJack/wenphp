<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/3/28
 * Time: 23:47
 */

namespace myWechat;
// 引入日志级别
//use think\Log;

class WechatMessage
{

    /***
     * 执行校验
     * 微信传过来的?signature=ddf6ccb3d577c3fdec964e112cc27753ed48f16c&echostr=1431847312409230063&timestamp=1553789163&nonce=661399004
     * @param $get array|object
     */
    public static function valid($get)
    {
        // echostr是在验证的时候才会发送这个字段的,发消息的时候是没有这个字段的
        $echoStr = $get["echostr"];
        // 进行签名验证,成功就输出 echostr 这个字段的字符串
        if(self::checkSignature($get)){
            // 公众号在 thinkphp 中验证总是无法通过,原因是,输出的echostr被公众号服务器上无法识别,所以加上这句话
            ob_clean(); // 清除输出缓存区
            // 输出echostr字符串,让公众号的服务器验证,并决定是否通过
            echo $echoStr;
            // 退出
            exit;
        }
    }

    /***
     * 校验token
     * @param $get
     * @return bool
     * array (
     *   'signature' => 'ddf6ccb3d577c3fdec964e112cc27753ed48f16c',
     *   'echostr' => '1431847312409230063',
     *   'timestamp' => '1553789163',
     *   'nonce' => '661399004',
     *   )
     */
    private static function checkSignature($get)
    {
        // signature 是微信传过来的 类似于签名的东西
        $signature = $get["signature"];
        // 微信传过的 时间戳
        $timestamp = $get["timestamp"];
        // 微信传过来的,具体怎么来的,也不清楚
        $nonce = $get["nonce"];
        // 获得自己定义的token  从总配置项里面获取token
        $token = WechatConfig::get('token');
        // 三个变量 按照字典排序 形成一个数组
        $tmpArr = array($token, $timestamp, $nonce);
        // 使用 SORT_STRING 这种规则进行排序
        sort($tmpArr, SORT_STRING);
        // 把数组转为字符串
        $tmpStr = implode($tmpArr);
        // 将字符串做一个哈希加密
        $tmpStr = sha1($tmpStr);
        // 将加密的结果和微信传过来的 签名进行比较,如果相等,则是正确的
        if($tmpStr == $signature){
            return true;
        }else{
            return false;
        }
    }

    /***
     * 接收消息
     * @param $request
     */
    public static function responseMsg($request)
    {
        //因为很多都设置了register_globals禁止,不能用 但是我的可以用
        //$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $postStr = file_get_contents("php://input");
        // 如果post内容不为空
        if (!empty($postStr)){
            // 将xml转换成对象
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            // 读取信息的类型
            $RX_TYPE = trim($postObj -> MsgType);
            // 判断类型
            switch ($RX_TYPE)
            {
                case "event":
                    //$result = $this->receiveEvent($postObj);
                    break;
                case "text":
                    $result = self::receiveText($postObj);
                    break;
                case "image":
                    //$result = $this->receiveImage($postObj);
                    break;
                case "location":
                    //$result = $this->receiveLocation($postObj);
                    break;
                case "voice":
                    //$result = $this->receiveVoice($postObj);
                    break;
                case "video":
                    //$result = $this->receiveVideo($postObj);
                    break;
                case "link":
                    //$result = $this->receiveLink($postObj);
                    break;
                default:
                    $result = "unknow msg type: ".$RX_TYPE;
                    break;
            }
            echo $result;
        }else {
            echo "";
            exit;
        }
    }

    /***
     * 接收文本信息
     * @param $object object|array
     * @return string
     */
    private static function receiveText($object)
    {
        // 获得关键词
        $keyword = trim($object -> Content);
        // 通过关键词返回相应的信息
        switch ($keyword)
        {
            case "文本":
                $content = "这是个文本消息";
                break;
            case "图文":
            case "单图文":
                $content[] = array("Title"=>"单图文标题", "Description"=>"单图文内容", "PicUrl"=>"http://discuz.comli.com/weixin/weather/icon/cartoon.jpg", "Url" =>"http://m.cnblogs.com/?u=txw1958");
                break;
            case "多图文":
                $content[] = array("Title"=>"多图文1标题", "Description"=>"", "PicUrl"=>"http://discuz.comli.com/weixin/weather/icon/cartoon.jpg", "Url" =>"http://m.cnblogs.com/?u=txw1958");
                $content[] = array("Title"=>"多图文2标题", "Description"=>"", "PicUrl"=>"http://d.hiphotos.bdimg.com/wisegame/pic/item/f3529822720e0cf3ac9f1ada0846f21fbe09aaa3.jpg", "Url" =>"http://m.cnblogs.com/?u=txw1958");
                $content[] = array("Title"=>"多图文3标题", "Description"=>"", "PicUrl"=>"http://g.hiphotos.bdimg.com/wisegame/pic/item/18cb0a46f21fbe090d338acc6a600c338644adfd.jpg", "Url" =>"http://m.cnblogs.com/?u=txw1958");
                break;
            case "音乐":
                $content = array("Title"=>"最炫民族风", "Description"=>"歌手：凤凰传奇", "MusicUrl"=>"http://121.199.4.61/music/zxmzf.mp3", "HQMusicUrl"=>"http://121.199.4.61/music/zxmzf.mp3");
                break;
            default:
                $content = "当前时间：".date("Y-m-d H:i:s",time());
                break;
        }
        $result ='';
        if(is_array($content)){
            if (isset($content[0]['PicUrl'])){
                //$result = $this->transmitNews($object, $content);
            }else if (isset($content['MusicUrl'])){
                //$result = $this->transmitMusic($object, $content);
            }
        }else{
            $result = self::transmitText($object, $content);
        }
        return $result;
    }

    private static function transmitText($object, $content)
    {
        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    </xml>";
        $result = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content);
        return $result;
    }
}