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
 * 主题模型
 * @package app\system\model
 */
class SystemTheme extends Model
{
    public static function getThemes(){
        $infos = cache('theme_infos');
        if(!$infos){
            if($res = Db::name('system_theme')->select()->toArray()){
                Cache::tag('theme_tag')->set('theme_infos', $res);
                return $res;
            }
            return false;
        }
        return $infos;
    }

}
