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
use app\system\model\SystemModule as ModuleModel;
use app\system\model\SystemComponent as ComponentModel;
use app\system\model\SystemTheme as ThemeModel;
use app\system\model\SystemConfig as ConfigModel;
use app\system\model\SystemMenu as MenuModel;
use app\system\model\SystemHook as HookModel;
use app\system\model\SystemRole as RoleModel;
use hi\Dir;
use hi\PclZip;
use hi\Cloud;
use think\facade\Db;
use think\Validate;
use think\facade\Cache;
use app\system\model\SystemLang as LangModel;
/**
 * 模块管理控制器
 * @package app\system\admin
 */
class Module extends Base
{
    public $tabData = [];

    protected function initialize()
    {
        parent::initialize();
        $tabData['tab'] = [
            [
                'title' => '已启用',
                'url' => url('index', ['status'=>2]),
            ],
            [
                'title' => '已停用',
                'url' => url('index', ['status'=>1]),
            ],
            [
                'title' => '待安装',
                'url' => url('index', ['status'=>0]),
            ],
            [
                'title' => '待升级',
                'url' => url('index', ['status'=>3]),
            ],
            [
                'title' => '待下载',
                'url' => url('index', ['status'=>4]),
            ],
            [
                'title' => '导入模块',
                'url' => url('import'),
            ],
        ];
        $this->tabData = $tabData;
        $this->appPath = base_path();
        $this->rootPath = root_path();
        $this->tempPath = $this->rootPath.'runtime/app/';
        $this->depr = config('view.view_depr');
        //重定向地址解析
        $tmparr = parse_url($_SERVER["HTTP_REFERER"]);
        $this->rstr = empty($tmparr['scheme']) ? 'http://' : $tmparr['scheme'] . '://';
        $this->rstr .= $tmparr['host'] . $tmparr['path'];
    }

    /**
     * 模块管理首页
     * @return mixed
     * @author 祈陌 <3411869134@qq.com>
     */
    public function index()
    {
        $status = $this->request->param('status/d', 2);
        $tabData = $this->tabData;
        $tabData['current'] = url('', ['status'=>$status]);
        $map = [];
        $tips = '';
        if($status < 3){
            $map['status'] = $status;
        }
        $map['system'] = 0;
        $modules = ModuleModel::where($map)->order('sort,id')->column('id,title,author,intro,icon,default,system,app_id,app_keys,identifier,name,version,status');

        if (0 == $status) {
            // 自动将本地未入库的模块导入数据库
            $allModule = ModuleModel::order('sort,id')->column('id,name', 'name');
            $files = Dir::getList($this->appPath);
            $sysDir = config('hi.modules');
            array_push($sysDir, 'extra');
            foreach ($files as $k => $f) {
                // 排除系统模块和已存在数据库的模块
                if (array_search($f, $sysDir) !== false || array_key_exists($f, $allModule) || !is_dir($this->appPath . $f)) {
                    continue;
                }
                if (file_exists($this->appPath . $f . '/info.php')) {
                    $info = moduleInfo($f);
                    $sql = [];
                    $sql['name'] = $info['name'];
                    $sql['identifier'] = $info['identifier'];
                    $sql['theme'] = $info['theme'];
                    if(isset($info['mobile_theme']) && $info['mobile_theme']){
                        $sql['mobile_theme'] = $info['mobile_theme'];
                    }
                    $sql['title'] = $info['title'];
                    $sql['intro'] = $info['intro'];
                    $sql['author'] = $info['author'];
                    $sql['icon'] = $info['icon'] ? '/' . substr($info['icon'], 1):'';
                    $sql['version'] = $info['version'];
                    $sql['url'] = $info['author_url'];
                    $sql['status'] = 0;
                    $sql['default'] = 0;
                    $sql['system'] = 0;
                    $db = ModuleModel::create($sql);
                    $sql['id'] = $db->id;
                    $modules = array_merge($modules, [$sql]);
                }
            }
        }
        $tips = $tips ? $tips : '<div class="hi-no-data-tips" style="padding: 50px 0;text-align: center">未发现相关模块，快去<a target="_blank" href="' . config('clouds.store_push_domain') . '"> <strong style="color:#428bca">应用市场</strong> </a>看看吧！</div>';

        if($status == 3 || $status == 4){
            $components = ComponentModel::where('app_type',0)->order('sort,id')->column('id,title,author,intro,app_id,app_keys,name,version,status');
            foreach ($components as $v){
                $modules[] = $v;
            }
            $themes = ThemeModel::where('app_type',0)->order('sort,id')->column('id,title,author,intro,app_id,app_keys,name,version,status');
            foreach ($themes as $v){
                $modules[] = $v;
            }
            $pushs = runHook('cloud_push', ['type'=>'module', 'method'=>$status == 3 ? 'upgrade' : 'download'], true);
            if($pushs && $pushs[0]) {
                foreach ($modules as $k => $v) {
                    foreach ($pushs[0] as $kk => $vv) {
                        $dependAppIns = in_array($vv['app_type'], ['component', 'theme']);
                        if ($status == 3) {
                            if ($v['name'] == $vv['app_name']) {
                                if($v['app_keys'] == $vv['app_key'] && $v['app_id'] == $vv['app_id'] && version_compare($vv['version'], $v['version'], '>')){
                                    $modules[$k] = $v;
                                    $modules[$k]['app_name'] = $dependAppIns ? $vv['app_name'] : $v['name'];
                                    unset($modules[$k]['name']);
                                    $modules[$k]['up_version'] = $vv['version'];
                                    $modules[$k]['status'] = 3;
                                    $modules[$k]['app_type'] = $vv['app_type'];
                                    $modules[$k]['version'] = $vv['version'];
                                    $modules[$k]['type'] = $vv['type'];
                                    $modules[$k]['file_size'] = $vv['file_size'];
                                    if ($dependAppIns) {
                                        $modules[$k]['title'] = $vv['title'];
                                        $modules[$k]['system'] = 0;
                                    }
                                    if (isset($vv['theme_name'])) {
                                        $modules[$k]['theme_name'] = $vv['theme_name'];
                                    }
                                    $modules[$k]['app_title'] = $vv['app_title'];
                                }else{
                                    $classModel = '\app\system\model\System'.ucfirst($vv['app_type']);
                                    $classModel::where('name', $v['name'])->update(['app_id'=>$vv['app_id'], 'app_keys'=>$vv['app_key']]);
                                    unset($modules[$k]);
                                }
                            } else {
                                unset($modules[$k]);
                            }
                        } else {
                            if (($v['name'] && $v['name'] == $vv['app_name'] && !$dependAppIns) || ($dependAppIns && $v['app_id'] && $v['app_id'] == $vv['app_id'])) {
                                unset($pushs[0][$kk]);
                            } else {
                                $pushs[0][$kk]['system'] = 0;
                                $pushs[0][$kk]['status'] = -1;
                            }
                        }
                    }
                }
                if ($status == 4) {
                    $modules = $pushs && isset($pushs[0]) ? $pushs[0] : [];
                }
            }else{
                $modules = [];
            }

        }

        $this->assign('emptyTips', $tips);
        $this->assign('dataInfo', array_values($modules));
        $this->assign('tabData', $tabData);
        $this->assign('tabType', 3);
        return $this->view();
    }

