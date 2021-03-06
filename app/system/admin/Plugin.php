<?php declare(strict_types=1);
namespace app\system\admin;
use app\system\model\SystemComponent as ComponentModel;
use app\system\model\SystemConfig as ConfigModel;
use app\system\model\SystemPlugin as PluginModel;
use app\system\model\SystemHookPlugin as HookPluginModel;
use app\system\model\SystemMenu as MenuModel;
use app\system\model\SystemLang as LangModel;
use app\system\model\SystemRole as RoleModel;
use app\system\model\SystemTheme as ThemeModel;
use think\facade\Cache;
use think\facade\Db;
use hi\Dir;
use hi\PclZip;
use think\Validate;

/**
 * 插件管理控制器
 * @package app\system\admin
 */
class Plugin extends Base
{
    public $tabData = [];

    /**
     * 初始化方法
     */
    protected function initialize()
    {
        parent::initialize();

        $tabData['tab'] = [
            [
                'title' => '已启用',
                'url' => url('index', ['status' => 2]),
            ],
            [
                'title' => '已停用',
                'url' => url('index', ['status' => 1]),
            ],
            [
                'title' => '待安装',
                'url' => url('index', ['status' => 0]),
            ],
            [
                'title' => '待升级',
                'url' => url('index', ['status' => 3]),
            ],
            [
                'title' => '待下载',
                'url' => url('index', ['status'=>4]),
            ],
            [
                'title' => '导入插件',
                'url' => url('import'),
            ],
        ];
        $this->tabData = $tabData;
        $this->appPath = base_path();
        $this->rootPath = root_path();
        //重定向地址解析
        $tmparr = parse_url($_SERVER["HTTP_REFERER"]);
        $this->rstr = empty($tmparr['scheme']) ? 'http://' : $tmparr['scheme'] . '://';
        $this->rstr .= $tmparr['host'] . $tmparr['path'];
    }

