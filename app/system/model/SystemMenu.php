<?php
// +----------------------------------------------------------------------
// | HiPHP框架[基于ThinkPHP5.1开发]
// +----------------------------------------------------------------------
// | Copyright (c) http://www.hiphp.net
// +----------------------------------------------------------------------
// | HiPHP承诺基础框架永久免费开源，您可用于学习和商用，但必须保留软件版权信息。
// +----------------------------------------------------------------------
// | Author: 祈陌 <3411869134@qq.com>，开发者QQ群：829699898
// +----------------------------------------------------------------------

namespace app\System\model;

use think\Model;
use app\system\model\SystemLang as LangModel;
/**
 * 后台菜单模型
 * @package app\System\model
 */
class SystemMenu extends Model
{
    public $error;
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = false;

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    public function lang()
    {
        return $this->hasOne('SystemMenuLang', 'menu_id', 'id');
    }

    /**
     * 读取菜单信息
     * @return array
     * @author 祈陌 <3411869134@qq.com>
     */
    public static function getMenus()
    {
        $result = cache('menu_all');
        if (!$result) {
            $result = self::where(['status' => 1])->order(['sort', 'create_time'])->select()->toArray();
            cache('menu_all', $result, null, 'menus');
        }
        return $result;
    }

    /**
     * 读取树形菜单
     * @param int $limitLevel 限制返回层数，0为不限制
     * @param int $currentLevel 当前层数
     * @return array
     * @author 祈陌 <3411869134@qq.com>
     */
    public static function getMenuTrees($pid, $limitLevel = 2, $currentLevel = 0)
    {
        $result = [];
        $menus = self::getMenus();
        if ($menus) {
            $i = 0;
            foreach ($menus as $v) {
                if ($pid == $v['pid'] && $v['is_menu']) {
                    if ($limitLevel > 0 && $limitLevel == $currentLevel) {
                        return $result;
                    }
                    //若插件有配置项
                    if(0 == $v['system'] && 2 == $currentLevel && strpos($v['module'], 'plugin') !== false){
                        $pluginName = str_replace('plugin.','',$v['module']);
                        if ($i<1 && $res = LangModel::getDefaultLang($pluginName)) {
                            $result[$i] = ['title' => '语言管理', 'icon' => 'icon iconfont iconyuyan', 'url' => 'system/lang/index?type=1&group=' . $pluginName, 'target' => '_self'];
                            $i++;
                        }
                        $info = pluginInfo($pluginName);
                        if($i<2 && isset($info['name']) && !empty($info['config'])){
                            $result[$i] = ['title'=>'插件配置', 'url'=>'system/plugin/setting?plugin='.$pluginName, 'target'=>'_self'];
                            if(isset($info['config_icon']) && true === $info['config_icon']){
                                $result[$i]['icon'] = 'icon iconfont iconsetting';
                            }
                            $i++;
                        }
                    }
                    //若模块有配置项
                    if(0 == $v['system'] && 1 == $currentLevel && strpos($v['module'], 'plugin') === false){
                        if ($i<1 && $res = LangModel::getDefaultLang($v['module'])) {
                            $result[$i] = ['title' => '语言管理', 'icon' => 'icon iconfont iconyuyan', 'url' => 'system/lang/index?type=0&group=' . $v['module'], 'target' => '_self'];
                            $i++;
                        }
                        $info = moduleInfo($v['module']);
                        if($i<2 && isset($info['name']) && $v['module'] == $info['name'] && !empty($info['config'])){
                            $result[$i] = ['title'=>'模块配置', 'url'=>'system/module/setting?module='.$v['module'], 'target'=>'_self'];
                            if(isset($info['config_icon']) && true === $info['config_icon']){
                                $result[$i]['icon'] = 'icon iconfont iconsetting';
                            }
                            $i++;
                        }

                    }
                    $result[$i] = $v;
                    $child = self::getMenuTrees($v['id'], $limitLevel, ($currentLevel + 1));
                    if (!empty($child)) {
                        $result[$i]['child'] = $child;
                    }
                    $i++;
                }
            }
        }
        cache('menu_trees', $result, null, 'menus');
        return $result;
    }

