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

use app\system\model\SystemConfig as ConfigModel;
use think\Validate;

/**
 * 系统配置控制器
 * @package app\system\admin
 */
class Config extends Base
{
    /**
     * 系统配置信息
     * @return mixed
     */
    public function index($group = 'base')
    {
        if ($this->request->isPost()) {
            $webPath = './';
            $data = $this->request->post();
            $types = $data['type'];
            if (isset($data['id'])) {
                $ids = $data['id'];
            } else {
                $ids = $data['id'] = '';
            }
            unset($data['upload']);
            $validate = new Validate(['__token__' => 'token',]);
            if (!$validate->check($data)) {
                return $this->response(0, $validate->getError());
            }
            // 系统配置储存
            if (!$types) return false;
            $adminPath = config('system.admin_path');
            foreach ($types as $k => $v) {
                if ($v == 'switch' && !isset($ids[$k])) {
                    ConfigModel::where('name', $k)->update(['value' => 0]);
                    continue;
                }
                if ($v == 'checkbox') {
                    if (isset($ids[$k])) {
                        $ids[$k] = implode(',', $ids[$k]);
                    } else {
                        $ids[$k] = '';
                    }
                }
                // 修改后台管理目录
                if ($k == 'admin_path' && $ids[$k] != config('system.admin_path')) {
                    if (is_file($webPath . config('system.admin_path')) && is_writable($webPath . config('system.admin_path'))) {
                        @rename($webPath . config('system.admin_path'), $webPath . $ids[$k]);
                        if (!is_file($webPath . $ids[$k])) {
                            $ids[$k] = config('system.admin_path');
                        }
                        $adminPath = $ids[$k];
                    }
                }
                ConfigModel::where('name', $k)->update(['value' => $ids[$k]]);
            }
            // 更新缓存
            $config = ConfigModel::getConfigs('', true);
//            if ($group == 'system') {
//                $rootPath = root_path();
//                if (file_exists($rootPath . '.env')) {
//                    unlink($rootPath . '.env');
//                }
//                $env = "//设置开启调试模式\napp_debug = " . ($config['system']['system_app_debug'] ? 'true' : 'false');
//                $env .= "\n//应用Trace\napp_trace = " . ($config['system']['system_app_trace'] ? 'true' : 'false');
//                file_put_contents($rootPath . '.env', $env);
//            }
            return $this->response(1, '保存成功');
        }
        $tabData = [];
        foreach (config('hi.config_group') as $key => $value) {
            $arr = [];
            $arr['title'] = $value;
            $arr['url'] = url('', ['group' => $key]);
            $tabData['tab'][] = $arr;
        }
        $map = [];
        $map['group'] = $group;
        $map['status'] = 1;
        $map['system'] = 1;
        $dataItem = ConfigModel::where($map)->order('sort,id')->column('id,name,title,group,url,value,type,options,tips');
        foreach ($dataItem as $k => &$v) {
            $v['id'] = $v['name'];
            if (!empty($v['options'])) {
                $v['options'] = parseAttr($v['options']);
            }
            if ($v['type'] == 'checkbox') {
                $v['value'] = explode(',', $v['value']);
            }
        }
        $tabData['current'] = url('', ['group' => $group]);
        $this->assign('dataItem', $dataItem);
        $this->assign('tabData', $tabData);
        $this->assign('tabType', 3);
        return $this->view();
    }

}
