<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/3/30
 * Time: 13:24
 */
/***
 * 重要说明,这个config.php里的配置为整个应用配置的基础配置
 * 这里面所有的key值不能更改和删除,当更改或删除后,造成后台调用的时候,取不到真实的key而报错
 * 这里面的value,可以修改,但是得保证同类型,即是 bool 必须是 true -> false false -> true
 * 还支持 Config::set(key,value) 新增或修改,如果存在则为修改,否则为新增
 * 还支持 Config::get(key) key = null 就是获得当前得全部配置项,输入 key 则是获得当前 key 的配置
 * 还支持当前模块目录下增加配置 config.php 配置文件
 * 当执行到当前模块下,后台会自动去判断当前模块目录下是否存在config.php文件,存在则添加,配置信息优先级 为 最高级
 * 模块目录下的config.php文件,不需要像设置与当前 config.php一样的配置文件,key都一样,这是不对的
 * 应该设置于当前模块和控制器及方法有关的配置,比如我在user模块下, 可以设置 return ['database' => [...格式需要与当前config.php里的一致]]
 * 这样就能保证,再不同的模块下可以使用不同的数据库
 * 再举个例子,当前后台程序已经确定了 当前的 模块 控制器 方法 , 你再去设置 默认模块 默认控制器,就没有意义了
 * 还有 app_open 网站开放 都已经执行到方法了,你模块下的配置说关闭 , 也是没有用的
 * 请记住以上说明,否则配置信息无法得到正确的读取
 * 原则,为了节省代码,核心区并没有设置主本配置文件,这样也是很危险的,造成开发人员对这里面的配置胡乱修改
 * 所以在这里特此声明
 */
return [
    // 系统层上的配置
    // 网站开启或者关闭
    'app_open' => true,
    // 开启调试模式
    'app_debug' => true,
    // 应用Trace 开启追踪
    'app_trace' => true,
    /***
     * 路由规则,开启与关闭,如果关闭路由规则,输入的 url 及必须是符合规范的,就是必须
     * 指定 (除了默认的) /模块名/控制器名/方法名?请求参数
     * 对路由要求不高的,可以建议关闭,因为路由里使用了大量的循环与正则进行匹配 /home/index/:id/get/:name 都有可能消耗服务器内存
     * 这里个人建议,关闭
     * 关闭后,自然就不会去读取application下的route.php文件了
     */
    // 路由开启
    'route_rule_on' => true,
    /***
     * 是否开启路由规则缓存 => 当开启缓存的时候 当缓存文件存在的时候,
     * 则不会执行application目录下的route.php文件,
     * 文件的失效时一天 24*60*60
     * Cache::set('route_rule_cache',[routeRules],过期时间)
     * 好处是避免了重复的运算,减少内存支出,
     * 开发时尽量不要开启,上线时建议开启
     * 注意,route_rule_on 关闭后,缓存失效,也不会读取,也不会缓存
     */
    'route_rule_cache' => false,
    // 缓存时间 默认时一天
    'route_rule_cache_expire' => 24*60*60,
    /***
     * 是否强制使用路由? 何为强制使用路由?
     * 当开启强制路由后,用户只能访问 route.php 里设置的路由,或者开发者忘记写路由规则了,用户也是无法访问的
     * 这就保证了,用户只能访问规定好的路由,其他的路由一律不可以访问
     * 不开启的话,用户可以访问任意存在的路由,只要存在控制器和方法
     * 建议上线后开启,但是要记得设置好路由规则
     * 需要注意,route_rule_on 关闭后强制路由失效
     */
    'url_route_must' => true,
    // 默认时区
    'default_timezone' => 'PRC',

    // 公共函数的目录地址,application下的common.php是默认的,如果存在就会自动加载,不存在就不管
    'common_path' => APP_PATH.'common/',  // 目录是可以自定义的,但是'common'这个名字是受保护的,不会被当作模块目录进行访问
    // 所有公共函数,放在application目录下的common目录下公共函数,需要在这里添加,则会去帮
    'common_files' => [
        // 是文件名,必须带.php, 会自动检测,存在就会添加,否则就会报错
         // 'common_1.php'
    ],

    // 应用层上的配置
    'application' => [
        // 默认应用层的命名空间,希望使用默认的
        'default_namespace' => 'app',
        // 默认模块名
        'default_module' => 'home',
        // 默认控制器名
        'default_controller' => 'Index',
        // 默认操作名
        'default_action'  => 'index',
    ],

    // view视图文件指定的替换 变量 会在 View类构造函数里自动将根目录 添加到固定的 目录前
    'view_replace_var' => [
        // url 地址统一替换 http://cdn.xxxx.com
        '__URL__'    => '',
        // 根目录,指的是 index.php 同级的目录
        // 当index.php在public目录下 => './' 或 '/' ,要是放在与 public 目录同级 => './public/' 或 '/public/'
        '__ROOT__' => './',
        // 静态目录 = 根目录 + static
        '__STATIC__' => 'static',
        // js目录 = 根目录 + 'static/js'
        '__JS__' => 'static/js',
        // css目录 = 根目录 + 'static/css'
        '__CSS__' => 'static/css',
        // img目录 = 根目录 + 'static/img'
        '__IMG__' => 'static/img',
    ],
    // 数据库上的配置
    'database' => [
        // 数据库类型
        'type'  => 'mysql',
        // 服务器地址
        'host' => '127.0.0.1',
        // 数据库名
        'dbname' => 'qdm18279868_db',
        // 用户名
        'username' => 'root',
        // 密码
        'password' => 'root',
        // 端口
        'port'  => '3306',
        // 数据库编码默认采用utf8
        'charset' => 'utf8',
    ]
];