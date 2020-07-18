<?php declare(strict_types=1);
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

use app\system\model\SystemRole as RoleModel;
use app\system\model\SystemUser as UserModel;

class User extends Base
{

    public function index()
    {
        if ($this->request->isAjax()) {
            $where = $data = [];
            $page = $this->request->param('page/d', 1);
            $limit = $this->request->param('limit/d', 15);
            $keyword = $this->request->param('keyword/s');
            $where[] = ['id', '<>', 0];
            if ($keyword) {
                $where[] = ['username', 'like', "%{$keyword}%"];
            }
            $data['data'] = UserModel::where($where)->page($page)->limit($limit)->select();
            $data['count'] = UserModel::where($where)->count('id');
            $data['code'] = 0;
            $data['msg'] = '';
            return json($data);
        }
        $assign['roles'] = RoleModel::column('id,name');
        $this->assign($assign);
        return $this->view();
    }

    public function add()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            $data['password'] = md5($data['password']);
            $data['password_confirm'] = md5($data['password_confirm']);
            $result = $this->validate($data, 'systemUser');
            if ($result !== true) {
                return $this->response(0,$result);
            }
            unset($data['id'], $data['password_confirm']);
            $data['last_login_ip'] = '';
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            if (!UserModel::create($data)) {
                return $this->response(0,'添加失败');
            }
            return $this->response(1,'添加成功');
        }
        $this->assign('roles', RoleModel::where('id', '>', 1)->order('id asc')->column('id,name'));
        return $this->view('storage');
    }

    public function edit($id = 0)
    {
        if ($id == 1 || ADMIN_ID == $id) {
            return $this->response(0,'禁止修改当前登录用户');
        }

        if ($this->request->isPost()) {
            $data = $this->request->post();
            if ($data['password']) {
                $data['password'] = md5($data['password']);
                $data['password_confirm'] = md5($data['password_confirm']);
            }
            $result = $this->validate($data, 'systemUser.update');
            if ($result !== true) {
                return $this->response(0,$result);
            }
            if ($data['password']) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            } else {
                unset($data['password']);
            }
            if (!UserModel::update($data)) {
                return $this->response(0,'修改失败');
            }
            return $this->response(1,'修改成功');
        }
        $row = UserModel::where('id', $id)->field('id,username,role_id,nick,email,mobile,status')->find()->toArray();
        $this->assign('roles', RoleModel::where('id', '>', 1)->order('id asc')->column('id,name'));
        $this->assign('formData', $row);
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
        $result = UserModel::where(['id' => $id])->update([$field=>$val]);
        if ($result === false) {
            return $this->response(0,'状态设置失败');
        }
        return $this->response(1,'状态设置成功', '', ['respond' => [$result, 'id', $id, $val]]);
    }

    public function remove()
    {
        $ids = $this->request->param('id/a');
        $model = new UserModel();
        if ($model->remove($ids)) {
            return $this->response(1,'删除成功');
        }
        return $this->response(0,$model->error);
    }


}
