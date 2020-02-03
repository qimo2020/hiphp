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
use app\system\model\SystemModule as moduleModel;
use app\system\model\SystemPlugin as PluginModel;
use think\facade\Cache;
use think\Model;

/**
 * 配置模型
 * @package app\system\model
 */
class SystemConfig extends Model
{
    public $error;
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    /**
     * 获取系统配置信息
     * @param  string $name 配置名(模块名或插件名)
     * @param  bool $update 是否更新缓存
     * @author 祈陌 <3411869134@qq.com>
     * @return mixed
     */
    public static function getConfigs($name = '', $update = false)
    {
        $result = Cache::get($name.'_config');
        if ($result == false || $update == true) {
            $configs = self::column('value,type,group', 'name');
            $result = [];
            foreach ($configs as $config) {
                $config['value'] = htmlspecialchars_decode($config['value']);
                switch ($config['type']) {
                    case 'array':
                    case 'checkbox':
                        $result[$config['group']][$config['name']] = parseAttr($config['value']);
                        break;
                    default:
                        $result[$config['group']][$config['name']] = $config['value'];
                        break;
                }
            }
            Cache::tag('hi_config')->set($name.'_config', $result);
        }
        return $name != '' ? $result[$name] : $result;
    }

    /**
     * 删除配置
     * @param int $module 应用类型; 1:模块; 0:插件
     * @author 祈陌 <3411869134@qq.com>
     * @return bool
     */
    public function del($group = '', $module=0) {
        if($group){
            $result = $module == 0 ? PluginModel::where('name', $group)->value('id') : ModuleModel::where('name', $group)->value('id');
            if (!$result) {
                $this->error = '应用不存在';
                return false;
            }
            self::where(['system'=>0, 'group'=>$group])->delete();
            return true;
        }
        $this->error = '参数传递错误';
        return false;
    }

}
