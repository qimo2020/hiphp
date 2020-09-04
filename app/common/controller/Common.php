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
namespace app\common\controller;
use app\BaseController;
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
        if(!defined('APP_HAS_INIT')){

            if (defined('INSTALL_ENTRANCE')){
                return;
            }
            $viewDepr = config('view.view_depr');
            // 当前模块名称
            $moduleName = strtolower(app('http')->getName());
            if (!$moduleName) {
                header('Location: ' . request()->domain());
                exit();
            }
            $params = Request::param();
            $isMobile = Request::isMobile();
            $appName = isset($params['_p']) && $params['_p'] && array_key_exists($params['_p'], cache('plugins')) ? $params['_p'] : $moduleName;
            $mobile_site_status = config($appName.'.mobile_site_status') ?? config('base.mobile_site_status');
            $mobile_domain = config($appName.'.mobile_domain') ?? config('base.mobile_domain');
            $mobile_response = config($appName.'.mobile_response') ?? config('base.mobile_response');
            $isMobileDir = $mobile_site_status && $isMobile === true && !$mobile_response ? 'mobile' . $viewDepr : '';
            // 插件/模块主题,静态资源目录解析[后台/前端]
            $appTheme = 'default';
            $viewReplaceStr = [
                // 站点根目录
                '__ROOT_DIR__' => ROOT_DIR,
                // 静态资源目录
                '__PUBLIC_STATIC__' => ROOT_DIR . 'static',
                // 扩展静态态资源目录
                '__PUBLIC_PACK__' => ROOT_DIR . 'pack',
            ];
            //插件/模块 静态资源模版变量加载
            config(['tpl_replace_string' => $viewReplaceStr], 'view');

            if (isset($params['_p'])) {
                $plugins = PluginModel::getPlugins();
                foreach ($plugins as $v) {
                    if ($appName == $v['name'] && 2 == $v['status']) {
                        $appTheme = $mobile_site_status && $isMobile === true ? ($v['mobile_theme'] ?: 'default') : ($v['theme'] ?: 'default');
                        break;
                    }
                }
                $viewReplaceStr['__PLUGIN_STATIC__'] = ROOT_DIR . 'static' . $viewDepr . 'p_' . $appName;
                $viewReplaceStr['__PLUGIN_STATIC_THEME__'] = $viewReplaceStr['__PLUGIN_STATIC__'] . $viewDepr . $isMobileDir . $appTheme;
            } else {
                if (in_array($appName, ['system']) === false) {
                    if (empty($appName)) {
                        $appName = config('app.default_app');
                    }
                    $moduleInfo = ModuleModel::where(['name' => $appName, 'status' => 2])->find();
                    if (!$moduleInfo) {
                        exit($appName . ' 模块可能未启用或者未安装！');
                    }
                    //模块的默认主题
                    $appTheme = $mobile_site_status && $isMobile === true ? ($moduleInfo['mobile_theme'] ?: 'default') : ($moduleInfo['theme'] ?: 'default');
                }
                // 模块静态资源目录[后台/前端]
                $viewReplaceStr['__MODULE_STATIC__'] = ROOT_DIR . 'static' . $viewDepr . 'm_' . $appName;
                $viewReplaceStr['__MODULE_STATIC_THEME__'] = $viewReplaceStr['__MODULE_STATIC__'] . $viewDepr . $isMobileDir . $appTheme;
            }
            //插件/模块 静态资源模版变量重载
            config(['tpl_replace_string' => $viewReplaceStr], 'view');

            if (defined('ADMIN_ENTRANCE')) {
                //后台主题目录调整
                if ('system' == $appName || isset($params['_p'])) {
                    $modules = ModuleModel::getModules();
                    foreach ($modules as $v) {
                        if ('system' == $v['name']) {
                            $appTheme = $v['theme'] ?: 'default';
                            break;
                        }
                    }
                    $themePath = base_path() . 'system/view/' . $appTheme;
                    config(['view_path' => $themePath . '/'], 'view');
                }
                if ('index' == $appName) {
                    header('Location: ' . url('system/entry/index'));
                    exit();
                }
            } else {
                if (config('base.site_status') != 1) {
                    exit('站点已关闭！');
                }
                // 前端主题路径[手机/PC]
                $domain = Request::domain();
                $pcDomain = $this->request->scheme() . '://' . config('base.site_domain');
                $themePath = base_path() . $appName . '/theme/';
                $domainScheme = $this->request->scheme() . '://';
                if ($mobile_site_status) {
                    if ($isMobile === true) {
                        if ($mobile_domain && $domainScheme . $mobile_domain != $domain) {
                            if ((isset($themePath) && is_dir($themePath . 'mobile/')) || (isset($appName) && is_dir(root_path() . 'plugins/' . $appName . '/theme/' . 'mobile/'))) {
                                header('Location: ' . $domainScheme . $mobile_domain . $this->request->url());
                                exit();
                            }
                        }
                        if (!$mobile_response) {
                            $themePath .= 'mobile/';
                        }

                    } else if (config('base.site_domain') && $isMobile === false && $mobile_domain && $domainScheme . $mobile_domain == $domain) {
                        header('Location: ' . $pcDomain . $this->request->url());
                        return;
                    }
                }
                //调整插件/模块主题目录地址
                if (in_array($appName, ['index']) === false) { //个别应用无需修改主题路径设置
                    config(['view_path' => $themePath . $appTheme . '/'], 'view');
                }
            }
            //修正插件重复初始化问题
            if(isset($params['_p'])){
                define('APP_HAS_INIT', true);
            }

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
        if (array_key_exists('builder', (array)cache('plugins')) && strpos($template, 'build') !== false) {
            $tpl = explode('/', $template);
            $tplPath = root_path() . "plugins/builder/view/block/{$tpl[1]}.";
        } else {
            if(defined('ADMIN_ENTRANCE') && ADMIN_ENTRANCE == 'admin'){
                $tplPath = root_path() . "plugins/{$plugin}/view/{$template}.";
            }else{
                $pluginTheme = 'default';
                $plugins = PluginModel::getPlugins();
                $mobile_site_status = config($plugin.'.mobile_site_status') ?? config('base.mobile_site_status');
                $mobile_response = config($plugin.'.mobile_response') ?? config('base.mobile_response');
                foreach ($plugins as $v){
                    if($plugin == $v['name'] && 2 == $v['status']){
                        $pluginTheme = $mobile_site_status && Request::isMobile() === true ? ($v['mobile_theme'] ?: 'default') : ($v['theme'] ?: 'default');
                        break;
                    }
                }
                $isMobileDir = !$mobile_response && $mobile_site_status && Request::isMobile() === true ? 'mobile/' : '';
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
    protected function response(int $type = 1, $msg = '', string $url = '', $data = '', int $wait = 3, array $header = [])
    {
        if (is_null($url) && isset($_SERVER["HTTP_REFERER"])) {
            $url = $_SERVER["HTTP_REFERER"];
        } else if ($url) {
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
        switch (strtolower($type)) {
            case 'html':
                $response = Response::create(app()->config->get('app.dispatch_success_tmpl'), 'view', 200)->header($header)->assign($result);
                break;
            case 'json':
                $response = Response::create($result, $type, 200)->header($header);
                break;
        }
        return $response;
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
