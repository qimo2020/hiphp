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
namespace app\common\controller;

use app\BaseController;
use app\system\model\SystemConfig as ConfigModel;
use app\system\model\SystemModule as ModuleModel;
use app\system\model\SystemPlugin as PluginModel;
use think\facade\View;
use think\Container;
use think\Response;
use think\exception\HttpResponseException;
use think\facade\Request;
/**
 * 框架公共控制器
 * @package app\common\controller
 */
class Common extends BaseController
{
    protected function initialize()
    {
        if (defined('INSTALL_ENTRANCE')){
            return;
        };
        $viewDepr = config('view.view_depr');
        // 当前模块名称
        $moduleName = strtolower(app('http')->getName());
        //若模块不存在于app目录下，则跳回首页
        if (!$moduleName) {
            header('Location: ' . request()->domain());
            exit();
        }
        // 载入系统配置
        config(ConfigModel::getConfigs());
        // 判断模块是否存在且已安装
        $moduleTheme = 'default';
        if (in_array($moduleName, ['index', 'system']) === false) {
            if (empty($moduleName)) {
                $moduleName = config('app.default_app');
            }
            $moduleInfo = ModuleModel::where(['name' => $moduleName, 'status' => 2])->find();
            if (!$moduleInfo) {
                exit($moduleName . ' 模块可能未启用或者未安装！');
            }
            // 设置模块的默认主题
            $moduleTheme = config('base.mobile_site_status') && Request::isMobile() === true ? ($moduleInfo['mobile_theme'] ?: 'default') : ($moduleInfo['theme'] ?: 'default');
        }
        $viewReplaceStr = [
            // 站点根目录
            '__ROOT_DIR__' => ROOT_DIR,
            // 公共静态资源目录
            '__PUBLIC_STATIC__' => ROOT_DIR . 'static',
        ];
        $isMobileDir = config('base.mobile_site_status') && Request::isMobile() === true ? 'mobile' . $viewDepr : '';
        // 模块静态资源目录[后台/前端]
        $viewReplaceStr['__MODULE_STATIC__'] = ROOT_DIR . 'static' . $viewDepr . 'm_'.$moduleName;
        $viewReplaceStr['__MODULE_STATIC_THEME__'] = $viewReplaceStr['__MODULE_STATIC__'] . $viewDepr . $isMobileDir . $moduleTheme;
        if ($pluginName = Request::param('_p')) {
            // 插件静态资源目录[后台/前端]
            $plugins = PluginModel::getPlugins();
            $pluginTheme = 'default';
            foreach ($plugins as $v){
                if($pluginName == $v['name'] && 2 == $v['status']){
                    $pluginTheme = config('base.mobile_site_status') && Request::isMobile() === true ? ($v['mobile_theme'] ?: 'default') : ($v['theme'] ?: 'default');
                    break;
                }
            }
            $viewReplaceStr['__PLUGIN_STATIC__'] = ROOT_DIR .  'static' . $viewDepr . 'p_' . $pluginName;
            $viewReplaceStr['__PLUGIN_STATIC_THEME__'] = $viewReplaceStr['__PLUGIN_STATIC__'] . $viewDepr . $isMobileDir . $pluginTheme;
        }
        config(['tpl_replace_string' => $viewReplaceStr], 'view');
        if (defined('ADMIN_ENTRANCE') && ADMIN_ENTRANCE == 'admin') {
            if ('index' == $moduleName) {
                header('Location: ' . url(moduleNameMap('system') . '/entry/index'));
                exit();
            }
        } else {
            if (config('base.site_status') != 1) {
                exit('站点已关闭！');
            }
            // 前端主题路径[手机/PC]
            $domain = Request::domain();
            $mobileDomain = $this->request->scheme() . '://' . config('base.mobile_domain');
            $pcDomain = $this->request->scheme() . '://' . config('base.site_domain');
            $themePath = base_path() . $moduleName . '/theme/';
            if(config('base.mobile_site_status')){
                if (Request::isMobile() === true) {
                    if (config('base.mobile_domain') && $mobileDomain != $domain) {
                        if((isset($themePath) && is_dir($themePath . 'mobile/')) || (isset($pluginName) && is_dir(root_path() . 'plugins/' . $pluginName . '/theme/' . 'mobile/'))){
                            header('Location: ' . $mobileDomain . $this->request->url());
                            exit();
                        }
                    }
                    $themePath .= 'mobile/';
                }else if(config('base.site_domain') && Request::isMobile() === false && config('base.mobile_domain') && $mobileDomain == $domain){
                    header('Location: ' . $pcDomain . $this->request->url());
                    exit();
                }
            }
            config(['view_path' => $themePath . $moduleTheme . '/'], 'view');
        }

    }

