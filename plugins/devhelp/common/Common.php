<?php
namespace plugins\devhelp\common;
defined('IN_SYSTEM') or die('Access Denied');
use app\system\admin\Base;
use app\system\model\SystemConfig as ConfigModule;
/**
 * [开发助手插件]前后台公共控制器
 * @package plugins\test\common
 */
class Common extends Base
{
    protected $globalInfo = [];

    protected function initialize()
    {
        parent::initialize();
        $this->globalInfo['currLink'] = 'run?'.http_build_query($this->request->param());
        $this->globalInfo['configs'] = ConfigModule::getConfigs($_GET['_p']);
        $this->assign('globalInfo', $this->globalInfo);
    }
}