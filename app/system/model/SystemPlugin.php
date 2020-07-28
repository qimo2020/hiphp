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

namespace app\system\model;

use think\facade\Env;
use think\facade\Cache;
use think\Model;

/**
 * 插件模型
 * @package app\system\model
 */
class SystemPlugin extends Model
{
    public $error;

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    // 写入时,转JSON
    public function setConfigAttr($value)
    {
        if (empty($value)) return '';
        return json_encode($value, 1);
    }

    /* 获取所有插件信息
     * @return array
     * @author 祈陌 <3411869134@qq.com>
     */
    public static function getPlugins(){
        $pluginInfos = cache('plugin_infos');
        if(!$pluginInfos){
            if($res = self::select()->toArray()){
                Cache::tag('plugin_tag')->set('plugin_infos', $res);
                return $res;
            }
            return false;
        }
        return $pluginInfos;
    }

    /* 获取单个插件信息
     * @return array
     * @author 祈陌 <3411869134@qq.com>
     */
    public static function getPlugin($name){
        $items = self::getPlugins();
        foreach ($items as $v){
            if($name == $v['name']){
                return $v;
            }
        }
        return false;
    }


}
