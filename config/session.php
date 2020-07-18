<?php
// +----------------------------------------------------------------------
// | 会话设置
// +----------------------------------------------------------------------

return [
    // session name
    'name'           => 'PHPSESSID',
    // SESSION_ID的提交变量,解决flash上传跨域
    'var_session_id' => '',
    // 驱动方式 支持file cache
    'type'           => 'file',
    // 存储连接标识 当type使用cache的时候有效
    'store'          => null,
    // 过期时间
<<<<<<< HEAD
    'expire'         => 7200,
=======
    'expire'         => 1440,
>>>>>>> d52ad045fd7ed93be13a3b7bc1a6f4fbc770c8c7
    // 前缀
    'prefix'         => '',
];
