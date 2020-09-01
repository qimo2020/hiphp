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
use hi\Dir;
use hi\Database as dbOper;
use think\facade\Db;

/**
 * 数据库管理控制器
 * @package app\system\admin
 */
class Database extends Base
{
    protected function initialize()
    {
        parent::initialize();
        $this->backupPath = root_path() . 'backup/' . trim(config('databases.backup_path'), '/') . '/';
        $tabData['tab'] = [
            [
                'title' => '备份数据库',
                'url' => url('index', ['group'=>'export']),
            ],
            [
                'title' => '恢复数据库',
                'url' => url('index', ['group'=>'import']),
            ],
        ];
        $this->tabData = $tabData;
    }

    public function index($group = 'export')
    {
        if ($this->request->isAjax()) {
            $group = $this->request->param('group');
            $data = [];
            if ($group == 'export') {
                $tables = Db::query("SHOW TABLE STATUS");
                foreach ($tables as $k => &$v) {
                    $v['id'] = $v['Name'];
                }
                $data['data'] = $tables;
                $data['code'] = 0;
            } else {
                //列出备份文件列表
                if (!is_dir($this->backupPath)) {
                    Dir::create($this->backupPath);
                }
                $flag = \FilesystemIterator::KEY_AS_FILENAME;
                $glob = new \FilesystemIterator($this->backupPath, $flag);
                $dataList = [];
                foreach ($glob as $name => $file) {
                    if (preg_match('/^\d{8,8}-\d{6,6}-\d+\.sql(?:\.gz)?$/', $name)) {
                        $name = sscanf($name, '%4s%2s%2s-%2s%2s%2s-%d');
                        $date = "{$name[0]}-{$name[1]}-{$name[2]}";
                        $time = "{$name[3]}:{$name[4]}:{$name[5]}";
                        $part = $name[6];
                        if (isset($dataList["{$date} {$time}"])) {
                            $info = $dataList["{$date} {$time}"];
                            $info['part'] = max($info['part'], $part);
                            $info['size'] = $info['size'] + $file->getSize();
                        } else {
                            $info['part'] = $part;
                            $info['size'] = $file->getSize();
                        }
                        $info['time'] = "{$date} {$time}";
                        $time = strtotime("{$date} {$time}");
                        $extension = strtoupper($file->getExtension());
                        $info['compress'] = ($extension === 'SQL') ? '无' : $extension;
                        $info['name'] = date('Ymd-His', $time);
                        $info['id'] = $time;
                        $dataList["{$date} {$time}"] = $info;
                    }
                }
                $data['data'] = $dataList;
                $data['code'] = 0;
            }
            return json($data);
        }
        $tabData = $this->tabData;
        $tabData['current'] = url('',['group'=>$group]);
        $this->assign('tabData', $tabData);
        $this->assign('tabType', 3);
        return $this->view($group);
    }

    /**
     * 备份数据库 [参考原作者 麦当苗儿 <zuojiazi@vip.qq.com>]
     * @param string|array $id 表名
     * @param integer $start 起始行数
     * @return mixed
     */
    public function export($id = '', $start = 0)
    {
        if ($this->request->isAjax()) {
            if (empty($id)) {
                return $this->response(0,'请选择您要备份的数据表');
            }
            if (!is_array($id)) {
                $tables[] = $id;
            } else {
                $tables = $id;
            }
            //读取备份配置
            $config = array(
                'path' => $this->backupPath,
                'part' => config('databases.part_size'),
                'compress' => config('databases.compress'),
                'level' => config('databases.compress_level'),
            );
            //检查是否有正在执行的任务
            $lock = "{$config['path']}backup.lock";
            if (is_file($lock)) {
                return $this->response(0,'检测到有一个备份任务正在执行，请稍后再试');
            } else {
                if (!is_dir($config['path'])) {
                    Dir::create($config['path'], 0755, true);
                }
                //创建锁文件
                file_put_contents($lock, $this->request->time());
            }
            //生成备份文件信息
            $file = [
                'name' => date('Ymd-His', $this->request->time()),
                'part' => 1,
            ];
            // 创建备份文件
            $database = new dbOper($file, $config);

            if ($database->create() !== false) {
                // 备份指定表
                foreach ($tables as $table) {
                    $start = $database->backup($table, $start);
                    while (0 !== $start) {
                        if (false === $start) {
                            return $this->response(0,'备份出错');
                        }
                        $start = $database->backup($table, $start[0]);
                    }
                }
                // 备份完成，删除锁定文件
                unlink($lock);
            }

            return $this->response(1,'备份完成');
        }
        return $this->response(0,'备份出错');
    }

