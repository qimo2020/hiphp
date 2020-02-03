<?php
// 应用公共文件
if (!function_exists('moduleNameMap')) {
    /**
     * 模块映射后的模块名
     * @param string $name 真实模块名
     * @return string
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
        $result = \think\facade\Event::trigger($name, $params, $once);
        if ($return) {
            return $result;
        }
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
            $pure_sql = implode($pure_sql, "\n");
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
        $path = root_path() . 'plugins/' . $name . '/info.php';
        if (!file_exists($path)) {
            return false;
        }
        return include_once $path;
    }
}

if (!function_exists('parse_sql')) {
    /**
     * 解析sql语句
     * @param  string $content sql内容
     * @param  int $limit 如果为1，则只返回一条sql语句，默认返回所有
     * @param  array $prefix 替换表前缀
     * @return array|string 除去注释之后的sql语句数组或一条语句
     */
    function parse_sql($sql = '', $limit = 0, $prefix = [])
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
            $pure_sql = implode($pure_sql, "\n");
            $pure_sql = explode(";\n", $pure_sql);
            return $pure_sql;
        } else {
            return $limit == 1 ? '' : [];
        }
    }
}

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


