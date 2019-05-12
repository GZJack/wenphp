<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/4/1
 * Time: 12:26
 */

namespace wen;

/***
 * 抽象类,只有一个功能,注册自动加载类
 * Class Loader
 * @package wen
 */
abstract class Loader
{
    /***
     * 依赖映射类实例挂载,
     * validate 挂载在Loader上,可以通过loader获得
     * @var array
     */
    private static $libraryMapClassInstance = [];

    /***
     * 自动加载类
     * 特别声明,这里是写死的,
     * wen -> 就是加载基类,核心代码
     * app -> 就是app里的模型类,控制器类
     * @param $classname string
     * @throws \Exception
     */
    final protected static function autoload($classname)
    {
        // 基类与应用类的路径,剩下的就是第三方类库
        $pathArray = [
            'wen' => CORE_PATH, // 核心代码路径
            'app' => APP_PATH, // 应用层路径
            // 其他的就默认的extend目录了,这个就没有什么好说的
        ];
        // app\user\controller; -> [0] => app [2] => user\controller
        $spacePathArray = explode('\\',$classname,2);
        // 类的目录路径
        $classDirPath = '';
        // 是否是 wen 或 app
        if(isset($pathArray[$spacePathArray[0]])){
            // 获得就可能是app 或 wen 的路径了
            $classDirPath = $pathArray[$spacePathArray[0]];
        }else{
            // 否则就是第三方类库 extend路径+空间名+'\'
            $classDirPath = EXTEND_PATH.$spacePathArray[0].DS;
        }
        // 然后路径拼接 加上文件加的名字,和后缀名
        $classDirPath .= $spacePathArray[1].EXT;
        // 将 \ 转换成与服务器匹配的 / \ 没有这句话,linux下行不通
        $classDirPath = str_replace('\\',DS,$classDirPath);
        // 保证文件的存在
        if(file_exists($classDirPath)){
            // 文件存在,则加载文件
            require $classDirPath;
        }else{
            // print_r('未找到此类');
            // 这个不仅给开发人员看,用户也能看见,因为乱传 控制器类,就会找不到文件,就会产生404错误
            //throw new \Exception("[ CLASS ] Not Found: $classname",404);
            if(App::$isAppDebug){
                Response::debug("[ CLASS ] Not Found: $classname",404,'Loader.autoload');
            }else{
                Response::send("[ CLASS ] Not Found: $classname",404);
            }
        }
    }

    /***
     * 注册自动加载函数
     */
    final public static function register()
    {
        // 挂载一个注册自动加载函数
        spl_autoload_register('self::autoload',true,true); // 注册自动加载
    }

    /***
     * 验证类的映射,用到的时候才去 new ,
     * (1) 当用户home模块下有对应的 validate 目录下 与当前控制器 同名的验证器,则去 new 这个验证器
     * (2) 如果没有,就去调用系统默认的Validate,的验证器
     * @param array $data
     * @param array $validate
     * @return  Validate
     */
    public static function validate($data=[], $validate=[])
    {
        if(isset(self::$libraryMapClassInstance['validate'])){
            return self::$libraryMapClassInstance['validate'];
        }
        // 临时承载验证器实例
        $_validateInstance = null;
        // 如果验证器为空,则需要在同模块下的validate目录下有与控制器同名的验证器继承类
        if(empty($validate)){
            $_validateFilePath = APP_PATH.App::$currentModule.DS.'validate'.DS.App::$currentController.EXT;
            // 存在就用用户定义的
            if(file_exists($_validateFilePath)){
                $_validateClass = App::$namespace.'\\'.App::$currentModule.'\\validate\\'.ucfirst(App::$currentController);
                // 需要这个类必须继承 Validate
                if(get_parent_class($_validateClass) === 'wen\\Validate'){
                    // 调用父类的获得当前的单例实例
                    $_validateInstance = $_validateClass::getInstance();
                    // 合并子类的 验证规则,消息,场景
                    $_validateInstance -> mergeExtends();
                }else{
                    // 没有继承,也不报错了,也不提醒了,不按教程来做,提醒也没有意思
                    $_validateInstance = Validate::getInstance();
                }
            }else{
                // 不存在,就使用默认的
                $_validateInstance = Validate::getInstance();
            }
        }else{
            $rules = isset($validate['rules']) ? $validate['rules'] : [];
            $messages = isset($validate['messages']) ? $validate['messages'] : [];
            $scenes = isset($validate['scenes']) ? $validate['scenes'] : [];
            $_validateInstance = new Validate($rules,$messages,$scenes);
        }
        // 挂载实例
        self::$libraryMapClassInstance['validate'] = $_validateInstance;
        // 为空就不要提交数据了
        if(empty($data)){
            return $_validateInstance;
        }else{
            // 提交数据
            $_validateInstance -> setData($data);
            // 返回实例
            return $_validateInstance;
        }
    }


