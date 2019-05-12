<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/4/1
 * Time: 17:47
 */

// 导入系统路由
use wen\Route;

/***
 * 域名绑定模块
 */
Route::domain('home','mobile.5156580.com');
// api 模块只可以使用  api.5156580.com 域名访问
Route::domain('api','api.5156580.com');

/***
 * 路由规则
 */

/***
 * api 模块之路由规则
 */
Route::rule('api/wechat/message.py','api/wechat/message');


// 简单地测试一下
Route::rule('home/my','home/index/index');
//Route::rule('home/my','home/index/index','get',['strict' => true]);
Route::rule('home/hello','home/index/hello?name=jack');
Route::rule('home/my/:id','home/index/index');
Route::rule('user/reg','user/reg/index',['strict' => true]);
//Route::rule('user','user/reg/index',['strict' => true]);

Route::rule('hello/:name','user/test/hello?status=1','GET');

Route::rule('hehe','home/index/index/aaa?frfr','get');

Route::rule('jack','user/test/test?status=1',['strict' => true]);

Route::rule('html/b.html','user/test/test?status=1');
Route::rule('show','home/show/show?status=1');
