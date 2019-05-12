<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/4/1
 * Time: 11:12
 */

namespace wen;
/***
 * Config,是一个抽象类,不可以实例化,
 * 实现功能
 * (1) set 挂载配置
 * (2) get 读取配置
 * Class Config
 * @package wen
 */
abstract class Config
{
    /***
     * 承载着整个应用的配置信息,当有实例来就挂载在这里
     * @var array
     */
    private static $appConfigInfoArray = [];

    /***
     * 挂载配置
     * @param $key string|array
     * @param $value null|mixed
     */
    final public static function set($key,$value=null)
    {
        /***
         * 如果没有设置value,就有两种可能,
         * 1. key 是一个数组, 2. key 是一个字符串
         * 是数组就直接合并 当前的数组
         * 是字符串就可能是一个文件地址,就需要把配置文件引入进来,并把结果合并到当前配置上
         */
        if(is_null($value)){
            if(is_array($key)){
                self::$appConfigInfoArray = array_merge(self::$appConfigInfoArray,$key);
            }elseif (is_string($key)){
                // 如果是一个文件的话,将内容读取出来,如果内容是一个数组的话,就可以合并到当前数组中去,否则不管
                if(is_file($key)){
                    $data = require $key;
                    // 判断结果是不是数组,
                    if(is_array($data)){
                        self::$appConfigInfoArray = array_merge(self::$appConfigInfoArray,$data);
                    }else{
                        // 既然是文件,但是又不是数组,那就是开发人员传入错误的文件造成的,报错
                        //throw new \Exception('Config::set() require filePath of content is not array',-1);
                        Response::debug("[ DEBUG ] require filePath of content is not array",-1,'Config.set');
                    }
                }
            }
        }else{
            // 如果有 value 就必须保证 key 是字符串
            if(is_string($key)){
                self::$appConfigInfoArray[$key] = $value;
            }else{
                // 这个也是给开发人员看的
                //throw new \Exception('Config::set() key is not string');
                Response::debug("[ DEBUG ] key is not string",-1,'Config.set');
            }
        }
    }

    /***
     * 读取配置
     * @param $key null|string
     * @return array|mixed
     */
    final public static function get($key=null)
    {
        if (is_string($key)){
            if (isset(self::$appConfigInfoArray[$key])){
                return self::$appConfigInfoArray[$key];
            }else{
                // 给开发人员看的,如果不存咋就抛出异常
                //throw new \Exception("Config::get() $key of value no exists",-1);
                Response::debug("[ DEBUG ] $key of value no exists",-1,'Config.get');
                // 不存在
                return null;
            }
        }else{
            return self::$appConfigInfoArray;
        }
    }

}