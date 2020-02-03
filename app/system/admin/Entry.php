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
use app\system\model\SystemUser as UserModel;
use think\captcha\facade\Captcha;
/**
 * 后台入口
 * @package app\system\Entry
 */
class Entry extends Common
{
    /**
     * 登陆页面
     * @author 祈陌 <3411869134@qq.com>
     * @return mixed
     */
    public function index()
    {
        $model = new UserModel;
        $loginError = (int)session('admin_login_error');
        if ($this->request->isPost()) {
            $username = $this->request->post('username/s');
            $password = $this->request->post('password/s');
            $captcha = $this->request->post('captcha/s');
            $data = [];
            if ($loginError >= 3) {
                if (empty($captcha)) {
                    return $this->response(0,'请输入验证码');
                }
                if (!captcha_check($captcha)) {
                    return $this->response(0,'验证码错误');
                }
            }
            if (!$model->login($username, $password)) {
                $loginError = ($loginError+1);
                session('admin_login_error', $loginError);
                $data['token'] = token();
                return $this->response(0, $model->error, url('index'), $data);
            }
            session('admin_login_error', 0);
            return $this->response(1,'登陆成功，页面跳转中...', url(moduleNameMap('system').'/index/index'));
        }

        if ($model->isLogin()) {
            return redirect(url('system/index/index'));
        }

        $this->assign('loginError', $loginError);
        return $this->view();
    }

    public function logout(){
        model('systemUser')->logout();
        redirect(url(moduleNameMap('system') . '/entry/index'));
    }

    public function captcha()
    {
        return Captcha::create();
    }


}