    /**
     * 模块配置
     * @return mixed
     * @author 祈陌 <3411869134@qq.com>
     */
    public function setting($module = '', $group='', $tab=0)
    {
        //保存数据
        if ($this->request->isPost()) {
            if(ADMIN_ID != 1){
                return $this->response(0,'非超级管理员禁止修改');
            }
            $data = $this->request->post();
            if(!isset($data['id']) && !isset($data['group']) && !is_numeric($data['group'])){
                return $this->response(0,'参数格式错误');
            }
            unset($data['upload']);  //清除上传字段
            $validate = new Validate([
                '__token__' => 'token',
            ]);
            if (!$validate->check($data)) {
                return $this->response(0,$validate->getError());
            }
            $group = $data['group'];
            $currConfigs = $fields = [];
            $fields = ConfigModel::where(['group'=>$group])->column('name');
            $moduleInfo = moduleInfo($group);
            if (!isset($moduleInfo['config']) || !$moduleInfo['config']) {
                return $this->response(0, '此模块无需配置');
            }
            foreach ($moduleInfo['config'] as $key=>$v) {
                parse_str(parse_url((string)$v['url'])['query'], $urlArray);
                if ($urlArray['tab'] == $tab) {
                    $currConfigs = $v['fields'];
                    break;
                }
            }
            if(!$currConfigs){
                return $this->response(0,'此插件配置格式错误');
            }
            $addFields = $updateFields = [];
            unset($data['__token__']);
            unset($data['tab']);
            unset($data['group']);
            unset($data['id']);
            foreach ($data as $key=>$v){
                if(in_array($key, $fields)){
                    $updateFields[$key] = $v;
                }else{
                    $addFields[$key] = $v;
                }
            }
            if($addFields){
                $insertData = [];
                foreach ($currConfigs as &$v){
                    if(array_key_exists($v['name'], $addFields)){
                        $v['status'] = 1;
                        $v['create_time'] = time();
                        $v['update_time'] = time();
                        $v['value'] = is_array($addFields[$v['name']])?arrayToStr($addFields[$v['name']],','):$addFields[$v['name']];
                        if(isset($v['options']) && $v['options'] && !in_array($v['type'], ['checkbox', 'select', 'radio', 'switch'])){
                            $v['options'] = array_filter(parseAttr($v['options']));
                        }else{
                            $v['options'] = '';
                        }
                        $insertData[] = $v;
                    }
                }
                if (!ConfigModel::insertAll($insertData)) {
                    return $this->response(0,'保存失败');
                }
            }
            if($updateFields){
                foreach ($currConfigs as &$v){
                    if(array_key_exists($v['name'], $updateFields)){
                        $value = $v['type']=='checkbox'?implode(',', $updateFields[$v['name']]):$updateFields[$v['name']];
                        $result = ConfigModel::where(['name'=>$v['name'], 'group'=>$group])->update(['value'=> $value]);
                    }else{
                        if($v['type'] == 'switch'){
                            $result = ConfigModel::where(['name'=>$v['name'], 'group'=>$group])->update(['value'=>0]);
                        }
                        if($v['type'] == 'checkbox'){
                            $result = ConfigModel::where(['name'=>$v['name'], 'group'=>$group])->update(['value'=>'']);
                        }
                    }
                }
            }
            ConfigModel::getConfigs('', true);
            Cache::tag('hiphp_config')->clear();
            return $this->response(1,'保存成功');
        }

        $where = [];
        if (isset($module)) {
            $where['name'] = $module;
        }else {
            return $this->response(0,'参数错误');
        }
        $row = ModuleModel::where($where)->field('id,name,title')->find();
        if($row){
            $row = $row->toArray();
        }
        $group = $group ? $group:$row['name'];
        $moduleInfo = moduleInfo($row['name']);
        if (!isset($moduleInfo['config']) || !$moduleInfo['config']) {
            return $this->response(0,'此插件无需配置');
        }

        //显示数据
        $info = [];
        if ($moduleInfo['config']) {
            if(isset($moduleInfo['config'][0]['fields']) && is_array($moduleInfo['config'][0]['fields']) && !empty($moduleInfo['config'][0]['fields'])){
                $tabData['current'] = url('',['group'=>$group,'tab'=>$tab, 'module'=>$module]);
                $map = [];
                $map['group'] = $group;
                $map['status'] = 1;
                $dataList = ConfigModel::where($map)->column('name,group,value,options');
                $newDataList = [];
                foreach ($dataList as $k => $v) {
                    $newDataList[$v['name']] = $v['value'];
                }
              
                foreach ($moduleInfo['config'] as $key=>$v){
                    //tab菜单数据加载
                    $tabData['tab'][$key]['title'] = $v['title'];
                    $tabData['tab'][$key]['url'] = $v['url'].'&module='.$module;
                    //tab表单数据加载
                    parse_str(parse_url((string)$v['url'])['query'], $urlArray);
                    if($urlArray['tab'] == $tab){
                        $info[$key] = $v;
                        foreach ($info[$key]['fields'] as &$vv){
                            if (isset($vv['options']) && $vv['options']) {
                                $options = $vv['options'];
                                $vv['options'] = array_filter(parseAttr($options));
                            }
                            if(array_key_exists($vv['name'], $newDataList)){
                                $vv['value'] = $newDataList[$vv['name']];
                            }else { //存在于info.php但不存在数据库的字段,会默认使用info.php里面的字段默认值
                                if ($vv['type'] == 'array') {
                                    $vv['value'] = str_replace('\r\n', "\r\n", $vv['value']);
                                }
                            }
                        }
                    }
                }
                $this->assign('tabData', $tabData);
                $this->assign('tabType', 3);
            }else{
                return $this->response(0,'此模块配置文件格式不正确');
            }
        }
        $formData['module'] = $module;
        $formData['config'] = $info;
        $formData['name'] = $row['name'];
        $this->assign('group', $group);
        $this->assign('tab', $tab);
        $this->assign('formData', $formData);
        return $this->view();
    }

