<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/4/18
 * Time: 22:02
 */

namespace wen;

// 继承缓存中的文件功能,具有操作缓存文件的功能
use wen\cache\driver\ViewFile;

/***
 * 渲染视图的类
 * Class View
 * @package wen
 */
class View extends ViewFile
{
    private static $instance = null;

    protected $data = [];

    /***
     * @var array 需要替换的变量
     */
    private static $replaceVars = [];

    /***
     * @var array 需要替换的嵌入文件
     */
    private static $replaceIncludes =[];

    /***
     * @var array 承载着config.php里view_replace_var 视图静态变量替换
     */
    private static $viewReplaceVar = [];

    /***
     * @var bool 是否公共视图(如果是公共视图,就会将数据一同缓存起来,并设置过期时间)
     */
    protected $isPublicView = false;

    /***
     * View constructor.
     */
    private function __construct()
    {
        // 读取配置参数里 视图替换的 变量
        self::$viewReplaceVar = Config::get('view_replace_var');
        // 得到根目录
        $root = self::$viewReplaceVar['__ROOT__'];
        // 遍历
        foreach (self::$viewReplaceVar as $key => $value){
            // 不等于根目录的项
            if($key !== '__ROOT__' && $key !== '__URL__'){
                self::$viewReplaceVar[$key] = $root.$value;
            }
        }
    }

