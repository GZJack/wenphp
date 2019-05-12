<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/3/30
 * Time: 0:17
 */

namespace myWechat;


class SingleBase
{
    /***
     * 当前实例
     * @var null
     */
    private static $instance = null;

    /***
     * 所有实例都一样的,都需要access_token
     * @var string
     */
    protected $access_token = '';

    /***
     * 构造函数,对于外边的类不能直接new
     * SingleBase constructor.
     * @param $access_token string
     */
    private function __construct($access_token)
    {
        echo '来了老弟';
        $this -> access_token = $access_token;
    }

    /***
     * 暴露接口获取当前实例
     * @param $access_token string
     * @return SingleBase|null
     */
    public static function getInstance($access_token)
    {
        if(self::$instance===null){
            echo $access_token;
            self::$instance = new self($access_token);
        }
        return self::$instance;
    }

    public function send()
    {
        echo '你好老弟';
    }


}