    /**
     * 插件管理首页
     * @return mixed
     *
     */
    public function index()
    {
        $status = $this->request->param('status/d', 2);
        $tabData = $this->tabData;
        $tabData['current'] = url('', ['status' => $status]);
        $map = [];
        if($status < 3){
            $map['status'] = $status;
        }
        $map['system'] = 0;
        $plugins = PluginModel::where($map)->order('sort,id')->column('id,title,author,intro,icon,system,app_id,app_keys,identifier,name,version,status,theme');
        if ($status == 0) {
            $pluginsPath = $this->rootPath . 'plugins/';
            // 自动将本地未入库的插件导入数据库
            $allPlugins = PluginModel::order('sort,id')->column('id,name', 'name');
            $files = Dir::getList($pluginsPath);
            foreach ($files as $k => $f) {
                // 排除已存在数据库的插件
                if (array_key_exists($f, $allPlugins) || !is_dir($pluginsPath . $f)) {
                    continue;
                }
                if (file_exists($pluginsPath . $f . '/info.php')) {
                    $info = include_once $pluginsPath . $f . '/info.php';
                    $sql = [];
                    $sql['name'] = $info['name'];
                    $sql['identifier'] = $info['identifier'];
                    if(isset($info['theme']) && $info['theme']){
                        $sql['theme'] = $info['theme'];
                    }
                    if(isset($info['mobile_theme']) && $info['mobile_theme']){
                        $sql['mobile_theme'] = $info['mobile_theme'];
                    }
                    $sql['title'] = $info['title'];
                    $sql['intro'] = $info['intro'];
                    $sql['author'] = $info['author'];
                    $sql['icon'] = $info['icon'] ? ROOT_DIR . substr($info['icon'], 1) : '';
                    $sql['version'] = $info['version'];
                    $sql['url'] = $info['author_url'];
                    $sql['status'] = 0;
                    $sql['system'] = 0;
                    $db = PluginModel::create($sql);
                    $sql['id'] = $db->id;
                    $plugins = array_merge($plugins, [$sql]);
                }
            }
        }
        if($status == 3 || $status == 4){
            $components = ComponentModel::where('app_type',0)->order('sort,id')->column('id,title,author,intro,app_id,app_keys,name,version,status');
            foreach ($components as $v){
                $plugins[] = $v;
            }
            $themes = ThemeModel::where('app_type',1)->order('sort,id')->column('id,title,author,intro,app_id,app_keys,name,version,status');
            foreach ($themes as $v){
                $plugins[] = $v;
            }
            $pushs = runHook('cloud_push', ['type'=>'plugin', 'method'=>$status == 3 ? 'upgrade' : 'download'], true);
            if($pushs && $pushs[0]){
                $upgrades = [];
                foreach ($plugins as $k=>$v){
                    foreach ($pushs[0] as $kk=>$vv){
                        $dependAppIns = in_array($vv['app_type'], ['component', 'theme']);
                        if($status == 3){
                            if($v['name'] == $vv['app_name']){
                                if($v['app_keys'] && $v['app_id'] && $v['app_keys'] == $vv['app_key'] && $v['app_id'] == $vv['app_id'] && version_compare($vv['version'], $v['version'],'>')){
                                    $upgrades[$k] = $v;
                                    $upgrades[$k]['app_name'] = $v['name'];
                                    $upgrades[$k]['up_version'] = $vv['version'];
                                    $upgrades[$k]['status'] = 3;
                                    $upgrades[$k]['app_type'] = $vv['app_type'];
                                    $upgrades[$k]['version'] = $vv['version'];
                                    $upgrades[$k]['type'] = $vv['type'];
                                    $upgrades[$k]['file_size'] = $vv['file_size'];
                                    if($dependAppIns){
                                        $upgrades[$k]['title'] = $vv['title'];
                                        $upgrades[$k]['system'] = 0;
                                    }
                                    if(isset($vv['theme_name'])){
                                        $upgrades[$k]['theme_name'] = $vv['theme_name'];
                                    }
                                    $upgrades[$k]['app_title'] = $vv['app_title'];
                                }else{
                                    $classModel = '\app\system\model\System'.ucfirst($vv['app_type']);
                                    $classModel::where('name', $v['name'])->update(['app_id'=>$vv['app_id'], 'app_keys'=>$vv['app_key']]);
                                }
                            }
                            $plugins = $upgrades;
                        }else{
                            if(($v['name'] && $v['name'] == $vv['app_name'] && !$dependAppIns) || ($dependAppIns && $v['app_id'] && $v['app_id'] == $vv['app_id'])){
                                unset($pushs[0][$kk]);
                            }else{
                                $pushs[0][$kk]['system'] = 0;
                                $pushs[0][$kk]['status'] = -1;
                            }
                        }
                    }
                }
                if($status == 4){
                    $plugins = $pushs && isset($pushs[0]) ? $pushs[0] : [];
                }
            }else{
                $plugins = [];
            }
        }

        $this->assign('page', ['page'=>$status]);
        $this->assign('emptyTips', '<div class="hi-no-data-tips" style="padding: 50px 0;text-align: center">未发现相关插件，快去<a target="_blank" href="' . config('clouds.store_push_domain') . '"> <strong style="color:#428bca">应用市场</strong> </a>看看吧！</div>');
        $this->assign('dataInfo', array_values($plugins));
        $this->assign('tabData', $tabData);
        $this->assign('tabType', 3);
        return $this->view();
    }

    /**
     * 插件设计
     * @return mixed
     */
    public function design()
    {
        if (config('system.app_debug') == 0) {
            return $this->response(0, '非开发模式禁止使用此功能');
        }
        if ($this->request->isPost()) {
            $model = new PluginModel();
            if (!$model->design($this->request->post())) {
                return $this->response(0, $model->getError());
            }
            return $this->response(1, '插件已生成完毕', (string)url('index?status=0'));
        }
        $tabData = [];
        $tabData['menu'] = [
            ['title' => '插件设计'],
            ['title' => '插件配置'],
            // ['title' => '插件菜单'],
        ];

        $this->assign('hiTabData', $tabData);
        $this->assign('hiTabType', 2);
        return $this->view();
    }

