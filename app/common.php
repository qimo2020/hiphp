<?php declare(strict_types=1);

use think\facade\Cache;
use think\facade\Db;
use think\exception\HttpException;
use think\facade\Request;

if (!function_exists('moduleNameMap')) {
    /**
     * 模块映射后的模块名
     * @param string $name 真实模块名
     * @return string 映射后的模块名
     * @author 祈陌 <3411869134@qq.com>
     */
    function moduleNameMap($name)
    {
        $maps = config('app.app_map');
        if (!$maps) return $name;
        $name = array_search($name, $maps) ?: $name;
        return $name;
    }
}

if (!function_exists('getModuleClass')) {
    /**
     * 获取模块类名
     * @param string $name 模块名
     * @return string
     * @author 祈陌 <3411869134@qq.com>
     */
    function getModuleClass($name)
    {
        return "app\\{$name}\\{$name}";
    }
}

if (!function_exists('getPluginClass')) {
    /**
     * 获取插件类名
     * @param string $name 插件名
     * @return string
     * @author 祈陌 <3411869134@qq.com>
     */
    function getPluginClass($name)
    {
        return "plugins\\{$name}\\{$name}";
    }
}

if (!function_exists('pluginActionExist')) {
    /**
     * 检查插件操作是否存在
     * @param string $path 插件操作路径：插件名/控制器/[操作]
     * @param string $group 控制器分组[admin,home]
     * @return bool
     */
    function pluginActionExist($path = '', $controllerLayer = 'admin')
    {
        if (strpos($path, '/')) {
            list($name, $controller, $action) = explode('/', $path);
        }
        $controller = empty($controller) ? 'Index' : ucfirst($controller);
        $action = empty($action) ? 'index' : $action;
        return method_exists("plugins\\{$name}\\{$controllerLayer}\\{$controller}", $action);
    }
}

if (!function_exists('pluginRun')) {
    /**
     * 运行插件操作
     * @param string $path 执行操作路径：插件名/控制器/[操作]
     * @param mixed $params 参数
     * @param string $group 控制器分组[admin,home]
     * @return mixed
     */
    function pluginRun($path = '', $params = [], $controllerLayer = 'admin')
    {
        if (!defined('IS_PLUGIN')) {
            define('IS_PLUGIN', true);
        }
        if (strpos($path, '/')) {
            list($name, $controller, $action) = explode('/', $path);
        } else {
            $name = $path;
        }
        $controller = empty($controller) ? 'index' : ucfirst($controller);
        $action = empty($action) ? 'index' : $action;
        if (!is_array($params)) {
            $params = (array)$params;
        }
        if(file_exists('../plugins/'.$name.'/common.php')){
            require_once ('../plugins/'.$name.'/common.php');
        }
        $class = "plugins\\{$name}\\{$controllerLayer}\\{$controller}";
        $obj = new $class;
        $_GET['_p'] = $name;
        $_GET['_c'] = $controller;
        $_GET['_a'] = $action;
        return call_user_func_array([$obj, $action], [$params]);
    }
}

