<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/3/30
 * Time: 18:09
 */

namespace app\home\controller;

use app\home\model\User as UserModel;
use wen\Cache;
use wen\Controller;
use wen\Request;

class Index extends Controller
{
//    public function __construct()
//    {
////        var_dump($request);
//        echo '我的';
//    }

     public function index()
     {
//         $this -> request -> hi();
         echo 'index 页';

//         var_dump(get_called_class());

         $user = new UserModel();

//         $result = $user -> where('status','=',0)
//             -> where('account','=','18588778589@qq.com')
//             -> orderBy(['Id'])
//             -> desc()
//             -> limit(2)
//             -> pages(2)
//             -> select();
//         echo '<pre>';

         $data = [
             'account' => '12321@qq.com888de8'
         ];

         $result = $user -> where('Id','=',$_GET['id'])
             ->delete();

         var_dump($result);

         return '123';


//         $user ->find();
         //UserModel::table('uuus');
             //->find();
//
//         $result = $user -> where('id',1)
//                         -> faild('name','sex','email')
//                         -> find();

//         User::table('user')
//               -> where('id','123')
//               -> find();

//         try{
////             $s = new  adds();
//             //throw new \Exception('哈哈');
////             throw new ErrorException(E_COMPILE_ERROR,'haole');
//         }catch (ErrorException $e){
////             throw $e;
//            echo $e -> getMessage();
//         }

//         trigger_error('adedaea');
         //throw new \PDOException();

         //throw new \Error('no id');
//         throw new \Exception('yeye');
//         throw new \PDOException();
//         try{
//
////             $a = new adsa();
//     }catch (){
//         throw new \Exception('adea');
//     }
        // echo '<br>';
         //echo microtime(true);
     }

     public function hello()
     {

         $user = new UserModel();

         $result = $user -> where('Id','=',246)
             -> field(['openid','account','status'])
             -> find();

         var_dump($result);

         //$request -> hi();
//         var_dump($info);
//         $info -> hi();
         echo 'hello 页';

//         echo '<pre>';
//        sayHello();
     }

    /**
     * Index constructor.
     */
//    public function index()
//    {
////        $test = new  Test();
//
////        $info -> hi();
//
////        sayHello();
//
//        echo '1232';
//    }

    public function add()
    {
//        $user = new UserModel();
//        $data = [
//            'userid' => ['a'=>'a'],
//            'account' => 'deeaea'
//        ];
//        $result = $user -> data($data)
//            -> insert();
//        var_dump($result);
//        var_dump($user -> insertId);



        $data = [
            'name' => "张三'李‘四",
            'age' => 15,
            'sex' => '男'
        ];
        //$this -> validate();

        //$test = json_encode($data,true);

//        echo '<pre> 文件数量:';
//
//        var_dump(get_included_files());
//
//        echo '<br>';

        //var_dump(json_decode($test,true));

        $validate = [
            'rules' => [
                'name' => 'require|email',
                'info' => 'require',
                'age' => 'number',
                'sex' => 'string|length:6,50|max:30'
            ],
            'messages' => [
                'name.require' => '用户名为必填',
                'name.email' => '必须是有效邮箱'
            ],
            'scenes' => [
                'add' => ['name','info'],
                'reg' => ['sex'],
                'login' => ['age','sex']
            ]
        ];

        $validate = $this -> validate($data,$validate);

        $validate -> setData($data);

        $result = $validate -> check('add');

        if($result !== true){
            echo $validate -> getError();
        }else{
            echo '<br>校验成功';
        }



        echo '<br>';

        //$test = json_decode();

        //var_dump($test);

        //Cache::set('jack','你好吗',200);
        //Cache::set('jack',json_decode($test),2000);

        //Cache::set('token','12132323232j3j2j3j2j3j2j1j2j3j1j31',7100);
        //Cache::set('token',$data,7100);

        //Cache::set('mmm',1000,200);

//        Cache::inc('mmm',1);
//
//        echo '<br>';
//        var_dump(Cache::get('mmm'));
//        echo '<br>';

        //Cache::rm('token');






        //var_dump(Cache::get('token'));
    }

}