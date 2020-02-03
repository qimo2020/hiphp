<?php
namespace plugins\builder;
use app\common\controller\Plugin;
use think\Db;
defined('IN_SYSTEM') or die('Access Denied');

/**
 * 构建器插件
 * @package plugins\builder
 */
class builder extends Plugin
{
    // [通用添加、修改专用] 模型名称，格式：模块名/模型名
    protected $hiModel = '';
    // [通用添加、修改专用] 表名(不含表前缀)
    protected $hiTable = '';
    // [通用添加、修改专用] 验证器类，格式：app\模块\validate\验证器类名
    protected $hiValidate = false;
    //[通用添加专用] 添加数据验证场景名
    protected $hiAddScene = false;
    //[通用更新专用] 更新数据验证场景名
    protected $hiEditScene = false;
    //[通用数据表格分页名]
    protected $hiTablePageName = 'page';
    //[通用数据表格分页每页行数名]
    protected $hiTableLimitName = 'limit';
    //[通用数据表格分页每页行数默认值]
    protected $hiTableLimitNum = 15;
    //[通用数据表格条件]
    protected $hiTableWhere = [];
    //[表单构建器数据]
    protected $buildData = [];

    public $hooks = [
        'system_builder' => 'run',
    ];

    public function run()
    {
        $this->request = app()->request;
    }

    public function buildData()
    {
        return [
            'buildForm' => [
                'module' => '',
                'action' => url(),
                'method' => 'POST',
                'class' => 'build-form',
                'token' => false,
                'ajax' => true,
                'resetBtn' => false,
                'backBtn' => false,
                'cancelBtn' => false,
                'submitBtn' => [
                    'title' => '提交保存',
                    'options' => [
                        'time' => 3000,
                        'refresh' => 0,
                        'url' => '',
                        'callback' => '',
                    ]
                ]
            ],
            'buildTable' => [

            ]
        ];
    }

    /**
     * 通用状态设置
     * @author 祈陌 <3411869134@qq.com>
     */
    public function status()
    {
        $val = $this->request->param('v/d');
        $id = $this->request->param('id/a');
        $field = $this->request->param('field/s', 'status');
        $hiModel = $this->request->param('hiModel');
        $hiTable = $this->request->param('hiTable');
        if ($hiModel) {
            $this->hiModel = $hiModel;
            $this->hiTable = '';
        }
        if ($hiTable) {
            $this->hiTable = $hiTable;
            $this->hiModel = '';
        }
        if (empty($id)) {
            return $this->response(0,'缺少id参数');
        }
        // 以下表操作需排除值为1的数据
        if ($this->hiModel == 'SystemMenu') {
            if (in_array('1', $id) || in_array('2', $id) || in_array('3', $id)) {
                return $this->response(0,'系统限制操作');
            }
        }
        if ($this->hiModel) {
            if (defined('IS_PLUGIN')) {
                if (strpos($this->hiModel, '\\') === false) {
                    $this->hiModel = 'plugins\\' . $this->request->param('_p') . '\\model\\' . $this->hiModel;
                }
                $obj = new $this->hiModel;
            } else {
                if (strpos($this->hiModel, '/') === false) {
                    $this->hiModel = app('http')->getName() . '/' . $this->hiModel;
                }
                $obj = invoke($this->hiModel);
            }
        } else if ($this->hiTable) {
            $obj = Db::name($this->hiTable);
        } else {
            return $this->response(0,'当前控制器缺少属性（hiModel、hiTable至少定义一个）');
        }
        $pk = $obj->getPk();
        $result = $obj->where([$pk => $id])->update([$field=>$val]);
        if ($result === false) {
            return $this->response(0,'状态设置失败');
        }
        return $this->response(1,'状态设置成功', '', ['respond' => [$result, $pk, $id, $val]]);
    }

    /**
     * 通用排序
     * @author 祈陌 <3411869134@qq.com>
     */
    public function sort()
    {
        $id = $this->request->param('id/a');
        $field = $this->request->param('field/s', 'sort');
        $val = $this->request->param('v/d');
        $hiModel = $this->request->param('hiModel');
        $hiTable = $this->request->param('hiTable');
        if ($hiModel) {
            $this->hiModel = $hiModel;
            $this->hiTable = '';
        }
        if ($hiTable) {
            $this->hiTable = $hiTable;
            $this->hiModel = '';
        }
        if (empty($id)) {
            return $this->response(0,'缺少id参数');
        }
        if ($this->hiModel) {
            if (defined('IS_PLUGIN')) {
                if (strpos($this->hiModel, '\\') === false) {
                    $this->hiModel = 'plugins\\' . $this->request->param('_p') . '\\model\\' . $this->hiModel;
                }
                $obj = new $this->hiModel;
            } else {
                if (strpos($this->hiModel, '/') === false) {
                    $this->hiModel = app('http')->getName() . '/' . $this->hiModel;
                }
                $obj = invoke($this->hiModel);
            }
        } else if ($this->hiTable) {
            $obj = Db::name($this->hiTable);
        } else {
            return $this->response(0,'当前控制器缺少属性（hiModel、hiTable至少定义一个）');
        }
        $pk = $obj->getPk();
        $result = $obj->where([$pk => $id])->setField($field, $val);
        if ($result === false) {
            return $this->response(0,'排序设置失败');
        }
        return $this->response(1,'排序设置成功', '');
    }

