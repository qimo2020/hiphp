CREATE TABLE `pre_member` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(36) NOT NULL,
  `nick` varchar(30) COMMENT '昵称',
  `avatar` varchar(200) COMMENT '头像',
  `status` tinyint(1) NOT NULL DEFAULT '2' COMMENT '会员状态:-1未激活/0禁用/1待审/2正常',
  `last_login_ip` varchar(128) COMMENT '最后登陆IP',
  `last_login_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后登陆时间',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  `delete_time` int(11),
  UNIQUE KEY `uuid` (`uuid`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='会员基础表';

CREATE TABLE `pre_member_auth_type` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL COMMENT '名称(如:账号,手机,邮箱等)',
  `identifier` varchar(15) NOT NULL COMMENT '标识(如:username,email,phone等)',
  `rule_hook` varchar(30) DEFAULT '' COMMENT '前置验证钩子',
  `rule_hook_after` varchar(30) DEFAULT '' COMMENT '后置验证钩子',
  `rule_hook_tp` varchar(30) DEFAULT '' COMMENT '前端验证钩子',
  `rule` text COMMENT '字段验证规则',
  `message` text COMMENT '字段验证错误提示',
  `check_after` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否插件验证',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='会员授权类型表';

CREATE TABLE `pre_member_auth` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tid` varchar(11) NOT NULL COMMENT '授权类型ID',
  `member_id` int(10) unsigned NOT NULL COMMENT '会员id',
  `account` varchar(60) NOT NULL COMMENT '账号(如:普通账号,手机号码,邮箱地址,身份证号码等)',
  `password` varchar(200) COMMENT '密码',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='会员授权映射表';