    /**
     * 插件配置
     * @return mixed
     */
    public function setting($plugin = '', $group = '', $tab = 0)
    {
        //保存数据
        if ($this->request->isPost()) {
            if(ADMIN_ID != 1){
                return $this->response(0,'非超级管理员禁止修改');
            }
            $data = $this->request->post();
            if (!isset($data['id']) || !isset($data['group'])) {
                return $this->response(0, '参数格式错误');
            }
            unset($data['upload']);  //清除上传字段
            $validate = new Validate([
                '__token__' => 'token',
            ]);
            if (!$validate->check($data)) {
                return $this->response(0, $validate->getError());
            }
            $group = $data['group'];
            $currConfigs = $fields = [];
            $fields = ConfigModel::where(['group' => $group])->column('name');
            $pluginsInfo = pluginInfo($group);
            if (!isset($pluginsInfo['config']) || !$pluginsInfo['config']) {
                return $this->response(0, '此插件无需配置');
            }
            foreach ($pluginsInfo['config'] as $key => $v) {
                parse_str(parse_url((string)$v['url'])['query'], $urlArray);
                if ($urlArray['tab'] == $tab) {
                    $currConfigs = $v['fields'];
                    break;
                }
            }
            if (!$currConfigs) {
                return $this->response(0, '此插件配置格式错误');
            }
            $addFields = $updateFields = [];
            unset($data['__token__']);
            unset($data['tab']);
            unset($data['group']);
            unset($data['id']);
            foreach ($data as $key => $v) {
                if (in_array($key, $fields)) {
                    $updateFields[$key] = $v;
                } else {
                    $addFields[$key] = $v;
                }
            }
            if ($addFields) {
                $insertData = [];
                foreach ($currConfigs as &$v) {
                    if (array_key_exists($v['name'], $addFields)) {
                        $v['status'] = 1;
                        $v['create_time'] = time();
                        $v['update_time'] = time();
                        $v['value'] = is_array($addFields[$v['name']]) ? arrayToStr($addFields[$v['name']], ',') : $addFields[$v['name']];
                        if (isset($v['options']) && $v['options'] && !in_array($v['type'], ['checkbox', 'select', 'radio', 'switch'])) {
                            $v['options'] = array_filter(parseAttr($v['options']));
                        }else{
                            $v['options'] = '';
                        }
                        $insertData[] = $v;
                    }
                }
                if (!ConfigModel::insertAll($insertData)) {
                    return $this->response(0, '保存失败');
                }
            }
            if ($updateFields) {
                foreach ($currConfigs as &$v) {
                    if (array_key_exists($v['name'], $updateFields)) {
                        $value = $v['type'] == 'checkbox' ? implode(',', $updateFields[$v['name']]) : $updateFields[$v['name']];
                        $result = ConfigModel::where(['name'=>$v['name'], 'group' => $group])->update(['value'=>$value]);
                    } else {
                        if ($v['type'] == 'switch') {
                            $result = ConfigModel::where(['name'=>$v['name'], 'group' => $group])->update(['value'=>0]);
                        }
                        if ($v['type'] == 'checkbox') {
                            $result = ConfigModel::where(['name'=>$v['name'], 'group' => $group])->update(['value'=>'']);
                        }
                    }
                }
            }
            ConfigModel::getConfigs('', true);
            Cache::tag('hiphp_config')->clear();
            return $this->response(1, '保存成功');
        }
        $row = PluginModel::where(['name'=>$plugin])->field('id,name,title')->find();
        $group = $group ? $group : $row['name'];
        $pluginsInfo = pluginInfo($row['name']);
        if (!isset($pluginsInfo['config']) || !$pluginsInfo['config']) {
            return $this->response(0, '此插件无需配置');
        }

        //显示数据
        $info = [];
        if ($pluginsInfo['config']) {
            $config = $pluginsInfo['config'];
            if (isset($config[0]['fields']) && is_array($config[0]['fields']) && !empty($config[0]['fields'])) {
                $tabData['current'] = url('', ['group'=>$group, 'tab'=>$tab, 'plugin'=>$plugin]);
                $map = [];
                $map['group'] = $group;
                $map['status'] = 1;
                $dataList = ConfigModel::where($map)->column('name,group,value,options');
                $newDataList = [];
                foreach ($dataList as $k => $v) {
                    $newDataList[$v['name']] = $v['value'];
                }
                foreach ($config as $key => $v) {
                    //tab菜单数据加载
                    $tabData['tab'][$key]['title'] = $v['title'];
                    $tabData['tab'][$key]['url'] = $v['url'] . '&plugin=' . $plugin;
                    //tab表单数据加载
                    parse_str(parse_url((string)$v['url'])['query'], $urlArray);
                    if ($urlArray['tab'] == $tab) {
                        $info[$key] = $v;
                        foreach ($info[$key]['fields'] as &$vv) {
                            if (isset($vv['options']) && $vv['options']) {
                                $options = $vv['options'];
                                $vv['options'] = array_filter(parseAttr($options));
                            }
                            if (array_key_exists($vv['name'], $newDataList)) {
                                if ($newDataList[$vv['name']]) {
                                    $vv['value'] = $newDataList[$vv['name']];
                                    if ($vv['type'] == 'array') {
                                        $vv['value'] = $newDataList[$vv['name']];
                                    }
                                }
                            } else { //存在于info.php但不存在数据库的字段,会默认使用info.php里面的字段默认值
                                if ($vv['type'] == 'array') {
                                    $vv['value'] = str_replace('\r\n', "\r\n", $vv['value']);
                                }
                            }
                        }
                    }
                }
                $this->assign('tabData', $tabData);
                $this->assign('tabType', 3);
            } else {
                return $this->response(0, '此插件配置文件格式不正确');
            }
        }
        $formData['plugin'] = $plugin;
        $formData['config'] = $info;
        $formData['name'] = $row['name'];
        $this->assign('group', $group);
        $this->assign('tab', $tab);
        $this->assign('formData', $formData);
        return $this->view();
    }

