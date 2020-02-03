<?php
namespace plugins\devhelp\admin;
defined('IN_SYSTEM') or die('Access Denied');
use app\system\model\SystemPlugin as PluginModel;
use plugins\devhelp\model\SystemPlugin as PluginPluginModel;
/**
 * [测试插件]后台Index控制器
 * @package plugins\test\admin
 */
class Plugin extends Admin
{
    protected function initialize()
    {
        parent::initialize();
        $this->tabData['tab'] = [
            [
                'title' => '插件列表',
                'url' => url('', ['_a'=>'index','_c'=>'plugin','_p'=>'devhelp']),
            ],
            [
                'title' => '生成插件',
                'url' => url('', ['_a'=>'create','_c'=>'plugin','_p'=>'devhelp']),
            ]
        ];
        $this->appPath = root_path().'plugins/';
    }

    public function index()
    {
        $tabData = $this->tabData;
        $where['system'] = 0;
        $data = PluginModel::where($where)->order('sort,id')->column('id,name,title,author,intro,icon,version');
        $this->assign('dataInfo', $data);
        $this->assign('tabData', $tabData);
        $this->assign('tabType', 3);
        return $this->view();
    }

    public function create(){
        if ($this->request->isPost()) {
            $pluginObj = new PluginPluginModel();
            $_param = request()->param();

            if(isset($_param['icon']) && $_param['icon']){
                $image = getimagesize('.'.$_param['icon']);
                if ($image[0] !== 200 || $image[1] !== 200 ) {
                    unlink('.'.$_param['icon']);
                    return $this->response(0,'图标尺寸不符合要求(200px * 200px)');
                }
            }
            $result = $pluginObj->design($_param);
            if(!$result){
                return $this->response(0,$pluginObj->error);
            }
            if(isset($_param['icon']) && $_param['icon']){
                copy('.'.$_param['icon'], './static/p_'.$_param['name'].'/images/app.png');
            }
            return$this->response(1,'已生成');
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
        $module = PluginModel::where('id', $id)->field('id,name,title,intro,author,version,url,identifier,icon')->find()->toArray();
        if (!$module) {
            return $this->response(0,'插件不存在');
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
                $appIconDir = '/static/p_'.$_param['name'].'/'.$_param['name'].'.png';
                copy('.'.$_param['icon'], '.'.$appIconDir);
                $_param['icon'] = $appIconDir;
            }
            if (!PluginModel::update($_param)) {
                return $this->response(0,'修改失败');
            }
            return$this->response(1,'保存成功');
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
        $result = $this->buildData();
        $result['buildForm']['module'] = 'devhelp';
        $result['buildForm']['action'] = $this->tabData['current'];
        $result['buildForm']['upload_group'] = 'p_devhelp';
        $result['buildForm']['items'] = [
            [
                'name'=>'name',
                'type'=>'input',
                'title'=>'插件名',
                'tips'=>'只能是字母，格式：开发者标识+字母串',
                'value'=>'',
                'options'=>''
            ],
            [
                'name'=>'title',
                'type'=>'input',
                'title'=>'插件标题',
                'tips'=>'',
                'value'=>'',
                'options'=>''
            ],
            [
                'name'=>'identifier',
                'type'=>'input',
                'title'=>'插件标识',
                'tips'=>'格式：模块名(只能为字母).开发者标识(只能为字母/数字/下划线).plugin',
                'value'=>'',
                'options'=>''
            ],
            [
                'name'=>'icon',
                'type'=>'image',
                'title'=>'插件图标',
                'value'=>'',
                'tips'=>'尺寸要求: 200 x 200 px',
            ],
            [
                'name'=>'intro',
                'type'=>'textarea',
                'title'=>'插件描述',
            ],
            [
                'name'=>'url',
                'type'=>'input',
                'title'=>'开发者网址',
                'tips'=>'建议填写',
                'value'=>'',
            ],
            [
                'name'=>'author',
                'type'=>'input',
                'title'=>'开发者',
                'tips'=>'建议填写',
                'value'=>'',
                'options'=>''
            ],
            [
                'name'=>'version',
                'type'=>'input',
                'title'=>'版本号',
                'tips'=>'格式采用三段式: 主版本号.次版本号.修订版本号',
                'value'=>'1.0.0',
                'options'=>''
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