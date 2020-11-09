<?php declare(strict_types=1);
// +----------------------------------------------------------------------
// | HiPHP框架[基于ThinkPHP6.0开发]
// +----------------------------------------------------------------------
// | Copyright (c) http://www.hiphp.net
// +----------------------------------------------------------------------
// | HiPHP承诺基础框架永久免费开源，您可用于学习和商用，但必须保留软件版权信息。
// +----------------------------------------------------------------------
// | Author: 祈陌 <3411869134@qq.com>，开发者QQ群：829699898
// +----------------------------------------------------------------------
namespace app\listener;
use think\facade\Route;
use think\facade\Request;
use app\system\model\SystemModule as ModuleModel;
use app\system\model\SystemConfig as ConfigModel;

class InitEvent
{
    public function handle()
    {
        $pathInfo = pathInfoParse();
        //入口标识
        define('IN_SYSTEM', true);
        // 获取站点根目录
        $entry = Request::baseFile();
        $rootDir = preg_replace(['/index.php$/', '/plugin.php$/', '/' . config('hi.admin_path') . '$/'], ['', '', ''], $entry);
        define('ROOT_DIR', $rootDir);
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
        $moduleName = strtolower(app('http')->getName());
        config(['controller_layer'=>$controllerDir], 'route');
        //检查安装
        if(!is_file(base_path() . 'install/install.lock')) {
            define('INSTALL_ENTRANCE', true);
            if($pathInfo == '' || $pathInfo && 'install' != explode('/', (string)$pathInfo)[0]){
                header('Location: ' . $rootDir.'?s=install');
                exit();
            }
            return;
        }
        $configs = ConfigModel::getConfigs();
        //载入系统的配置信息
        config($configs);
        //插件入口
        if(defined('PLUGIN_ENTRANCE') && 'plugin' === PLUGIN_ENTRANCE || 'plugin' == explode('/', (string)$pathInfo)[0]) {
            app('http')->name('system')->setBind(true);
            return;
        }
        //默认模块
        $bind = Route::getBind();
        $request = \request();
        if(($request->domain() == $request->scheme() . '://' . config('base.site_domain')) && !defined('ADMIN_ENTRANCE') && !defined('PLUGIN_ENTRANCE') && !$bind){
            $pathInfo = array_filter(explode('/', (string)$pathInfo));
            if (empty($pathInfo) || !in_array($pathInfo[0], array_column(ModuleModel::getModules(), 'name'))) {
                if(ModuleModel::getDefaultModule()){
                    $moduleName = ModuleModel::getDefaultModule()['name'] ?: strtolower(config('app.default_app'));
                    app('http')->name($moduleName)->setBind(true);
                }
            }
        }

        //域名绑定应用
        $moduleBinds = $configs['system']['domain_binds'];
        if(is_array($moduleBinds) && !empty($moduleBinds)){
            $domainBinds = config('app.domain_bind');
            foreach($moduleBinds as $key=>$val){
                $domainBinds[$key] = $val;
            }
            config(['domain_bind'=>$domainBinds], 'app');
        }
        if(config('system.domain_cross')){
            config(['domain'=>config('system.domain_cross')], 'cookie');
        }
        //非开发模式下异常页面调整
        if(!defined('ADMIN_ENTRANCE')){
            $domain = str_replace(request()->scheme().'://', '', request()->domain());
            if(!$moduleBinds || !$appName = array_search($domain, $moduleBinds)){
                $appName = $moduleName ?? $pathInfo[0];
            }
            $exceptionTpl = base_path() . $appName . '/exception/' . 'http.tpl';
            if(!app()->isDebug()){
                if(('plugin' == $appName = explode('/', (string)$pathInfo)[0]) || defined('PLUGIN_ENTRANCE')){
                    $exceptionTpl = isset($appName) ? root_path() . 'plugins/' . $appName . '/exception/'  . 'http.tpl' : $exceptionTpl;
                }
                if(!file_exists($exceptionTpl)){
                    $exceptionTpl = base_path() . 'system/exception/http.tpl';
                }
                config(['exception_tmpl' => $exceptionTpl], 'app');
            }
        }

    }



}