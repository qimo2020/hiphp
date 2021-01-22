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
namespace app\install\home;

use app\common\controller\Common;
use app\system\model\SystemUser as UserModel;
use think\facade\Db;
use think\exception\ValidateException;
use think\facade\Env;

class Error extends Common
{
    public function initialize()
    {

    }

    public function index($step = 0)
    {

        // 检测PHP环境
        if (version_compare(PHP_VERSION, '7.1.0', '<')) {
            return $this->response(0,'PHP版本过低，最少需要PHP7.1.0，请升级PHP版本！','/?s=install');
        };

        if (strpos($this->request->url(), 'public/')) {
            return $this->response(0,'请将网站根目录指向public','/?s=install');
        }

        if (is_file($this->app->getAppPath() . 'install.lock')) {
            return $this->response(0,'如需重新安装，请手动删除/install.lock文件','/?s=install');
        }
        switch ($step) {
            case 2:
                session('install_error', false);
                return self::step2();
                break;
            case 3:
                if (session('install_error')) {
                    return $this->response(0,'环境检测未通过，不能进行下一步操作！','/?s=install/step=2');
                }
                return self::step3();
                break;
            case 4:
                if (session('install_error')) {
                    return $this->response(0,'环境检测未通过，不能进行下一步操作！','/?s=install/step=3');
                }
                return self::step4();
                break;
            case 5:
                if (session('install_error')) {
                    return $this->response(0,'初始失败！','/?s=install');
                }
                return self::step5();
                break;

            default:
                session('install_error', false);
                return $this->view();
                break;
        }
    }

    /**
     * 第二步：环境检测
     * @return mixed
     */
    private function step2()
    {
        $data = [];
        $data['env'] = self::checkNnv();
        $data['dir'] = self::checkDir();
        $data['func'] = self::checkFunc();
        $this->assign('data', $data);
        return $this->view('step2');
    }

    /**
     * 第三步：初始化配置
     * @return mixed
     */
    private function step3()
    {
        return $this->view('step3');
    }

    /**
     * 第四步：执行安装
     * @return mixed
     */
    private function step4()
    {
        if ($this->request->isPost()) {

            if (!is_writable(root_path() . '.env')) {
                return $this->response(0,'[.env]无读写权限！');
            }

            $data = $this->request->post();
            $data['type'] = 'mysql';
            $rule = [
                'hostname|服务器地址' => 'require',
                'hostport|数据库端口' => 'require|number',
                'database|数据库名称' => 'require',
                'username|数据库账号' => 'require',
                'prefix|数据库前缀' => 'require|regex:^[a-z0-9]{1,20}[_]{1}',
                'cover|覆盖数据库' => 'require|in:0,1',
            ];
            try {
                $validate = $this->validate($data, $rule);
            } catch (ValidateException $e) {
                return $this->response(0,$e->getError());
            }
            $cover = $data['cover'];
            unset($data['cover']);

            foreach ($data as $k => $v) {
                if (array_key_exists($k, config('database.connections.mysql')) === false) {
                    return $this->response(0,'参数' . $k . '不存在！');
                }
            }
            // 不存在的数据库会导致连接失败
            $database = $data['database'];
            $mysqlConfig = config('database');
            foreach($data as $key=>$v){
                if (array_key_exists($key, config('database.connections.mysql'))) {
                    $mysqlConfig['connections']['mysql'][$key] = $v;
                }
            }
            unset($mysqlConfig['connections']['mysql']['database']);
            config($mysqlConfig, 'database');
            // 检测数据库连接
            $db_connect = Db::connect('mysql');
            try {
                $db_connect->execute('select version()');
            } catch (\Exception $e) {
                return $this->response(0,'数据库连接失败，请检查数据库配置！');
            }
            // 不覆盖检测是否已存在数据库
            if (!$cover) {
                $check = $db_connect->execute('SELECT * FROM information_schema.schemata WHERE schema_name="' . $database . '"');
                if ($check) {
                    return $this->response(0, '该数据库已存在，如需覆盖，请选择覆盖数据库！');
                }
            }

            // 创建数据库
            if (!$db_connect->execute("CREATE DATABASE IF NOT EXISTS `{$database}` DEFAULT CHARACTER SET utf8")) {
                return $this->response(0, $db_connect->getError());
            }

            // 生成配置文件
            self::mkDatabase($data);
            return $this->response(1,'数据库连接成功', '');
        } else {
            return $this->response(0,'非法访问');
        }
    }

