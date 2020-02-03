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
use think\facade\Cache;
use app\system\model\SystemRole as RoleModel;
use app\system\model\SystemMenu as MenuModel;
class Role extends Base
{

    public function index()
    {
        if ($this->request->isAjax()) {
            $data = [];
            $page = $this->request->param('page/d', 1);
            $limit = $this->request->param('limit/d', 15);
            $data['data'] = RoleModel::where('id', '<>', 1)->select()->toArray();
            $data['count'] = RoleModel::where('id', '<>', 1)->count('id');
            $data['code'] = 0;
            $data['msg'] = '';
            return json($data);
        }
        return $this->view();
    }

    public function add()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            $result = $this->validate($data, 'SystemRole');
            if($result !== true) {
                $this->response(0,$result);
            }
            unset($data['id']);
            sort($data['auth']);
            if (!RoleModel::create($data)) {
                $this->response(0,'添加失败');
            }
            Cache::tag('menus')->clear();
            $this->response(1,'添加成功');
        }
        $tabData = [];
        $tabData['tab'] = [['title' => '添加角色'], ['title' => '设置权限']];
        $this->assign('nodes', MenuModel::getAuthTree(0));
        $this->assign('tabData', $tabData);
        $this->assign('tabType', 2);
        return $this->view('storage');
    }

    public function edit($id = 0)
    {
        if ($id <= 1) {
            $this->response(0,'禁止编辑');
        }
        if ($this->request->isPost()) {
            $data = $this->request->post();
            // 当前登陆用户不可更改自己的分组角色
            if (ADMIN_ROLE == $data['id']) {
                $this->response(0,'禁止修改当前角色(原因：您不是超级管理员)');
            }
            $result = $this->validate($data, 'systemRole');
            if($result !== true) {
                $this->response(0,$result);
            }
            sort($data['auth']);
            if (!RoleModel::update($data)) {
                $this->response(0,'修改失败');
            }
            Cache::tag('menus')->clear();
            $this->response(1,'修改成功');
        }
        $row = RoleModel::where('id', $id)->field('id,name,intro,auth,status')->find()->toArray();
        $tabData = [];
        $tabData['tab'] = [['title' => '添加角色'], ['title' => '设置权限']];
        $this->assign('tabData', $tabData);
        $this->assign('tabType', 2);
        $this->assign('formData', $row);
        $this->assign('nodes', MenuModel::getAuthTree(0, $row['auth']));
        return $this->view('storage');
    }

    public function status()
    {
        $val = $this->request->param('v/d');
        $id = $this->request->param('id/a');
        $field = $this->request->param('field/s', 'status');
        if (empty($id)) {
            return $this->response(0,'缺少id参数');
        }
        $result = RoleModel::where(['id' => $id])->update([$field=>$val]);
        if ($result === false) {
            return $this->response(0,'状态设置失败');
        }
        return $this->response(1,'状态设置成功', '', ['respond' => [$result, 'id', $id, $val]]);
    }

    public function remove()
    {
        $ids   = $this->request->param('id/a');
        $model = new RoleModel();
        if ($model->remove($ids)) {
            $this->response(1,'删除成功');
        }
        $this->response(0, $model->error);
    }

}
