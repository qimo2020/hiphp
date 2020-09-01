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
namespace app\system\admin;
use app\system\model\SystemMenu as MenuModel;
use app\system\model\SystemModule as ModuleModel;
use app\system\model\SystemPlugin as PluginModel;
class Index extends Base
{
    public function index()
    {
        if($this->request->isAjax()){
            $menus = MenuModel::getMenuTrees(0, 3);
            $homeInfo = ['title'=>'首页', 'url'=>'system/index/welcome','icon'=>'fa fa-home'];
            return json(['menuInfo'=>$menus, 'homeInfo'=>$homeInfo, 'module'=>app('http')->getName()]);
        }
        $apps = array_merge(ModuleModel::getModules(), PluginModel::getPlugins());
        $fontItems = [];
        foreach ($apps as $k=>$v){
            if($v['status'] == 2 && $v['system'] == 0){
                if(strpos($v['identifier'], 'module') !== false){
                    $rootPath = root_path() . 'app';
                    $group = 'm_';
                }else{
                    $rootPath = root_path() . 'plugins';
                    $group = 'p_';
                }
                if (file_exists($file = $rootPath . '/' . $v['name'] . '/info.php') && $info = include_once $file) {
                    if(isset($info['iconfont']) && !empty($info['iconfont']) && file_exists(public_path() . ($font = 'static/' . $group . $v['name'] . '/' . $info['iconfont']))){
                        $fontItems[$k]['link'] = $font;
                        $fontItems[$k]['version'] = $v['version'];
                    }
                }
            }
        }
        $this->assign('fontItems', $fontItems);
        return $this->view();
    }

    public function welcome()
    {
        return $this->view();
    }

}
