<?php
namespace plugins\devhelp\admin;
defined('IN_SYSTEM') or die('Access Denied');
use app\system\model\SystemModule as ModuleModel;
use plugins\devhelp\model\SystemModule as PluginModuleModel;

/**
 * [测试插件]后台Index控制器
 * @package plugins\test\admin
 */
class Module extends Admin
{
    protected function initialize()
    {
        parent::initialize();
        $this->tabData['tab'] = [
            [
                'title' => '模块列表',
                'url' => url('',['_a'=>'index', '_c'=>'module', '_p'=>'devhelp']),
            ],
            [
                'title' => '生成模块',
                'url' => url('',['_a'=>'create', '_c'=>'module', '_p'=>'devhelp']),
            ]
        ];
        if(!cache('plugins') || !array_key_exists('builder', cache('plugins'))){
            exit('提示: 你还没安装【后台开发构建器】插件');
        }
        $this->buiderObj = new \plugins\builder\builder();
    }

    public function index()
    {
        $tabData = $this->tabData;
        $where['system'] = 0;
        $data = ModuleModel::where($where)->order('sort,id')->column('id,name,title,author,intro,icon,version');
        $this->assign('dataInfo', $data);
        $this->assign('tabData', $tabData);
        $this->assign('tabType', 3);
        return $this->view();
    }

    public function create(){
        if ($this->request->isPost()) {
            $moduleObj = new PluginModuleModel();
            $_param = request()->param();
            if(isset($_param['icon']) && $_param['icon']){
                $image = getimagesize('.'.$_param['icon']);
                if ($image[0] !== 200 || $image[1] !== 200 ) {
                    unlink('.'.$_param['icon']);
                    return $this->response(0,'图标尺寸不符合要求(200px * 200px)');
                }
            }
            $result = $moduleObj->design($_param);
            if(!$result){
                return $this->response(0,$moduleObj->error);
            }
            if(isset($_param['icon']) && $_param['icon']){
                $iconPath = '/static/m_'.$_param['name'].'/images/app.png';
                copy('.'.$_param['icon'], '.'.$iconPath);
                unlink('.'.$_param['icon']);
                ModuleModel::where(['name'=>$_param['name']])->update(['icon'=>$iconPath]);
            }
            return $this->response(1,'已生成');
        }
        $tabData = $this->tabData;
        $buildData = $this->buildForm();
        $this->assign('tabData', $tabData);
        $this->assign('tabType', 3);
        $this->assign('buildData', $buildData);
        return $this->view('build/form');
    }

    public function design()
    {
        $id = getNum();
        if (config('system.app_debug') == 0) {
            return $this->response(0,'非开发模式禁止使用此功能');
        }
        $module = ModuleModel::where('id', $id)->field('id,name,title,intro,author,version,url,identifier,icon')->find()->toArray();
        if (!$module) {
            return $this->response(0,'模块不存在');
        }
        if ($this->request->isPost()) {
            $_param = request()->param();
            if(isset($_param['icon']) && $_param['icon']){
                $image = getimagesize('.'.$_param['icon']);
                if ($image[0] !== 200 || $image[1] !== 200 ) {
                    unlink('.'.$_param['icon']);
                    return $this->response(0,'图标尺寸不符合要求(200px * 200px)');
                }
            }
            if(isset($_param['icon']) && $_param['icon']){
                $appIconDir = '/static/m_'.$_param['name'].'/app.png';
                copy('.'.$_param['icon'], '.'.$appIconDir);
                $_param['icon'] = $appIconDir;
            }
            if (!ModuleModel::update($_param)) {
                return $this->response(0,'修改失败');
            }
            return $this->response(1,'保存成功');
        }
        $buildData = $this->buildForm();
        foreach ($buildData['buildForm']['items'] as $k=>$v){
            if(in_array($v['name'], ['line', 'file', 'dir'])){
                unset($buildData['buildForm']['items'][$k]);
            }
        }
        $buildData['buildForm']['items'][] = ['name'=>'id', 'type'=>'hidden', 'value'=>$id];

        $this->assign('formData', $module);
        $this->assign('buildData', $buildData);
        return $this->view('build/form');
    }

    private function buildForm(){
        $result = $this->buiderObj->buildData();
        $result['buildForm']['module'] = 'devhelp';
        $result['buildForm']['action'] = $this->tabData['current'];
        $result['buildForm']['upload_group'] = 'p_devhelp';
        $result['buildForm']['items'] = [
            [
                'name'=>'name',
                'type'=>'input',
                'title'=>'模块名',
                'tips'=>'只能是字母，格式：开发者标识+字母串',
                'value'=>'',
                'options'=>'',
                'verify'=>'required'
            ],
            [
                'name'=>'title',
                'type'=>'input',
                'title'=>'模块标题',
                'tips'=>'',
                'value'=>'',
                'options'=>'',
                'verify'=>'required'
            ],
            [
                'name'=>'identifier',
                'type'=>'input',
                'title'=>'模块标识',
                'tips'=>'格式：模块名(只能为字母).开发者标识(只能为字母/数字/下划线).module',
                'value'=>'',
                'options'=>'',
                'verify'=>'required'
            ],
            [
                'name'=>'icon',
                'type'=>'image',
                'title'=>'模块图标',
                'value'=>'',
                'tips'=>'尺寸要求: 200 x 200 px',
            ],
            [
                'name'=>'intro',
                'type'=>'textarea',
                'title'=>'模块描述',
                'verify'=>'required'
            ],
            [
                'name'=>'url',
                'type'=>'input',
                'title'=>'开发者网址',
                'tips'=>'建议填写',
                'value'=>'',
                'verify'=>'required'
            ],
            [
                'name'=>'author',
                'type'=>'input',
                'title'=>'开发者',
                'tips'=>'建议填写',
                'value'=>'',
                'options'=>'',
                'verify'=>'required'

            ],
            [
                'name'=>'version',
                'type'=>'input',
                'title'=>'版本号',
                'tips'=>'格式采用三段式: 主版本号.次版本号.修订版本号',
                'value'=>'1.0.0',
                'options'=>'',
                'verify'=>'required'
            ],
            [
                'name'=>'line',
                'type'=>'line',
                'title'=>'快速生成目录',
            ],
            [
                'name'=>'file',
                'type'=>'input',
                'title'=>'公共文件',
                'tips'=>'多个文件以","号隔开',
                'value'=>'common.php',
                'options'=>''
            ],
            [
                'name'=>'dir',
                'type'=>'array',
                'title'=>'模块目录',
                'tips'=>'admin为后台模块;home为前台目录',
                'value'=>"admin\r\nhome\r\nmodel\r\nsql",
                'options'=>''
            ]
        ];
        return $result;
    }


}