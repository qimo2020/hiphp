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
namespace app\System\model;

use think\Model;
use app\system\model\SystemUser as UserModel;

/**
 * 后台角色模型
 * @package app\System\model
 */
class SystemRole extends Model
{
    public $error;
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    public function setAuthAttr($value)
    {
        if (empty($value)) return '';
        return json_encode($value);
    }

    public function getAuthAttr($value)
    {
        if (empty($value)) return [];
        return json_decode($value, true);
    }

    /**
     * 获取所有角色
     * @return array
     * @author 祈陌 <3411869134@qq.com>
     */
    public static function getAll()
    {
        return self::column('id,name');
    }

    /**
     * 检查访问权限
     * @param int $id 需要检查的节点ID
     * @return bool
     * @author 祈陌 <3411869134@qq.com>
     */
    public static function checkAuth($id = 0)
    {
        $login = session('admin_user');
        // 超级管理员直接返回true
        if ($login['uid'] == '1' || $login['role_id'] == '1') {
            return true;
        }
        // 获取当前角色的权限明细
        $roleAuth = (array)session('role_auth_' . $login['uid']);
        if (!$roleAuth) {
            $roleAuth = self::getRoleAuth($login['role_id']);
            // 非开发模式，缓存数据
            if (config('system.app_debug') == 0) {
                session('role_auth_' . $login['uid'], $roleAuth);
            }
        }
        if (!$roleAuth) return false;
        return in_array($id, $roleAuth);
    }

    /**
     * 获取角色权限ID集
     * @author 祈陌 <3411869134@qq.com>
     */
    public static function getRoleAuth($id)
    {
        $where['id'] = is_array($id) ? ['in',implode(',', $id)] : $id;
        $rows = self::where($where)->field('auth')->select()->toArray();
        $auths = [];
        foreach ($rows as $k => $v) {
            $auths = array_merge($auths, $v['auth']);
        }
        return array_unique($auths);
    }

    /**
     * 删除角色
     * @param string $id 用户ID
     * @return bool
     * @author 祈陌 <3411869134@qq.com>
     */
    public function remove($id = 0)
    {
        if (is_array($id)) {
            $error = '';
            foreach ($id as $k => $v) {
                if ($v == 1) {
                    $error .= '不能删除超级管理员角色[' . $v . ']！<br>';
                    continue;
                }
                if ($v <= 0) {
                    $error .= '参数传递错误[' . $v . ']！<br>';
                    continue;
                }
                if (UserModel::where('role_id', $v)->find()) {
                    $error .= '删除失败，已有管理员绑定此角色[' . $v . ']！<br>';
                    continue;
                }
                $map = [];
                $map['id'] = $v;
                self::where($map)->delete();
            }
            if ($error) {
                $this->error = $error;
                return false;
            }
        } else {
            $id = (int)$id;
            if ($id <= 0) {
                $this->error = '参数传递错误！';
                return false;
            }
            if ($id == 1) {
                $this->error = '不能删除超级管理员角色！';
                return false;
            }
            // 判断是否有用户绑定此角色
            if (UserModel::where('role_id', $id)->find()) {
                $this->error = '删除失败，已有管理员绑定此角色！<br>';
                return false;
            }
            self::where('id', $id)->delete();
        }
        return true;
    }
}
