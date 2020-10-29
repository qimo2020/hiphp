<?php declare(strict_types=1);
namespace app\member\admin;
use app\member\model\Member as memberModel;
class Member extends Common
{
    protected function initialize()
    {
        parent::initialize();
        $this->buiderObj->hiModel = 'Member';
    }

    public function index()
    {
        if ($this->request->isAjax()) {
            $data = [];
            $page = $this->request->param('page/d', 1);
            $limit = $this->request->param('limit/d', 30);
            $data['data'] = memberModel::withJoin('auth', 'LEFT')->where([['auth.account','<>','']])->page($page)->limit($limit)->select();

            $data['count'] = memberModel::count('id');
            $data['code'] = 0;
            $data['msg'] = '';
            return json($data);
        }
        $this->assign('buildData', $this->buildTable());
        return $this->view('build/table');
    }

    public function add()
    {
        if($this->request->isPost()){
            return $this->save();
        }
        return $this->buiderObj->_save();
    }

    public function edit(){
        if($this->request->isPost()){
            return $this->save();
        }else{
            $params = $this->request->get();
            $this->buiderObj->assignData['tid'] = $params['tid'];
            $first = \app\member\model\MemberAuth::find($params['id']);
            $this->buiderObj->assignData['account'] = $first->account;
        }
        return $this->buiderObj->_save([], 'edit');
    }

    public function save(){
        $post = $this->request->post();
        $model = new memberModel();
        $type = \app\member\model\MemberAuthType::find($post['tid'])->toArray();
        if(isset($post['account']) && null !== $type && $type['rule']){
            $post[$type['identifier']] = $post['account'];
            $rules[$type['identifier'].'|'.$type['title']] = $type['rule'];
            if(count($rulesArr = explode('|', $type['rule'])) != count($messageArr = explode('|', $type['message']))){
                return $this->response(0, $this->messages['validate_rule_error']);
            }
            foreach($messageArr as $k=>$v){
                $messages[$type['identifier'].'.'.(strpos($rulesArr[$k], ':') !== false ? explode(':', $rulesArr[$k])[0] : $rulesArr[$k])] = $v;
            }
            try {
                validate()->rule(['account'=>['unique:member_auth']])->rule($rules)->message(['account.unique'=>$this->messages['account_exist']])->message($messages)->check($post);
                validate(\app\member\validate\Member::class)->check($post);
            } catch (\think\exception\ValidateException $e) {
                return $this->response(0, $e->getError());
            }
        }
        $post['password'] = md5($post['password']);
        $result = $model->_save($post);
        if($result === false){
            return $this->response(0, $model::$error);
        }
        return $this->response(1, $this->messages['save_success']);
    }

    public function status()
    {
        return $this->buiderObj->status();
    }

    public function remove()
    {
        return $this->buiderObj->_remove();
    }

    public function buildForm($op = 'add'){
        $result = \app\member\model\MemberAuthType::column('id,title');
        $identifiers = array_column($result, 'title', 'id');
        $result = $this->buiderObj->buildData();
        $result['buildForm']['upload_group'] = 'm_member';
        $result['buildForm']['items'] = [
            [
                'name'=>'tid',
                'type'=>'select',
                'title'=>'账号类型',
                'tips'=>'',
                'value'=>'',
                'options'=>$identifiers,
                'attribute'=>$op=='add'?'':'disabled'
            ],
            [
                'name'=>'account',
                'type'=>'input',
                'title'=>'账号',
                'tips'=>'账号格式:中文/字母/数字',
                'value'=>'',
            ],
            [
                'name'=>'nick',
                'type'=>'input',
                'title'=>'昵称',
                'tips'=>'中文/字母/数字等',
                'value'=>'',
            ],
            [
                'name'=>'password',
                'type'=>'password',
                'title'=>'密码',
                'tips'=>'不能低于6位',
                'value'=>'',
            ],
            [
                'name'=>'password_confirm',
                'type'=>'password',
                'title'=>'确认密码',
                'tips'=>'不能低于6位',
                'value'=>''
            ],
        ];
        if($op == 'edit'){
            $result['buildForm']['items'][] = [
                'name'=>'tid',
                'type'=>'hidden',
                'value'=>'',
            ];

        }
        return $result;
    }
    public function buildTable()
    {
        $result = $this->buiderObj->buildData();
        $result['buildTable'] = [
            'toolbar' => [
                [
                    'title' => '添加',
                    'url' => 'add',
                    'class' => 'layui-btn layui-btn-sm layui-btn-normal hi-iframe-pop',
                    'data'=>[
                        'title'=>'添加会员',
                    ]
                ],
                [
                    'title' => '启用',
                    'url' => 'status?v=1',
                    'class' => 'layui-btn layui-btn-sm hi-table-ajax',
                ],
                [
                    'title' => '禁用',
                    'url' => 'status?v=0',
                    'class' => 'layui-btn layui-btn-sm layui-btn-warm hi-table-ajax',
                ],
                [
                    'title' => '删除',
                    'url' => 'remove',
                    'class' => 'layui-btn layui-btn-sm layui-btn-danger j-page-btns',
                    'data'=>[
                        'title'=>'删除会员',
                    ]
                ],
            ],
            'config' => [
                'page' => true,
                'limit' => 20,
                'cols' => [
                    [
                        'type' => 'checkbox',
                    ],
                    [
                        'field' => 'id',
                        'title' => 'ID',
                        'width' => 80,
                    ],
                    [
                        'field' => 'nick',
                        'title' => '用户昵称',
                        'width' => 250,
                    ],
                    [
                        'field' => 'last_login_time',
                        'title' => '最近登录时间',
                        'width' => 200,
                    ],
                    [
                        'field' => 'create_time',
                        'title' => '创建时间',
                        'width' => 200,
                    ],
                    [
                        'field' => 'status',
                        'title' => '状态',
                        'templet' => '#switchStatusTpl',
                        'type'=>'switch',
                        'operate' => [
                            'filter'=>'switchStatus',
                            'url'=>url('status'),
                            'text'=>"开启|关闭"
                        ],
                        'width' => 100,
                    ],
                    [
                        'title' => '操作',
                        'templet' => '#perateTpl',
                        'type'=>'button',
                        'operate' => [
                            [
                                'text'=>'编辑',
                                'url'=>url('edit').'?id={{d.id}}&tid={{d.auth.tid}}',
                                'class'=>"layui-btn-normal",
                            ],
                            [
                                'text'=>'删除',
                                'url'=>url('remove').'?id={{d.id}}&tid={{d.auth.id}}',
                                'class'=>"layui-btn-danger j-tr-del"
                            ]
                        ],
                        'width' => 200,
                    ],
                ],
            ],
        ];
        return $result;
    }

}