    /***
     * 获得当前实例
     * @return static
     */
    public static function getInstance()
    {
        if(is_null(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    /***
     * 记录需要渲染的值
     * @param $name string|array
     * @param null|mixed $value
     */
    public function assign($name, $value='')
    {
        // 如果name为字符串,并且value有值的情况下,进行存值
        if(is_string($name)){
            $this -> data[$name] = $value;
        }
        // 如果第一个是数组的话,就直接合并data
        if(is_array($name)){
            $this -> data = array_merge($this -> data,$name);
        }
    }

    /***
     * 获取模板,真实的路径
     * @param $template string
     * @return string
     */
    private static function getTemplate($template)
    {
        // 所支持的模板后缀名
        $fileSuffix = ['.php','.html','.htm','.tpl'];
        // 通过模版名获得文件名
        $fileName = !empty($template) ? $template : App::$currentAction;
        // 是否存在后缀,如果没有后缀会自动添加 .html
        $hasSuffix = false;
        // 遍历
        foreach ($fileSuffix as $suffix){
            // 不存在才进来找,如果存在了,就只等到循环完成
            if($hasSuffix===false){
                $hasSuffix = substr($fileName,-strlen($suffix)) === $suffix ? true : false;
            }
        }
        // 如果还是false,我们就默认添加上.html
        if($hasSuffix===false){
            $fileName .= '.html';
        }
        // 格式 模块/视图/控制器/方法.html
        $template = APP_PATH.App::$currentModule.DS.'view'.DS.strtolower(App::$currentController).DS.$fileName;
        // 判断文件是否存在
        if(!file_exists($template)){
            // 提示一个debug
            Response::debug("[ VIEW ] not found file : $template",404,'View.getTemplate');
            exit;
        }
        return $template;
    }

    /***
     * 去除换行
     * @param $content string
     * @return mixed|string
     */
    private static function clearWrap(&$content)
    {
        return $content = str_replace(array("\r\n", "\r", "\n"), " ", $content);
    }


    /***
     * 获得模板内容
     * @param $filePath string
     * @return string
     */
    private static function getContent($filePath)
    {
        // 文件存在才去读取
        if(file_exists($filePath)){
            return file_get_contents($filePath);
        }
        return '';
    }

    /***
     * 给双引号添加一个反斜杠
     * @param $content string
     * @return mixed|string
     */
    private static function replaceMark(&$content)
    {
        return $content = str_replace('"', '\\"', $content);
    }

    /***
     * 替换变量 {{$ (中间不能出现空格)
     * @param $content string
     * @return mixed|string
     */
    private static function replaceVar(&$content)
    {
        // 遍历
        foreach (self::$replaceVars as $key => $value){
            $_replace = '<?php echo $'.$value.'; ?>';
            $content = str_replace($key,$_replace,$content);
        }
        return $content;
    }

    /***
     * 查找所有变量 {$ 不能有空格
     * @param $content string
     * @return null|string|string[]
     */
    private static function findVar($content)
    {
        // 把所有的变量都一一找出来,并存起来 {{$  ^}}
        return preg_replace_callback('/\{\$([^}]+)\}/',function ($m){
            self::$replaceVars[$m[0]] = $m[1];
            return '';
        },$content);
    }

    /***
     * 替换前后的双引号和单引号
     * @param $str string
     * @return string
     */
    private static function clearMark($str)
    {
        // 获取左边第一个字符串,如果是 “ ’ 双引号或者单引号,就进行替换
        $leftStr = substr($str,0,1);
        $_value =$str;
        // 是否有双引号
        if($leftStr==='"'){
            $_value = trim($_value,'"');
        }else if ($leftStr === "'"){
            $_value = trim($_value,"'");
        }
        return $_value;
    }

    /***
     * 将匹配的参数 生成数组形式 key => value
     * @param $value string
     * @return array
     */
    private static function setToArray($value)
    {
        // 返回的结果
        $result = [];
        // 去掉两边的空格
        $value = trim($value);
        // 以空格转换为数组
        $values = explode(' ',$value);
        foreach ($values as $item){
            $_item = [];
            if(strpos($item,'=') >= 1){
                // 以=将其转为数组
                $_items = explode('=',$item);
                // 第一项做key,第二项做值
                $_item[$_items[0]] = $_items[1];
            }else{
                // 比如mix 混入
                $_item[$item] = true;
            }
            // 最后一项一项地合并起来
            $result = array_merge($result,$_item);
        }
        return $result;
    }

    /***
     * 获得引入的文件真实地址,默认是同级模块下view 控制器 的文件
     * @param $filePath string
     * @return string
     */
    private static function getViewIncludeFilePath($filePath)
    {
        // 替换掉前后的单引号和双引号
        $filePath = self::clearMark($filePath);
        // 替换 / 符号
        $filePath = str_replace('/',DS,$filePath);
        // 目录目录
        $dirPath = App::$currentModule.DS.'view'.DS.strtolower(App::$currentController).DS;

        // 如果是开发模式下
        if (App::$isAppDebug){
            // 验证文件是否存在
            $_filePath = APP_PATH.$dirPath.$filePath;
            if (!file_exists($_filePath)){
                Response::debug("[ VIEW ] not found file: $_filePath",404,'View.getViewIncludeFilePath');
            }
        }
        // 只返回目录 + 文件名
        return $dirPath.$filePath;
    }

    /***
     * 替换包含模板文件
     * {include file="public/header"}
     * @param $content string
     * @return null|string|string[]
     */
    private static function replaceInclude(&$content)
    {
        foreach (self::$replaceIncludes as $key => $value){
            // 将所有的内容转为数组
            $arrayValue = self::setToArray($value);
            // 判断是否存在
            $filePath = isset($arrayValue['file']) ? $arrayValue['file'] :'';
            // 获得当前视图的目录+文件名
            $filePath = self::getViewIncludeFilePath($filePath);
            // 是否引入内容进行混合
            $isMix = isset($arrayValue['mix']) ? true : false;
            // 被替换的内容
            $_replace = '';
            // 进行混合
            if($isMix){
                // 使用混入,将代码
                $_replace = file_get_contents(APP_PATH.$filePath);
            }else{
                // 使用真实的字符串路径是无法访问的到的
                $_replace = '<?php include(APP_PATH.\''.$filePath.'\'); ?>';
            }
            // 替换的内容
            $content = str_replace($key,$_replace,$content);
        }
        return $content;
    }

    /***
     * 查找所有{include 不能出现空格
     * @param $content string
     * @return null|string|string[]
     */
    private static function findInclude($content)
    {
        // 把所有的include都一一找出来,并存起来
        return preg_replace_callback('/\{include([^}]+)\}/',function ($m){
            self::$replaceIncludes[$m[0]] = $m[1];
            return '';
        },$content);
    }


    /***
     * 替换所有静态路径
     * @param $content string
     * @return string|mixed
     */
    private static function replaceStaticPath(&$content)
    {
        // 循环替换所有设置静态资源路径
        foreach (self::$viewReplaceVar as $key => $value){
            $content = str_replace($key,$value,$content);
        }
        return $content;
    }

    /***
     * 先将模板文件编译成php文件进行缓存
     * 当无缓存,或者超时后,就会构建一个新的缓存文件出来
     * @param string $template
     * @param null|array $value
     * @param int $expire
     * @return mixed
     */
    public function view($template='',$value=null,$expire=0)
    {
        // 如果传入的值不为null,且为数组,则进行添加
        if (!is_null($value) && is_array($value)){
            $this -> assign($value);
        }
        // 获得真实的模板路径
        $template = self::getTemplate($template);
        // 定义一个用来接收内容的
        $content = '';
        // 开启内存缓存
        ob_start();
        ob_implicit_flush(0);
        // 如果是公共文件就不需要这个了
        if($this -> isPublicView === true){
            // 如果是公共的,我们就将数据也添加到content里进行一起保存
            self::addCacheView($content,$this -> data);
        }else{
            // 准备将数据转成变量的形式输出
            extract($this -> data,EXTR_OVERWRITE);
        }
        // 先去读取缓存文件
        if(App::$isAppDebug !==false || $this -> view_file_exists($template) === false){
            // 通过读取模板文件获得的
            $content .= self::getContent($template);
            // 将模板文件去掉换行
            self::clearWrap($content);
            // 替换所有静态资源的路径
            self::replaceStaticPath($content);
            // 替换双引号
            //$content = self::replaceMark($content);
            // 查找所有的include
            self::findInclude($content);
            // 替换所有的include
            self::replaceInclude($content);
            // 把所有变量都找出来 {{$
            self::findVar($content);
            // 替换所有的变量
            self::replaceVar($content);
            // 写入缓存视图文件
            $this -> view_file_write($template,$content,$expire);
            // 注销变量
            $content = null;
        }
        // 加载缓存上的php文件
        include $this -> getCacheKey($template);

        //echo ob_get_clean();
        // 将内存的东西全部返回去
        return ob_get_clean();
    }

    /***
     * 将所有的变量转换成<?php $xxx=123;?> 存在缓存文件里
     * @param $content string
     * @param $data array
     * @return string
     */
    private static function addCacheView(&$content,$data)
    {
        // 构建所有变量的字符串
        $varStr = '<?php ';
        foreach ($data as $key => $value){
            $varStr .= "$$key = ".var_export($value,true).';';
        }
        $varStr .= ' ?>';
        return $content = $varStr.$content;
    }

    /***
     * 设置缓存公共视图文件
     * @param string $template
     * @param null|array $value
     * @param int $expire
     * @return mixed
     */
    public function publicViewCache($template='',$value=null,$expire=300)
    {
        // 设置成公共文件
        $this -> isPublicView = true;
        // 获得真实的模板路径
        $filePath = self::getTemplate($template);
        // 判断文件是否存在
        if ($this -> view_file_exists($filePath) === false){
            return $this -> view($template,$value,$expire);
        }else{
            // 加载缓存上的php文件
            include $this -> getCacheKey($filePath);
            return null;
        }
    }
}