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
namespace app\system\home;
use app\common\controller\Common;
use app\system\model\SystemUser as UserModel;
use think\facade\Request;

class Error extends Common
{
    public function __call($method, $args)
    {
        $controller = strtolower($this->request->controller());
        if ($controller == 'jump') {
            //【后台】跳转至目标URL
            $url = urldecode($this->request->param('url'));
            if (!(new UserModel)->isLogin()) {
                return $this->response(0,'无操作权限');
            }
            if (stripos($url, 'http') === false) {
                return $this->response(0,'URL地址不合法');
            }
            return $this->response(1,'正在跳转至目标网站', $url);
        }
        return $this->response(0,'禁止访问');
    }
}
