<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/3/30
 * Time: 12:52
 */
namespace wen;
// 引入基础文件,并启动
if(!USE_ZIP_MIN) require __DIR__.'/Base.php'; // 如果使用了迷你框架,无需将此文件在此引入了,因为也不存在此文件(就两处,另一处在Base.php run()方法里)
// 启动执行
Base::run();