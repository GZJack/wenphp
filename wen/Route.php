<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/4/1
 * Time: 13:04
 */

namespace wen;

/***
 * 抽象类,不能new,也最好不要用来继承,继承来说也没有什么意义
 * Class Route
 * @package wen
 */
abstract class Route
{
    /***
     * 用来挂载所有路由规则的数组
     * @var array
     */
    public static $routeRules = [];

    /***
     * 用来挂载域名加模块的,保证对应的域名才能访问对应的模块
     */
    public static $ModuleBindDomains = [];

    /***
     * 用于记录是否已经初始化了,如果初始化了,就不能在初始化了
     * @var bool
     */
    private static $hasInitialized = false;

    /***
     * 设置一个前缀,就不会影响到别人设置路由不能使用 path,或 method strict 这一类的关键词语了,因为在前面加了关键词
     * @var string
     */
    private static $routeOptionsKeyPrefix = 'wen_route_';
    /***
     * @var bool 是否开启了强制路由
     */
    private static $isUrlRouteMust = false;

    /***
     * @var bool 是否开启路由缓存
     */
    private static $isRouteRuleCache = false;
    /***
     * @var int 路由规则的缓存失效 默认是86400 一天的秒数
     */
    private static $intRouteRuleCacheExpire = 86400;
    /***
     * @var bool 路由规则缓存是否过时了,如果过时了,就会自动保存一份缓存
     * 在哪里保存呢? 总不能在rule()设置一次就保存一次吧?
     * 所以,只能再checkRoute()校验的时候进行保存一次
     * 默认值,未超时
     */
    private static $isRouteRuleCacheTimeOut = false;

    /***
     * @var array 暴露出去的真实路由数组 [0] => 模块 [1] => 控制器 [2] => 方法
     * 此时的路由未必是成功,或者正确的路由
     * 原因是,有可能没有开启强制路由,又或者是开发者,在书写路由时,产生的错误,例如 path => user/reg 就只写两个 造成没有了方法
     */
    public static $arrayRealRoute = [];
        /***
     * 路由初始化,就是简单地将,默认在application下的 route.php 加载一下就可以了
     * 让里面的函数自动执行,就会生成了路由规则
     */
    final public static function init()
    {
        // 等于 true 已经初始化了
        if(self::$hasInitialized) return null;
        self::$hasInitialized = true; // 开始初始化
        // 赋值强制了路由
        self::$isUrlRouteMust = Config::get('url_route_must');
        // 赋值路由是否开启了缓存
        self::$isRouteRuleCache = Config::get('route_rule_cache');
        // 赋值路由规则缓存的时效
        self::$intRouteRuleCacheExpire = Config::get('route_rule_cache_expire');

        /***
         * 如果开启了路由缓存的话,就不会去读取application目录下的route.php文件了
         * 而是读取缓存数据
         */
        if (self::$isRouteRuleCache){
            // 读取缓存
            $routeRuleCache = Cache::get('route_rule_cache',false);
            // 只要不是 默认值 false 则是有数据,否则会去
            if($routeRuleCache !== false){
                self::$routeRules = $routeRuleCache; // 赋值
                // 当读取了缓存里的值了,自然就不用去加载route.php文件了
                return true; // 终止往下执行
            }
            // $routeRuleCache 等于 false 即是超时了
            self::$isRouteRuleCacheTimeOut = true;
        }

        // 拼接应用层下的默认路由文件
        $appRouteFilePath = APP_PATH.'route.php';
        // 存在就加入,不存在就不管了
        if(file_exists($appRouteFilePath)){
            require $appRouteFilePath;
        }
        return true;
    }

    /***
     * 挂载路由规矩缓存
     */
    final protected static function setRouteRuleCache()
    {
        // 如果开启了路由缓存 并且 路由缓存过时了
        if(self::$isRouteRuleCache && self::$isRouteRuleCacheTimeOut)
        {
            Cache::set('route_rule_cache',self::$routeRules,self::$intRouteRuleCacheExpire);
        }
    }
    /***
     * 通过域名绑定模块,如果没有指定,就是可以直接访问的
     * 只要一绑定了域名,执行到该模块就必须是这个域名才能执行下入,否则就是404
     * @param $module string
     * @param $domain string
     */
    final public static function domain($module,$domain)
    {
        self::$ModuleBindDomains[$module] = $domain;
    }

