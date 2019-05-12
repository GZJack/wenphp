<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/4/4
 * Time: 12:23
 */




class A
{
    protected $name = '李四';
    private static $instance = null;
    public static function getInstance()
    {
        if(is_null(self::$instance))
        {
            self::$instance = new static();
        }
        return self::$instance;
    }

    public function callC()
    {
        echo $this -> name;
    }
}