<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/3/30
 * Time: 12:55
 */

namespace wen;
// 版本
define('WEN_VERSION', '1.1.0');
// 区别不同系统的分割符 '/' '\'
define('DS', DIRECTORY_SEPARATOR);
// 文件名的后缀
define('EXT', '.php');
// 整个项目的根目录
defined('ROOT_PATH') or define('ROOT_PATH', dirname(realpath(APP_PATH)) . DS);
// 核心源码目录 即是当前目录
define('CORE_PATH', __DIR__ . DS);
// 第三方类库
defined('EXTEND_PATH') or define('EXTEND_PATH', ROOT_PATH . 'extend' . DS);
// 运行时产生的缓存,和 日志文件
defined('RUNTIME_PATH') or define('RUNTIME_PATH', ROOT_PATH . 'runtime' . DS);
// 日志目录 在 runtime目录下的 log 目录
defined('LOG_PATH') or define('LOG_PATH', RUNTIME_PATH . 'log' . DS);
// 缓存目录 在 runtime目录下的 cache 目录
defined('CACHE_PATH') or define('CACHE_PATH', RUNTIME_PATH . 'cache' . DS);
// 总配置目录
defined('CONF_PATH') or define('CONF_PATH', ROOT_PATH . 'config' . DS);

/***
 * 基础类
 * Class Base
 * @package wen
 */
class Base
{

    /***
     * 将所有要开始的函数放在这里面
     */
    public static function run()
    {
        // 1.先引入自动加载类并注册,必须手动引入自动加载类,剩下的类就会自动加载了
        if(!USE_ZIP_MIN) require 'Loader.php'; // 如果使用了迷你框架,无需将此文件在此引入了,因为也不存在此文件(就两处,另一处在start.php)
        // 2.注册自动加载类
        Loader::register();
        // 3.错误异常机制注册
        Error::register();
        // 4.配置文件的加载
        Config::set(CONF_PATH.'config.php');
        // 5.判断是否关闭网站了,关闭了,就没有必要启动应用程序了
        if(!Config::get('app_open')) return Response::appClose();
        // 6.所有准备都就绪了,就开始应用程序启动
        App::start();
        // echo '欢迎使用 Wen php框架';
        return true;
    }
}