    /***
     * 挂载路由规则的函数
     * 只要没执行一条加载,就会记录下一条规则
     * @param $rule string  路由规则
     * @param string $route  真实路由
     * @param string|array $method  允许提交的方法
     * @param array $options  配置项,如使用严格模式
     */
    final public static function rule($rule, $route = '', $method = '*',$options=[])
    {
        /***
         * 例子-> (1) user/reg =>  user/reg/index
         *        (2) user/info/:id => user/info/index?status=
         *
         * 结果是 => ['user' => [
         *              'reg' => [ 'path' => 'user/reg/index', 'method' => '*'],
         *              'info' => [ ':id' => [ 'path' => 'user/reg/index', 'method' => '*']]
         *          ]]
         *
         * 先将路由规则进行分类,分组
         */
        // 先将路由规则进行数组化
        $arrayRule = explode('/',$rule);
        // 将其倒叙,先做尾部
        $arrayRule = array_reverse($arrayRule);
        // 用于接收本次路由规则对象
        $newRuleArray = array();
        // 初始化赋值,将 path type 赋值到尾部的变量
        $isInit = false;
        // 可以省略书写 method 方法,则 options 就往前提
        if(!is_string($method) && is_array($method)){
            $options = $method;
            $method = '*';
        }else{
            // 如果 method 方法被省略了,但是输入的 $method 又不是数组,就只能恢复默认
            if(!is_string($method) && !is_array($method)){
                $method = '*';
                $options = [];
            }
        }

        /***
         * 路由对象初始化构建项,需要扩展的可以在这里进行添加
         * path => 是真实的路劲
         * method => 是限定用户的请求方法, * 表示任何方法都可以访问
         * strict => 严格模式,当设置到模块后,就必须保证 这个模块都必须遵循路由表规定的规则来访问,否则就是404
         */
        $initArray = array(
            // 真实路径
            'path' => $route,
            // 允许的请求方法
            'method' => strtoupper($method)
        );
        // 合拼配置项 这里不做 method 大写了,尽量不要再这里面传
        $initArray = array_merge($options,$initArray);
        // 读取前缀 并传入到下面的匿名函数中去
        $prefix = self::$routeOptionsKeyPrefix;
        // 给初始化配置其添加前缀  合并数组 array_combine 映射 array_map key加前缀 array_keys 获得所有key
        $_initArrayKeyAddPrefix = array_combine(array_map(function ($key) use ($prefix){
                return $prefix.$key; // 返回加了前缀的key
        },array_keys($initArray)),$initArray);

        // 倒叙后遍历
        foreach ($arrayRule as $key => $value){
            // 如果初始化了,就将最底层的一项作为下一项的 值
            if($isInit===false){
                // 已经初始化了
                $isInit = true;
                // 初始化赋值  加了前缀
                $newRuleArray[$value] = $_initArrayKeyAddPrefix;
            }else{
                // 取出第一项作为 下一项的值
                $newRuleArray[$value] = array_splice($newRuleArray,0,1);
            }
        }
        // 然后将构建好的对象 进行递归合并起来,再赋值给 总路由规则
        self::$routeRules = array_merge_recursive(self::$routeRules,$newRuleArray);
    }

    /***
     * 校验是否有带: 冒号的key 如果有,把其对应的值饭回来,否则是一个false 就是没有带冒号的
     * 如果没有带冒号的 key 在强制路由或者 严格模式下, 是要返回404的
     * @param $isOkArray array
     * @param $noRoutePathInfoArray array
     * @return array|bool|mixed
     */
    protected static function checkColonKey($isOkArray,$noRoutePathInfoArray)
    {
        // 是否有 : 符号
        $hasColonKey = false; // 默认没有
        // 等于1,就看看当前的 剩余路由里有没有带 :id 或 :xxx
        foreach ($isOkArray as $key => $value){
            // 第一个字符的 ASCII 是否等于 58 如果是58 就是 :
            if(ord($key) === 58){
                // 有带冒号的idKey了
                $hasColonKey = true;
                // 只找第一个 有:id 就将key=>:id的值赋给ok数组
                $isOkArray = $value; // 获得新值
                // 获得右边的变量
                $rightVar = substr($key,1);
                // 挂载到路由参数值请求类当中去
                $_route = [];
                // 组成对象
                $_route[$rightVar] = $noRoutePathInfoArray[0];
                // 将对应的key,和对应的value传到Request类中并进行挂载
                Request::route($_route,null); // 第一个参数是数组的时候就是挂载值
                break;// 跳出循环
            }
        }
        // 有带冒号的 -> 则返回 对应的数组 没有则返回 false
        return ($hasColonKey === true) ? $isOkArray : false;
    }

