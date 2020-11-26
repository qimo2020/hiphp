<?php declare(strict_types=1);
namespace plugins\builder;
use app\common\controller\Plugin;
use think\facade\Db;
use think\exception\ValidateException;
use app\common\model\SystemAnnex as AnnexModel;
defined('IN_SYSTEM') or die('Access Denied');

/**
 * 构建器插件
 * @package plugins\builder
 */
class builder extends Plugin
{
    //[构建器额外js代码]
    public $jsCode = '';
    //[构建器额外css代码]
    public $cssCode = '';
    // [通用添加、修改专用] 模型名称，格式：模块名/模型名
    public $hiModel = '';
    // [通用添加、修改专用] 表名(不含表前缀)
    public $hiTable = '';
    //独立验证
    public $hiMakeValidate = false;
    // [通用添加、修改专用] 验证器类，格式：app\模块\validate\验证器类名;注意:验证器类与独立验证 无法同时使用
    public $hiValidate = false;
    //数据验证场景名
    public $hiValidateScene = false;
    //[通用数据表格分页名]
    public $hiPageName = 'page';
    //[通用数据表格分页每页行数名]
    public $hiLimitName = 'limit';
    //[通用数据表格分页每页行数默认值]
    public $hiLimitNum = 15;
    //[通用数据表格条件]
    public $hiWhere = [];
    //[通用数据表格初始排序]
    public $hiSort = 'id desc';
    //[表单构建器数据]
    public $buildData = [];
    //[通用修改专用]表单赋值数据
    public $assignData = [];
    //模型数据列表
    public $tableHasContact = [];
    //数据保存条件
    public $multiPriCondition = [];
    public $hooks = [
        'system_builder' => 'run',
    ];

    public function __construct($obj=null)
    {
        $this->request = app()->request;
        $this->app = $obj;
        configs('builder', true);
    }

