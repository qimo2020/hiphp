<?php
// +----------------------------------------------------------------------
// | HiPHP框架[基于ThinkPHP6.0开发]
// +----------------------------------------------------------------------
// | Copyright (c) http://www.hiphp.net
// +----------------------------------------------------------------------
// | HiPHP承诺基础框架永久免费开源，您可用于学习和商用，但必须保留软件版权信息。
// +----------------------------------------------------------------------
// | Author: 祈陌 <3411869134@qq.com>，开发者QQ群：829699898
// +----------------------------------------------------------------------
use think\facade\Request;
use think\facade\Route;

$pathInfo = Request::instance()->pathinfo();
if('api' == explode('/', $pathInfo)[0]){
    if(in_array('cloud', config('app.domain_bind'))){
        //先定义中间件, 修改前端控制器目录为api
        Route::rule('api/v:version/:controller/:action', 'api/v:version.:controller/:action');
        Route::rule('api/:controller/:action', ':controller/:action');
    }else{
        Route::rule('api/v:version/:controller/:action', 'api/v:version.:controller/:action');
        Route::rule('api/:controller/:action', 'api/:controller/:action');
    }
}
Route::rule('login', 'login/:action');
Route::rule('regist', 'regist/:action');
Route::rule('logout', 'logout/:action');





