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
 * 模块模型
 * @package app\system\model
 */
class SystemModule extends Model
{

    public static function getDefaultModule(){
        $moduleInfos = self::getModules();
        foreach ($moduleInfos as $value){
            if($value['default'] == 1 && $value['status'] == 2){
                return $value;
            }
        }
        return false;
    }
    /* 获取模块信息[应用初始化标签位中无法使用model查询,故使用Db查询解决,待官方完善框架后可修改]
     * @return array
     * @author 祈陌 <3411869134@qq.com>
     */
    public static function getModules(){
        $moduleInfos = cache('module_infos');
        if(!$moduleInfos){
            if($res = Db::name('system_module')->select()->toArray()){
                Cache::tag('module_tag')->set('module_infos', $res);
                return $res;
            }
            return false;
        }
        return $moduleInfos;
    }

}
