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
                    ConfigModel::where(['name'=>$k, 'group'=>$group])->update(['value' => 0]);
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
                        //重置配置文件hi.php
                        $hiDir = config_path().'hi.php';
                        $hiConfigs = config('hi');
                        $hiConfigStrs = "<?php\nreturn [";
                        $hiConfigStrs .= "\n'tables' => " . $this->toArrStr($hiConfigs['tables']);
                        $hiConfigStrs .= ",\n'config_group' => " . $this->toArrStr($hiConfigs['config_group']);
                        $hiConfigStrs .= ",\n'modules' => " . $this->toArrStr($hiConfigs['modules']);
                        $hiConfigStrs .= ",\n'configs' => " . $this->toArrStr($hiConfigs['configs']);
                        $diff = array_diff($hiConfigs['scriptName'], ['index','admin','router','think']);
                        if($diff){
                            foreach($hiConfigs['scriptName'] as $key=>$v){
                                if(in_array($v, $diff)){
                                    unset($hiConfigs['scriptName'][$key]);
                                }
                            }
                        }
                        $adminPathNoExt = str_replace('.php', '', $adminPath);
                        if(!in_array($adminPathNoExt, $hiConfigs['scriptName'])){
                            $hiConfigs['scriptName'][] = $adminPathNoExt;
                        }
                        $hiConfigStrs .= ",\n'scriptName' => " . $this->toArrStr($hiConfigs['scriptName']);
                        $hiConfigStrs .= ",\n'admin_path' => '" . $adminPath . "'";
                        $hiConfigStrs .= "\n];";
                        unlink($hiDir);
                        file_put_contents($hiDir, $hiConfigStrs);
                    }
                }
                ConfigModel::where(['name'=>$k, 'group'=>$group])->update(['value' => $ids[$k]]);
            }
           // 更新配置缓存
           $config = ConfigModel::getConfigs('', true);
            //重置环境变量文件
           if ('system' == $group) {
               $rootPath = root_path();
               if (file_exists($envDir = $rootPath . '.env')) {
                   $envConfigs = env();
                   $env = "APP_DEBUG = " . ($config['system']['app_debug'] ? 'true' : 'false');
                   $env .= "\n\n[APP]\nDEFAULT_TIMEZONE = " . $envConfigs['APP_DEFAULT_TIMEZONE'];
                   $env .= "\n\n[DATABASE]\nTYPE = " . $envConfigs['DATABASE_TYPE'];
                   $env .= "\nHOSTNAME = " . $envConfigs['DATABASE_HOSTNAME'];
                   $env .= "\nDATABASE = " . $envConfigs['DATABASE_DATABASE'];
                   $env .= "\nUSERNAME = " . $envConfigs['DATABASE_USERNAME'];
                   $env .= "\nPASSWORD = " . $envConfigs['DATABASE_PASSWORD'];
                   $env .= "\nHOSTPORT = " . $envConfigs['DATABASE_HOSTPORT'];
                   $env .= "\nCHARSET = " . $envConfigs['DATABASE_CHARSET'];
                   $env .= "\nPREFIX = " . $envConfigs['DATABASE_PREFIX'];
                   $env .= "\nDEBUG = " . ($envConfigs['DATABASE_DEBUG'] ? 'true' : 'false');
                   $env .= "\n\n[LANG]\ndefault_lang = " . $envConfigs['LANG_DEFAULT_LANG'];
                   unlink($envDir);
                   file_put_contents($envDir, $env);
               }
           }
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

    protected function toArrStr($array){
        $result = '[';
        foreach($array as $k=>$v){
            if(!is_numeric($k)){
                $result .= "'" . $k . "' => '".$v . "',";
                continue;
            }
            $result .= "'".$v . "',";
        }
        $result = rtrim($result, ",");
        $result .= ']';
        return $result;
    }

}
