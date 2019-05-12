<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/5/8
 * Time: 17:57
 */

namespace app\home\controller;

use wEmail\Email;

use app\home\model\User;

class Show
{
    public function show()
    {
        echo '<pre>';
//        var_dump($_SERVER);
//        $email = Email::getInstance();
//        $email -> emailUserRegModel([]);
//        $email -> setUserEmailAddress(['283856261@qq.com','306056405@qq.com','zhang85661976@126.com']);
//        $email -> setUserEmailAddress('283856261@qq.com');
////        $email -> send();

        $user = new User();

        $result = $user -> select();

        var_dump($result);


        return '<h1>测试1</h1>';
    }
}