    /**
     * 安装模块
     * @return mixed
     */
    public function install($id = 0)
    {
        if ($this->request->isPost()) {
            $postData = $this->request->post();
            $result = self::execInstall($id, $postData['clear']);
            if ($result !== true) {
                return $this->response(0,$result);
            }
            Cache::tag('menus')->clear();
            return $this->response(1,'模块已安装成功', (string)url('index', ['status'=>2]));
        }

        $mod = ModuleModel::where('id', $id)->find();

        if (!$mod) {
            return $this->response(0,'模块不存在');
        }

        if ($mod['status'] > 0) {
            return $this->response(0,'请勿重复安装此模块');
        }

        $modPath = $this->appPath . $mod['name'] . '/';
        // 模块自定义配置
        if (!file_exists($modPath . 'info.php')) {
            return $this->response(0,'模块配置文件不存在[info.php]');
        }
        $info = moduleInfo($mod['name']);
        // 模块依赖检查
        if(isset($info['module_depend']) && $info['module_depend']){
            $info = $this->checkAppDepend('module_depend', $info);
        }
        // 插件依赖检查
        if(isset($info['plugin_depend']) && $info['plugin_depend']){
            $info = $this->checkAppDepend('plugin_depend', $info);
        }
        $info['id'] = $mod['id'];
        $info['demo_data'] = file_exists($modPath . 'sql/demo.sql') ? true : false;
        $this->assign('tables', $this->checkTable($info['tables']));
        $this->assign('formData', $info);

        return $this->view();
    }

    private function checkAppDepend($depend, $info)
    {
        if(strpos($depend, 'module') === false){
            $dependType = 'plugin_depend';
            $dependTips = '插件';
            $this->appPath = $this->rootPath . 'plugins/';
        }else{
            $dependType = 'module_depend';
            $dependTips = '模块';
        }
        foreach ($info[$dependType] as $k => &$v) {
            if (!isset($v[3])) {
                $v[3] = '=';
            }
            $v[4] = '✔︎';
            $v[5] = '';
            // 判断应用是否存在
            if (!is_dir($this->appPath . $v[0])) {
                $v[4] = '<span class="red">✘ '.$dependTips.'不存在</span>';
                $info[$dependType][$k] = $v;
                continue;
            }
            if (!file_exists($this->appPath . $v[0] . '/info.php')) {
                $v[4] = '<span class="red">✘ '.$dependTips.'配置文件不存在</span>';
                $info[$dependType][$k] = $v;
                continue;
            }
            $dependInfo = $dependType == 'module_depend' ? Db::name('system_module')->where('name', $v[0])->find() : Db::name('system_plugin')->where('name', $v[0])->find();
            $dinfo = $dependInfo === null ? [] : $dependInfo;
            if(!$dependInfo){
                $v[6] = '未安装';
                $v[4] = '<span style="color:red">✘ '.$dependTips.'未安装</span>';
                continue;
            }
            $v[5] = $dinfo['version'];
            if($dinfo['status'] == 0){
                $v[6] = '未安装';
                $v[4] = '<span style="color:red">✘ '.$dependTips.'未安装</span>';
                continue;
            }else{
                $v[6] = $dinfo['status'] == 1 ? '已安装' : '已开启';
            }
            // 判断依赖的应用标识是否一致
            if ($dinfo['identifier'] != $v[1]) {
                $v[4] = '<span style="color:red">✘ '.$dependTips.'标识不匹配</span>';
                $info[$dependType][$k] = $v;
                continue;
            }
            // 版本对比
            if (version_compare($dinfo['version'], $v[2], $v[3]) === false) {
                $v[4] = '<span style="color:red">✘ 需要的版本必须' . $v[3] . $v[2] . '</span>';
                $info[$dependType][$k] = $v;
                continue;
            }
            $info[$dependType][$k] = $v;
        }
        return $info;
    }