    /**
     * 第五步：数据库安装
     * @return mixed
     */
    private function step5()
    {
        $account = $this->request->post('account');
        $password = $this->request->post('password');

        if (empty(config('database.connections.mysql.hostname')) || empty(config('database.connections.mysql')) || empty(config('database.connections.mysql.username'))) {
            return $this->response(0,'请先点击测试数据库连接！');
        }

        if (empty($account) || empty($password)) {
            return $this->response(0,'请填写管理账号和密码！');
        }

        $rule = [
            'account|管理员账号' => 'require|alphaNum|length:4,20',
            'password|管理员密码' => 'require|length:6,20',
        ];

        $validate = $this->validate(['account' => $account, 'password' => $password], $rule);
        if (true !== $validate) {
            return $this->response(0, $validate);
        }

        // 导入系统初始数据库结构
        $sqlFile = base_path() . 'install/sql/install.sql';
        if (file_exists($sqlFile)) {
            $sql = file_get_contents($sqlFile);
            $sqlList = parseSql($sql, 0, ['hi_' => config('database.connections.mysql.prefix')]);
            if ($sqlList) {
                $sqlList = array_filter($sqlList);
                foreach ($sqlList as $v) {
                    try {
                        Db::execute($v);
                    } catch (\Exception $e) {
                        return $this->response(0,$e->getMessage(),'/?s=install/step=3');
                    }
                }
            }
        }

        // 注册管理员账号
        $user = new UserModel;
        $map = [];
        $map['role_id'] = 1;
        $map['nick'] = '超级管理员';
        $map['username'] = $account;
        $map['password'] = password_hash(md5($password), PASSWORD_DEFAULT);;
        $map['auth'] = '';
        $map['email'] = '';
        $map['mobile'] = '';
        $map['last_login_ip'] = '';
        $map['last_login_time'] = request()->time();
        $res = $user->create($map);

        if (!$res) {
            return $this->response(0,$user->getError() ? $user->getError() : '管理员账号设置失败！');
        }
        file_put_contents($this->app->getAppPath() . 'install.lock', "如需重新安装，请手动删除此文件\n安装时间：" . date('Y-m-d H:i:s'));

        // 获取站点根目录
        $rootDir = request()->baseFile();
        $rootDir = preg_replace(['/index.php$/'], [''], $rootDir);

        return $this->response(1, '恭喜您！系统安装成功，准备跳转至后台...', $rootDir . 'admin.php');
    }

    /**
     * 环境检测
     * @return array
     */
    private function checkNnv()
    {
        $items = [
            'os' => ['操作系统', '不限制', '类Unix', PHP_OS, 'ok'],
            'php' => ['PHP版本', '7.1.0', '7.1.0及以上', PHP_VERSION, 'ok'],
            'gd' => ['GD库', '2.0', '2.0及以上', '未知', 'ok'],
        ];

        if ($items['php'][3] < $items['php'][1]) {

            $items['php'][4] = 'no';
            session('install_error', true);

        }

        $tmp = function_exists('gd_info') ? gd_info() : [];

        if (empty($tmp['GD Version'])) {

            $items['gd'][3] = '未安装';
            $items['gd'][4] = 'no';
            session('install_error', true);

        } else {

            $items['gd'][3] = $tmp['GD Version'];

        }

        return $items;
    }

    /**
     * 目录权限检查
     * @return array
     */
    private function checkDir()
    {
        $items = [
            ['dir', root_path() . 'app', 'app', '读写', '读写', 'ok'],
            ['dir', root_path() . 'extend', 'extend', '读写', '读写', 'ok'],
            ['dir', root_path() . 'backup', 'backup', '读写', '读写', 'ok'],
            ['dir', root_path() . 'plugins', 'plugins', '读写', '读写', 'ok'],
            ['file', root_path() . 'config', 'config', '读写', '读写', 'ok'],
            ['file', root_path() . '.env', '.env', '读写', '读写', 'ok'],
            ['file', root_path() . 'version.php', 'version.php', '读写', '读写', 'ok'],
            ['file', './admin.php', 'public/admin.php', '读写', '读写', 'ok'],
        ];

        foreach ($items as &$v) {

            if ($v[0] == 'dir') {// 文件夹

                if (!is_writable($v[1])) {

                    if (is_dir($v[1])) {
                        $v[4] = '不可写';
                        $v[5] = 'no';
                    } else {
                        $v[4] = '不存在';
                        $v[5] = 'no';
                    }

                    session('install_error', true);
                }

            } else {// 文件

                if (!is_writable($v[1])) {

                    $v[4] = '不可写';
                    $v[5] = 'no';
                    session('install_error', true);

                }

            }

        }

        return $items;
    }

    /**
     * 函数及扩展检查
     * @return array
     * @author 祈陌 <3411869134@qq.com>
     */
    private function checkFunc()
    {
        $items = [
            ['pdo', '支持', 'yes', '类'],
            ['pdo_mysql', '支持', 'yes', '模块'],
            ['zip', '支持', 'yes', '模块'],
            ['fileinfo', '支持', 'yes', '模块'],
            ['curl', '支持', 'yes', '模块'],
            ['xml', '支持', 'yes', '函数'],
            ['file_get_contents', '支持', 'yes', '函数'],
            ['mb_strlen', '支持', 'yes', '函数'],
            ['gzopen', '支持', 'yes', '函数'],
        ];

        foreach ($items as &$v) {
            if (('类' == $v[3] && !class_exists($v[0])) || ('模块' == $v[3] && !extension_loaded($v[0])) || ('函数' == $v[3] && !function_exists($v[0]))) {
                $v[1] = '不支持';
                $v[2] = 'no';
                session('install_error', true);
            }
        }

        return $items;
    }

    /**
     * 生成数据库配置文件
     * @author 祈陌 <3411869134@qq.com>
     */
    private function mkDatabase(array $data)
    {
        $code = <<<INFO
APP_DEBUG = true

[APP]
DEFAULT_TIMEZONE = Asia/Shanghai

[DATABASE]
TYPE = mysql
HOSTNAME = {$data['hostname']}
DATABASE = {$data['database']}
USERNAME = {$data['username']}
PASSWORD = {$data['password']}
HOSTPORT = {$data['hostport']}
CHARSET = utf8
PREFIX = {$data['prefix']}
DEBUG = true

[LANG]
default_lang = zh-cn

INFO;
        $res = file_put_contents(root_path() . '.env', $code);
        // 写入是否成功
        if (!$res) {
            return $this->response(0,'[/.env]数据库配置写入失败！');
            exit;
        }
    }

}
