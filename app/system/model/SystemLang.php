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

use think\facade\Cache;
use think\Model;
use think\facade\Db;
/**
 * 语言模型
 * @package app\system\model
 */
class SystemLang extends Model
{
    public $error;

    /* 获取应用默认语言包数据
     * @return array
     * @author 祈陌 <3411869134@qq.com>
     */
    public static function getDefaultLang($group){
        $defaultLangs = cache('lang_default_'.$group);
        if(!$defaultLangs){
            $defaultPack = Db::name('system_language')->where(['group'=>$group, 'default'=>1])->find();
            if($res = self::where(['group'=>$group, 'pack'=>$defaultPack['id']])->select()->toArray()){
                Cache::tag('lang_'.$group)->set('lang_default_'.$group, $res);
                return $res;
            }
            return false;
        }
        return $defaultLangs;
    }

    /* 获取应用语言数据
     * @return array
     * @author 祈陌 <3411869134@qq.com>
     */
    public static function getLangs($group){
        $langs = cache('lang_all_'.$group);
        if(!$langs){
            if($res = self::where(['group'=>$group])->select()){
                Cache::tag('lang_'.$group)->set('lang_all_'.$group, $res);
                return $res;
            }
            return false;
        }
        return json_decode($langs, 1);
    }

    /* 设置默认语言包
     * @return bool
     * @author 祈陌 <3411869134@qq.com>
     */
    public static function setDefaultLanguage($group, $pack){
        $res = Db::name('system_language')->where(['group'=>$group])->update(['default'=>0]);
        if( $res && $res = Db::name('system_language')->where(['group'=>$group, 'name'=>$pack])->update(['default'=>1]) ){
            cache('lang_default_'.$group, null);
            return true;
        }
        return false;
    }

    /* 导入语言包数据
     * @author 祈陌 <3411869134@qq.com>
     */
    public static function importLang($group, $pack, $data){
        $newData = [];
        $i = 0;
        foreach ($data as $key=>$v){
            foreach ($v as $vv) {
                foreach ($vv as $kk=>$vvv) {
                    $newData[$i]['group'] = $group;
                    $newData[$i]['pack'] = $pack;
                    $newData[$i]['name'] = $kk;
                    $newData[$i]['langvar'] = $vvv;
                    $i++;
                }
            }
        }
        $res = self::insertAll($newData);
        if(!$res) return false;
        return true;
    }


    /* 清除语言数据
     * @author 祈陌 <3411869134@qq.com>
     */
    public static function langClear($group){
        $res = Db::name('system_language')->where(['group'=>$group])->delete();
        if(!$res) return false;
        $res = self::where('group', $group)->delete();
        if(!$res) return false;
        return true;
    }

}