    /**
     * 执行模块安装
     * @param  int $id 模块ID
     * @param  integer $clear 清空旧数据
     * @return bool|string
     */
    public function execInstall($id, $clear = 1)
    {
        $mod = ModuleModel::where('id', $id)->find();
        if (!$mod) {
            return '模块不存在';
        }
        if ($mod['status'] > 0) {
            return '请勿重复安装此模块';
        }
        $modPath = $this->appPath . $mod['name'] . '/';
        if (!file_exists($modPath . 'info.php')) {
            return '模块配置文件不存在[info.php]';
        }
        $info = include_once $modPath . 'info.php';
        $moduleClass = getModuleClass($mod['name']);
        if (!class_exists($moduleClass)) {
            return '模块类不存在';
        }
        $moduleObj = new $moduleClass;
        if (!$moduleObj->install()) {
            return '模块安装前的方法执行失败（原因：' . $moduleObj->getError . '）';
        }
        // 过滤系统表
        foreach ($info['tables'] as $t) {
            if (in_array($t, config('hi.tables'))) {
                return '模块数据表与系统表重复[' . $t . ']';
            }
        }

        // 导入安装SQL
        $sqlFile = realpath($modPath . 'sql/install.sql');
        if (file_exists((string)$sqlFile)) {
            $sql = file_get_contents($sqlFile);
            $sqlList = parseSql($sql, 0, [$info['db_prefix'] => env('database.prefix')]);
            if ($sqlList) {
                if ($clear == 1) {// 清空所有数据
                    foreach ($info['tables'] as $table) {
                        if (Db::query("SHOW TABLES LIKE '" . env('database.prefix') . $table . "'")) {
                            Db::execute('DROP TABLE IF EXISTS `' . env('database.prefix') . $table . '`;');
                        }
                    }
                }
                $sqlList = array_filter($sqlList);
                foreach ($sqlList as $v) {
                    // 过滤sql里面的系统表
                    foreach (config('hi.tables') as $t) {
                        if (stripos($v, '`' . env('database.prefix') . $t . '`') !== false) {
                            return 'install.sql文件含有系统表[' . $t . ']';
                        }
                    }
                    if (stripos($v, 'DROP TABLE') === false) {
                        try {
                            if ($clear == 0) { //选择不清空时，若遇到已存在的数据表，将沿用此数据表
                                foreach ($info['tables'] as $table) {
                                    if (stripos($v, env('database.prefix') .$table) && !Db::query("SHOW TABLES LIKE '" . env('database.prefix') . $table . "'")) {
                                        Db::execute($v);
                                    }
                                }
                            }else{
                                Db::execute($v);
                            }
                        } catch (\Exception $e) {
                            return $e->getMessage();
                        }
                    }
                }
            }
        }
        // 导入菜单
        if (file_exists($modPath . 'menu.php')) {
            $menus = include_once $modPath . 'menu.php';
            // 如果不是数组且不为空就当JSON数据转换
            if (!is_array($menus) && !empty($menus)) {
                $menus = json_decode($menus, 1);
            }
            if (MenuModel::importMenu($menus, $mod['name']) == false) {
                // 执行回滚
                MenuModel::where('module', $mod['name'])->delete();
                return '添加菜单失败，请重新安装';
            }
        }
        // 导入模块钩子
        if (!empty($info['hooks'])) {
            $hookModel = new HookModel;
            foreach ($info['hooks'] as $k => $v) {
                $map = [];
                $map['name'] = $k;
                $map['intro'] = $v;
                $map['source'] = 'module.' . $mod['name'];
                $hookModel->storage($map);
            }
        }
        Cache::tag('plugin_tag')->clear();

        // 导入语言包
        if (!empty($info['language'])) {
            foreach ($info['language'] as $k => $v) {
                $langs = include_once $modPath . 'lang/'.$v.'.php';
                if($langs){
                    $packId = Db::name('system_language')->insertGetId(['group'=>$mod['name'], 'name'=>$v, 'default'=> 0 == $k ? 1 : 0]);
                    if($packId){
                        LangModel::importLang($mod['name'], $packId, $langs);
                        cache('menu_trees', null);
                        Cache::tag('lang_'.$mod['name'])->clear();
                    }
                }
            }
        }

        // 导入模块配置
        if (isset($info['config']) && !empty($info['config'])) {
        
            foreach ($info['config'] as $value){
                foreach ($value['fields'] as &$v) {
                    $v['status'] = 1;
                    $v['create_time'] = time();
                    $v['update_time'] = time();
                    $v['group'] = $info['name'];
                    $v['options'] = isset($v['options'])?$v['options']:'';
                    $v['value'] = is_array($v['value']) ? arrayToStr($v['value'], ',') : $v['value'];
                    if (isset($v['options']) && $v['options'] && !in_array($v['type'], ['checkbox', 'select', 'radio', 'switch'])) {
                        $v['options'] = array_filter(parseAttr($v['options']));
                    }
                    if (!ConfigModel::insert($v)) {
                        return '导入模块配置失败(原因：可能是info.php文件参数异常)，请重新安装！';
                    }
                }
            }
    
        }
        // 更新模块基础信息
        $sqlmap = [];
        $sqlmap['title'] = $info['title'];
        $sqlmap['identifier'] = $info['identifier'];
        $sqlmap['intro'] = $info['intro'];
        $sqlmap['author'] = $info['author'];
        $sqlmap['url'] = $info['author_url'];
        $sqlmap['version'] = $info['version'];
        $sqlmap['status'] = 2;
        if(isset($info['theme']) && $info['theme']){
            $sqlmap['theme'] = $info['theme'];
        }
        if(isset($info['mobile_theme']) && $info['mobile_theme']){
            $sqlmap['mobile_theme'] = $info['mobile_theme'];
        }
        ModuleModel::where('id', $id)->update($sqlmap);
        ConfigModel::getConfigs('', true);
        Cache::tag('module_tag')->clear();
        if (!$moduleObj->installAfter()) {
            return '模块安装后的方法执行失败（原因：' . $moduleObj->getError . '）';
        }
        return true;
    }