    /**
     * 通用数据表格
     * @return mixed
     * @author 祈陌 <3411869134@qq.com>
     */
    protected function _table()
    {
        $this->buildData = $this->buildTable();
        if ($this->request->isAjax()) {
            $hiModel = $this->request->param('hiModel');
            $hiTable = $this->request->param('hiTable');
            $hiTablePageName = $this->request->param($this->hiTablePageName);
            $hiTableLimitName = $this->request->param($this->hiTableLimitName);
            if ($hiModel) {
                $this->hiModel = $hiModel;
                $this->hiTable = '';
            }
            if ($hiTable) {
                $this->hiTable = $hiTable;
                $this->hiModel = '';
            }
            if ($hiTablePageName) {
                $this->hiTablePageName = $hiTablePageName;
            }
            if ($hiTableLimitName) {
                $this->hiTableLimitName = $hiTableLimitName;
            }
            $page = $this->request->param($this->hiTablePageName . '/d', 1);
            $limit = $this->request->param($this->hiTableLimitName . '/d', $this->hiTableLimitNum);
            if ($this->hiModel) {
                if (defined('IS_PLUGIN')) {
                    if (strpos($this->hiModel, '\\') === false) {
                        $this->hiModel = 'plugins\\' . $this->request->param('_p') . '\\model\\' . $this->hiModel;
                    }
                    $model = new $this->hiModel;
                } else {
                    if (strpos($this->hiModel, '/') === false) {
                        $this->hiModel = app('http')->getName() . '/' . $this->hiModel;
                    }
                    $model = invoke($this->hiModel);
                }
                $data['data'] = $model->where($this->hiTableWhere)->page($page)->limit($limit)->select();
            } else if ($this->hiTable) {
                $data['data'] = Db::name(strtolower($this->hiTable))->where($this->hiTableWhere)->page($page)->limit($limit)->select();
            }
            $data['code'] = 0;
            $data['msg'] = '';
            return json($data);
        }
        $this->assign('buildData', $this->buildData);
        return $this->fetch('build/table');
    }

