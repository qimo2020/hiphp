<?php declare(strict_types=1);
namespace app\member\admin;
class Memberauth extends Common
{
    protected function initialize()
    {
        parent::initialize();
        $this->buiderObj->hiModel = 'MemberAuthType';
    }

    public function index()
    {
        return $this->buiderObj->_table();
    }

    public function add()
    {
        if($this->request->isPost()){
            $post = $this->request->post();
            if($post['rule_hook'] && $post['rule']){
                return $this->response(0, '钩子验证与验证规则不能同时设置');
            }
        }
        return $this->buiderObj->_save();
    }

    public function edit(){

        return $this->buiderObj->_save([], 'edit');
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
        $result = $this->buiderObj->buildData();
        $result['buildForm']['upload_group'] = 'm_member';
        if($op == 'add'){
            $result['buildForm']['hiData'] = ['pop'=>true];
        }
        $result['buildForm']['items'] = [
            [
                'name'=>'title',
                'type'=>'input',
                'title'=>'授权方式',
                'tips'=>'',
                'value'=>'',
            ],
            [
                'name'=>'identifier',
                'type'=>'input',
                'title'=>'授权标识',
                'tips'=>'必须是小写字母',
                'value'=>'',
            ],
            [
                'name'=>'rule_hook',
                'type'=>'input',
                'title'=>'前置钩子',
                'tips'=>'新增用户数据前的嵌入点',
                'value'=>'',
            ],
            [
                'name'=>'rule_hook_after',
                'type'=>'input',
                'title'=>'后置钩子',
                'tips'=>'新增用户数据后的嵌入点',
                'value'=>'',
            ],
            [
                'name'=>'rule_hook_tp',
                'type'=>'input',
                'title'=>'前端钩子',
                'tips'=>'可用于前端构建js逻辑',
                'value'=>'',
            ],
            [
                'name'=>'rule',
                'type'=>'input',
                'title'=>'验证规则',
                'tips'=>'可以参考tp5.1或tp6.0的验证规则',
                'value'=>'',
            ],
            [
                'name'=>'message',
                'type'=>'input',
                'title'=>'验证提示',
                'tips'=>'验证规则的错误提示，请按顺序与验证规则对应',
                'value'=>'',
            ],
            [
                'name'=>'check_after',
                'type'=>'switch',
                'title'=>'插件验证',
                'tips'=>'是否需第三方插件验证',
                'value'=>0,
                'options'=>['关闭', '开启']
            ],
            [
                'name'=>'status',
                'type'=>'switch',
                'title'=>'状态',
                'tips'=>'',
                'value'=>1,
                'options'=>['关闭', '开启']
            ]
        ];

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
                        'title'=>'添加',
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
                        'title'=>'删除',
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
                        'field' => 'title',
                        'title' => '授权方式',
                        'width' => 200,
                    ],
                    [
                        'field' => 'identifier',
                        'title' => '授权标识',
                        'width' => 250,
                    ],
                    [
                        'field' => 'status',
                        'title' => '状态',
                        'templet' => '#switchStatusTpl',
                        'type'=>'switch',
                        'operate' => [
                            'filter'=>'switchStatus',
                            'url'=>'status',
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
                                'url'=>url('edit').'?id={{d.id}}',
                                'class'=>"layui-btn-normal"
                            ],
                            [
                                'text'=>'删除',
                                'url'=>url('remove').'?id={{d.id}}',
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