<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/4/12
 * Time: 12:21
 */

namespace app\user\controller;


use wen\Controller;
use wen\Request;
use wen\Response;
use wen\Sqlite;

class Test extends Controller
{

    public function test(Request $request,Response $response)
    {
        //echo '牛';
//        echo "<h1>6666</h1>";
//        echo include '../view/index.html';
//        return '<h1>M123</h1>';
//        ob_start();
//        ob_implicit_flush(0);

        //echo '<br><pre>';

        // 通过单例获得Sqlite实例,传入用户的userId -> 即可以获得对应的数据库MD5(userId).db文件
        //$sqlite = Sqlite::getInstance('zhangwensheng');

        // 增  返回受影响的记录条数
        //$count = $sqlite -> add('test',['name'=>'张三','age'=>18]);
        // 获得新增Id
        //$lastId = $sqlite -> lastId;

        // 删(软删除)  返回受影响的记录条数
        //$count = $sqlite -> delete('test','msgId_5cc06ed4329da');
        //var_dump($count);

        // 改 返回受影响的记录条数
        //$count = $sqlite -> update('test','msgId_5cc039b155072','hello');

        // 查 单条查询(返回的是结果)
        //$result = $sqlite -> find('test','msgId_5cc039b155072');
        //var_dump($result);

        // 查 多条查询(返回的是结果集)
        //$result = $sqlite -> select('test');
        //var_dump($result);

        // 关闭数据库连接
        //$sqlite -> close();

        // 自动校验GET里的参数,通过,则会自动执行,不通过,则停止往下执行
        //$request -> has(['name','id','type'],'get');

//        echo '字符串测试<br>';
//        $str = 'application/json';
//        var_dump(stripos($str,'json'));
//        echo stripos($str,'jsona');
//
//        echo '<br>';

        //$request -> method('post');

//        var_dump($request -> header());
//        $request -> header(['wode'=>'我们的']);
//        var_dump($request -> header());

        //$request -> has(['type','id','name'],'get');


        //var_dump($_COOKIE);
        //var_dump($_REQUEST);

        //$result = $request -> getParam(null,'get');
        //var_dump($result);

//        var_dump($request -> method);

       // var_dump($request->getParam());
//        var_dump($request->request_uri);
//        var_dump($_POST);



        //var_dump($request -> getSession());

        //var_dump(array_change_key_case($_SERVER,CASE_LOWER));



            //var_dump(file_put_contents('php://input'));

        //$response -> setSession('wen','meiyoushenmedadaoli');

        //$response -> setCookie('lisi','778hhfgg',20);

        ///setcookie('zhang','de');


        //session_destroy();
        //var_dump($_SESSION);
        echo '123';

//        $this -> assign(['title' => '标题','name'=>'姓名','age'=>18,'names'=>['张三','李四','王五']]);
        //extract(['title' => '标题','name'=>'姓名','age'=>18],EXTR_OVERWRITE);

//        require APP_PATH.'user/view/test/test.php';
//
//        echo ob_get_clean();
//        return $this -> publicViewCache('test.html');

    }

    public function hello(Request $request)
    {
       // echo 'status => '.$request -> getParam('status').' hello '.(($request -> getParam('name')) ? $request -> getParam('name') : 'world');

        $data = [
            'name' => "13345645678",
            'age' => 15,
            'sex' => '男'
        ];

        $validate = [
            'rules' => [
                'name|姓名' => 'require|length:6,50|phone',
                'age|年龄' => 'number|between:18,100',
                'sex' => 'string|length:6,50|max:30'
            ],
            'messages' => [
                'name.require' => '用户名为必填',
                'name.email' => '必须是有效邮箱'
            ],
            'scenes' => [
                'add' => ['name','info'],
                'reg' => ['sex'],
                'login' => ['name','age','sex']
            ]
        ];

        $validate = $this -> validate($data,$validate);

        //$validate -> abc('a');
        //$validate -> setData($data);

        $result = $validate -> check('login');

        if($result !== true){
            echo $validate -> getError();
        }else{
            echo '<br>校验成功';
        }



    }
}