    /**
     * 通用保存[新增/修改]
     * @return mixed
     * @author 祈陌 <3411869134@qq.com>
     */
    protected function _save($postData = [], $type = 'add')
    {
        $this->buildData = $this->buildForm($type);
        //获取主键字段名
        if ($this->hiModel) { // 通过Model添加
            if (defined('IS_PLUGIN')) {
                if (strpos($this->hiModel, '\\') === false) {
                    $this->hiModel = 'plugins\\' . $this->request->param('_p') . '\\model\\' . $this->hiModel;
                }
                $model = new $this->hiModel;
            } else {
                if (strpos($this->hiModel, '/') === false) {
                    $this->hiModel = app('http')->getName() . '/' . $this->hiModel;
                }
                $model = invoke($this->hiModel);
            }
            $pk = $model->getPk();
        }else if($this->hiTable){ // 通过Db添加
            $db = Db::name($this->hiTable);
            $pk = $db->getPk();
        }
        //post提交
        if ($this->request->isPost()) {
            $hiModel = $this->request->param('hiModel');
            $hiTable = $this->request->param('hiTable');
            $hiValidate = $this->request->param('hiValidate');
            $hiScene = $this->request->param('hiScene');
            if ($hiModel) {
                $this->hiModel = $hiModel;
                $this->hiTable = '';
            }
            if ($hiTable) {
                $this->hiTable = $hiTable;
                $this->hiModel = '';
            }
            if ($hiValidate) {
                $this->hiValidate = $hiValidate;
            }
            if ($hiScene) {
                $this->hiAddScene = $hiScene;
            }
            $postData = !empty($postData) ? $postData : $this->request->post();
            if ($this->hiValidate) {// 数据验证
                if (strpos($this->hiValidate, '\\') === false) {
                    if (defined('IS_PLUGIN')) {
                        $this->hiValidate = 'plugins\\' . $this->request->param('_p') . '\\validate\\' . $this->hiValidate;
                    } else {
                        $this->hiValidate = 'app\\' . app('http')->getName() . '\\validate\\' . $this->hiValidate;
                    }
                }
                if ($this->hiAddScene) {
                    $this->hiValidate = $this->hiValidate . '.' . $this->hiAddScene;
                }
                if ($this->hiEditScene) {
                    $this->hiValidate = $this->hiValidate . '.' . $this->hiEditScene;
                }
                $result = $this->validate($postData, $this->hiValidate);
                if ($result !== true) {
                    return $this->response(0,$result);
                }
            }
            if ($this->hiModel) {// 通过Model添加
                $result = isset($postData[$pk]) && is_numeric($postData[$pk]) ? $model->allowField(true)->save($postData, [$pk => $postData[$pk]]) : $model->allowField(true)->save($postData);
                if (!$result) {
                    return $this->response(0,$model->getError());
                }
            } else if ($this->hiTable) { // 通过Db添加
                $result = isset($postData[$pk]) && is_numeric($postData[$pk]) ? $db->where($pk, $postData[$pk])->update($postData) : $db->allowField(true)->insert($postData);
                if (!$result) {
                    return $this->response(0,'保存失败');
                }
            } else {
                return $this->response(0,'当前控制器缺少属性（hiModel、hiTable至少定义一个）');
            }
            return $this->response(1,'保存成功', '');
        }
        $pkVal = $this->request->param($pk);
        if(isset($pkVal) && is_numeric($pkVal)){
            if ($this->hiModel) {
                $row = $model->where($pk, $pkVal)->find()->toArray();
            }else if($this->hiTable){
                $row = $db->where($pk, $pkVal)->find();
            }
            $this->buildData['buildForm']['items'][] = ['name'=>$pk, 'type'=>'hidden', 'value'=>$pkVal];
            $this->assign('formData', $row);
        }
        $this->assign('buildData', $this->buildData);
        return $this->fetch('build/form');
    }

    /**
     * 通用删除记录
     * @return mixed
     * @author 祈陌 <3411869134@qq.com>
     */
    public function _remove()
    {
        $id = $this->request->param('id/a');
        $hiModel = $this->request->param('hiModel');
        $hiTable = $this->request->param('hiTable');
        if ($hiModel) {
            $this->hiModel = $hiModel;
            $this->hiTable = '';
        }
        if ($hiTable) {
            $this->hiTable = $hiTable;
            $this->hiModel = '';
        }
        if (empty($id)) {
            return $this->response(0,'缺少id参数');
        }
        if ($this->hiModel) {
            if (defined('IS_PLUGIN')) {
                if (strpos($this->hiModel, '\\') === false) {
                    $this->hiModel = 'plugins\\' . $this->request->param('_p') . '\\model\\' . $this->hiModel;
                }
                $obj = new $this->hiModel;
            } else {
                if (strpos($this->hiModel, '/') === false) {
                    $this->hiModel = app('http')->getName() . '/' . $this->hiModel;
                }
                $obj = invoke($this->hiModel);
            }
            try {
                if(is_array($id)){
                    foreach ($id as $v) {
                        $row = $obj->withTrashed()->get($v); //查询数据(包含软删除数据)
                        if (!$row) continue;
                        if (!$row->delete()) {
                            return $this->response(0,$row->getError());
                        }
                    }
                }else{
                    $row = $obj->withTrashed()->get($id);
                    if (!$row) return $this->response(0,'数据不存在');
                    if (!$row->delete()) {
                        return $this->response(0,$row->getError());
                    }
                }
            } catch (\think\Exception $err) {
                if (strpos($err->getMessage(), 'withTrashed')) {
                    if(is_array($id)) {
                        foreach ($id as $v) {
                            $row = $obj->get($v);
                            if (!$row) continue;
                            if (!$row->delete()) {
                                return $this->response(0,$row->getError());
                            }
                        }
                    }else{
                        $row = $obj->get($id);
                        if (!$row) $this->error('数据不存在');
                        if (!$row->delete()) {
                            return $this->response(0,$row->getError());
                        }
                    }
                } else {
                    return $this->response(0,$err->getMessage());
                }
            }
        } else if ($this->hiTable) {
            $obj = Db::name($this->hiTable);
            $pk = $obj->getPk();
            if(is_array($id)) {
                $obj->where($pk, 'in', $id)->delete();
            }else{
                $obj->where($pk, $id)->delete();
            }
        } else {
            return $this->response(0,'当前控制器缺少属性（hiModel、hiTable至少定义一个）');
        }
        return $this->response(1,'删除成功', '');
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