    /***
     * 将path_info,传进来让加载器自动加载 控制类,并执行方法
     * @param $successPathInfo array 这个是一个成功的路由path_info 就是 [0] 就是模块, [1] 就是控制器 [2] 就是方法
     * @param string $namespace 默认空间名
     * @throws \Exception
     */
    public static function loadControllerClassCallAction($successPathInfo,$namespace='app')
    {
        // 模块名
        $module = $successPathInfo[0];
        // 控制器类
        $controller = $successPathInfo[1];
        // 方法
        $action = $successPathInfo[2];
        // 将带有 _ 下划线的控制器进行转成两个 大驼峰命名 UserInfo url => user_info 这样子访问
        if(strlen(strstr($controller,"_",true))>0){
            // 将下划线 转为 ' ' 空格
            $controller = str_replace('_',' ',$controller);
            // 每个单词的首字母大写
            $controller = ucwords($controller);
            // 再去掉空格,形成大驼峰命名法
            $controller = str_replace(' ','',$controller);
        }else{
            // 不存在下划线的话,就需要首字母大写
            $controller = ucfirst($controller);
        }
        // 进行拼接成控制器的文件路径
        $_controllerFilePath = APP_PATH.$module.DS.'controller'.DS.$controller.EXT;
        /***
         * 特别说明: 这里在有命名空间的情况下
         * 就不能再使用 require_once 加载 对应的文件后 再执行 new 类 -> 对应的方法
         * 而且 new $controller 的时候,会以 \Controller 的命名空间进行 new 控制器
         * 以不是 app 命名空间下 new 实例,就会被自动注册类函数,再执行到 extend 第三方类库上
         * 造成了没有new到真实的控制器类,而且还会报错,找不到此类,或 文件
         * 所以就只能好好利用 new 一个类  自动加载函数就会帮我们自动加载 该 控制类文件
         */
        // 看看文件是否存在
        if(!file_exists($_controllerFilePath)){
            //throw new \Exception("[ FILE ] Not Found: $controllerFilePath",404);
            Response::debug("[ FILE ] Not Found: $_controllerFilePath",404,'Loader.loadControllerClassCallAction');
        }
        // 存在文件则继续执行
        // 1.开始拼接一个控制类的命名空间
        $namespaceControllerClass = $namespace."\\".$module."\\controller\\".$controller;
        // 利用反射类,进行new 控制器类,并执行方法,利用一个错误捕获,处理一些404情况
        try {
            // 给控制器类 构造函数添加依赖注入,一个是请求类,一个是反应类,和方法中使用依赖
            $_controllerActionArgs = array(Request::getInstance(),Response::getInstance());
            // 得到一个反射类 ReflectionClass
            $reflectionClass = new  \ReflectionClass($namespaceControllerClass);
            // 得到控制器类上的构造函数,当控制器类上,没有构造函数,就会返回 null
            $hasConstructor = $reflectionClass -> getConstructor();
            // 用于接收控制器类的实例
            $_controllerClassInstance = null;
            // 如果有构造函数,我们就将依赖注入的 请求类 和 反应类,传入构造函数中,否则就不传任何参数
            if(is_null($hasConstructor)){  // 类似于,你要我就给你,不要我就不给你
                // 得到一个控制器类实例 object(\app\home\controller\Index){} 实例
                // 这里不需要传任何参数,因为控制器类和继承类都没有构造函数,传参的话就会报错
                $_controllerClassInstance = $reflectionClass -> newInstance(); // 传参方式,一个一个传
            }else{
                // 当前控制器类有构造函数,或者继承的类有构造函数,就会被执行到这里去,并把请求类和反应类的实例传入,
                // 只要有构造函数,就往里传参,不管构造函数是否使用,都无所谓,不影响传入
                $_controllerClassInstance = $reflectionClass -> newInstanceArgs($_controllerActionArgs); // 以数组方式传,并一一对应
            }
            // 判断是否有此方法
            $hasMethod = $reflectionClass -> hasMethod($action);
            // 有方法才执行
            if($hasMethod){
                // 得到一个反射方法类对象 ReflectionMethod
                $method = $reflectionClass -> getMethod($action);
                // 执行这个方法
                // $method -> invoke($controllerClassInstance); // 传参方式不一样,这里使用第二种
                // 只能接收一个 array 参数 这个array中的索引 与方法中的参数 一一对应,需要多个传参,就往数组里放,就可以了
                $result = $method -> invokeArgs($_controllerClassInstance,$_controllerActionArgs); // 以数组方式传,并一一对应
                // ↑面这个invokeArgs是必须传一个数组进去,空数组也行,只要存在这个方法,而且这个方法,必须是
                // 必须是public (静态和非静态都可以),方法一存在,就会执行,至于入的传参,方法中引不引入依赖注入,是无所谓的
                if($result !== null){
                    Response::sendActionReturn($result);
                }
            }else{
                // 没有方法,产生一个错误页面,就是 404
                //throw new \Exception("[ FILE ] Not Found: $controllerFilePath",404);
                Response::debug("[ FILE ] Not Found: $_controllerFilePath",404,'Loader.loadControllerClassCallAction');
            }
        } catch (\ReflectionException $e) {
            // 已经拦截了方法和文件,这里剩下的错误,就是没有这个类 class 不存在了, 或者这个不是一个类,又或者 方法名 和 类名 同名
            //throw new \Exception("[ FILE ] Not Found: $controllerFilePath",404);
            Response::debug("[ FILE ] Not Found: $_controllerFilePath",404,'Loader.loadControllerClassCallAction');
        }

    }




}