    /**
     * 导入模块
     * @return mixed
     * @author 祈陌 <3411869134@qq.com>
     */
    public function import()
    {
        if ($this->request->isPost()) {
            $_file = $this->request->param('file');
            if (empty($_file)) {
                return $this->response(0,'请上传模块安装包');
            }
            $file = realpath('.' . $_file);
            if (ROOT_DIR != '/') {// 针对子目录处理
                $file = realpath('.' . str_replace(ROOT_DIR, $this->depr, $_file));
            }
            if (!file_exists((string)$file)) {
                return $this->response(0,'上传文件无效');
            }
            $decomPath = '.' . trim($_file, '.zip');
            if (!is_dir($decomPath)) {
                Dir::create($decomPath, 0777);
            }
            // 解压安装包到$decomPath
            $archive = new PclZip();
            $archive->PclZip($file);
            if (!$archive->extract(PCLZIP_OPT_PATH, $decomPath, PCLZIP_OPT_REPLACE_NEWER)) {
                Dir::delDir($decomPath);
                @unlink($file);
                return $this->response(0,'导入失败(' . $archive->error_string . ')');
            }
            if (!is_dir($decomPath . $this->depr . 'uploads' . $this->depr . 'app')) {
                Dir::delDir($decomPath);
                @unlink($file);
                return $this->response(0,'导入失败，安装包不完整(-1)');
            }
            // 获取模块名
            $files = Dir::getList($decomPath . $this->depr . 'uploads' . $this->depr . 'app'. $this->depr);
            if (!isset($files[0])) {
                Dir::delDir($decomPath);
                @unlink($file);
                return $this->response(0,'导入失败，安装包不完整(-2)');
            }
            $appName = $files[0];
            // 防止重复导入模块
            if (is_dir($this->appPath . $appName)) {
                Dir::delDir($decomPath);
                @unlink($file);
                return $this->response(0,'模块已存在');
            }
            // 应用目录
            $appPath = $decomPath .'/uploads/app/' . $appName .'/';

            // 获取安装包基本信息
            if (!file_exists($appPath . 'info.php')) {
                Dir::delDir($decomPath);
                @unlink($file);
                return $this->response(0,'安装包缺少[info.php]文件');
            }
            // 复制app目录
            if (!is_dir($this->appPath . $appName)) {
                Dir::create($this->appPath . $appName, 0777);
            }
            Dir::copyDir($appPath, $this->appPath . $appName);
            // 复制static目录
            if (!is_dir($appStatic='.'.$this->depr.'static' . $this->depr . 'm_' . $appName . $this->depr)) {
                Dir::create($appStatic, 0755);
            }
            Dir::copyDir($decomPath . $this->depr . 'uploads' . $this->depr . 'public' . $this->depr . 'static' . $this->depr . 'm_' . $appName, '.' . $this->depr . 'static' . $this->depr . 'm_' . $appName);
            // 删除临时目录和安装包
            Dir::delDir($decomPath);
            @unlink($file);
            Cache::tag('module_tag')->clear();
            return $this->response(1,'模块导入成功', (string)url('', ['status'=>0]));
        }
        $tabData = $this->tabData;
        $tabData['current'] = url('');
        $this->assign('tabData', $tabData);
        $this->assign('tabType', 3);
        return $this->view();
    }

