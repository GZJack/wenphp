<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/3/31
 * Time: 11:42
 */

namespace wen;


class Controller
{
    /***
     * 挂载在本实例上的 请求类实例
     * @var null
     */
    protected $request = null;
    /***
     * 挂载在本实例上的 反应类实例
     * @var null
     */
    protected $response = null;

    /***
     * 构造函数,如果子类 控制器中继承了这个Controller类,这个构造方法是不能被覆盖和重写的,继承了我,就不要再设置构造函数了
     * Controller constructor.
     * @param Request $request
     * @param Response $response
     */
    final public function __construct(Request $request,Response $response)
    {
        // 挂载
        $this -> request = ($request instanceof Request) ? $request : Request::getInstance();
        $this -> response = ($response instanceof Response) ? $response : Response::getInstance();
    }


    /***
     * 控制器下的验证器函数,继承后就可以直接使用
     * @param array $data
     * @param array $validate
     * @return Validate
     */
    public function validate($data=[], $validate=[])
    {
        return Loader::validate($data,$validate);
    }

    /***
     * 继承了Controller类后,就可以实现对视图的渲染
     * @param string $template
     * @param null|array $value
     * @param int $expire
     * @return bool|string
     */
    protected function view($template='',$value=null,$expire=0)
    {
        // 拿到view实例
        $view = View::getInstance();
        // 执行view方法
        return $view -> view($template,$value,$expire);
    }

    /***
     * 公共文件,带数据缓存的文件
     * @param string $template
     * @param null|array $value
     * @param int $expire
     * @return mixed
     */
    protected function publicViewCache($template='',$value=null,$expire = 300)
    {
        // 拿到view实例
        $view = View::getInstance();
        // 执行view方法
        return $view -> publicViewCache($template,$value,$expire);
    }

    protected function assign($name, $value='')
    {
        // 得到这个view实例,将值传给了view实例进行处理
        $view = View::getInstance();
        // 让view实例的 assign 来处理这些变量
        $view -> assign($name, $value);
    }

}