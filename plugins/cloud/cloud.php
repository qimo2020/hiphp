<?php declare(strict_types=1);
// +----------------------------------------------------------------------
// | HiPHP框架[基于ThinkPHP6.0开发]
// +----------------------------------------------------------------------
// | Copyright (c) http://www.HiPHP.net
// +----------------------------------------------------------------------
// | HiPHP承诺基础框架永久免费开源，您可用于学习和商用，但必须保留软件版权信息。
// +----------------------------------------------------------------------
// | Author: 祈陌 <3411869134@qq.com>，开发者QQ群：829699898
// +----------------------------------------------------------------------
namespace plugins\cloud;
use app\common\controller\Plugin;
use plugins\cloud\model\Push;

defined('IN_SYSTEM') or die('Access Denied');
/**
 * 云端推送插件
 * @package plugins\cloud
 */
class cloud extends Plugin
{
    /**
     * @var array 插件钩子清单
     */
    public $hooks = [
        'cloud_temp'=>'temp',
        'cloud_push'=>'push',
    ];

    public function temp($params)
    {
        $cloudObj = new Push();
        $data = [
            'apiUrl'=>$cloudObj->apiUrl,
            'apiBind'=>$cloudObj->apiUrl.$cloudObj->apiBind
        ];
        $this->assign('params', $params);
        $this->assign('data', $data);
        $this->view('index');
    }

    public function push($params)
    {
        if($diff = array_diff(array_keys($params), ['type', 'method'])){
            return [];
        }
        $cloudObj = new Push();
        if(config('hi_cloud.token') && config('hi_cloud.sid') && config('hi_cloud.expire') && config('hi_cloud.expire') > time()){
            $result = $cloudObj->cloud->data($params)->api('push');
//            $filePath = root_path() . "text.txt";
//            $myfile = fopen($filePath, "w") or die("Unable to open file!");
//            fwrite($myfile, json_encode($result));
//            fclose($myfile);
            if (isset($result['code']) && $result['code'] == 1) {
                return $result['data'];
            }
        }
        return [];
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
     * 升级前的业务处理，可在此方法实现，默认返回true
     * @return bool
     */
    public function upgrade()
    {
        return true;
    }

    /**
     * 升级后的业务处理，可在此方法实现，默认返回true
     * @return bool
     */
    public function upgradeAfter()
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