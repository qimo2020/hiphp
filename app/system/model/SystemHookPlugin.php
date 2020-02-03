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

namespace app\system\model;

use app\system\model\SystemHook as HookModel;
use think\Model;

/**
 * 插件模型
 * @package app\system\model
 */
class SystemHookPlugin extends Model
{

    /**
     * 索引入库
     * @param array $hooks 钩子
     * @param string $plugins 插件名称
     * @return bool
     * @author 祈陌 <3411869134@qq.com>
     */
    public static function storage($hooks = [], $plugins = '')
    {
        if (!empty($hooks) && is_array($hooks)) {
            $hookModel = new HookModel;
            // 添加钩子
            foreach ($hooks as $k => $v) {
                if (is_numeric($k)) {
                    $k = $v;
                }
                if (!$hookModel->storage(['name' => $k, 'source' => 'plugin.' . $plugins, 'intro' => $v])) {
                    return false;
                }
            }

            $data = [];
            foreach ($hooks as $k => $v) {
                if (is_numeric($k)) {
                    $k = $v;
                }
                // 清除重复数据
                if (self::where(['hook' => $k, 'plugins' => $plugins])->find()) {
                    continue;
                }
                $data[] = [
                    'hook' => $k,
                    'plugins' => $plugins,
                    'create_time' => request()->time(),
                    'update_time' => request()->time(),
                ];
            }

            if (empty($data)) {
                return true;
            }

            return self::insertAll($data);
        }
        return false;
    }

    /**
     * 删除插件钩子索引
     * @param string $plugins 插件名称
     * @return bool
     * @author 祈陌 <3411869134@qq.com>
     */
    public static function del($plugins = '')
    {
        if (!empty($plugins)) {
            // 删除插件钩子
            if (!HookModel::delHook('plugin.' . $plugins)) {
                return false;
            }
            // 删除索引
            if (self::where('plugins', $plugins)->delete() === false) {
                return false;
            }
        }
        return true;
    }

}