    /**
     * 恢复数据库 [参考原作者 麦当苗儿 <zuojiazi@vip.qq.com>]
     * @param string|array $ids 表名
     * @param integer $start 起始行数
     * @return mixed
     */
    public function import($id = '')
    {
        if (empty($id)) {
            return $this->response(0,'请选择您要恢复的备份文件');
        }

        $name = date('Ymd-His', $id) . '-*.sql*';
        $path = $this->backupPath . $name;
        $files = glob($path);
        $list = array();

        foreach ($files as $name) {
            $basename = basename($name);
            $match = sscanf($basename, '%4s%2s%2s-%2s%2s%2s-%d');
            $gz = preg_match('/^\d{8,8}-\d{6,6}-\d+\.sql.gz$/', $basename);
            $list[$match[6]] = array($match[6], $name, $gz);
        }

        ksort($list);

        // 检测文件正确性
        $last = end($list);

        if (count($list) === $last[0]) {

            foreach ($list as $item) {

                $config = [
                    'path' => $this->backupPath,
                    'compress' => $item[2]
                ];

                $database = new dbOper($item, $config);
                $start = $database->import(0);

                // 导入所有数据
                while (0 !== $start) {

                    if (false === $start) {
                        return $this->response(0,'数据恢复出错');
                    }

                    $start = $database->import($start[0]);
                }
            }

            return $this->response(1,'数据恢复完成');
        }

        return $this->response(0,'备份文件可能已经损坏，请检查');
    }

    /**
     * 优化数据表
     * @return mixed
     */
    public function optimize($id = '')
    {
        if (empty($id)) {
            return $this->response(0,'请选择您要优化的数据表');
        }

        if (!is_array($id)) {
            $table[] = $id;
        } else {
            $table = $id;
        }

        $tables = implode('`,`', $table);
        $res = Db::query("OPTIMIZE TABLE `{$tables}`");
        if ($res) {
            return $this->response(1,'数据表优化完成');
        }

        return $this->response(0,'数据表优化失败');
    }

    /**
     * 修复数据表
     * @return mixed
     */
    public function repair($id = '')
    {
        if (empty($id)) {
            return $this->response(0,'请选择您要修复的数据表');
        }

        if (!is_array($id)) {
            $table[] = $id;
        } else {
            $table = $id;
        }

        $tables = implode('`,`', $table);
        $res = Db::query("REPAIR TABLE `{$tables}`");

        if ($res) {
            return $this->response(1,'数据表修复完成');
        }

        return $this->response(0,'数据表修复失败');
    }

    /**
     * 删除备份
     * @return mixed
     */
    public function del($id = '')
    {
        if (empty($id)) {
            return $this->response(0,'请选择您要删除的备份文件');
        }
        if(is_array($id)){
            foreach ($id as $v){
                $name = date('Ymd-His', $v) . '-*.sql*';
                $path = $this->backupPath . $name;
                array_map("unlink", glob($path));
            }
        }else{
            $name = date('Ymd-His', $id) . '-*.sql*';
            $path = $this->backupPath . $name;
            array_map("unlink", glob($path));
        }
        if (count(glob($path)) && glob($path)) {
            return $this->response(0,'备份文件删除失败，请检查权限');
        }

        return $this->response(1,'备份文件删除成功');
    }
}
