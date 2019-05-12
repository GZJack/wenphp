<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/3/23
 * Time: 8:49
 */

namespace wEmail;
// 引入PHPMailerMin类
// use myEmail\PHPMailerMin; 同一个命名控件下不用use
// 引入签名类
use wRequest\Sign;
/***
 * 用于操作有效发送的一个类
 * Class Email
 * @package myEmail
 */
class Email
{
    // 定义几个常量
    // 可以访问用户数据的服务器,注册激活,通过邮箱改密码,都需要通过该链接来实现的
    const ServerUrl = 'https://mobile.5156580.com';
    // 设定邮件编码
    const CharSet = "UTF-8";
    // 设定SMTP服务器
    const Host = "smtp.5156580.com";
    // 启用SMTP调试 1 = errors  2 =  messages
    const SMTPDebug = 0;
    // 服务器需要验证
    const SMTPAuth = true;
    //默认端口
    const Port = 25;
    //SMTP服务器的用户帐号
    const Username = "password@5156580.com";
    //SMTP服务器的用户密码
    const Password = "HaHa5656";
    // 私有
    protected $email;
    // 接收邮件的用户 接收 字符串或数组
    protected $userEmailAddress = "";
    // 是否发送成功,发送成功后要清除一些收件邮箱地址
    protected $isSendSuccess = false;

    public static $instance;//声明一个静态变量(保存在类中唯一的一个实例)
    // 防止外边使用 new
    private function __construct()
    {
        $mail = new PHPMailerMin(true); //实例化PHPMailer类,true表示出现错误时抛出异常
        $mail -> IsSMTP(); // 使用SMTP
        $mail -> CharSet = self::CharSet;//设定邮件编码
        $mail -> Host       = self::Host; // SMTP server
        $mail -> SMTPDebug  = self::SMTPDebug;// 启用SMTP调试 1 = errors  2 =  messages
        $mail -> SMTPAuth   = self::SMTPAuth;// 服务器需要验证
        $mail -> Port       = self::Port;//默认端口
        $mail -> Username   = self::Username; //SMTP服务器的用户帐号
        $mail -> Password   = self::Password;//SMTP服务器的用户密码
        // 把实例挂载在当前实例上
        $this -> email = $mail;
    }

    /***
     * 单例模式
     */
    public static function getInstance()
    {
        if(!self::$instance) self::$instance = new self();
        return self::$instance;
    }


    /***
     * 设置激活链接
     * @param $userInfo array
     * @return string
     */
    protected function setActivationUrl ($userInfo)
    {
        $from = $userInfo['regFrom'];
        $type = $userInfo['regType'];
        $userEmail = $userInfo['email'];
        $code = $userInfo['tmpVerificationCode'];
        $timestamp = $userInfo['tmpVerificationTime'];
        // 定义一个对象
        $newGet['from'] = $from;
        $newGet['type'] = $type;
        $newGet['email'] = $userEmail;
        $newGet['code'] = $code;
        $newGet['timeout'] = $timestamp;
        // 进行签名
        $sign = Sign::simpleSign($newGet,Sign::$UserRegSignKeys);
        $url = '/user/activation?from='.$from.'&type='.$type.'&email='.$userEmail;
        $url .= '&code='.$code.'&timeout='.$timestamp.'&sign='.$sign;
        // 回收内存
        $newGet = null;
        return self::ServerUrl.$url;
    }

    /***
     * 设置激活 body
     * @param $activationUrl string
     * @return string
     */
    protected function setActivationBody ($activationUrl)
    {
        $bodyStr = '<h4>尊敬的用户:</h4>';
        $bodyStr .= '离注册成功只差一步了,请点击下面的链接进行激活';
        // 激活链接拼接
        $bodyStr .= '<a href="'.$activationUrl.'">'.$activationUrl.'</a>';
        // 返回拼接结果
        return $bodyStr;
    }

    /***
     * 设置用户接收的邮箱地址
     * @param $userEmailAddress string|array
     */
    public function setUserEmailAddress ($userEmailAddress)
    {
        $this -> userEmailAddress = $userEmailAddress;
    }

    /***
     * 发送用户注册信息
     * @param $userInfo array 这是注册页面传进来的用户信息,里面会有邮箱,验证嘛,和有效期,来源渠道
     */
    public function emailUserRegModel ($userInfo)
    {
        // 设置了邮件标题
        $this -> email -> Subject = '注册激活';
//        $activationUrl = $this -> setActivationUrl($userInfo);
        $activationUrl = 'http://m.5156580.com';
        $activationBody = $this -> setActivationBody($activationUrl);
        $this -> email -> Body = $activationBody;
        $this -> email -> IsHTML(true);
    }

    /***
     * 发送邮件
     */
    public function send()
    {
        // 这里面的方法都会发生错误的
        try {
            $this -> email ->AddReplyTo('zhang85661976@126.com', '回复'); //收件人回复时回复到此邮箱
            // 获取当前需要发送的用户,可以是字符串或数组,数组表示多个人
            $userEmailAddress = $this -> userEmailAddress;
            // 当用户接收地址为空,则无法发送邮件
            if($userEmailAddress === ''){
                return false;
            }
            // $this -> email-> AddAddress('283856261@qq.com', '张三'); //收件人如果多人发送循环执行AddAddress()方法即可 还有一个方法时清除收件人邮箱ClearAddresses()
            // 发送单个用户
            if(is_string($userEmailAddress)){
                 $this -> email -> AddAddress($userEmailAddress, $userEmailAddress); //收件人如果多人发送循环执行AddAddress()方法即可 还有一个方法时清除收件人邮箱ClearAddresses()
            }elseif (is_array($userEmailAddress)){
                for ($i=0;$i<count($userEmailAddress);$i++){
                    $this -> email -> AddAddress($userEmailAddress[$i], $userEmailAddress[$i]); //收件人如果多人发送循环执行AddAddress()方法即可 还有一个方法时清除收件人邮箱ClearAddresses()
                }
            }
            $this -> email -> SetFrom('password@5156580.com', '用户中心');//发件人的邮箱
//            $this -> email ->Subject = '这里是邮件的标题';
//            $this -> email ->Body = '邮件内容';
//            $this -> email ->IsHTML(true);
            $this -> email ->Send();
            echo "Message Sent OK";
            return true;
        } catch (Exception $e) {
            echo $e->getMessage();
            return false;
        } catch (\Exception $e) {
            echo $e ->getMessage();
            return false;
        }
    }

}