if (!function_exists('runHook')) {
    /**
     * 监听钩子的行为
     * @param string $name 钩子名称
     * @param array $params 参数
     * @param  bool $return 是否需要返回结果
     * @param  bool $once 只获取一个有效返回值
     */
    function runHook($name = '', $params = null, $return = false, $once = false)
    {
        if(!defined('RUNHOOK')){
            define('RUNHOOK', $name);
        }
        pluginConfigInit();
        $result = \think\facade\Event::trigger($name, $params, $once);
        if ($return) {
            return $result;
        }
    }
}
if (!function_exists('pathInfoParse')) {
    function pathInfoParse()
    {
        $pathInfo = '';
        if($_GET && isset($_GET['s']) && $pathInfo = $_GET['s']){
            $pathInfo = ltrim($pathInfo, '/');
        }
        return $pathInfo;
    }
}
if (!function_exists('parseSql')) {
    /**
     * 解析sql语句
     * @param  string $content sql内容
     * @param  int $limit 如果为1，则只返回一条sql语句，默认返回所有
     * @param  array $prefix 替换表前缀
     * @return array|string 除去注释之后的sql语句数组或一条语句
     */
    function parseSql($sql = '', $limit = 0, $prefix = [])
    {
        // 被替换的前缀
        $from = '';
        // 要替换的前缀
        $to = '';

        // 替换表前缀
        if (!empty($prefix)) {
            $to = current($prefix);
            $from = current(array_flip($prefix));
        }

        if ($sql != '') {
            // 纯sql内容
            $pure_sql = [];

            // 多行注释标记
            $comment = false;

            // 按行分割，兼容多个平台
            $sql = str_replace(["\r\n", "\r"], "\n", $sql);
            $sql = explode("\n", trim($sql));

            // 循环处理每一行
            foreach ($sql as $key => $line) {
                // 跳过空行
                if ($line == '') {
                    continue;
                }

                // 跳过以#或者--开头的单行注释
                if (preg_match("/^(#|--)/", $line)) {
                    continue;
                }

                // 跳过以/**/包裹起来的单行注释
                if (preg_match("/^\/\*(.*?)\*\//", $line)) {
                    continue;
                }

                // 多行注释开始
                if (substr($line, 0, 2) == '/*') {
                    $comment = true;
                    continue;
                }

                // 多行注释结束
                if (substr($line, -2) == '*/') {
                    $comment = false;
                    continue;
                }

                // 多行注释没有结束，继续跳过
                if ($comment) {
                    continue;
                }

                // 替换表前缀
                if ($from != '') {
                    $line = str_replace('`' . $from, '`' . $to, $line);
                }
                if ($line == 'BEGIN;' || $line == 'COMMIT;') {
                    continue;
                }
                // sql语句
                array_push($pure_sql, $line);
            }
            // 只返回一条语句
            if ($limit == 1) {
                return implode($pure_sql, "");
            }
            // 以数组形式返回sql语句
            $pure_sql = implode("\n", $pure_sql);
            $pure_sql = explode(";\n", $pure_sql);
            return $pure_sql;
        } else {
            return $limit == 1 ? '' : [];
        }
    }
}

if (!function_exists('getClientIp')) {
    /**
     * 获取客户端IP地址
     * @param int $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
     * @param bool $adv 是否进行高级模式获取（有可能被伪装）
     * @return mixed
     */
    function getClientIp($type = 0, $adv = false)
    {
        $type = $type ? 1 : 0;
        static $ip = NULL;
        if ($ip !== NULL) return $ip[$type];
        if ($adv) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $pos = array_search('unknown', $arr);
                if (false !== $pos) unset($arr[$pos]);
                $ip = trim($arr[0]);
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $long = sprintf("%u", ip2long($ip));
        $ip = $long ? array($ip, $long) : array('0.0.0.0', 0);
        return $ip[$type];
    }
}

if (!function_exists('moduleInfo')) {
    /**
     * 获取目录中的模块信息
     * @param string $name 模块名
     * @return bool
     */
    function moduleInfo($name = '')
    {
        $path = base_path() . $name . '/info.php';
        if (!file_exists($path)) {
            return false;
        }
        return include_once $path;
    }
}

if (!function_exists('pluginInfo')) {
    /**
     * 获取插件信息
     * @param string $name 插件名
     * @return bool
     */
    function pluginInfo($name = '')
    {
        $appPath = root_path() . 'plugins/' . $name . '/depend.php';
        if (file_exists($appPath)) {
            $class = 'plugins\\'.$name.'\\depend';
            $depend = new $class();
        }
        $path = root_path() . 'plugins/' . $name . '/info.php';
        if (!file_exists($path)) {
            return false;
        }
        return include $path;
    }
}