    public function run()
    {
        return true;
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
        $id = $this->request->param('id/a');
        $val = $this->request->param('v/d', '');
        $field = $this->request->param('field/s', 'status');
        $val = $val ?: $this->request->param($field);
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
                    $this->hiModel = 'plugins\\' . $this->request->param('_p') . '\\model\\' . ucfirst($this->hiModel);
                }
                $obj = new $this->hiModel;
            } else {
                $this->hiModel = 'app\\'.app("http")->getName().'\\model\\'.ucfirst($this->hiModel);
                $obj = new $this->hiModel;
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
        return $this->response(1,'状态设置成功');
    }

    /**
     * 通用排序
     * @author 祈陌 <3411869134@qq.com>
     */
    public function _sort()
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
                    $this->hiModel = 'plugins\\' . $this->request->param('_p') . '\\model\\' . ucfirst($this->hiModel);
                }
                $obj = new $this->hiModel;
            } else {
                $this->hiModel = 'app\\'.app("http")->getName().'\\model\\'.ucfirst($this->hiModel);
                $obj = new $this->hiModel;
            }
        } else if ($this->hiTable) {
            $obj = Db::name($this->hiTable);
        } else {
            return $this->response(0,'当前控制器缺少属性（hiModel、hiTable至少定义一个）');
        }
        $pk = $obj->getPk();
        $result = $obj->where([$pk => $id])->update([$field=>$val]);
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
    public function _table()
    {
        $this->buildData = $this->app->buildTable();
        if ($this->request->isAjax()) {
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
            $page = $this->request->param($this->hiPageName . '/d', 1);
            $limit = $this->request->param($this->hiLimitName . '/d', $this->hiLimitNum);
            if ($this->hiModel) {
                if (defined('IS_PLUGIN')) {
                    $this->hiModel = 'plugins\\' . $this->request->param('_p') . '\\model\\' . ucfirst($this->hiModel);
                    $model = new $this->hiModel;
                } else {
                    $this->hiModel = 'app\\'.app("http")->getName().'\\model\\'.ucfirst($this->hiModel);
                    $model = new $this->hiModel;
                }
                $counts = $model->where($this->hiWhere)->count();
                $data['data'] = $this->tableHasContact ? $model->with($this->tableHasContact)->where($this->hiWhere)->order($this->hiSort)->page($page,$limit)->select():$model->where($this->hiWhere)->order($this->hiSort)->page($page,$limit)->select();
            } else if ($this->hiTable) {
                $counts = Db::name(strtolower($this->hiTable))->where($this->hiWhere)->count();
                $data['data'] = Db::name(strtolower($this->hiTable))->where($this->hiWhere)->order($this->hiSort)->page($page,$limit)->select();
            }
            if(method_exists($this->app,'buildRow')){
                $data['data'] = $this->app->buildRow($data['data']);
            }
            $data['code'] = 0;
            $data['msg'] = '';
            if(isset($counts)){
                $data['count'] = $counts;
            }
            return json($data);
        }
        $this->assign('jsCode', $this->jsCode);
        $this->assign('cssCode', $this->cssCode);
        $this->assign('buildData', $this->buildData);
        return $this->view('build/table');
    }

    /**
     * 通用保存[新增/修改]
     * @return mixed
     * @author 祈陌 <3411869134@qq.com>
     */
    public function _save($postData = [], $type = 'add', $issave=true)
    {
        $this->buildData = $this->app->buildForm($type);
        //获取主键字段名
        if ($this->hiModel) { // 通过Model添加
            if (defined('IS_PLUGIN')) {
                $this->hiModel = 'plugins\\' . $this->request->param('_p') . '\\model\\' . ucfirst($this->hiModel);
                $model = new $this->hiModel;
            } else {
                $this->hiModel = 'app\\'.app("http")->getName().'\\model\\'.ucfirst($this->hiModel);
                $model = new $this->hiModel;
            }
            $pk = $model->getPk();
        }else if($this->hiTable){ // 通过Db添加
            $db = Db::name($this->hiTable);
            $pk = $db->getPk();
        }
        //post提交
        if ($this->request->isPost() && $issave !== false) {
            Db::startTrans();
            try {
                $hiModel = $this->request->param('hiModel');
                $hiTable = $this->request->param('hiTable');
                $hiValidate = $this->request->param('hiValidate');
                $hiScene = $this->request->param('hiScene');
                $hiMakeValidate = $this->request->param('hiMakeValidate');
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
                    $this->hiValidateScene = $hiScene;
                }
                if ($hiMakeValidate) {
                    $this->hiMakeValidate = $hiMakeValidate;
                }
                $postData = !empty($postData) ? $postData : $this->request->post();
                foreach ($postData as $k=>$v){ //解析数组字段(如checkbox表单)
                    if(!empty($v) && is_array($v)){
                        $postData[$k] = json_encode($v);
                    }
                }
                if ($this->hiValidate) {// 数据验证
                    if (strpos($this->hiValidate, '\\') === false) {
                        if (defined('IS_PLUGIN')) {
                            $this->hiValidate = 'plugins\\' . $this->request->param('_p') . '\\validate\\' . ucfirst($this->hiValidate);
                        } else {
                            $this->hiValidate = 'app\\' . app('http')->getName() . '\\validate\\' . ucfirst($this->hiValidate);
                        }
                    }
                    try {
                        validate($this->hiValidate)->scene((string)$this->hiValidateScene)->check($postData);
                    } catch (ValidateException $e) {
                        // 验证失败 输出错误信息
                        return $this->response(0, $e->getError());
                    }
                }else if($this->hiMakeValidate){ //数据独立验证
                    if(!array_key_exists('rules', $this->hiMakeValidate) || !array_key_exists('messages', $this->hiMakeValidate)){
                        return $this->response(0, 'make validate error');
                    }
                    try {
                        validate($this->hiMakeValidate['rules'], $this->hiMakeValidate['messages'])->check($postData);
                    } catch (ValidateException $e) {
                        // 验证失败 输出错误信息
                        return $this->response(0, $e->getError());
                    }
                }
                //前置资源数据操作
                if(method_exists($this->app,'annexBefore')){
                    $postData = $this->app->annexBefore($postData);
                    if(false === $postData){
                        if(method_exists($this->app,'getError')){
                            return $this->response(0, $this->app->getError());
                        }else{
                            return $this->response(0, 'error');
                        }
                    }
                }
                $condition = [];
                if ($this->hiModel) {// 通过Model添加
                    if(isset($postData[$pk]) && is_numeric($postData[$pk])){
                        $this->dataId = $postData[$pk];
                        $result = $model->where([$pk => $postData[$pk]])->strict(false)->update($postData);
                    }else if($this->multiPriCondition){
                        foreach ($this->multiPriCondition as $c){
                            $condition[$c] = $postData[$c];
                        }
                        if(null === $model->where($condition)->find()){
                            $result = $model->create($postData);
                            $this->dataId = $result->$pk;
                        }else{
                            $result = $model->where($condition)->strict(false)->update($postData);
                        }
                    }else{
                        $result = $model->create($postData);
                        $this->dataId = $result->$pk;
                    }
                    if (!$result && $result != null) {
                        return $this->response(0, $model->error ?? '保存失败');
                    }
                } else if ($this->hiTable) { // 通过Db添加
                    if(isset($postData[$pk]) && is_numeric($postData[$pk])){
                        $this->dataId = $postData[$pk];
                        $result = $db->where($pk, $postData[$pk])->strict(false)->update($postData);
                    }else if($this->multiPriCondition){
                        foreach ($this->multiPriCondition as $c){
                            $condition[$c] = $postData[$c];
                        }
                        if(null === $db->where($condition)->find()){
                            $result = $this->dataId = $db->strict(false)->insertGetId($postData);
                        }else{
                            $result = $db->where($condition)->strict(false)->update($postData);
                        }
                    }else{
                        $result = $this->dataId = $db->strict(false)->insertGetId($postData);
                    }
                    if (!$result) {
                        return $this->response(0,'保存失败');
                    }
                } else {
                    return $this->response(0,'当前控制器缺少属性（hiModel、hiTable至少定义一个）');
                }
                //后置资源数据操作
                if(method_exists($this->app,'annexAfter')){
                    $annexAfter = $this->app->annexAfter($this->dataId);
                    if(false === $annexAfter){
                        if(method_exists($this->app,'getError')){
                            return $this->response(0, $this->app->getError());
                        }else{
                            return $this->response(0, 'error');
                        }
                    }
                }
                Db::commit();
                return $this->response(1,'保存成功');
            } catch (\Exception $e) {
                Db::rollback();
                if(method_exists($this->app,'getError')){
                    return $this->response(0, $e->getMessage());
                }else{
                    return $this->response(0, 'error');
                }
            }
        }
        $params = $this->request->param();
        $condition = [];
        if(isset($params[$pk])){
            $condition[$pk] = $params[$pk];
            $this->buildData['buildForm']['items'][] = ['name'=>$pk, 'type'=>'hidden', 'value'=>$params[$pk]];
        }else if($this->multiPriCondition){
            foreach ($this->multiPriCondition as $c){
                if(isset($params[$c])){
                    $condition[$c] = $params[$c];
                }
            }
        }
        $row = [];
        if($condition){
            if ($this->hiModel) {
                $row = $model->where($condition)->find()->toArray();
            }else if($this->hiTable){
                $row = $db->where($condition)->find();
            }
            //资源数据解析
            if(method_exists($this->app,'annexGet')){
                $row = $this->app->annexGet($row);
            }
            foreach ($row as $k=>&$v){ //解析数组字段(如checkbox表单)
                $format = json_decode((string)$v, true);
                if($format) $v = $format;
            }
        }
        //合并赋值数据
        if(!empty($row) && !empty($this->assignData)){
            foreach ($this->assignData as $k=>$val){
                $row[$k] = $val;
            }
        }
        $this->assign('formData', $row);
        $this->assign('buildData', $this->buildData);
        return $this->view('build/form');
    }

    /**
     * 通用删除记录
     * @force 软删除 false为软删除
     * @return mixed
     * @author 祈陌 <3411869134@qq.com>
     */
    public function _remove($force=null)
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
            $where = $this->request->param();
        }
        if ($this->hiModel) {
            if (defined('IS_PLUGIN')) {
                if (strpos($this->hiModel, '\\') === false) {
                    $this->hiModel = 'plugins\\' . $this->request->param('_p') . '\\model\\' . $this->hiModel;
                }
                $modelObj = new $this->hiModel;
            } else {
                $this->hiModel = 'app\\'.app("http")->getName().'\\model\\'.ucfirst($this->hiModel);
                $modelObj = new $this->hiModel;
            }
            try {
                if(!empty($id) && is_array($id)){
                    foreach ($id as $v) {
                        $row = $modelObj->find($v);
                        if (!$row) continue;
                        //务必在模型里配置好软删除条件
                        $delRes = $force === false ? $row->delete() : $row->force()->delete();
                        if(!$delRes){
                            return $this->response(0, $row->error());
                        }
                    }
                }else{
                    $row = isset($where) ? $modelObj->where($where)->find() : $modelObj->find($id);
                    if (!$row) return $this->response(0, '数据不存在');
                    $delRes = $force === false ? $row->delete() : $row->force()->delete();
                    if(!$delRes){
                        return $this->response(0, $row->error());
                    }
                }
            } catch (\think\Exception $err) {
                return $this->response(0, $err->getMessage());
            }
        } else if ($this->hiTable) {
            $obj = Db::name($this->hiTable);
            $pk = $obj->getPk();
            if(is_array($id)) {
                $delRes = $force === false ? $obj->useSoftDelete('delete_time', time())->where($pk, 'in', $id)->delete() : $obj->where($pk, 'in', $id)->delete();
                if(!$delRes){
                    return $this->response(0, '删除失败');
                }
            }else{
                $delRes = $force === false ? $obj->useSoftDelete('delete_time',time())->where($pk, $id)->delete() : $obj->where($pk, $id)->delete();
                if(!$delRes){
                    return $this->response(0, '删除失败');
                }
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
     * 升级前
     * @return mixed
     */
    public function upgrade(){
        return true;
    }
    /**
     * 升级后
     * @return mixed
     */
    public function upgradeAfter(){
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