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
namespace app\system\home;
use app\common\controller\Common;
use app\system\model\SystemPlugin;
class Plugin extends Common
{
    public function __call($method, $args)
    {
        /**
         * 注意: 禁止[模块名/插件名/模块的控制器名/模块的操作方法/pathinfo访问模式中到第一个参数] 命名为 plugin
         * 支持以下两种URL模式
         * URL模式1 [/plugin/插件名(/api)/控制器/方法/参数1/参数1值/参数2值]
         * URL模式2 [/plugin.php?s=插件名(/api)/控制器/方法/参数1值/参数2值] 推荐
         */
        $params = $this->request->param();
        if (!isset($params['_p'])) {
            return $this->response(0, '缺少参数[_p]');
        }
        $plugin = $params['_p'];
        $controller = isset($params['_c']) && !empty($params['_c']) ? ucfirst($params['_c']) : 'Index';
        $action = isset($params['_a']) && !empty($params['_a']) ? $params['_a'] : 'Index';
        $_GET['_p'] = $plugin;
        $_GET['_c'] = $controller;
        $_GET['_a'] = $action;
        $params = $this->request->except(['_p', '_c', '_a'], 'param');
        if (empty($plugin)) {
            return $this->response(0, '插件参数传递错误！');
        }
        if (!SystemPlugin::where(['name' => $plugin, 'status' => 2])->find() ) {
            return $this->response(0, "插件可能不存在或者未安装！");
        }
        $controllerLayer = isset($params['api']) && !empty($params['api']) && 'api' == $params['api'] ? 'api' : 'home';

        if (!pluginActionExist($plugin.'/'.$controller.'/'.$action, $controllerLayer)) {
            return $this->response(0, "插件方法不存在[".$plugin.'/'.$controller.'/'.$action."]！");
        }

        return pluginRun($plugin.'/'.$controller.'/'.$action, $params, $controllerLayer);
    }

}
