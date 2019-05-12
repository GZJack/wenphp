<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/3/30
 * Time: 12:47
 */
// 定义应用程序的目录常量
define('APP_PATH',__DIR__.'/../application/');
// 定义本应用使用的框架文件,有迷你和开源版
define('USE_ZIP_MIN',false);
// 使用压缩迷你文件框架
if(USE_ZIP_MIN){
    // 引入迷你框架文件
    require __DIR__.'/../wen/wen.min.php';
}else{
    // 引入启动文件
    require __DIR__.'/../wen/start.php';
}