    /**
     * 卸载模块
     * @return mixed
     * @author 祈陌 <3411869134@qq.com>
     */
    public function uninstall()
    {
        $id = getNum();
        $mod = ModuleModel::where('id', $id)->find();
        if (!$mod) {
            return $this->response(0,'模块不存在');
        }
        if ($mod['status'] == 0) {
            return $this->response(0,'模块未安装');
        }

        if ($this->request->isPost()) {
            $modPath = $this->appPath . $mod['name'] . '/';
            // 模块自定义配置
            if (!file_exists($modPath . 'info.php')) {
                return $this->response(0,'模块配置文件不存在[info.php]');
            }
            $info = moduleInfo( $mod['name']);
            $moduleClass = getModuleClass($mod['name']);
            if (!class_exists($moduleClass)) {
                return '模块类不存在';
            }
            $moduleObj = new $moduleClass;
            if (!$moduleObj->uninstall()) {
                return '模块卸载前的方法执行失败（原因：' . $moduleObj->getError . '）';
            }
            // 过滤系统表
            foreach ($info['tables'] as $t) {
                if (in_array($t, config('hi.tables'))) {
                    return $this->response(0,'模块数据表与系统表重复[' . $t . ']');
                }
            }
            $post = $this->request->post();
            // 导入SQL
            $sqlFile = realpath($modPath . 'sql/uninstall.sql');
            if (file_exists((string)$sqlFile) && $post['clear'] == 1) {
                $sql = file_get_contents($sqlFile);
                $sqlList = parseSql($sql, 0, [$info['db_prefix'] => env('database.prefix')]);
                if ($sqlList) {
                    $sqlList = array_filter($sqlList);
                    foreach ($sqlList as $v) {
                        // 防止删除整个数据库
                        if (stripos(strtoupper($v), 'DROP DATABASE') !== false) {
                            return $this->response(0,'uninstall.sql文件疑似含有删除数据库的SQL');
                        }
                        // 过滤sql里面的系统表
                        foreach (config('hi.tables') as $t) {
                            if (stripos($v, '`' . env('database.prefix') . $t . '`') !== false) {
                                return $this->response(0,'uninstall.sql文件含有系统表[' . $t . ']');
                            }
                        }
                        try {
                            Db::execute($v);
                        } catch (\Exception $e) {
                            return $e->getMessage();
                        }
                    }
                }
            }
            //删除每个角色拥有该模块的节点权限
            $moduleMenuIds = MenuModel::where('module', $mod['name'])->field('id')->select()->toArray();
            if($moduleMenuIds){
                $moduleMenuIds = array_column($moduleMenuIds, 'id');
                $roleIds = RoleModel::column('id');
                foreach ($roleIds as $roleId){
                    if(1 == $roleId) continue;
                    $roleAuth = RoleModel::getRoleAuth($roleId);
                    $intersectArr = array_intersect($roleAuth, $moduleMenuIds);
                    if(empty($intersectArr)) continue;
                    $newAuths = array_diff($roleAuth, $intersectArr);
                    sort($newAuths);
                    RoleModel::where('id', $roleId)->update(['auth'=>json_encode($newAuths)]);
                }
            }
            MenuModel::where('module', $mod['name'])->delete();
            // 删除当前模块菜单
            MenuModel::where('module', $mod['name'])->delete();
            // 删除模块钩子
            HookModel::where('source', 'module.' . $mod['name'])->delete();
            Cache::tag('plugin_tag')->clear();
            // 更新模块状态为未安装
            ModuleModel::where('id', $id)->update(['status' => 0, 'default' => 0]);
            //删除配置数据
            $model = new ConfigModel();
            if (!$model->del($mod['name'], 1)) {
                return $this->response(0,'模块配置信息删除失败');
            }
            //删除语言数据
            $res = LangModel::langClear($mod['name']);
            if($res){
                Cache::tag('menus')->clear();
                Cache::tag('lang_'.$mod['name'])->clear();
            }
            //更新缓存
            Cache::tag('menus')->clear();
            Cache::tag('module_tag')->clear();
            Cache::tag('hiphp_config')->clear();
            if (!$moduleObj->uninstallAfter()) {
                return '模块卸载后的方法执行失败（原因：' . $moduleObj->getError . '）';
            }
            return $this->response(1,'模块已卸载成功', (string)url('index', ['status'=>0]));
        }
        $this->assign('formData', $mod);
        return $this->view();
    }

    /**
     * 删除模块
     * @return mixed
     */
    public function del()
    {
        $id = getNum();
        $module = ModuleModel::where('id', $id)->find();
        if (!$module) {
            return $this->response(0,'模块不存在');
        }
        if ($module['name'] == 'system') {
            return $this->response(0,'禁止删除系统模块');
        }
        if ($module['status'] != 0) {
            return $this->response(0,'已安装的模块禁止删除');
        }
        // 删除模块文件
        $path = $this->appPath . $module['name'];
        if (is_dir($path) && Dir::delDir($path) === false) {
            return $this->response(0,'模块删除失败[' . $path . ']');
        }
        $error = '';
        // 删除模块相关附件
        $path = './static/m_' . $module['name'];
        if (is_dir($path) && Dir::delDir($path) === false) {
            $error .= '<br>模块删除失败[' . $path . ']';
        }
        $path = './uploads/m_' . $module['name'];
        if (is_dir($path) && Dir::delDir($path) === false) {
            $error .= '<br>模块删除失败[' . $path . ']';
        }
        // 删除模块记录
        ModuleModel::where('id', $id)->delete();
        // 删除菜单记录
        MenuModel::where('module', $module['name'])->delete();
        // 删除权限记录 TODO
        if ($error) {
            return $this->response(0, $error);
        }
        Cache::tag('module_tag')->clear();
        return $this->response(1,'模块删除成功');
    }

