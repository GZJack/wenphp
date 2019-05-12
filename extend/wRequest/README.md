# 请求类说明
**myRequest是我自己定义的静态类,需要实现以下接口及方法**


## Error基类
> Error类,是提供处理返回结果对象的方法,是一个基础类,是被用来继承的
> 1. setUseScene('user.reg') 设置返回处理环境,在控制器里的方法中一开始就进行调用,errMsg 错误场景如:user.reg:fail
> 2. getUseScene() 继承类的 errMsgMerge() 重写方法中,使用
> 3. getErrMsg() 同上
> 4. setErrMsg() 在对应的基类上,一开始就可以设置错误提示 如: self::checkGetParam($get,$keys)
> 5. setErrCode() 设置错误码,同上 如: self::setErrCode(20001)
> 6. errMsgMerge() 这个是合成错误语句,在子类上需要进行重写,每个环境拼接都不一样,所以需要重写
> 7. getError() 获得错误码,就是当子类校验的结果返回来了false的时候,通过这个方法获得错误信息,如 Request::getError()
> 8. setError() 设置即返回错误, 如: Request::setError(20002,'缺少 type 参数')
> 9. setFailReturn() 设置失败返回结果 如: Request::setFailReturn(20002,'缺少 type 参数')
> 10. setOkReturn() 设置成功即返回结果 如: Request::setOkReturn('注册成功') 成功带原因的
> 11. getOkReturn() 获得一个成功的结果 如: Request::getOkReturn()

### 暴露的接口
|方法|说明|
|---|:---:|
|setUseScene()|在控制器方法上设置返回场景|
|getError()|在控制器方法上获得错误结果|
|setError()|在控制器方法上设置错误结果即返回|
|setFailReturn()|在控制器方法上设置失败结果即返回|
|setOkReturn()|在控制器方法上设置成功结果即返回,带成功原因|
|getOkReturn()|在控制器方法上获得成功结果|


## Request类模块
> Request类且继承Error类,是拦截及判断$_GET,$_POST,所传进来的参数是否合法
> 1. 提供了 checkGetParam($_GET,['from','type']),保证必须$_GET提交?from=webapp.reg&type=phone.reg
> 2. 提供了 checkPostParam($_POST,['account','password'])

### 暴露的接口
|方法|说明|
|---|:---:|
|setUseScene()|静态方法,设置errMsg的错误场景|
|checkGetParam()|静态方法,查询get里有没有缺少必要参数|
|checkPostParam()|静态方法,查询post里有没有缺少必要参数|


## Scene类模块
> Scene类且继承Error类,是对场景的验证的,注册,登录等都需要验证场景渠道
> 1. checkRegScene() 验证注册渠道场景, 如: Scene::checkRegScene($regFrom) === false