    /**
     * 加载模板输出
     * @access protected
     * @param string $template 模板文件名
     * @param array $vars 模板输出变量
     * @return mixed
     * @author 祈陌 <3411869134@qq.com>
     */
    final protected function view($template = '', $vars = [])
    {
        if (defined('IS_PLUGIN')) {
            return self::pluginFetch($template , $vars);
        }
        if (cache('plugins') && array_key_exists('builder', cache('plugins')) && strpos($template, 'build') !== false) {
            $tpl = explode('/', $template);
            $tplPath = root_path() . "plugins/builder/view/block/{$tpl[1]}.";
            $template = strtolower($tplPath . config('view.view_suffix'));
        }

        return View::fetch($template, $vars);
    }

    /**
     * 渲染插件模板
     * @param string $template 模板文件名或者内容
     * @param array $vars 模板输出变量
     * @return string
     * @author 祈陌 <3411869134@qq.com>
     */
    final protected function pluginFetch($template = '', $vars = [])
    {
        $plugin = $_GET['_p'];
        $controller = $_GET['_c'];
        $action = $_GET['_a'];
        if (!$template) {
            $template = $controller . '/' . parse_name($action);
        } elseif (strpos($template, '/') == false) {
            $template = $controller . '/' . $template;
        }
        if (array_key_exists('builder', cache('plugins')) && strpos($template, 'build') !== false) {
            $tpl = explode('/', $template);
            $tplPath = root_path() . "plugins/builder/view/block/{$tpl[1]}.";
        } else {
            if(defined('ADMIN_ENTRANCE') && ADMIN_ENTRANCE == 'admin'){
                $tplPath = root_path() . "plugins/{$plugin}/view/{$template}.";
            }else{
                $pluginTheme = 'default';
                $plugins = PluginModel::getPlugins();
                foreach ($plugins as $v){
                    if($plugin == $v['name'] && 2 == $v['status']){
                        $pluginTheme = config('base.mobile_site_status') && Request::isMobile() === true ? ($v['mobile_theme'] ?: 'default') : ($v['theme'] ?: 'default');
                        break;
                    }
                }
                $isMobileDir = config('base.mobile_site_status') && Request::isMobile() === true ? 'mobile/' : '';
                $tplPath = root_path() . "plugins/{$plugin}/theme/{$isMobileDir}{$pluginTheme}/{$template}.";
            }
        }
        $template = strtolower($tplPath . $this->app->config->get('view.view_suffix'));
        return View::fetch($template, $vars);
    }

    /**
     * 模板变量赋值
     * @access protected
     * @param  mixed $name 要显示的模板变量
     * @param  mixed $value 变量的值
     * @return $this
     * @author 祈陌 <3411869134@qq.com>
     */
    final protected function assign($name, $value = '')
    {
        View::assign($name, $value);
        return $this;
    }

    /**
     * 获取模板引擎
     * @access public
     * @param string $type 模板引擎类型
     * @return \think\View
     */
    final protected function engine(string $type = null)
    {
        return View::engine($type);
    }

    /**
     * 操作成功跳转[兼容html/json]
     * @access protected
     * @param string $type 返回类型
     * @param mixed $msg 提示信息
     * @param string $url 跳转的URL地址
     * @param mixed $data 返回的数据
     * @param integer $wait 跳转等待时间
     * @param array $header 发送的Header信息
     * @return void
     */
    protected function response($type = 1, $msg = '', string $url = null, $data = '', int $wait = 3, array $header = [])
    {
        if (is_null($url) && isset($_SERVER["HTTP_REFERER"])) {
            $url = $_SERVER["HTTP_REFERER"];
        } elseif ($url) {
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : $this->app->route->buildUrl($url);
        }
        $result = [
            'code' => $type ?: 0,
            'msg' => $msg,
            'data' => $data,
            'url' => $url,
            'wait' => $wait,
        ];
        $type = $this->getResponseType();
        if ('html' == strtolower($type)) {
            $type = 'view';
        }
        switch ($type) {
            case 'view':
                $response = Response::create(app()->config->get('app.dispatch_success_tmpl'), $type)->header($header)->assign($result);
                break;
            case 'json':
                $response = Response::create($result, $type, 200)->header($header);
                break;
        }
        throw new HttpResponseException($response);
    }

    /**
     * 获取当前的response 输出类型
     * @access protected
     * @return string
     */
    protected function getResponseType()
    {
        if (!$this->app) {
            $this->app = Container::get('app');
        }

        $isAjax = $this->app->request->isAjax();
        return $isAjax ? 'json' : 'html';
    }

}