if (!function_exists('parseAttr')) {
    /**
     * 配置值解析成数组
     * @param string $value 配置值
     * @return array|string
     */
    function parseAttr($value = '')
    {
        if (is_array($value)) return $value;
        $array = preg_split('/[,;\r\n]+/', trim($value, ",;\r\n"));
        if (strpos($value, ':')) {
            $value = array();
            foreach ($array as $val) {
                list($k, $v) = explode(':', $val);
                $value[$k] = $v;
            }
        } else {
            $value = $array;
        }
        return $value;
    }
}

if (!function_exists('getNum')) {
    /**
     * 获取数值型
     * @param string $field 要获取的字段名
     * @return bool
     */
    function getNum($field = 'id')
    {
        $_id = input('param.' . $field . '/d', 0);
        if ($_id > 0) {
            return $_id;
        }
        if (request()->isAjax()) {
            json(['msg' => '参数传递错误', 'code' => 0]);
        } else {
            throw new HttpException(404, $field . '参数传递错误！');
        }
        exit;
    }
}

if (!function_exists('db')) {
    /**
     * 创建/切换数据库连接查询
     * @access public
     * @param string|null $name  连接配置标识
     * @param bool        $force 强制重新连接
     * @return BaseQuery
     */
    function db($name = '', $force = true)
    {
        return Db::connect((string)$name, $force);
    }
}

if (!function_exists('checkPluginDepends')) {
/**
 * 检测依赖插件以及插件的版本是否正确
 * @param $pluginDepends array 依赖的插件,举例 [['builder','>=','1.0.0'],'devhelp'], ['builder','>=','1.0.0'],['devhelp','>=','1.0.0']
 * @exit string
 */
    function checkPluginDepends($pluginDepends)
    {
        $result = true;
        if(!is_array($pluginDepends)){
            $result = false;
        }
        foreach ($pluginDepends as $v){
            if(is_array($v) && count($v) == 3){
                $plugin = [];
                foreach (\app\system\model\SystemPlugin::getPlugins() as $val){
                    if($val['name'] == strtolower($v[0])){
                        $plugin = $val;
                        break;
                    }
                }
                if(!$plugin){
                    $result = false;
                    $msg = '缺少['.$v[0].']插件';
                }
                if($plugin['status'] != 2){
                    $result = false;
                    $msg = '插件['.$plugin['title'].']未开启';
                }
                if(version_compare($plugin['version'], $v[2], $v[1]) === false){
                    $result = false;
                    $msg = '插件['.$plugin['title'].']的版本必须' . $v[1] . $v[2];
                }
            }else{
                if(!cache('plugins') || !array_key_exists(strtolower($v), cache('plugins'))){
                    $result = false;
                    $msg = '缺少['.$v.']插件或未开启';
                }
            }
        }
        if(!$result){
            exit($msg);
        }
    }
}

if (!function_exists('checkModuleDepends')) {
    function checkModuleDepends($moduleDepends)
    {
        $result = true;
        if(!is_array($moduleDepends)){
            $result = false;
        }
        foreach ($moduleDepends as $v){
            if(is_array($v) && count($v) == 3){
                $module = [];
                foreach (\app\system\model\SystemModule::getModules() as $val){
                    if($val['name'] == strtolower($v[0])){
                        $module = $val;
                        break;
                    }
                }
                if(!$module){
                    $result = false;
                    $msg = '缺少['.$v[0].']模块';
                }
                if($module['status'] != 2){
                    $result = false;
                    $msg = '模块['.$module['title'].']未开启';
                }
                if(version_compare($module['version'], $v[2], $v[1]) === false){
                    $result = false;
                    $msg = '模块['.$module['title'].']的版本必须' . $v[1] . $v[2];
                }
            }else{
                $modules = \app\system\model\SystemModule::getModules();
                if(!$modules){
                    $result = false;
                    $msg = '缺少['.$v.']模块';
                }
                $mods = array_column($modules, 'name');
                if(!in_array(strtolower($v), $mods)){
                    $result = false;
                    $msg = '缺少['.$v.']模块';
                }
                foreach ($modules as $val){
                    if($val['name'] == strtolower($v)){
                        if($val['status'] != 2){
                            $result = false;
                            $msg = '['.$val['title'].']模块未开启';
                        }
                        break;
                    }
                }
            }
        }
        if(!$result){
            exit($msg);
        }
    }
}