    /***
     * @param $pathInfo array
     * @param $method string
     * @return bool
     */
    final public static function checkRoute($pathInfo,$method)
    {
        // 一进来先去挂载路由规则缓存,决定是否挂载,由函数内部决定
        self::setRouteRuleCache();
        // 获取前缀
        $prefix = self::$routeOptionsKeyPrefix;
        /***
         * 首先声明: 严格模式和强制路由模式是互不冲突的
         * 强制路由,是实现在整个应用下,
         * 严格模式,比较放松,之管理 一个模块下,或者一个自定义的路由下 所有的子路由,必须匹配中了才能访问
         * 当前 模块/控制器/自定义路径 后的所有子路由 是否使用了严格模式
         * (意识就是需要严格匹配中了,才能访问,如果没有匹配中,那就不能访问)
         */
        $isUseStrictMode = false;
        // 是否出现未存在的情况,当出现未存在的,有可能是:id
        $isShowNoExistsOfPathInfo = false; // 默认都存在
        // 是否 存在 第一级模块 -> 也就是 path_info [0] 第一个项要匹配中了,
        $isOneModelExists = true; // 默认是进入的

        // 未匹配中的字段名称
        $noRoutePathInfoArray = [];

        // 匹中的项
        $isOkArray = null;
        // 遍历当前的路由
        foreach ($pathInfo as $index => $item){
            // 是初始值null,表示第一次进入
            if(is_null($isOkArray)){
                // 看看能不能找到第一级 模块
                if(isset(self::$routeRules[$item])){
                    $isOkArray = self::$routeRules[$item];
                }else{
                    // 第一级都找不到,说明该模块未定义路由规则
                    $isOneModelExists = false;
                    break; // 跳出循环
                }
            }else{
                // 是否使用了验证模式,就不能使用:id因为严格模式要求连最后一项都是一致的
                if(isset($isOkArray[$prefix.'strict'])){
                    // 严不严格,是有父级别的说了算的,下面再新赋值,也不能管
                    $isUseStrictMode = is_bool($isOkArray[$prefix.'strict']) ? $isOkArray[$prefix.'strict'] : $isUseStrictMode;
                }
                // 只要出现不存在path_info的情况下,并且这项存在
                if($isShowNoExistsOfPathInfo === false && isset($isOkArray[$item])){
                    // 修改当前值
                    $isOkArray = $isOkArray[$item];
                }else{
                    // 出现不存在的path_info
                    $isShowNoExistsOfPathInfo = true;
                }
                // 当有未匹配中的,就进行记录有哪些字段未匹配中,有可能未匹配中的是:id
                if($isShowNoExistsOfPathInfo === true){
                    array_push($noRoutePathInfoArray,$item);
                }
            }
        }
        /***
         * 不能通过,需要返回的几种表现
         * (1) -> 强制路由下 一级路由未匹配中 则404
         * (2) -> 在严格模式或强制路由的情况下 path_info 有且只有一个 未匹配中的字段 大于1个的 则404
         * (3) -> 是第二种的 辅助方法, 当路由规则里,没有设置 :id 的,就是404
         */
        // 第一种情况
        if(self::$isUrlRouteMust === true && $isOneModelExists === false){
            // 执行404
            Response::waitSend("[ ROUTE ] route rule is url_route_must = true",'Route.checkRoute');
            // 不通过,禁止往下执行
            return false;
        }
        /***
         * 第二种情况,严格模式 或 强制路由 两者没有任何关系,当出现有不存在的,
         * 即是 第一级模块已经匹配中了
         * 这时,未匹配中的 字段只可以有一项,多一项就404
         */
        // 看看是否有不存在的
        if($isShowNoExistsOfPathInfo === true){
            // 就是大于1的路由 就是不符合, 如果是为 0 , 则是第一级路由也没有匹配中
            $noRouteCount = count($noRoutePathInfoArray);
            // 当只有一项不存在的字段的时候,我们默认它就是 :id
            if ($noRouteCount === 1){
                // 检验当前的 $isOkArray 数组里是否有带有 :id 的key
                $hasColonKey = self::checkColonKey($isOkArray,$noRoutePathInfoArray);
                // 如果有,则重新赋值 $isOkArray
                if($hasColonKey !== false){
                    $isOkArray = $hasColonKey;
                    // 这会匹配上:id 需要将没有存在的进行修改成 没有不存在的
                    $isShowNoExistsOfPathInfo = false;
                }else{
                    // 执行404
                    Response::waitSend("[ ROUTE ] route rule not is :key",'Route.checkRoute');
                    // 不通过,禁止往下执行
                    return false;
                }
            }

            /***
             * 如果使用了严格模式或者强制路由,只要有两个或以上的字段不存在,则无法通过
             */
            if($isUseStrictMode === true || self::$isUrlRouteMust === true){
                // 大于一项
                if($noRouteCount > 1) {
                    // 执行404
                    Response::waitSend("[ ROUTE ] route rule check current path_info is error",'Route.checkRoute');
                    // 不通过,禁止往下执行
                    return false;
                }
            }
        }
        //  $isShowNoExistsOfPathInfo===false 那就是全部匹配中了
        /***
         * 如果在又没有 强制路由,又没有严格模式 ? 那是不是 就随便乱来了呀? 答案是,肯定是不可以的
         * 执行到这里,既没有出现404错误页面,
         * (1) 没有强制路由,没有严格模式 -> 进入了一级模块 又没有 不存在的字段  属于完全匹配 包括带:id匹配上的
         * (2) 有强制路由，也有严格模式
         */
        if($isOneModelExists === true && $isShowNoExistsOfPathInfo === false){
            // 进入了一级路由,又没有出现 未匹配中的,说明已经完全匹配了
            // 最后一项的path,记录一下最后一个路径
            $lastPath = isset($isOkArray[$prefix.'path']) ? $isOkArray[$prefix.'path'] : '';
            // 最后一项的method,记录一下最后一项方法
            $lastMethod = isset($isOkArray[$prefix.'method']) ? $isOkArray[$prefix.'method'] : '*';

            // 如果限定的方法和当前提交的方法不一致,返回404
            if($lastMethod !== '*' && $lastMethod !== $method){
                // 执行404
                Response::waitSend("[ ROUTE ] current $method method not is $lastMethod",'Route.checkRoute');
                // 不通过,停止往下执行
                return false;
            }
            // 开始判断 path 里是否有 ? 问号,携带参数
            $hasParam = stripos($lastPath,'?',1);
            // 有问号了
            if($hasParam>1){
                $pathParamArray = explode('?',$lastPath);
                // 成功获取真实的路由
                self::$arrayRealRoute = explode('/',$pathParamArray[0]);
                // 需要提交的参数
                $_ParamArray = [];
                parse_str($pathParamArray[1],$_ParamArray);
                // 将带?的参数挂载起来
                Request::route($_ParamArray,null);
            }else{
                // 没有问号,成功获得真实路由
                self::$arrayRealRoute = explode('/',$lastPath);
            }
            // 通过,可以往下执行
            return true;
        }
        // 既没有严格模式,又无强制路由,又未进入第一级模块,又或者 未匹配中的字段大于 2 以上的情况,就直接返回 当前用户输入的path_info
        self::$arrayRealRoute = $pathInfo;
        // 同意往下执行
        return true;
    }

    /***
     * 校验当前域名是否通过
     * @param $module string 校验的模块
     * @param $domain string 校验的当前域名
     * @return bool
     */
    final public static function checkDomain($module,$domain)
    {
        // 是否存在当前模块对域名的限制
        if(isset(self::$ModuleBindDomains[$module])){
            // 限定域名不一致,则不能通过
            if (self::$ModuleBindDomains[$module] !== $domain){
                // 得执行一个 Response 请求,发送404错误
                Response::waitSend('[ DOMAIN ] domain request error','Route.checkDomain');
                // 不能通过
                return false;
            }
        }
        // 不存在显示,相等,通过
        return true;
    }
}