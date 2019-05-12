<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/4/3
 * Time: 17:18
 */

namespace wen\cache\driver;

use wen\cache\Driver;

class File extends Driver
{
    /***
     * 基本配置
     * @var array
     */
    protected $options = [
        'expire'        => 0,  // 过期时间,0表示十年以后
        'cache_subDir'  => true, // 是否使用子目录
        'prefix'        => '', // 前缀
        'path'          => CACHE_PATH, // 保存的路径
        'data_compress' => false, // 是否压缩
    ];
    /***
     * 用来存取读取时,得到的超时时间,方便实现自增 自减,还能把 超时时间放进去
     * @var integer
     */
    private $tmpTimeOut = 0;
    /***
     * 是否是自增 或 自减
     * @var bool
     */
    private $isIncOrDec = false;

    /***
     * 设置配置参数
     * @param $options array
     */
    final public function connect($options=[])
    {
        $this -> options = array_merge($this -> options,$options);
    }

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
     * 检查是否存在当前值
     * @param $name string
     * @return bool
     */
    final public function has($name)
    {
        return $this -> get($name) ? true : false;
    }

    /***
     * 获取值
     * @param $name string
     * @param bool $default
     * @return bool|mixed
     */
    final public function get($name, $default = false)
    {
        // 生成文件名
        $fileName = $this -> getCacheKey($name);
        if (!is_file($fileName)) {
            return $default;
        }else{
            // 判断是否过期
            $time_out = filemtime($fileName);
            // 自增或自减的时候,才能赋值超时时间
            if($this -> isIncOrDec === true){
                $this -> tmpTimeOut = $time_out;
            }
            // 超期了就返回默认值,并删除文件
            if($time_out < $_SERVER["REQUEST_TIME"]){
                // 清除已经过期的文件
                unlink($fileName);
                return $default;
            }
            // 引入文件 并返回结果
            return require $fileName;
        }
    }

    /***
     * 设置值
     * @param $name string
     * @param $value mixed
     * @param integer|\DateTime $expire
     * @return bool
     */
    final public function set($name, $value, $expire = null)
    {
        // 生成文件名,并生成文件夹
        $filename = $this -> getCacheKey($name, true);
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
            // 如果是自增 自减,对文件的时间不做修改,
            if(strlen($this -> tmpTimeOut) !== 10){
                // $expire 如果传经来的本来就是时间戳了,那就没有必要再加一个当前时间戳了
                $expire = time() + $expire;
            }

        }
        // 将数据转成字符串
        $fileData = var_export($value,true);
        // 接收返回结果
        $result = file_put_contents($filename, "<?php\n return $fileData;");

        // 返回了结果,就证明已经成功了
        if ($result) {
            // 给文件设置超期期限
            touch($filename, $expire);
            // 清除当前写入文件缓存
            clearstatcache();
            return true;
        } else {
            return false;
        }
    }

    /***
     * 自增
     * @param $name string
     * @param integer $step
     * @return bool
     */
    final public function inc($name,$step=1)
    {
        // 执行自增
        $this -> isIncOrDec = true;
        // 读取当前的值
        $value = $this -> get($name);
        // 如果是数字就会进行自增
        if (gettype($value) === 'integer'){
            $value += $step;
        }else{
            $value = $step;
        }
        return $this -> set($name,$value,$this -> tmpTimeOut);
    }

    /***
     * 自减
     * @param $name string
     * @param integer $step
     * @return bool
     */
    final public function dec($name,$step=1)
    {
        // 执行自减
        $this -> isIncOrDec = true;
        // 读取当前值
        $value = $this -> get($name);
        // 如果是数字就会进行自增
        if (gettype($value) === 'integer'){
            $value -= $step;
        }else{
            $value = -$step;
        }
        return $this -> set($name,$value,$this -> tmpTimeOut);
    }

    final public function rm($name)
    {
        return $this -> rmFile($this -> getCacheKey($name));
    }

    /***
     * 获取结果后删除缓存
     * @param $name string
     * @return bool|mixed
     */
    final public function pull($name)
    {
        $value = $this -> get($name);
        $this -> rmFile($this -> getCacheKey($name));
        return $value;
    }

    /***
     * 清除 cache 目录
     * @param null $path
     * @return bool
     */
    final public function clear($path=null)
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
                        $this -> clear($path.$val.'/');
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

    /***
     * 删除缓存文件
     * @param $filePath string|mixed
     * @return bool
     */
    final private function rmFile($filePath)
    {
        return file_exists($filePath) && unlink($filePath);
    }

}