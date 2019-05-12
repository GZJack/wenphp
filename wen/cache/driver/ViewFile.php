<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/4/22
 * Time: 11:54
 */

namespace wen\cache\driver;
/***
 * 视图缓存文件
 * Class ViewFile
 * @package wen\cache\driver
 */
class ViewFile
{
    /***
     * 基本配置
     * @var array
     */
    protected $options = [
        'expire'        => 0,  // 过期时间,0表示十年以后
        'cache_subDir'  => false, // 是否使用子目录
        'prefix'        => '', // 前缀
        'path'     => RUNTIME_PATH.'view'.DS, // 视图缓存文件目录路劲
        'data_compress' => false, // 是否压缩
    ];

    /***
     * 生成一个唯一的key
     * @param string $name
     * @param bool $auto
     * @return string
     */
    final protected function getCacheKey($name, $auto = false)
    {
        $name = md5($name);
        if ($this -> options['cache_subDir']) {
            // 使用子目录
            $name = substr($name, 0, 2) . DS . substr($name, 2);
        }
        if ($this -> options['prefix']) {
            $name = $this -> options['prefix'] . DS . $name;
        }
        $filename = $this -> options['path'] . $name . '.php';
        $dir      = dirname($filename);
        if ($auto && !is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $filename;
    }


    /***
     * view视图里独用的方法  判断缓存文件是否存在
     * @param $name string
     * @return bool
     */
    final protected function view_file_exists($name)
    {
        $fileName = $this -> getCacheKey($name);
        // 判断文件是否存在
        if (file_exists($fileName)){
            // 判断是否过期
            $time_out = filemtime($fileName);
            // 超期了就返回默认值,并删除文件
            if($time_out < time()){
                // 清除已经过期的文件
                unlink($fileName);
                return false; // 不存在
            }else{
                return true; // 存在
            }
        }else{
            return false; // 不存在
        }
    }

    /***
     * 写入视图缓存文件
     * 视图缓存文件说明有两种,一种私有缓存视图文件,二公有缓存视图文件
     * 私有视图缓存,是不存在数据在视图文件上,每一次请求,都会 assign 设置了数据,才将此视图文件读取
     * 公有视图缓存,写入的时候,就会将data 数据一起写入到缓存文件种
     * @param $name string 可以是文件的 path 也可以是 请求的 url
     * @param $content string 这是需要写入缓存的内容
     * @param null|\DateTime|int $expire
     * @return bool
     */
    final protected function view_file_write($name,$content,$expire = null)
    {
        // 生成文件名,并生成文件夹
        $fileName = $this -> getCacheKey($name, true);
        // 默认
        if (is_null($expire)) {
            $expire = $this -> options['expire'];
        }
        // 如果设置了 日期 就将日期转换成 时间戳
        if ($expire instanceof \DateTime) {
            $expire = $expire -> getTimestamp();
        } else {
            // 永久就是十年后的今天才失效
            $expire = 0 === $expire ? 1 * 365 * 24 * 3600 : $expire;
            // $expire 如果传经来的本来就是时间戳了,那就没有必要再加一个当前时间戳了
            $expire = $_SERVER["REQUEST_TIME"] + $expire;
        }
        // 接收返回结果
        $result = file_put_contents($fileName, $content);

        // 返回了结果,就证明已经成功了
        if ($result) {
            // 给文件设置超期期限
            touch($fileName, $expire);
            // 清除当前写入文件缓存
            clearstatcache();
            return true;
        } else {
            return false;
        }
    }


    /***
     * 清除 cache 目录
     * @param null $path
     * @return bool
     */
    final public function view_dir_clear($path=null)
    {
        $path = is_null($path) ? $this -> options['path'] : $path;
        //如果是目录则继续
        if(is_dir($path)){
            //扫描一个文件夹内的所有文件夹和文件并返回数组
            $p = scandir($path);
            foreach($p as $val){
                //排除目录中的.和..
                if($val !== "." && $val !== ".."){
                    //如果是目录则递归子目录，继续操作
                    if(is_dir($path.$val)){
                        //子目录中操作删除文件夹和文件
                        $this -> view_dir_clear($path.$val.'/');
                        //目录清空后删除空文件夹
                        rmdir($path.$val.'/');
                    }else{
                        //如果是文件直接删除
                        unlink($path.$val);
                    }
                }
            }
        }
        return true;
    }

}