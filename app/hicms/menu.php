<?php
return [
    [
        'module'=>'hicms',
        'title'=>'内容',
        'icon'=>'icon iconfont iconversion',
        'param'=>'',
        'url'=>'hicms/index/index',
        'create_time'=>time(),
        'childs'=>[
            [
                'module'=>'hicms',
                'title'=>'文章管理',
                'icon'=>'',
                'param'=>'',
                'url'=>'hicms/article/index',
                'sort'=>0,
                'create_time'=>time(),
                'childs'=>[
                    [
                        'module'=>'hicms',
                        'title'=>'新增文章',
                        'icon'=>'',
                        'param'=>'',
                        'url'=>'hicms/article/add',
                        'sort'=>0,
                        'spread'=>1,
                        'create_time'=>time(),
                    ],
                    [
                        'module'=>'hicms',
                        'title'=>'修改文章',
                        'icon'=>'',
                        'param'=>'',
                        'url'=>'hicms/article/edit',
                        'sort'=>1,
                        'spread'=>1,
                        'create_time'=>time(),
                    ]
                ]
            ],
            [
                'module'=>'hicms',
                'title'=>'栏目管理',
                'icon'=>'',
                'param'=>'',
                'url'=>'hicms/category/index',
                'sort'=>1,
                'create_time'=>time(),
                'childs'=>[
                    [
                        'module'=>'hicms',
                        'title'=>'新增文章',
                        'icon'=>'',
                        'param'=>'',
                        'url'=>'hicms/category/add',
                        'sort'=>0,
                        'spread'=>1,
                        'create_time'=>time(),
                    ],
                    [
                        'module'=>'hicms',
                        'title'=>'修改文章',
                        'icon'=>'',
                        'param'=>'',
                        'url'=>'hicms/category/edit',
                        'sort'=>1,
                        'spread'=>1,
                        'create_time'=>time(),
                    ]
                ]
            ]
        ]
    ]
];