if (!function_exists('pluginConfigInit')) {
    /**
     * 1.使用插件时若要读取自定义配置文件数据，建议使用此函数在入口初始化一下
     * 2.runHook入口的无需初始化
     * 3.A插件钩子方式调用B插件时，B插件入口有必要使用此函数携带$pluginName参数初始化配置文件数据
     */
    function pluginConfigInit($pluginName = null){
        $pathInfo = Request::instance()->pathinfo();
        if($pluginName === null && ('plugin' == strtolower(explode('/', $pathInfo)[0]) || defined('PLUGIN_ENTRANCE') || defined('RUNHOOK') || defined('IS_PLUGIN'))){
            $pluginName = '';
            if(defined('RUNHOOK')){
                $hookPlugins = cache('hook_plugins');
                foreach ($hookPlugins as $v){
                    if($v['hook'] == RUNHOOK){
                        $pluginName = $v['plugins'];
                        break;
                    }
                }
            }else if(defined('IS_PLUGIN')){
                $pluginName = Request::param('_p');
            }else if(defined('PLUGIN_ENTRANCE')){
                $pluginName = explode('/', $pathInfo)[0];
            }else{
                $pluginName = explode('/', $pathInfo)[1];
            }
        }
        if(file_exists($path = root_path() . 'plugins/' . strtolower($pluginName) . '/config/')){
            $fiels = scandir($path);
            $files = array_splice($fiels, 2);
            $configs = [];
            foreach ($files as $v){
                $configs[str_replace(".php","", $v)] = include_once $path.$v;
            }
            $appConfigs = configs($pluginName, true);
            $appConfigs['config'] = $configs;
            config([$pluginName=>$appConfigs]);
        }
    }
}

if (!function_exists('configs')) {
    /**
     * 当前应用配置载入:建议开发者在单个应用[模块/插件]入口使用此函数初始化应用配置(此函数区别于config()做了兼容处理);
     * @varStr String 应用名[模块/插件]
     * @plugin bool 是否为插件, 如在插件加载配置时，碰到无法加载的情况，可使用此参数强行加载, 建议使用
     * @return array|string
     */
    function configs($varStr, $plugin = null){
        $appName = strpos($varStr, '.') !== false ? explode('.', $varStr)[0] : $varStr;
        $appConfigs = config($appName);
        if(!$appConfigs){
            $result = Cache::get($appName.'_config');
            $res = config();
            if(!$result){
                $configs = Db::name('system_config')->where('group', $appName)->field('value,type,group,name')->select()->toArray();
                foreach ($configs as $config) {
                    $config['value'] = htmlspecialchars_decode($config['value']);
                    if('array' == $config['type'] || 'checkbox' == $config['type']){
                        $res[$config['group']][$config['name']] = $cache[$config['name']] = parseAttr($config['value']);
                    }else{
                        $res[$config['group']][$config['name']] = $cache[$config['name']] = $config['value'];
                    }
                }
                //载入应用额外的公共配置信息
                $params = Request::param('_p');
                $version = (isset($params['_p']) || defined('RUNHOOK') || $plugin === true ? pluginInfo($appName) : moduleInfo($appName))['version'];
                //开发者需注意, 此处配置变名禁止与应用自定义变量配置重复
                $configs = ['version'=>$version];
                foreach ($configs as $key=>$val){
                    $res[$appName][$key] = $cache[$key] = $val;
                }
                $appConfigs = $cache;
                Cache::tag('hiphp_config')->set($appName.'_config', $cache);
            }else{
                $res[$appName] = $appConfigs = $result;
            }
            config($res);
        }
        return $appConfigs;
    }
}
/****************************通用便捷函数START********************************/
if (!function_exists('arrayToStr')) {
    function arrayToStr($array, $mark)
    {
        if (!is_array($array)) {
            return '';
        }
        if (count($array) > 1) {
            $string = implode($mark, $array);
        } else {
            $string = $array[0];
        }
        return $string;
    }
}