    /**
     * 安装插件
     */
    public function install($id = 0)
    {
        if ($this->request->isPost()) {
            $postData = $this->request->post();
            $result = $this->execInstall($id, $postData['clear']);
            if ($result !== true) {
                return $this->response(0, $result);
            }
            Cache::tag('menus')->clear();
            return $this->response(1, '插件已安装成功', (string)url('index?status=2'));
        }
        $plug = PluginModel::where('id', $id)->find();
        if (!$plug) {
            return $this->response(0,'插件不存在');
        }
        if ($plug['status'] > 0) {
            return $this->response(0,'请勿重复安装此插件');
        }
        $plugPath = $this->rootPath .'plugins/'. $plug['name'] . '/';
        // 插件自定义配置
        if (!file_exists($plugPath . 'info.php')) {
            return $this->response(0,'插件配置文件不存在[info.php]');
        }
        $info = pluginInfo($plug['name']);
        // 模块依赖检查
        if(isset($info['module_depend']) && $info['module_depend']){
            $info = $this->checkAppDepend('module_depend', $info);
        }
        // 插件依赖检查
        if(isset($info['plugin_depend']) && $info['plugin_depend']){
            $info = $this->checkAppDepend('plugin_depend', $info);
        }
        $info['id'] = $plug['id'];
        $this->assign('tables', $this->checkTable($info['tables']));
        $this->assign('formData', $info);
        return $this->view();
    }

