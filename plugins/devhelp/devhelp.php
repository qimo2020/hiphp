<?php
namespace plugins\devhelp;
use app\common\controller\Plugin;
defined('IN_SYSTEM') or die('Access Denied');

/**
 * 开发助手插件
 * @package plugins\devhelp
 */
class devhelp extends Plugin
{

    public $hooks = [
        'devhelp_login' => 'login',
        'devhelp_loginout' => 'loginout'
    ];

    public function login()
    {
        $this->view('index');
    }

    public function loginout($params)
    {
        $this->assign('params', $params);
        $this->view('index');
    }

    /**
     * 安装前的业务处理，可在此方法实现，默认返回true
     * @return bool
     */
    public function install()
    {
        return true;
    }

    /**
     * 安装后的业务处理，可在此方法实现，默认返回true
     * @return bool
     */
    public function installAfter()
    {
        return true;
    }

    /**
     * 卸载前的业务处理，可在此方法实现，默认返回true
     * @return bool
     */
    public function uninstall()
    {
        return true;
    }

    /**
     * 卸载后的业务处理，可在此方法实现，默认返回true
     * @return bool
     */
    public function uninstallAfter()
    {
        return true;
    }

}