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

use app\system\model\SystemHook as HookModel;
use app\system\model\SystemHookPlugin as HookPluginModel;

/**
 * 钩子控制器
 * @package app\system\admin
 */
class Hook extends Base
{
    public function index()
    {
        if ($this->request->isAjax()) {
            $where = $data = [];
            $page = $this->request->param('page/d', 1);
            $limit = $this->request->param('limit/d', 15);
            $keyword = $this->request->param('keyword');
            if ($keyword) {
                $where[] = ['name', 'like', "%{$keyword}%"];
            }
            $data['data'] = HookModel::where($where)->page($page)->limit($limit)->select();
            $data['count'] = HookModel::where($where)->count('id');
            $data['code'] = 0;
            return json($data);
        }
        return $this->view();
    }

    public function add()
    {
        if ($this->request->isPost()) {
            $moduleObj = new HookModel();
            if (!$moduleObj->storage()) {
                return $this->response(0,$moduleObj->getError());
            }
            return $this->response(1,'保存成功');
        }
        $this->assign('hook_plugins', '');
        return $this->view('storage');
    }

    public function edit($id = 0)
    {
        if ($this->request->isPost()) {
            $mod = new HookModel();
            if (!$mod->storage()) {
                return $this->response(0,$mod->getError());
            }
            return $this->response(1,'保存成功');
        }
        $row = HookModel::where('id', $id)->field('id,name,intro,system')->find()->toArray();
        if ($row['system'] == 1) {
            return $this->response(0,'禁止编辑系统钩子');
        }
        // 关联的插件
        $hookPlugins = HookPluginModel::where('hook', $row['name'])->order('sort')->column('id,plugins,status,sort');
        $this->assign('formData', $row);
        $this->assign('hook_plugins', $hookPlugins);
        return $this->view('form');
    }

    public function remove()
    {
        $id = $this->request->param('id/a');
        $map = [];
        $map['id'] = $id;
        $rows = HookModel::where($map)->field('id,system')->select();
        foreach ($rows as $v) {
            // 排除系统钩子
            if ($v['system'] == 1) {
                return $this->response(0,'禁止删除系统钩子');
            }
        }
        $map = [];
        $map['id'] = $id;
        $res = HookModel::where($map)->delete();
        if ($res === false) {
            return $this->response(0,'操作失败');
        }
        return $this->response(1,'操作成功');
    }

    public function status()
    {
        $id = $this->request->param('id/a');
        $val = $this->request->param('v/d');
        $map = [];
        $map['id'] = $id;
        $rows = HookModel::where($map)->field('id,system')->select();
        foreach ($rows as $v) {
            if ($v['system'] == 1) {
                return $this->response(0,'禁止操作系统钩子');
            }
        }
        $res = HookModel::where($map)->update(['status'=>$val]);
        if ($res === false) {
            return $this->response(0,'操作失败');
        }
        return $this->response(1,'操作成功');
    }

    public function hookPluginStatus()
    {
        $id = $this->request->param('id/a');
        $val = $this->request->param('val/d');
        $map = [];
        $map['id'] = $id;
        $res = HookPluginModel::where($map)->update(['status'=>$val]);

        if ($res === false) {
            return $this->response(0,'操作失败');
        }

        return $this->response(1,'操作成功');
    }
}