    /**
     * 执行插件安装
     * @access public
     * @param int $id 插件ID
     * @return bool|string
     */
    public function execInstall($id, $clear = 1)
    {
        $plug = PluginModel::where('id', $id)->find();
        if (!$plug) {
            return '插件不存在';
        }
        if ($plug['status'] > 0) {
            return '请勿重复安装此插件';
        }

        $plugPath = $this->rootPath . 'plugins/' . $plug['name'] . '/';
        if (!file_exists($plugPath . 'info.php')) {
            return '插件文件[info.php]丢失';
        }
        $info = include_once $plugPath . 'info.php';
        $pluginClass = getPluginClass($plug['name']);
        if (!class_exists($pluginClass)) {
            return '插件不存在';
        }
        $pluginObj = new $pluginClass;
        if (!$pluginObj->install()) {
            return '插件安装前的方法执行失败（原因：' . $pluginObj->getError . '）';
        }
        // 将插件钩子注入到钩子索引表
        if (isset($pluginObj->hooks) && !empty($pluginObj->hooks)) {
            $hooks = cache('hooks');
//            foreach ($pluginObj->hooks as $v){
//                if(isset($hooks[$v])) return '出现重复命名钩子';
//            }
            if (!HookPluginModel::storage($pluginObj->hooks, $plug['name'])) {
                return '安装插件钩子时出现错误，请重新安装';
            }
        }

        // 导入SQL
        $sqlFile = realpath($plugPath . 'sql/install.sql');
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
        if (file_exists($plugPath . 'menu.php')) {
            $menus = include_once $plugPath . 'menu.php';
            // 如果不是数组且不为空就当JSON数据转换
            if (!is_array($menus) && !empty($menus)) {
                $menus = json_decode($menus, 1);
            }
            if (MenuModel::importMenu($menus, 'plugin.' . $plug['name'], 'plugin') == false) {
                // 执行回滚
                MenuModel::where('module', 'plugin.' . $plug['name'])->delete();
                return '插件菜单失败(原因：可能是param参数异常)，请重新安装！';
            }
        }
        // 导入插件配置信息
        if (isset($info['config']) && !empty($info['config'])) {
            foreach ($info['config'] as $value) {
                foreach ($value['fields'] as &$v) {
                    $v['status'] = 1;
                    $v['create_time'] = time();
                    $v['update_time'] = time();
                    $v['group'] = $info['name'];
                    $v['options'] = isset($v['options'])?$v['options']:'';
                    $v['value'] = is_array($v['value']) ? arrayTostr($v['value'], ',') : $v['value'];
                    if (isset($v['options']) && $v['options'] && !in_array($v['type'], ['checkbox', 'select', 'radio', 'switch'])) {
                        $v['options'] = array_filter(parseAttr($v['options']));
                    }
                    if (!ConfigModel::insert($v)) {
                        return '导入插件配置失败(原因：可能是info.php文件参数异常)，请重新安装！';
                    }
                }
            }
           
        }
        // 更新插件基础信息
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
        PluginModel::where('id', $id)->update($sqlmap);
        ConfigModel::getConfigs('', true);
        // 导入语言包数据
        if (!empty($info['language'])) {
            foreach ($info['language'] as $k => $v) {
                $langs = include_once $plugPath . 'lang/'.$v.'.php';
                if($langs){
                    $packId = Db::name('system_language')->insertGetId(['group'=>$plug['name'], 'name'=>$v, 'default'=> 0 == $k ? 1 : 0]);
                    if($packId){
                        LangModel::importLang($plug['name'], $packId, $langs);
                    }
                }
            }
        }
        if (!$pluginObj->installAfter()) {
            return '插件安装后的方法执行失败（原因：' . $pluginObj->getError() . '）';
        }
        Cache::tag('plugin_tag')->clear();
        Cache::tag('menus')->clear();
        Cache::tag('lang_'.$plug['name'])->clear();
        return true;
    }

