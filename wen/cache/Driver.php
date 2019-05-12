<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/4/3
 * Time: 17:15
 */

namespace wen\cache;


abstract class Driver
{
    protected $options = [
        'expire'        => 0,
        'cache_subDir'  => true,
        'prefix'        => '',
        'path'          => CACHE_PATH,
        'data_compress' => false,
    ];

    abstract public function connect($options=[]);

    abstract public function has($name);

    abstract public function get($name, $default = false);

    abstract public function set($name, $value, $expire = null);

    abstract public function inc($name, $step = 1);

    abstract public function dec($name, $step = 1);

    abstract public function rm($name);

    abstract public function pull($name);



    /***
     * 统一生成固定的key,
     * @param $name string
     * @return string
     */
    protected function getCacheKey($name)
    {
        return md5($name);
    }



}