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

namespace plugins\cloud\home;

use app\system\model\SystemUser as UserModel;
use hi\Dir;
use hi\PclZip;
use plugins\cloud\lib\Cloud as CloudModel;
use think\facade\Cache;
use think\facade\Db;

defined('IN_SYSTEM') or die('Access Denied');
class Upgrade extends Base
{
    public $appType = 'system';
    public $identifier = 0;
    public $appVersion = '';

    protected function initialize()
    {
        parent::initialize();
        pluginConfigInit();
        $modelObj = new UserModel();
        $login = $modelObj->isLogin();
        if ($login === false || !$login['uid']) {
            return $this->response(0,'请登陆之后在操作', '/'.config('system.admin_path').'?system/entry/index');
        }
        $this->rootPath = root_path();
        $this->appPath = base_path();
        $cloudHost = config('clouds.cloud_push_domain') ?: '';
        $this->downloadDir = './download';
        $this->cloud = new CloudModel($cloudHost);
        $this->appType = $this->request->param('app_type/s', 'system');
        $this->appName = $this->request->param('app_name/s', 'system');
        $this->appId = $this->request->param('app_id/s', '');
        $this->appKey = $this->request->param('app_key/s', '');
        $this->appVersion = $this->request->param('version/s', config('hiphp.version'));
    }

    public function index()
    {
        return $this->view();
    }

    public function download(){
        if(!$this->request->isPost()){
            return $this->response(0,'request error');
        }
        $params = $this->request->post();
        $group = $this->appType == 'system' ? $this->appType : $this->appType . '_' . $this->appName;

        $downloadDir = $this->downloadDir .'/'. $group;
        if (!is_dir($downloadDir)) {
            if (false === mkdir($downloadDir, 0755, true)) {
                return $this->response(0,'创建下载文件夹失败，请检查目录权限。');
            }
        }
        $lock = $downloadDir . '/download.lock';
        switch ($params['method']){
            case 'prepare':
                if (is_file($lock)) {
                    return $this->response(0,'下载任务执行中，请手动删除此文件后重试！<br>文件地址：'.$lock);
                }else{
                    file_put_contents($lock, time());
                }
                $temp_path = $downloadDir . "/" . randomStr() . time() . ".zip";
                session('download_file_temp_' . $group, $temp_path);
                return $this->response(1, $lock);
                break;
            case 'start':
                $temp_path = session('download_file_temp_' . $group);
                if (!$temp_path) {
                    return $this->response(0,'下载错误，请重新下载');
                }
                $post = ['app_id'=>$this->appId, 'app_key'=>$this->appKey, 'version'=>$this->appVersion, 'app_type'=>$this->appType];
                if(isset($params['op']) && $params['op'] == 'upgrade'){
                    $post['upgrade'] = 1;
                }
                try {
                    $download = $this->cloud->data($post)->down('download', $temp_path);
                    if(isset($download['code']) && $download['code'] == 0){
                        if(is_file($temp_path)) @unlink($lock);
                        @unlink($lock);
                        return $this->response(0, '发生错误, 终止下载', '', $download);
                    }
                } catch (\think\Exception $e) {
                    return $this->response(0, '发生错误, 终止下载');
                }
                if(is_file($lock)) @unlink($lock);
                return $this->response(1, '', '', (array)compact('temp_path'));
                break;
            case 'progress':
                $file = session('download_file_temp_'.$group);
                $file_size = 0;
                if($file && is_file($file)){
                    $file_size = round(filesize($file) / 1024 * 100) / 100;
                }
                return $this->response(1, '', '', (array)compact('file_size'));
                break;
        }
    }

    public function import(){
        if(!$this->request->isPost()){
            return $this->response(0,'error');
        }
        $params = $this->request->param();
        if(!file_exists($params['file']) || !is_file($params['file'])){
            return $this->response(0,'file not found');
        }
        $res = $this->_import($params);
        if(false === $res){
            return $this->response(0, (string)self::getError());
        }
        return $this->response(1, (string)self::getMsg());
    }

