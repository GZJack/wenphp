<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/4/3
 * Time: 17:39
 */

namespace wen;

use wen\cache\driver\File;

class Cache
{
    /***
     * 当前缓存实例
     * @var null
     */
    private static $cacheInstance = null;


    /***
     * 获得当前配置缓存的实例
     * @return null|File
     */
    private static function getCacheInstance()
    {
        if(self::$cacheInstance === null){
            self::$cacheInstance = new File();
        }
        return self::$cacheInstance;
    }

    /***
     * 设置配置参数,可以统一修改默认值
     * @param $options array
     */
    public static function connect($options=[])
    {
        self::getCacheInstance() -> connect($options);
    }


    /***
     * 判断当前值是否存在
     * @param $name string
     * @return bool
     */
    public static function has($name)
    {
        return self::getCacheInstance() -> has($name);
    }

    /***
     * 缓存设置值
     * @param $name string
     * @param $value mixed
     * @param null|integer|\DateTime $expire
     * @return bool
     */
    public static function set($name, $value, $expire = null)
    {
        return self::getCacheInstance() -> set($name, $value, $expire);
    }

    /***
     * 获取缓存值
     * @param $name string
     * @param bool|mixed $default
     * @return bool|mixed
     */
    public static function get($name,$default=false)
    {
        return self::getCacheInstance() -> get($name,$default);
    }

    /***
     * 缓存自增
     * @param $name string
     * @param integer $step
     * @return bool
     */
    public static function inc($name,$step=1)
    {
        return self::getCacheInstance() -> inc($name,$step);
    }

    /***
     * 缓存自减
     * @param $name string
     * @param integer $step
     * @return bool
     */
    public static function dec($name,$step=1)
    {
        return self::getCacheInstance() -> dec($name,$step);
    }

    /***
     * 获取缓存后删除
     * @param $name string
     * @return bool|mixed
     */
    public static function pull($name)
    {
        return self::getCacheInstance() -> pull($name);
    }

    /***
     * 删除缓存
     * @param $name string
     * @return bool
     */
    public static function rm($name)
    {
        return self::getCacheInstance() -> rm($name);
    }

    /***
     * 清除缓存目录
     * @return bool
     */
    public static function clearCacheDir()
    {
        return self::getCacheInstance() -> clear();
    }

}