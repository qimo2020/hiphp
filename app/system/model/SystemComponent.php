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

use think\Model;
use think\facade\Cache;
use think\facade\Db;
/**
 * 组件模型
 * @package app\system\model
 */
class SystemComponent extends Model
{
    public static function getComponents(){
        $infos = cache('component_infos');
        if(!$infos){
            if($res = Db::name('system_component')->select()->toArray()){
                Cache::tag('component_tag')->set('component_infos', $res);
                return $res;
            }
            return false;
        }
        return $infos;
    }

}
