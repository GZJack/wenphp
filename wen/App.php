<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/4/1
 * Time: 11:11
 */

namespace wen;

class App
{

    /**
     * 应用层的命名空间
     * @var string
     */
    public static $namespace = 'app';

    /***
     * @var array 成功的path_info
     */
    public static $successPathInfo = [];

    /***
     * 当前的模块,即是 user home
     * @var null
     */
    public static $currentModule = null;
    /***
     * 当前的控制器
     * @var null
     */
    public static $currentController = null;
    /***
     * 当前的方法
     * @var null
     */
    public static $currentAction = null;
    /***
     * 是否开发模式
     * @var bool
     */
    public static $isAppDebug = true;

    /***
     * @var bool 是否开启了强制路由
     */
    private static $isUrlRouteMust = false;
    /***
     * @var bool 是否开启了路由功能
     */
    private static $isRouteRuleOn = true;

    /***
     * 初始化应用程序
     * 设置了是否开放模式
     * 设置了系统时区
     */
    private static function init()
    {
        // 开发调试模式
        self::$isAppDebug = Config::get('app_debug');
        // 应用层的命名空间
        self::$namespace = Config::get('application')['default_namespace'];
        // 默认模块
        self::$currentModule = Config::get('application')['default_module'];
        // 默认控制器
        self::$currentController = Config::get('application')['default_controller'];
        // 默认方法
        self::$currentAction = Config::get('application')['default_action'];
        // 强制路由
        self::$isUrlRouteMust = Config::get('url_route_must');
        // 是否开启了路由
        self::$isRouteRuleOn = Config::get('route_rule_on');
        // 如果关闭了路由,强制路由就必须失效
        if(self::$isRouteRuleOn === false){
            self::$isUrlRouteMust = false;
        }
        // 设置系统时区
        date_default_timezone_set(Config::get('default_timezone'));
    }

    /***
     * 加载所有公共函数文件
     */
    private static function autoCommonFile()
    {
        // 自动加载 系统默认的公共函数文件
        if(file_exists(APP_PATH.'common.php')){
            require APP_PATH.'common.php';
        }
        // 读取公共函数配置
        $commonFiles = Config::get('common_files');
        // 加载用户自定义的公共函数文件
        if(!empty($commonFiles) && is_array($commonFiles)){
            $commonPath = Config::get('common_path');
            // 遍历添加
            foreach ($commonFiles as $fileName){
                // 拼接成文件路径
                $filePath = $commonPath.$fileName;
                // 判断是否存在
                if(file_exists($filePath)){
                    require $filePath;
                }else{
                    Response::debug("[ File ] Not Found: $filePath",-1,'App.autoCommonFile');
                }
            }
        }
    }



    /***
     * 设置成功的path_info
     * @param $realRoutePath array 真实路由path
     * @return bool
     */
    protected static function setSuccessPathInfo($realRoutePath)
    {
        // 如果开启了强制路由 就必须 真实的路由里只能有 三个 值 模块,
        if(self::$isUrlRouteMust){
            // 在强制路由下,必须是三个的
            if(count($realRoutePath) === 3){
                self::$currentModule = $realRoutePath[0];
                self::$currentController = $realRoutePath[1];
                self::$currentAction = $realRoutePath[2];
                // 赋值成功的path_info
                self::$successPathInfo = $realRoutePath;
                // 设置成功
                return true;
            }else{
                Response::waitSend('[ ROUTE ] route rule is url_route_must = true','App.setSuccessPathInfo');
                // 设置失败
                return false;
            }
        }
        self::$currentModule = (isset($realRoutePath[0]) && !empty($realRoutePath[0])) ? $realRoutePath[0] : self::$currentModule;
        self::$currentController = (isset($realRoutePath[1])) ? $realRoutePath[1] : self::$currentController;
        self::$currentAction = (isset($realRoutePath[2])) ? $realRoutePath[2] : self::$currentAction;
        // 赋值成功path_info
        self::$successPathInfo = array(
            self::$currentModule,
            self::$currentController,
            self::$currentAction
        );
        // 设置成功
        return true;
    }


    /***
     * 整个应用程序开始工作
     */
    final public static function start()
    {
        // 1.应用初始化
        self::init();
        // 2.加载公共函数common.php
        self::autoCommonFile();
        // 3.路由开始初始化,将所有路由进行 关闭了就不会初始化了 等于 true为开启 才进行初始化
        if(self::$isRouteRuleOn === true) Route::init();
        // 4.请求开始构建,会请请求的所有有用的值会挂载在这个类上
        $request = Request::getInstance();
        // 4.1 构建开始
        $request -> build();
        // 4.2 获得path_info
        $pathInfo = Request::getPathInfo($request->request_uri,$request->script_name);
        // 4.3 检查校验路由,关闭了,也就不用继续校验了
        if(self::$isRouteRuleOn === true){
            // 4.3.1 检验当前路由,并获取真实路由 等于 true 可以通过 Route::$arrayRealRoute
            if(!Route::checkRoute($pathInfo,$request->method)){
                Response::send('[ ROUTE ] url error',404,'App.start');
                return null;
            }
            // 4.3.2 获取真实路由,并设置成成功的路由path_info
            if(self::setSuccessPathInfo(Route::$arrayRealRoute) === false){
                Response::send('[ ROUTE ] url error',404,'App.start');
                return null;
            }
            // 4.3.3 校验访问当前模块的域名是否正确
            if(Route::checkDomain(self::$currentModule,$request -> domain) === false){
                Response::send('[ ROUTE ] domain error',404,'App.start');
                return null;
            }
        }else{
            // 4.3.4 如果是关闭了路由,就只要将 $pathInfo 设置为成功的path_info , 关闭了路由， 强制路由就会失效,这里就不用 if 语句来判断了
            self::setSuccessPathInfo($pathInfo);
        }
        // 5. 开始通过成功的path_info进行加载控制器和方法
        Loader::loadControllerClassCallAction(self::$successPathInfo);
    }

}