    /**
     * 获取当前访问节点信息，支持扩展参数筛查
     * @param string $id 节点ID
     * @return array
     */
    public static function getCurrInfo($id = 0)
    {
        $map = [];
        if (empty($id)) {
            $module = app('http')->getName();
            $controller = request()->controller();
            $action = request()->action();
            $map[] = ['url', '=', $module . '/' . $controller . '/' . $action];
        } else {
            $map[] = ['id', '=', (int)$id];
        }
        $map[] = ['status', '=', 1];
        $rows = self::where($map)->column('id,title,url,param');
        if (!$rows) {
            return false;
        }
        sort($rows);
        $_param = request()->param();
        if (count($rows) > 1) {
            if (!$_param) {
                foreach ($rows as $k => $v) {
                    if ($v['param'] == '') {
                        return $rows[$k];
                    }
                }
            }
            foreach ($rows as $k => $v) {
                if ($v['param']) {
                    parse_str($v['param'], $param);
                    ksort($param);
                    $paramArr = [];
                    foreach ($param as $kk => $vv) {
                        if (isset($_param[$kk])) {
                            $paramArr[$kk] = $_param[$kk];
                        }
                    }
                    $where = [];
                    $where[] = ['param', '=', http_build_query($paramArr)];
                    $where[] = ['url', '=', $module . '/' . $controller . '/' . $action];
                    $res = self::where($where)->field('id,title,url,param')->find();
                    if ($res) {
                        return $res;
                    }
                }
            }
            $map[] = ['param', '=', ''];
            $res = self::where($map)->field('id,title,url,param')->find();
            if ($res) {
                return $res;
            } else {
                return false;
            }
        }

        // 扩展参数判断
        if ($rows[0]['param']) {
            parse_str($rows[0]['param'], $param);
            ksort($param);
            foreach ($param as $k => $v) {
                if (!isset($_param[$k]) || $_param[$k] != $v) {
                    return false;
                }
            }
        } else {// 排除敏感参数
            $param = ['hiModel', 'hiTable', 'hiValidate', 'hiScene'];
            foreach ($param as $k => $v) {
                if (isset($_param[$v])) {
                    return false;
                }
            }
        }
        return $rows[0];
    }

    /**
     * 保存入库
     * @return bool
     */
    public function storage($data = [])
    {
        if (empty($data)) {
            $data = request()->post();
        }
        // 只允许超级管理员在开发模式下修改
        if (isset($data['id']) && !empty($data['id'])) {
            if ($data['module'] == 'system' && (ADMIN_ID != 1 || config('system.app_debug') == 0)) {
                $this->error = '禁止修改系统模块！';
                return false;
            }
        }
        $data['url'] = trim($data['url'], '/');
        // 扩展参数解析为json
        if ($data['param']) {
            $data['param'] = trim(htmlspecialchars_decode($data['param']), '&');
            parse_str($data['param'], $param);
            ksort($param);
            $data['param'] = http_build_query($param);
        }
        $valid = new \app\system\validate\SystemMenu;
        if ($valid->check($data) !== true) {
            $this->error = $valid->getError();
            return false;
        }
        $res = isset($data['id']) && !empty($data['id']) ? $this->update($data) : $this->create($data);
        if (!$res) {
            $this->error = '保存失败！';
            return false;
        }
        cache('menu_all', null);
        return $res;
    }

    /**
     * 读取权限树形菜单
     * @param int $limitLevel 限制返回层数，0为不限制
     * @param array $auth 角色，0为不限制
     * @param int $currentLevel 当前层数
     * @return array
     * @author 祈陌 <3411869134@qq.com>
     */
    public static function getAuthTree($pid, $auth = [], $limitLevel = 5, $currentLevel = 0)
    {
        $result = [];
        $menus = self::getMenus();
        if ($menus) {
            $i = 0;
            foreach ($menus as $v) {
                if ($pid == $v['pid']) {
                    if (0 < $limitLevel && $limitLevel == $currentLevel) {
                        return $result;
                    }
                    $result[$i] = $v;
                    if (in_array($v['id'], $auth) !== false) {
                        $result[$i]['ischeck'] = true;
                    }
                    $child = self::getAuthTree($v['id'], $auth, $limitLevel, ($currentLevel + 1));
                    if (!empty($child)) {
                        $result[$i]['child'] = $child;
                    }
                    $i++;
                }
            }
        }
        return $result;
    }

    /**
     * 批量导入菜单
     * @param array $data 菜单数据
     * @param string $mod 模型名称或插件名称
     * @param string $type [module,plugins]
     * @param int $pid 父ID
     * @return bool
     */
    public static function importMenu($data = [], $mod = '', $type = 'module', $pid = 0)
    {
        if (empty($data)) {
            return true;
        }
        if ($type == 'module') {// 模型菜单
            foreach ($data as $v) {
                if (!isset($v['pid'])) {
                    $v['pid'] = $pid;
                }
                $childs = '';
                if (isset($v['childs'])) {
                    $childs = $v['childs'];
                    unset($v['childs']);
                }
                $res = (new \app\System\model\SystemMenu)->storage($v);
                if (!$res) {
                    return false;
                }
                if (!empty($childs)) {
                    self::importMenu($childs, $mod, $type, $res['id']);
                }
            }
        } else {// 插件菜单
            if ($pid == 0) {
                $pid = 3;
            }
            foreach ($data as $v) {
//                if (empty($v['param']) && empty($v['url'])) {
//                    return false;
//                }
                if (!isset($v['pid'])) {
                    $v['pid'] = $pid;
                }
                $v['module'] = $mod;
                $childs = '';
                if (isset($v['childs'])) {
                    $childs = $v['childs'];
                    unset($v['childs']);
                }
                $res = (new \app\system\model\SystemMenu)->storage($v);
                if (!$res) {
                    return false;
                }
                if (!empty($childs)) {
                    self::importMenu($childs, $mod, $type, $res['id']);
                }
            }
        }
        cache('menu_all', null);
        return true;
    }

}