    /**
     * 卸载插件
     */
    public function uninstall($id = 0)
    {
        $plug = PluginModel::where('id', $id)->find();
        if (!$plug) {
            return $this->response(0, '插件不存在');
        }
        if ($plug['status'] == 0) {
            return $this->response(0, '插件未安装');
        }
        if ($this->request->isPost()) {
            $plugPath = root_path() . 'plugins/' . $plug['name'] . '/';

            // 插件基本信息
            if (!file_exists($plugPath . 'info.php')) {
                return $this->response(0, '插件文件[info.php]丢失');
            }
            $info = include_once $plugPath . 'info.php';

            $pluginClass = getPluginClass($plug['name']);
            if (!class_exists($pluginClass)) {
                return $this->response(0, '插件不存在');
            }

            $pluginObj = new $pluginClass;

            if (!$pluginObj->uninstall()) {
                return $this->response(0, '插件卸载前的方法执行失败（原因：' . $pluginObj->getError() . '）');
            }
            if (!HookPluginModel::del($plug['name'])) {
                return $this->response(0, '插件相关钩子删除失败');
            }
            // 导入SQL
            $sqlFile = realpath($plugPath . 'sql/uninstall.sql');
            $post = $this->request->post();
            if (file_exists((string)$sqlFile) && $post['clear'] == 1) {
                $sql = file_get_contents($sqlFile);
                $sqlList = parseSql($sql, 0, [$info['db_prefix'] => env('database.prefix')]);
                if ($sqlList) {
                    $sqlList = array_filter($sqlList);
                    foreach ($sqlList as $v) {
                        // 防止删除整个数据库
                        if (stripos(strtoupper($v), 'DROP DATABASE') !== false) {
                            return $this->response(0, 'uninstall.sql文件疑似含有删除数据库的SQL');
                        }
                        // 过滤sql里面的系统表
                        foreach (config('hi.tables') as $t) {
                            if (stripos($v, '`' . env('database.prefix') . $t . '`') !== false) {
                                return $this->response(0, 'uninstall.sql文件含有系统表[' . $t . ']');
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
            //删除每个角色拥有该插件的节点权限
            $pluginMenuIds = MenuModel::where('module', 'plugin.' . $plug['name'])->field('id')->select()->toArray();
            if ($pluginMenuIds) {
                $pluginMenuIds = array_column($pluginMenuIds, 'id');
                $roleIds = RoleModel::column('id');
                foreach ($roleIds as $roleId) {
                    if (1 == $roleId) continue;
                    $roleAuth = RoleModel::getRoleAuth($roleId);
                    $intersectArr = array_intersect($roleAuth, $pluginMenuIds);
                    if (empty($intersectArr)) continue;
                    $newAuths = array_diff($roleAuth, $intersectArr);
                    sort($newAuths);
                    RoleModel::where('id', $roleId)->update(['auth' => json_encode($newAuths)]);
                }
            }
            // 删除插件菜单
            MenuModel::where('module', 'plugin.' . $plug['name'])->delete();
            // 更新插件状态为未安装
            PluginModel::where('id', $id)->update(['status' => 0]);
            //删除插件配置数据
            $model = new ConfigModel();
            if (!$model->del($plug['name'])) {
                return $this->response(0, '插件配置信息删除失败');
            }
            //删除语言数据
            $res = LangModel::langClear($plug['name']);
            if ($res) {
                Cache::tag('menus')->clear();
                Cache::tag('lang_' . $plug['name'])->clear();
            }
            //更新缓存
            Cache::tag('menus')->clear();
            Cache::tag('plugin_tag')->clear();
            Cache::tag('hiphp_config')->clear();
            if (!$pluginObj->uninstallAfter()) {
                return $this->response(0, '插件卸载后的方法执行失败（原因：' . $pluginObj->getError() . '）');
            }
            return $this->response(1, '插件已卸载成功', (string)url('index?status=0'));
        }
        $this->assign('formData', $plug);
        return $this->view();
    }

    /**
     * 导入插件
     * @return mixed
     */
    public function import()
    {
        if ($this->request->isPost()) {
            $_file = $this->request->param('file');
            if (empty($_file)) {
                return $this->response(0, '请上传插件安装包');
            }
            $file = realpath('.' . $_file);
            if (ROOT_DIR != '/') {// 针对子目录处理
                $file = realpath('.' . str_replace(ROOT_DIR, '/', $_file));
            }
            if (!file_exists($file)) {
                return $this->response(0, '上传文件无效');
            }
            $decomPath = '.' . trim(str_replace(ROOT_DIR, '/', $_file), '.zip');

            if (!is_dir($decomPath)) {
                Dir::create($decomPath, 0777);
            }
            // 解压安装包到$decomPath
            $archive = new PclZip();
            $archive->PclZip($file);
            if (!$archive->extract(PCLZIP_OPT_PATH, $decomPath, PCLZIP_OPT_REPLACE_NEWER)) {
                Dir::delDir($decomPath);
                @unlink($file);
                return $this->response(0, '导入失败(' . $archive->error_string . ')');
            }
            // 获取插件名
            $files = Dir::getList($decomPath . '/uploads/plugins/');

            if (!isset($files[0])) {
                Dir::delDir($decomPath);
                @unlink($file);
                return $this->response(0, '导入失败，安装包不完整');
            }
            $appName = $files[0];
            // 防止重复导入插件
            if (is_dir($this->rootPath . 'plugins/' . $appName)) {
                Dir::delDir($decomPath);
                @unlink($file);
                return $this->response(0, '插件已存在');
            } else {
                Dir::create($this->rootPath . 'plugins/' . $appName, 0777);
            }
            // 复制插件
            Dir::copyDir($decomPath . '/uploads/plugins/' . $appName . '/', $this->rootPath . 'plugins/' . $appName);
            // 复制静态资源
            Dir::copyDir($decomPath . '/uploads/public/static/p_' . $appName, './static/p_' . $appName);
            // 删除临时目录和安装包
            Dir::delDir($decomPath);
            @unlink($file);
            return $this->response(1, '插件导入成功', (string)url('index?status=0'));
        }
        $tabData = $this->tabData;
        $tabData['current'] = url();
        $this->assign('tabData', $tabData);
        $this->assign('tabType', 3);
        return $this->view();
    }

    public function status()
    {
        $val = $this->request->param('v/d');
        $id = $this->request->param('id/d');
        $val = $val + 1;// 因为layui开关效果只支持0和1

        $res = PluginModel::where('id', $id)->find();

        if ($res['status'] <= 0) {
            return $this->response(0, '只允许操作已安装插件');
        }
        $res = PluginModel::where('id', $id)->update(['status'=>$val]);
        if ($res === false) {
            return $this->response(0, '操作失败', 'index');
        }
        Cache::tag('plugin_tag')->clear();

        return $this->response(1, '操作成功', $this->rstr);
    }

    public function del($id = 0)
    {
        $plug = PluginModel::where('id', $id)->find();
        if (!$plug) {
            return $this->response(0, '插件不存在');
        }
        if ($plug['status'] != 0) {
            return $this->response(0, '请先卸载插件[' . $plug['name'] . ']！');
        }
        if (Dir::delDir(root_path() . 'plugins/' . $plug['name']) === false) {
            return $this->response(0, '插件目录失败(原因：可能没有权限)！');
        }
        // 删除插件静态资源目录
        Dir::delDir('./static/p_' . $plug['name']);

        if (!PluginModel::where('id', $id)->delete()) {
            return $this->response(0, '插件数据删除失败');
        }
        Cache::tag('plugin_tag')->clear();
        return $this->response(1, '插件删除成功', $this->rstr);
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
        $module = PluginModel::where($where)->find();
        if (!$module) {
            return $this->response(0,'插件不存在或未安装');
        }
        $path = $this->rootPath . 'plugins/' . $module['name'] . '/theme/';
        if (!is_dir($path)) {
            return $this->response(0,'插件主题不存在');
        }
        $themes = $this->getThemes($module['name']);
        $themesMobile = $this->getThemes($module['name'], 1);
        $this->assign('formData', $module);
        $this->assign('themes', $themes);
        $this->assign('themesMobile', $themesMobile);
        return $this->view();
    }

    private function getThemes($appName, $isMobile=0){
        $path = $this->rootPath .'plugins/' . $appName . '/theme/'.($isMobile == 1 ? 'mobile/':'');
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
            $themes[$k]['thumb'] = '/static/p_' . $appName . '/' . ($isMobile == 1 ? 'mobile/':'') . $v . '/cover.png';

            if (!is_file('.'.$themes[$k]['thumb'])) {
                $themes[$k]['thumb'] = '/static/m_system/images/default_cover.png';
            }
        }
        return $themes;
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
        $module = PluginModel::where(['id' => $id, 'status' => 2])->find();
        if (!$module) {
            return $this->response(0,'插件不存在或未安装');
        }
        $mobile = $this->request->param('mobile');
        $updateData = isset($mobile) && $mobile == 1 ? ['mobile_theme'=>$theme] : ['theme'=>$theme];
        $res = PluginModel::where('id', $id)->update($updateData);
        if (!$res) {
            return $this->response(0,'设置默认主题失败');
        }
        Cache::tag('plugin_tag')->clear();
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
        $module = PluginModel::where(['id' => $id, 'status' => 2])->find();
        if (!$module) {
            return $this->response(0,'插件不存在或未安装');
        }
        $path = $this->appPath. $module['name'] . '/theme/';
        Dir::delDir($path . $theme);
        return $this->response(1,'删除成功');
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
                $v[4] = '<span class="red">✘ '.$dependTips.'标识不匹配</span>';
                $info[$dependType][$k] = $v;
                continue;
            }
            // 版本对比
            if (version_compare($dinfo['version'], $v[2], $v[3]) === false) {
                $v[4] = '<span class="red">✘ 需要的版本必须' . $v[3] . $v[2] . '</span>';
                $info[$dependType][$k] = $v;
                continue;
            }
            $info[$dependType][$k] = $v;
        }
        return $info;
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
     * 执行内部插件
     * @return mixed
     */
    public function run()
    {
        $plugin = $_GET['_p'] = $this->request->param('_p');
        $controller = $_GET['_c'] = ucfirst($this->request->param('_c', 'Index'));
        $action = $_GET['_a'] = $this->request->param('_a', 'index');
        $params = $this->request->except(['_p', '_c', '_a'], 'param');
        if (empty($plugin)) {
            return $this->response(0, '插件名必须传入[_p]');
        }
        if (!PluginModel::where(['name' => $plugin, 'status' => 2])->find()) {
            return $this->response(0, "插件可能不存在或者未安装");
        }
        $controllerLayer = 'admin';
        $apps = \app\system\model\SystemPlugin::getPlugins();
        foreach ($apps as $v) {
            if ($plugin == $v['name'] && 2 == $v['status']) {
                $appTheme = $v['theme'];
                break;
            }
        }
        if(isset($appTheme) && $appTheme && 'default' != $appTheme){
            $this->app->setNamespace('plugins');
            $class = $this->app->parseClass($plugin . '\\' . $controllerLayer . '\\' . $appTheme, $controller);
            if (class_exists($class)) {
                $controllerLayer = $controllerLayer . '\\' . $appTheme;
            }
        }
        if (!pluginActionExist($plugin . '/' . $controller . '/' . $action, $controllerLayer)) {
            return $this->response(0, "找不到插件方法：{$plugin}/{$controller}/{$action}");
        }
        return pluginRun($plugin . '/' . $controller . '/' . $action, $params, $controllerLayer);
    }
}
