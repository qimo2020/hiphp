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

use think\Model;
use app\system\model\SystemMenu as MenuModel;
use app\system\model\SystemRole as RoleModel;
use app\system\model\SystemLog as LogModel;

/**
 * 后台用户模型
 * @package app\System\model
 */
class SystemUser extends Model
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
        return json_decode($value, 1);
    }

    public function setRoleIdAttr($value)
    {
        if (empty($value)) return '';
        return implode(',', (array)$value);
    }

    public function getRoleIdAttr($value)
    {
        if (empty($value)) return [];
        return explode(',', $value);
    }

    // 获取最后登陆ip
    public function setLastLoginIpAttr($value)
    {
        return getClientIp();
    }

    // 格式化最后登录时间
    public function getLastLoginTimeAttr($value)
    {
        return empty($value) ? '无' : date('Y-m-d H:i', $value);
    }

    // 权限
    public function role()
    {
        return $this->hasOne('SystemRole', 'id', 'role_id')->bind(['rid' => 'id', 'role_title' => 'name']);
    }

    /**
     * 删除用户
     * @param string $id 用户ID
     * @return bool
     */
    public function remove($id = 0)
    {
        $menu_model = new MenuModel();
        if (is_array($id)) {
            $error = '';
            foreach ($id as $k => $v) {
                if ($v == ADMIN_ID) {
                    $error .= '不能删除当前登陆的用户[' . $v . ']！<br>';
                    continue;
                }

                if ($v == 1) {
                    $error .= '不能删除超级管理员[' . $v . ']！<br>';
                    continue;
                }

                if ($v <= 0) {
                    $error .= '参数传递错误[' . $v . ']！<br>';
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
            if ($id == ADMIN_ID) {
                $this->error = '不能删除当前登陆的用户！';
                return false;
            }
            if ($id == 1) {
                $this->error = '不能删除超级管理员！';
                return false;
            }
            $map = [];
            $map['id'] = $id;
            self::where($map)->delete();
        }

        return true;
    }

    /**
     * 用户登录
     * @param string $username 用户名
     * @param string $password 密码
     * @param bool $remember 记住登录 TODO
     * @return bool|mixed
     */
    public function login($username = '', $password = '', $remember = false)
    {
        $username = trim($username);
        $password = trim($password);
        $map = [];
        $map['status'] = 1;
        $map['username'] = $username;

        $validate = new \app\system\validate\SystemUser;

        if ($validate->scene('login')->check(input('post.')) !== true) {
            $this->error = $validate->getError();
            return false;
        }

        $user = self::where($map)->find();
        if (!$user) {
            $this->error = '用户不存在或被禁用！';
            return false;
        }

        // 密码校验
        if (!password_verify($password, $user->password)) {
            $this->error = '登录密码错误！';
            return false;
        }

        // 检查是否分配角色
        if ($user->role_id == 0) {
            $this->error = '登录账号未分配角色！';
            return false;
        }

        // 角色信息
        $auths = [];
        if ($user->id !== 1) {
            $auths = RoleModel::getRoleAuth($user->role_id);
            if (empty($auths)) {
                $this->error = '绑定的角色不可用！';
                return false;
            }
        }

        // 自动清除过期的系统日志
        LogModel::where('create_time', '<', strtotime('-' . (int)config('System.log_retention') . ' days'))->delete();

        // 更新登录信息
        $user->last_login_time = time();
        $user->last_login_ip = getClientIp();

        if ($user->save()) {
            // 执行登陆
            $login = [];
            $login['uid'] = $user->id;
            $login['role_id'] = implode(',', $user->role_id);
            $login['nick'] = $user->nick;
            $login['mobile'] = $user->mobile;
            $login['email'] = $user->email;

            // 缓存角色权限
            session('role_auth_' . $user->id, $auths);
            // 缓存登录信息
            session('admin_user', $login);
            session('admin_user_sign', $this->dataSign($login));
            return $user->id;
        }

        return false;
    }

    /**
     * 判断是否登录
     * @return bool|array
     */
    public function isLogin()
    {
        $user = session('admin_user');
        if (isset($user['uid'])) {
            if (!self::where('id', $user['uid'])->find()) {
                return false;
            }
            return session('admin_user_sign') == $this->dataSign($user) ? $user : false;
        }
        return false;
    }

    /**
     * 退出登陆
     * @return bool
     */
    public function logout()
    {
        $user = session('admin_user');
        session('admin_user', null);
        session('admin_user_sign', null);
        if (isset($user['uid'])) {
            session('role_auth_' . $user['uid'], null);
        }
    }

    /**
     * 数据签名认证
     * @param array $data 被认证的数据
     * @return string 签名
     */
    public function dataSign($data = [])
    {
        if (!is_array($data)) {
            $data = (array)$data;
        }
        ksort($data);
        $code = http_build_query($data);
        $sign = sha1($code);
        return $sign;
    }

}
