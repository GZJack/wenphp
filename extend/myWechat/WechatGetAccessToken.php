<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/3/29
 * Time: 15:44
 */

namespace myWechat;

// 引入请求类
use wHttp\Http;
// 引入缓存
use wen\Cache;

/***
 * 专门获取access_token
 * Class WechatGetAccessToken
 * @package myWechat
 */
class WechatGetAccessToken
{

    /***
     * 获取access_token链接 _YES 需要替换 _NO 则直接拼接 accss_token
     */
    const GET_ACCESS_TOKEN_URL_YES = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s';

    /***
     * 缓存初始化
     */
    private static function cacheInit()
    {
//        $options = [
//            // 缓存类型为File
//            'type'  =>  'File',
//            // 缓存有效期为永久有效
//            'expire'=>  0,
//            //缓存前缀
//            'prefix'=>  'think',
//            // 指定缓存目录  与 application 目录同级下的 runtime下的toket
//            'path'  =>  APP_PATH.'/../runtime/token/',
//        ];
//        Cache::connect($options);
    }


    /***
     * 通过服务器获取access_token
     * @return array
     */
    private static function get_access_token()
    {
        // 获取链接 _YES 需要替换
        $url = self::GET_ACCESS_TOKEN_URL_YES;
        // 获取配置参数
        $options = WechatConfig::get();
        // 将参数替换上链接上
        $url = sprintf($url,$options['appid'],$options['appsecret']);
        // 进行get请求
        $result = Http::get($url);
        // 如果有 access_token选项则表示成功
        if(isset($result['access_token'])){
            // 加一个时间戳
            $result['timeout'] = time() + 7100;
            // 序列化存起来
            $resultStr = json_encode($result);
            // 进行缓存
            Cache::set('access_token',$resultStr);
            // 返回token
            return $result;
        }
        // 将错误写到错误类中去,可以从错误类中获取
        Error::set('获取access_token失败');
        // 返回一个无用的 token
        return [
            'access_token' => 'get access_token fail',
            'timeout' => 0
        ];
    }

    /***
     * access_token 只能在这里面使用,不可以在外面获取
     * @return array
     */
    public static function getAccessToken()
    {
        // 初始化cache
        self::cacheInit();
        // 读取token,并校验是否超时了
        $access_token = Cache::get('access_token');
        echo '获取缓存了';
        // 在没有token的情况下,向服务器请求获取access_token
        if($access_token === false){
            return self::get_access_token();
        }else{
            // 从缓存上获取
            $result = json_decode($access_token,true); // 没有就从网络上获取
            // 获取时间戳的差
            $poor = (int) $result['timeout'] - time();
            // 如果小于当前时间了,则为超时了
            if($poor < 0){
                return self::get_access_token(); // 再从网络上获取
            }else{
                return $result;
            }
        }
    }

    /***
     * 暴露一个接口,当access_token失效了,但是时间又没有超时,就得强制换access_token,尽量少用这个接口
     * access_token调用的次数,一天只有2000次
     */
    public static function switchAccessToken()
    {
        return self::get_access_token();
    }
}