    /**
     * 设置默认模块
     * @return mixed
     */
    public function setDefault()
    {
        $id = $this->request->param('id/d');
        $val = $this->request->param('v/d');
        if ($val == 1) {
            $res = ModuleModel::where('id', $id)->find();
            if ($res['system'] == 1) {
                return $this->response(0,'禁止设置系统模块');
            }
            if ($res['status'] != 2) {
                return $this->response(0,'禁止设置未启用或未安装的模块');
            }
            ModuleModel::where('id > 0')->update(['default'=>0]);
            ModuleModel::where('id', $id)->update(['default'=>1]);
        } else {
            ModuleModel::where('id', $id)->update(['default'=>0]);
        }
        Cache::tag('module_tag')->clear();

        return $this->response(1,'操作成功', $this->rstr);
    }

    /**
     * 状态设置
     * @return mixed
     */
    public function status()
    {
        $val = $this->request->param('v/d');
        $id = getNum();
        $val = $val + 1;//layui开关只支持0和1
        if ($id == 1) {
            return $this->response(0,'禁止设置系统模块');
        }
        $res = ModuleModel::where('id', $id)->find();
        if ($res['status'] <= 0) {
            return $this->response(0,'只允许操作已安装模块');
        }
        $res = ModuleModel::where('id', $id)->update(['status'=>$val]);
        if ($res === false) {
            return $this->response(0,'操作失败');
        }
        Cache::tag('module_tag')->clear();

        return $this->response(1,'操作成功', $this->rstr);
    }

    /**
     * 主题管理
     * @return mixed
     */
    public function theme($id = 0)
    {
        $where = [];
        $where[] = ['status', '=', 2];
        if (is_numeric($id)) {
            $where[] = ['id', '=', $id];
        } else {
            $where[] = ['name', '=', $id];
        }
        $module = ModuleModel::where($where)->find();
        if (!$module) {
            return $this->response(0,'模块不存在或未安装');
        }
        $path = $this->appPath . $module['name'] . '/theme/';
        if (!is_dir($path)) {
            return $this->response(0,'模块主题不存在');
        }
        $themes = $this->getThemes($module['name']);
        $themesMobile = $this->getThemes($module['name'], 1);
        $this->assign('formData', $module);
        $this->assign('themes', $themes);
        $this->assign('themesMobile', $themesMobile);
        return $this->view();
    }

    private function getThemes($appName, $isMobile=0){
        $path = $this->appPath . $appName . '/theme/'.($isMobile == 1 ? 'mobile/':'');
        $theme = Dir::getList($path);
        $themes = [];
        foreach ($theme as $k => $v) {
            if (is_file($path . $v . '/config.json')) {
                $json = file_get_contents($path . $v . '/config.json');
                $themes[$k] = json_decode($json, 1);
            } elseif (is_file($path . $v . '/config.xml')) {
                $xml = file_get_contents($path . $v . '/config.xml');
                $themes[$k] = xml2array($xml);
            } else {
                continue;
            }
            $themes[$k]['sql'] = isset($themes[$k]['sql']) && $themes[$k]['sql'] ? $themes[$k]['sql'] : 0;
            if (is_file($path . $v . '/install.sql')) {
                $themes[$k]['sql'] = 1;
            }
            $themes[$k]['name'] = $v;
            $themes[$k]['thumb'] = '/static/m_' . $appName . '/' . ($isMobile == 1 ? 'mobile/':'') . $v . '/cover.png';

            if (!is_file('.'.$themes[$k]['thumb'])) {
                $themes[$k]['thumb'] = '/static/m_system/images/default_cover.png';
            }
        }
        return $themes;
    }

    /**
     * 执行主题SQL安装
     * @return mixed
     */
    public function exeSql()
    {
        $app = $this->request->param('app_name');
        $theme = $this->request->param('theme');
        $path = './template/' . $app . '/' . $theme . '/';
        if (!is_file($path . 'install.sql')) {
            return $this->response(0,'SQL文件不存在');
        }
        if (is_file($path . 'config.json')) {
            $json = file_get_contents($path . 'config.json');
            $config = json_decode($json, 1);
        } elseif (is_file($path . 'config.xml')) {
            $xml = file_get_contents($path . 'config.xml');
            $config = xml2array($xml);
        } else {
            return $this->response(0,'缺少配置文件');
        }
        if (!isset($config['db_prefix'])) {
            return $this->response(0,'配置文件缺少db_prefix配置');
        }
        $sql = file_get_contents($path . 'install.sql');
        $sqlList = parseSql($sql, 0, [$config['db_prefix'] => config('database.connections.mysql.prefix')]);
        if ($sqlList) {
            $sqlList = array_filter($sqlList);
            foreach ($sqlList as $v) {
                // 防止删除整个数据库
                if (stripos(strtoupper($v), 'DROP DATABASE') !== false) {
                    return $this->response(0,'install.sql文件疑似含有删除数据库的SQL');
                }
                // 过滤sql里面的系统表
                foreach (config('hi_system.tables') as $t) {
                    if (stripos($v, '`' . config('database.connections.mysql.prefix') . $t . '`') !== false) {
                        return $this->response(0,'install.sql文件含有系统表[' . $t . ']');
                    }
                }
                try {
                    Db::execute($v);
                } catch (\Exception $e) {
                    return $this->response(0,$e->getMessage());
                }
            }
        }
        return $this->response(1,'导入成功');
    }

