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
/*
 * 注意: 禁止[模块名/插件名/模块的控制器名/模块的操作方法/pathinfo访问模式中到第一个参数] 命名为 plugin
 * 支持以下两种URL模式
 * URL模式1 [/plugin/插件名(/api)/控制器/方法/参数1/参数1值/参数/参数2值]
 * URL模式2 [/plugin.php?s=插件名(/api)/控制器/方法/参数1/参数1值/参数/参数2值] 推荐
 */
$pathInfo = Request::instance()->pathinfo();
if(!defined('ADMIN_ENTRANCE') && 'plugin' == explode('/', $pathInfo)[0]){
    Route::rule('plugin/:_p/:api/:_c/:_a', 'plugin/index')->pattern(['api' => 'api']);
    Route::rule('plugin/:_p/:api/:_c', 'plugin/index')->pattern(['api' => 'api']);
    Route::rule('plugin/:_p/:api', 'plugin/index')->pattern(['api' => 'api']);
    Route::rule('plugin/:_p/:_c/:_a', 'plugin/index');
    Route::rule('plugin/:_p/:_c', 'plugin/index');
    Route::rule('plugin/:_p', 'plugin/index');
}
if(defined('PLUGIN_ENTRANCE') && 'plugin' === PLUGIN_ENTRANCE){
    Route::rule(':_p/:api/:_c/:_a', 'plugin/index')->pattern(['api' => 'api']);
    Route::rule(':_p/:api/:_c', 'plugin/index')->pattern(['api' => 'api']);
    Route::rule(':_p/:api', 'plugin/index')->pattern(['api' => 'api']);
    Route::rule(':_p/:_c/:_a', 'plugin/index');
    Route::rule(':_p/:_c', 'plugin/index');
    Route::rule(':_p', 'plugin/index');
}
