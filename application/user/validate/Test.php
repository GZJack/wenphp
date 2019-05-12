<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/4/13
 * Time: 1:32
 */

namespace app\user\validate;


use wen\Validate;

class Test extends Validate
{
     protected $rules = [
         'name|姓名' => 'require|\d+|length:6,50|email',
         'age|年龄' => 'number',
         'sex' => 'string|length:6,50|max:30'
     ];
     protected $scenes = [
         'add' => ['name','info'],
         'reg' => ['sex'],
         'login' => ['name','age','sex']
     ];

     public function isLaoDi($rule,$key,$data,$params,$alias)
     {
         echo '我是老弟了';
         return $alias;
     }
}