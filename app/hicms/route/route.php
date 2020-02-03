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
    Route::rule('api/v:version/:controller/:action', 'api/v:version.:controller/:action');
    Route::rule('api/:controller/:action', 'api/:controller/:action');
}




