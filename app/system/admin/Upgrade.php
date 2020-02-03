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
use hi\Cloud;
use think\facade\Env;
use app\system\model\SystemModule as ModuleModel;
use app\system\model\SystemPlugin as PluginsModel;

class Upgrade extends Base
{
    public $appType = 'system';
    public $identifier = 0;
    public $appVersion = '';

    protected function initialize()
    {
        parent::initialize();
        $this->rootPath = root_path();
        $this->appPath = base_path();
        $this->updatePath = $this->rootPath . 'backup/uppack/';
        $this->updateBackPath = $this->rootPath . 'backup/upback/';
        $this->cloud = new Cloud(config('hi_cloud.identifier'), $this->updatePath);
        $this->appType = $this->request->param('app_type/s', 'system');
        $this->identifier = $this->request->param('identifier/s', 'system');
        $this->cacheUpgradeList = 'upgrade_version_list' . $this->identifier;
        $this->appKey = '';

        $map = [];
        $map[] = ['identifier', '=', $this->identifier];
        $map[] = ['status', '<>', 0];

        switch ($this->appType) {
            case 'module':
                $this->appInfo = ModuleModel::where($map)->find();
                $this->appKey = $this->appInfo->app_keys;
                $this->appVersion = $this->appInfo->version;
                break;
            case 'plugin':
                $this->appInfo = PluginsModel::where($map)->find();
                $this->appKey = $this->appInfo->app_keys;
                $this->appVersion = $this->appInfo->version;
                break;
            case 'theme':
                $appName = $this->request->param('app_name');
                if ($appName) {
                    cookie('upgrade_app_name', $appName);
                }
                $this->appVersion = $this->request->param('app_version');
                break;
            default:
                $this->appVersion = config('hiphp.version');
                break;
        }
        if (!$this->appVersion) {
            return $this->response(0,'未安装的插件或模块禁止更新');
        }
    }

    public function index()
    {
        $this->assign('api_url', $this->cloud->apiUrl());
        return $this->view();
    }

    public function bind(){
        if ($this->request->isPost()) {
            $username = $this->request->post('username/s');
            $password = $this->request->post('password/s');
            $storeKey = $this->request->post('key/s');
            $data = [];
            $data['username'] = $username;
            $data['password'] = $password;
            $data['store_key'] = $storeKey;
            $data['domain'] = config('base.base_site_domain');
            $data['version'] = config('hiphp.version');
            $result = $this->cloud->data($data)->api('bind');
            if(config('system.system_app_debug')){
                file_put_contents('../runtime/demo.txt', json_encode($result));
            }
            if (!file_exists('../runtime/demo.txt')) {
                return $this->response(0,'../runtime/demo.txt写入失败');
            }

            if (isset($result['code']) && $result['code'] == 1) {
                $file = $this->rootPath . 'config/hi_cloud.php';
                $str = "<?php\n// 请妥善保管此文件，谨防泄漏\nreturn ['identifier' => '" . $result['identifier'] . "','expire' => '" . $result['expire'] . "'];\n";
                if (file_exists($file)) {
                    unlink($file);
                }
                file_put_contents($file, $str);
                if (!file_exists($file)) {
                    return $this->response(0,'config/hi_cloud.php写入失败');
                }
                return $this->response(1,'恭喜您，已成功绑定应用中心账号');
            }
            return $this->response(0,$result['msg'] ? $result['msg'] : '应用中心绑定失败！(-0)');
        }
    }
}