    protected function _import($params){
            $decomPath = '.' . trim($params['file'], '.zip');
            if (!is_dir($decomPath)) {
                Dir::create($decomPath, 0777);
            }
            $archive = new PclZip();
            $archive->PclZip(realpath($params['file']));
            if (!$archive->extract(PCLZIP_OPT_PATH, $decomPath, PCLZIP_OPT_REPLACE_NEWER)) {
                Dir::delDir($decomPath);
                @unlink($params['file']);
                self::$error = '导入失败(' . $archive->error_string . ')';
                return false;
            }
            $appBaseDir = $params['app_type'] == 'system'  ? '' : ($params['type'] == 0 ? 'app' : 'plugins');
            $appRootDir = root_path() . $appBaseDir; //应用根目录
            $tempAppDir = $decomPath . '/uploads/' . $appBaseDir; //安装包应用根目录
            if (!is_dir($tempAppDir)) {
                Dir::delDir($decomPath);
                @unlink($params['file']);
                self::$error = '导入失败，安装包不完整(-1)';
                return false;
            }
            // 获取应用名
            $files = Dir::getList($tempAppDir . '/');
            if (!isset($files[0])) {
                Dir::delDir($decomPath);
                @unlink($params['file']);
                self::$error = '导入失败，安装包不完整(-1)';
                return false;
            }
            $dependAppIns = in_array($params['app_type'], ['module', 'plugin']);
            // 新应用[模块/插件]防止重复导入
            if (!isset($params['op']) && $dependAppIns && is_dir($appRootDir . '/' . $params['app_name'])) {
                Dir::delDir($decomPath);
                @unlink($params['file']);
                self::$error = '应用已存在';
                return false;
            }

            // 安装包应用目录
            $appTempPath = isset($params['app_type']) && $params['app_type'] == 'system' ? $tempAppDir : $tempAppDir .'/' . $params['app_name'] . '/';
            // 应用[模块/插件]获取安装包基本信息
            if($dependAppIns){
                $infoFilePath = $appTempPath . 'info.php';
                $infoData = include_once $infoFilePath;
            }else if(in_array($params['app_type'], ['component', 'system'])){
                $infoFilePath = $decomPath . '/' . 'config.xml';
                $infoXml = file_get_contents($infoFilePath);
                $infoData = xml2array($infoXml);
            }else if('theme' == $params['app_type']){
                $infoFilePath = $appTempPath . 'theme/' . $params['theme_name'] . '/' . 'config.xml';
                $infoXml = file_get_contents($infoFilePath);
                $infoData = xml2array($infoXml);
            }
            if (!file_exists($infoFilePath)) {
                Dir::delDir($decomPath);
                @unlink($params['file']);
                self::$error = '安装包缺少配置信息文件';
                return false;
            }
            if(isset($params['op']) && $params['op'] == 'upgrade'){
                $first = $params['app_type'] == 'system' ? config('hiphp') : Db::name('system_' . $params['app_type'])->where(['app_id'=>$params['app_id']])->find();
                if(null !== $first && version_compare($infoData['version'], $first['version'],'<=')){
                    Dir::delDir(realpath($decomPath));
                    @unlink($params['file']);
                    self::$error = '安装包版本有误';
                    return false;
                }
                if(in_array($params['app_type'], ['module', 'plugin', 'system'])) { //模块/插件/系统升级前置后置操作
                    $appClassPath = ($params['app_type'] == 'system' ? $decomPath . '/uploads/app' : $tempAppDir) . '/' . $params['app_name'] . '/' . $params['app_name'] . ".php";
                    if (!is_file($appClassPath)) {
                        Dir::delDir($decomPath);
                        @unlink($params['file']);
                        self::$error = '应用类文件不存在:'.$appClassPath;
                        return false;
                    }
                    include_once $appClassPath; //应用类和临时应用类,命名空间相同时,需要区分
                    $appClass = ($params['app_type'] == 'system'  ? 'app' : $appBaseDir) . "\\{$params['app_name']}\\{$params['app_name']}";
                    if (!class_exists($appClass)) {
                        Dir::delDir($decomPath);
                        @unlink($params['file']);
                        self::$error = '应用类不存在:'.$appClass;
                        return false;
                    }
                    $appObj = new $appClass;
                    if (!$appObj->upgrade()) {
                        Dir::delDir($decomPath);
                        @unlink($params['file']);
                        self::$error = '应用升级前的方法执行失败（原因：' . $appObj->getError . '）';
                        return false;
                    }
                }
            }else{
                if(in_array($params['app_type'], ['module', 'plugin'])){
                    $sql = [];
                    $sql['name'] = $infoData['name'];
                    $sql['identifier'] = $infoData['identifier'];
                    if(isset($info['theme']) && $infoData['theme']){
                        $sql['theme'] = $infoData['theme'];
                    }
                    if(isset($info['mobile_theme']) && $infoData['mobile_theme']){
                        $sql['mobile_theme'] = $infoData['mobile_theme'];
                    }
                    $sql['title'] = $infoData['title'];
                    $sql['intro'] = $infoData['intro'];
                    $sql['author'] = $infoData['author'];
                    $sql['icon'] = $infoData['icon'] ? ROOT_DIR . substr($infoData['icon'], 1) : '';
                    $sql['version'] = $infoData['version'];
                    $sql['url'] = $infoData['author_url'];
                    $sql['status'] = 0;
                    $sql['system'] = 0;
                    $sql['app_id'] = $params['app_id'];
                    $sql['app_keys'] = $params['app_key'];
                    $create = \app\system\model\SystemPlugin::create($sql);
                }else{
                    $sql['name'] = $params['app_name'];
                    $sql['title'] = $infoData['title'];
                    $sql['intro'] = $infoData['intro'];
                    $sql['author'] = $infoData['author'];
                    if(isset($infoData['icon'])){
                        $sql['icon'] = $infoData['icon'] ? '/' . substr($infoData['icon'], 1):'';
                    }
                    $sql['version'] = $infoData['version'];
                    $sql['status'] = 0;
                    $sql['app_keys'] = $params['app_key'];
                    $sql['app_id'] = $params['app_id'];
                    if(!$dependAppIns){
                        $sql['app_type'] = $params['type'];
                    }
                    $class = '\\app\\system\\model\\SystemComponent';
                    if('theme' == $params['app_type']){
                        $sql['theme_name'] = $params['theme_name'];
                        $class = '\\app\\system\\model\\SystemTheme';
                    }
                    $create = $class::create($sql);
                }
            }
            // 应用目录
            $appPath = $params['app_type'] == 'system' ? $appRootDir : $appRootDir .'/' . $params['app_name'];
            // 复制应用目录
            if (!is_dir($appPath)) {
                Dir::create($appPath, 0777);
            }
            Dir::copyDir(realpath($appTempPath),  $appPath);

            //静态资源目录处理
            if($params['app_type'] != 'system'){
                $appStaticDir = './static/' . ($params['type'] == 0 ? 'm_' : 'p_') . $params['app_name'] . '/';//应用静态资源目录
                $tempAppStaticDir = $decomPath . '/' . 'uploads' . '/' . 'public' . ltrim($appStaticDir, '.');//安装包应用静态资源目录
                if('theme' == $params['app_type']){
                    $appStaticDir .= $params['theme_name'];
                    $tempAppStaticDir .= $params['theme_name'];
                }
                if (!is_dir($appStaticDir)) {
                    Dir::create($appStaticDir, 0755);
                }
                Dir::copyDir($tempAppStaticDir, $appStaticDir);
            }
            // 删除临时目录和安装包
            Dir::delDir(realpath($decomPath));
            @unlink(realpath($params['file']));
            $tag = $params['app_type'] == 'system' ? 'hiphp_config' : $params['app_type'];
            Cache::tag($tag . '_tag')->clear();
            if(isset($params['op']) && $params['op'] == 'upgrade'){
                if(in_array($params['app_type'], ['module', 'plugin', 'system'])) {
                    if (isset($appObj) && !$appObj->upgradeAfter()) {
                        self::$error = '应用升级后的方法执行失败（原因：' . $appObj->getError . '）';
                        return false;
                    }
                }
                if('system' == $params['app_type']){
                    $versions = config('hiphp');
                    $versions['version'] = $infoData['version'];
                    $config = var_export($versions, true);
                    $config = str_replace(['array (', ')'], ['[', ']'], $config);
                    $config = preg_replace("/(\s*?\r?\n\s*?)+/", "\n", $config);
                    $code = <<<INFO
<?php
return [
    'hiphp' => {$config}
];
INFO;
                    file_put_contents(root_path() . 'version.php', $code);
                }else{
                    $result = Db::name('system_' . $params['app_type'])->where(['app_id'=>$params['app_id']])->update(['version'=>$infoData['version']]);
                }
                self::$msg = '升级成功';
            }else{
                self::$msg = '下载成功';
            }
            return true;
    }