if (!function_exists('xml2array')) {
    /**
     * XML转数组
     * @param string $xml xml格式内容
     * @param bool $isnormal
     * @return array
     */
    function xml2array(&$xml, $isnormal = FALSE)
    {
        $xml_parser = new hi\Xml($isnormal);
        $data = $xml_parser->parse($xml);
        $xml_parser->destruct();
        return $data;
    }
}

if (!function_exists('randomStr')) {
    /**
     * 生成随机字符串，数字，大小写字母随机组合
     * @param int $length  长度
     * @param int $type    类型，1 纯数字，2 纯小写字母，3 纯大写字母，4 数字和小写字母，5 数字和大写字母，6 大小写字母，7 数字和大小写字母, 8 数字和大小写字母以及特殊字符
     * @param int $special 特殊字符,用逗号隔开,格式如 $special = '$,_,-,~';
     */
    function randomStr(int $length = 6, int $type = 1, $special = null)
    {
        $number = range(0, 9);
        $lowerLetter = range('a', 'z');
        $upperLetter = range('A', 'Z');
        $special = null !== $special ? explode(',', $special) : ['_','-','$','.'];
        if ($type == 1) {
            $charset = $number;
        } elseif ($type == 2) {
            $charset = $lowerLetter;
        } elseif ($type == 3) {
            $charset = $upperLetter;
        } elseif ($type == 4) {
            $charset = array_merge($number, $lowerLetter);
        } elseif ($type == 5) {
            $charset = array_merge($number, $upperLetter);
        } elseif ($type == 6) {
            $charset = array_merge($lowerLetter, $upperLetter);
        } elseif ($type == 7) {
            $charset = array_merge($number, $lowerLetter, $upperLetter);
        } elseif ($type == 8) {
            $charset = array_merge($number, $lowerLetter, $upperLetter, $special);
        }else {
            $charset = $number;
        }
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= $charset[mt_rand(0, count($charset) - 1)];
            if ($type == 4 && strlen($str) >= 2) {
                if (!preg_match('/\d+/', $str) || !preg_match('/[a-z]+/', $str)) {
                    $str = substr($str, 0, -1);
                    $i = $i - 1;
                }
            }
            if ($type == 5 && strlen($str) >= 2) {
                if (!preg_match('/\d+/', $str) || !preg_match('/[A-Z]+/', $str)) {
                    $str = substr($str, 0, -1);
                    $i = $i - 1;
                }
            }
            if ($type == 6 && strlen($str) >= 2) {
                if (!preg_match('/[a-z]+/', $str) || !preg_match('/[A-Z]+/', $str)) {
                    $str = substr($str, 0, -1);
                    $i = $i - 1;
                }
            }
            if ($type == 7 && strlen($str) >= 3) {
                if (!preg_match('/\d+/', $str) || !preg_match('/[a-z]+/', $str) || !preg_match('/[A-Z]+/', $str)) {
                    $str = substr($str, 0, -2);
                    $i = $i - 2;
                }
            }
            if ($type == 8 && strlen($str) >= 4) {
                $specialStr = '';
                foreach ($special as $v){
                    $specialStr .= '\\'.$v.'|';
                }
                if (!preg_match('/'.rtrim($specialStr, '|').'/', $str) || !preg_match('/\d+/', $str) || !preg_match('/[a-z]+/', $str) || !preg_match('/[A-Z]+/', $str)) {
                    $str = substr($str, 0, -3);
                    $i = $i - 3;
                }
            }

        }
        return $str;
    }
}

/****************************通用便捷函数END********************************/