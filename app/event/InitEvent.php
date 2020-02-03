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
namespace app\event;
use think\facade\Route;
use think\facade\Request;
use app\system\model\SystemModule as ModuleModel;

/*
 * 应用初始化事件
 */
class InitEvent
{
    public function handle()
    {
        $pathInfo = Request::instance()->pathinfo();
        //入口标识
        define('IN_SYSTEM', true);
        //版本信息
        $version = include_once(root_path() . 'version.php');
        config($version);

        if(!defined('PLUGIN_ENTRANCE')){
            $api = explode('/', $pathInfo)[1];
        }

        //控制器层目录和视图目录解析
        if(defined('ADMIN_ENTRANCE') && 'admin' === ADMIN_ENTRANCE){
            $controllerDir = 'admin';
        }else if(isset($api) && $api == 'api'){
            $controllerDir = 'api';
        }else{
            config(['view_dir_name'=>'theme'], 'view');
            $controllerDir = 'home';
        }
        config(['controller_layer'=>$controllerDir], 'route');

        //检查安装
        if(!is_file(base_path() . 'install/install.lock')) {
            define('INSTALL_ENTRANCE', true);
            if($pathInfo == '' || $pathInfo && 'install' != explode('/', $pathInfo)[0]){
                $entry = Request::baseFile();
                $rootDir = preg_replace(['/index.php$/', '/plugin.php$/', '/admin.php$/'], ['', '', ''], $entry);
                header('Location: ' . $rootDir.'?s=install');
                exit();
            }
            return;
        }

        //插件入口
        if(defined('PLUGIN_ENTRANCE') && 'plugin' === PLUGIN_ENTRANCE || 'plugin' == explode('/', $pathInfo)[0]) {
            app('http')->name('system')->setBind(true);
            return;
        }
        //默认模块
        $bind = Route::getBind();
        if(!defined('ADMIN_ENTRANCE') && !defined('PLUGIN_ENTRANCE') && !$bind){
            $pathInfo = array_filter(explode('/', $pathInfo));
            if (empty($pathInfo) || !in_array($pathInfo[0], array_column(ModuleModel::getModules(), 'name'))) {
                if(ModuleModel::getDefaultModule()){
                    app('http')->name(ModuleModel::getDefaultModule()['name'])->setBind(true);
                }
            }
        }

    }



}