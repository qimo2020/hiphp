<?php
namespace plugins\devhelp\admin;
defined('IN_SYSTEM') or die('Access Denied');
use app\system\admin\Base;
use app\system\model\SystemConfig as ConfigModel;
use app\system\model\SystemLang as LangModel;
use app\system\model\SystemPlugin as PluginModel;
/**
 * [开发助手插件]前后台公共控制器
 * @package plugins\test\common
 */
class Admin extends Base
{
    protected $globalInfo = [];

    protected function initialize()
    {
        parent::initialize();
        $plugin = strtolower($_GET['_p']);
        $this->tabData['current'] = url('?'.http_build_query($this->request->param()));
        $this->globalInfo['configs'] = ConfigModel::getConfigs($plugin);
        $this->globalInfo['langvars'] = LangModel::getDefaultLang($plugin);
        $appInfos = PluginModel::getPlugins();
        foreach ($appInfos as $v){
            if($v['name'] == $plugin){
                $this->globalInfo['appInfos'] = $v;
                break;
            }
        }
        $this->assign('globalInfo', $this->globalInfo);
    }


}