    public function bind(){
        if ($this->request->isPost()) {
            $password = $this->request->post('password/s');
            $params['domain'] = request()->domain();
            $params['ip'] = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : getClientIp();
            $params['timestamp'] = time();
            $sign = \hi\Sign::getSign($params, $password, true);
            $data['sign'] = $sign;
            $data['code'] = $password;
            $result = $this->cloud->data($data)->api('bind');
            if(config('system.app_debug')){
                file_put_contents($this->rootPath.'runtime/demo.txt', json_encode($result));
            }
            if (!file_exists($this->rootPath.'runtime/demo.txt')) {
                return $this->response(0,(string)$this->rootPath.'runtime/demo.txt写入失败');
            }
            if (isset($result['code']) && $result['code'] == 1) {
                $file = $this->rootPath . 'config/hi_cloud.php';
                $str = "<?php\n// 请妥善保管此文件，谨防泄漏\nreturn ['sid' => '" . $result['sid'] . "','token' => '" . $result['token'] . "','expire' => '" . $result['expire'] . "'];\n";
                if (file_exists($file)) {
                    unlink($file);
                }
                file_put_contents($file, $str);
                if (!file_exists($file)) {
                    return $this->response(0,'config/hi_cloud.php写入失败');
                }
                return $this->response(1,'恭喜您，已成功绑定应用中心账号');
            }
            $msg = isset($result['msg']) && $result['msg'] ? $result['msg'] : '应用中心绑定失败！';
            return $this->response(0, $msg);
        }
    }
}