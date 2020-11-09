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
use think\facade\Event;
use think\facade\Cache;
use app\system\model\SystemHook as hookModel;
use app\system\model\SystemPlugin as pluginModel;
use app\system\model\SystemHookPlugin as hookPluginModel;
/*
 * 钩子事件
 * @package app\event
 */
class HookEvent
{
    public function handle()
    {
        if (defined('INSTALL_ENTRANCE')) return;
        $hookPlugins = cache('hook_plugins');
        $hooks = cache('hooks');
        $plugins = cache('plugins');
        if (!$hookPlugins) {
            $hooks = hookModel::where('status', 1)->column('status', 'name');
            $plugins = pluginModel::where('status', 2)->column('status', 'name');
            $hookPlugins = hookPluginModel::where('status', 1)->field('hook,plugins')->order('sort')->select()->toArray();
            Cache::tag('plugin_tag')->set('hook_plugins', $hookPlugins);
            Cache::tag('plugin_tag')->set('hooks', $hooks);
            Cache::tag('plugin_tag')->set('plugins', $plugins);
        }
        //批量事件订阅
        if ($hookPlugins) {
            foreach ($hookPlugins as $value) {
                if (isset($hooks[$value['hook']]) && isset($plugins[$value['plugins']])) {
                    Event::subscribe(getPluginClass($value['plugins']));
                }
            }
        }

    }



}