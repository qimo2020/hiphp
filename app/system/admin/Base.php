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
namespace app\system\admin;
use app\common\controller\Common;
use think\facade\Event;
use app\system\model\SystemMenu as MenuModel;
use app\system\model\SystemUser as UserModel;
use app\system\model\SystemRole as RoleModel;
/**
 * 后台控制器基类
 * @package app\system\admin
 */
class Base extends Common
{

    protected function initialize()
    {
        parent::initialize();
        $modelObj = new UserModel();
        $login = $modelObj->isLogin();
        if (!$login['uid']) {
            return $this->response(0,'请登陆之后在操作', url('entry/index'));
        }
        if (!defined('ADMIN_ID')) {
            define('ADMIN_ID', $login['uid']);
            define('ADMIN_ROLE', $login['role_id']);
            $currentMenuInfo = MenuModel::getCurrInfo();
            if ($currentMenuInfo) {
                if (!RoleModel::checkAuth($currentMenuInfo['id']) && !in_array($currentMenuInfo['url'], ['system/index/index', 'index/system/welcome'])) {
                    return $this->response(0,'[' . $currentMenuInfo['title'] . '] 访问权限不足');
                }
            } else {
                $currentMenuInfo = ['title' => '', 'url' => '', 'id' => 0];
            }

            //非ajax请求
            if (!$this->request->isAjax()) {
                //当前节点信息
                $this->assign('hiCurrentMenu', $currentMenuInfo);
                //菜单对象
                $this->assign('hiMenuObj', new MenuModel());
                //分组默认数据
                $this->assign('tabType', 0);
                $this->assign('tabData', '');
                //表单默认变量
                $this->assign('formData', '');
                //登录用户数据
                $this->assign('login', $login);
            }
        }
        $this->engine()->layout('block/base');


    }


}