    /**
     * 设置默认主题
     * @return mixed
     */
    public function setDefaultTheme($id = 0, $theme = '')
    {
        if (empty($theme)) {
            return $this->response(0,'参数传递错误');
        }
        $module = ModuleModel::where(['id' => $id, 'status' => 2])->find();
        if (!$module) {
            return $this->response(0,'模块不存在或未安装');
        }
        $mobile = $this->request->param('mobile');
        $updateData = isset($mobile) && $mobile == 1 ? ['mobile_theme'=>$theme] : ['theme'=>$theme];
        $res = ModuleModel::where('id', $id)->update($updateData);
        if (!$res) {
            return $this->response(0,'设置默认主题失败');
        }
        Cache::tag('module_tag')->clear();
        return $this->response(1,'设置成功');
    }

    /**
     * 删除主题
     * @return mixed
     */
    public function delTheme($id = 0, $theme = '')
    {
        if (empty($theme)) {
            return $this->response(0,'参数传递错误');
        }
        $module = ModuleModel::where(['id' => $id, 'status' => 2])->find();
        if (!$module) {
            return $this->response(0,'模块不存在或未安装');
        }
        $path = $this->appPath. $module['name'] . '/theme/';
        Dir::delDir($path . $theme);
        Cache::tag('module_tag')->clear();
        return $this->response(1,'删除成功');
    }

    /**
     * 生成目录
     * @param array $list 目录列表
     */
    private function mkDir($list)
    {
        foreach ($list as $dir) {
            if (!is_dir(root_path() . $dir)) {
                Dir::create(root_path() . $dir);
            }
        }
        
    }

    /**
     * 添加模块菜单
     * @param array $data 菜单数据
     * @param string $mod 模型名称
     * @param int $pid 父ID
     * @return bool
     */
    private function addMenu($data = [], $mod = '', $pid = 0)
    {
        if (empty($data)) {
            return false;
        }
        foreach ($data as $v) {
            $v['pid'] = $pid;
            $childs = $v['childs'];
            unset($v['childs']);
            $modObj = new MenuModel();
            $res = $modObj->storage($v);
            if (!$res) {
                return false;
            }
            if (!empty($childs)) {
                $this->addMenu($childs, $mod, $res['id']);
            }
        }
        return true;
    }

    /**
     * 检查表是否存在
     * @param array $list 目录列表
     * @return array
     */
    private function checkTable($tables = [])
    {
        $res = [];
        foreach ($tables as $k => $v) {
            $res[$k]['table'] = env('database.prefix') . $v;
            $res[$k]['exist'] = '<span style="color:green">✔︎</span>';
            if (Db::query("SHOW TABLES LIKE '" . env('database.prefix') . $v . "'")) {
                $res[$k]['exist'] = '<strong style="color:red">表名已存在</strong>';
            }
        }
        return $res;
    }

    /**
     * 生成模块信息文件
     */
    private function mkInfo($data = [])
    {
        // 配置内容
        $config = <<<INFO
<?php
// +----------------------------------------------------------------------
// | hiPHP框架[基于ThinkPHP5.1开发]
// +----------------------------------------------------------------------
// | Copyright (c) http://www.hiphp.net
// +----------------------------------------------------------------------
// | hiPHP承诺基础框架永久免费开源，您可用于学习和商用，但必须保留软件版权信息。
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------
/**
 * 模块基本信息
 */
return [
    // 模块名[必填]
    'name'        => '{$data['name']}',
    // 模块标题[必填]
    'title'       => '{$data['title']}',
    // 模块唯一标识[必填]，格式：模块名.[应用市场ID].module.[应用市场分支ID]
    'identifier'  => '{$data['identifier']}',
    // 主题模板[必填]，默认default
    'theme'        => 'default',
    // 模块图标[选填]
    'icon'        => '{$data['icon']}',
    // 模块简介[选填]
    'intro' => '{$data['intro']}',
    // 开发者[必填]
    'author'      => '{$data['author']}',
    // 开发者网址[选填]
    'author_url'  => '{$data['url']}',
    // 版本[必填],格式采用三段式：主版本号.次版本号.修订版本号
    // 主版本号【位数变化：1-99】：当模块出现大更新或者很大的改动，比如整体架构发生变化。此版本号会变化。
    // 次版本号【位数变化：0-999】：当模块功能有新增或删除，此版本号会变化，如果仅仅是补充原有功能时，此版本号不变化。
    // 修订版本号【位数变化：0-999】：一般是 Bug 修复或是一些小的变动，功能上没有大的变化，修复一个严重的bug即发布一个修订版。
    'version'     => '{$data['version']}',
    // 模块依赖[可选]，格式[[模块名, 模块唯一标识, 依赖版本, 对比方式]]
    'module_depend' => {$data['module_depend']},
    // 插件依赖[可选]，格式[[插件名, 插件唯一标识, 依赖版本, 对比方式]]
    'plugin_depend' => {$data['plugin_depend']},
    // 模块数据表[有数据库表时必填,不包含表前缀]
    'tables' => {$data['tables']},
    // 原始数据库表前缀,模块带sql文件时必须配置
    'db_prefix' => '{$data['db_prefix']}',
    // 模块预埋钩子[非系统钩子，必须填写]
    'hooks' => {$data['hooks']},
    // 模块配置，格式['sort' => '100','title' => '配置标题','name' => '配置名称','type' => '配置类型','options' => '配置选项','value' => '配置默认值', 'tips' => '配置提示'],各参数设置可参考管理后台->系统->系统功能->配置管理->添加
    'config' => {$data['config']},
];
INFO;
        return file_put_contents($this->appPath . $data['name'] . '/info.php', $config);
    }
}