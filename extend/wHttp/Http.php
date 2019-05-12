<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/3/28
 * Time: 21:09
 */

namespace wHttp;

/***
 * 实现两个请求,get和post请求
 * Class Http
 * @package myHttp
 */
class Http
{
    // 设置版本号 便于日后维护
    const VERSION = '1.0.0';
    /***
     * get 请求的请求头
     * @var array
     */
    private static $HttpGetHeader = array("Content-type:application/json;","Accept:application/json");

    /***
     * post 请求的请求头
     * @var array
     */
    private static $HttpPostHeader = array("Content-type:application/json;charset='utf-8'","Accept:application/json");

    /***
     * 通用的请求头
     * @var array
     */
    private static $HttpBaseHeader = array('Content-type:application/json');


    /**
     * get 请求
     * @param $url string
     * @return mixed
     */
    public static function get($url)
    {
        // 初始化
        $curl = curl_init();
        // 抓取url
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
        // 不验证https
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl,CURLOPT_HTTPHEADER,self::$HttpGetHeader);
        $output = curl_exec($curl);
        curl_close($curl);
        return json_decode($output,true);
    }


    /***
     * post 请求
     * @param $url string
     * @param $data array
     * @return mixed
     */
    public static function post($url,$data)
    {
        // 将json序列化
        $data  = json_encode($data);
        // 初始化
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl,CURLOPT_HTTPHEADER,self::$HttpPostHeader);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return json_decode($output,true);
    }


    private static function base($url,$data,$type)
    {
        // 序列化
        $data = json_encode($data);
        $curl = curl_init(); //初始化CURL句柄
        curl_setopt($curl, CURLOPT_URL, $url); //设置请求的URL
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
        curl_setopt ($curl, CURLOPT_HTTPHEADER, self::$HttpBaseHeader);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,1); //设为TRUE把curl_exec()结果转化为字串，而不是直接输出
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST,$type); //设置请求方式
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);//设置提交的字符串
        $output = curl_exec($curl);
        curl_close($curl);
        return json_decode($output,true);
    }


    /**
     * put 请求
     * @param $url string
     * @param $data array
     * @return mixed
     */
    public static function put($url,$data)
    {
        return self::base($url,$data,'PUT');
    }

    /***
     * delete 请求
     * @param $url string
     * @param $data array
     * @return mixed
     */
    public static function del($url,$data)
    {
        return self::base($url,$data,'DELETE');
    }

    /***
     * patch 请求
     * @param $url string
     * @param $data array
     * @return mixed
     */
    public static function patch($url,$data)
    {
        return self::base($url,$data,'PATCH');
    }



}