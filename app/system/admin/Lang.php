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
use app\system\model\SystemLang as LangModel;
use think\facade\Db;

class Lang extends Base
{
    protected function initialize()
    {
        parent::initialize();
        //加载构建器
        if(!array_key_exists('builder', cache('plugins'))){
            $this->response(0, '构建器未安装');
        }
        $this->builder = new \plugins\builder\builder();
    }

    public function index($group = '', $type = '')
    {
        $status = $this->request->param('status/d', 0);
        $this->tabData['current'] = url('',['type'=>$type, 'group'=>$group, 'status'=>$status]);
        $this->tabData['tab'] = [
            [
                'title' => '语言包选择',
                'url' => url('', ['type'=>$type, 'group'=>$group, 'status' => 0]),
            ],
            [
                'title' => '语言变量设置',
                'url' => url('', ['type'=>$type, 'group'=>$group, 'status' => 1]),
            ]
        ];
        if (0 == $status) {
            if ($this->request->isPost()) {
                $name = $this->request->param('name/s', 0);
                $res = LangModel::setDefaultLanguage($group, $name);
                return $this->response(1,'设置成功');
            }
            $formData = Db::name('system_language')->where(['group'=>$group, 'default'=>1])->find();
            $buildData = $this->buildFormLangSetting();
        }else if(1 == $status){
            if ($this->request->isPost()) {
                foreach ($this->request->post() as $k=>$v){
                    $res = LangModel::where(['group'=>$this->request->param('group/s'), 'pack'=>$this->request->post('packid/d'), 'name'=>$k])->update(['langvar'=>$v]);
                }
                cache('lang_default_'.$group, null);
                return $this->response(1,'已修改');
            }
            $default = Db::name('system_language')->where(['group'=>$group, 'default'=>1])->find();
            $formData = LangModel::where(['group'=>$group, 'pack'=>$default['id']])->find();
            $buildData = $this->buildFormLang($group, $type, $default['name']);
        }
        $this->assign('formData', $formData);
        $this->assign('tabData', $this->tabData);
        $this->assign('buildData', $buildData);
        $this->assign('tabType', 3);
        return $this->view('build/form');
    }

    private function buildFormLang($group, $type, $packName){
        $result = $this->builder->buildData();
        $result['buildForm']['action'] = $this->tabData['current'];
        $langs = LangModel::getDefaultLang($group);
        $fieldItems = [];
        $langInfos = 0 == $type ? include_once base_path() . $group . '/lang/' . $packName . '.php' : include_once root_path() . 'plugins/' . $group . '/lang/' . $packName . '.php';
        $i = 0;
        foreach ($langInfos as $key=>$v){
            $fieldItems[$i] = ['title'=>$key, 'type'=>'line'];
            $i++;
            foreach ($v as $kk=>$vv){
                foreach ($langs as $kkk=>$vvv){
                    $keys = array_keys($v);
                    $fieldItems[$i]['title'] = $keys[$kkk];
                    $fieldItems[$i]['name'] = $vvv['name'];
                    $fieldItems[$i]['type'] = iconv_strlen($vvv['langvar'],"UTF-8") > 100 ? 'textarea' : 'text';
                    $fieldItems[$i]['value'] = $vvv['langvar'];
                    $fieldItems[$i]['tips'] = '调用字段为 [ '.$vvv['name'].' ]';
                    $i++;
                }
            }
        }
        $languege = Db::name('system_language')->where(['name'=>$packName])->find();
        $fieldItems[] = ['type'=>'hidden','name'=>'packid', 'value'=>$languege['id']];
        $result['buildForm']['items'] = array_values(array_column($fieldItems, NULL, 'name'));
        return $result;
    }

    private function buildFormLangSetting(){
        $result = $this->builder->buildData();
        $result['buildForm']['action'] = $this->tabData['current'];
        $result['buildForm']['items'] = [
            [
                'name'=>'name',
                'type'=>'select',
                'title'=>'语言包选择',
                'value'=>'china',
                'tips'=>'',
                'options'=>['china'=>'china','english'=>'english']
            ]
        ];
        return $result;
    }

}
