
# Dump of table hi_system_annex
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hi_system_annex`;

CREATE TABLE `hi_system_annex` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `annex_type` varchar(20) NOT NULL DEFAULT '' COMMENT '资源类型:image/file/video',
  `ext` varchar(10) NOT NULL DEFAULT '' COMMENT '资源后缀',
  `file` varchar(255) NOT NULL COMMENT '资源地址',
  `hash` varchar(64) NOT NULL COMMENT '资源hash值',
  `save_dir` varchar(30) NOT NULL DEFAULT '' COMMENT '储存目录',
  `size` decimal(12,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '附件大小KB',
  `driver` varchar(15) COMMENT '驱动标识:默认空为local',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '使用状态(0未使用，1已使用)',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0',
  `delete_time` int(11),
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='[系统]资源表';

# Dump of table hi_system_annex_group
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hi_system_annex_group`;

CREATE TABLE `hi_system_annex_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `annex_type` varchar(20) NOT NULL DEFAULT '' COMMENT '资源类型:image/file/video',
  `count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '资源数量',
  `size` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '资源大小kb',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='[系统]资源统计表';

# Dump of table hi_system_config
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hi_system_config`;

CREATE TABLE `hi_system_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `system` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否为系统配置(1是，0否)',
  `group` varchar(60) NOT NULL DEFAULT 'system' COMMENT '分组名',
  `title` varchar(20) NOT NULL COMMENT '配置标题',
  `name` varchar(50) NOT NULL COMMENT '配置名称，由英文字母和下划线组成',
  `value` text NOT NULL COMMENT '配置值',
  `type` varchar(20) NOT NULL DEFAULT 'input' COMMENT '配置类型()',
  `options` text NOT NULL COMMENT '配置项(选项名:选项值)',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '文件上传接口',
  `tips` varchar(255) NOT NULL COMMENT '配置提示',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) unsigned NOT NULL COMMENT '状态',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8 COMMENT='[系统] 系统配置';


INSERT INTO `hi_system_config` (`id`, `system`, `group`, `title`, `name`, `value`, `type`, `options`, `url`, `tips`, `sort`, `status`, `create_time`, `update_time`)
VALUES
  ('13', '1', 'base', '网站域名', 'site_domain', '', 'input', '', '', '', '2', '1', '1573109710', '1573109779'),
  ('14', '1', 'upload', '图片上传大小限制', 'image_size', '0', 'input', '', '', '单位：KB，0表示不限制大小', '3', '1', '1573109710', '1573109779'),
  ('15', '1', 'upload', '允许上传图片格式', 'image_ext', 'jpg,png,gif,jpeg,ico', 'input', '', '', '多个格式请用英文逗号（,）隔开', '4', '1', '1573109710', '1573109779'),
  ('16', '1', 'upload', '缩略图裁剪方式', 'thumb_type', '2', 'select', '1:等比例缩放\r\n2:缩放后填充\r\n3:居中裁剪\r\n4:左上角裁剪\r\n5:右下角裁剪\r\n6:固定尺寸缩放\r\n', '', '', '5', '1', '1573109710', '1573109779'),
  ('17', '1', 'upload', '图片水印开关', 'image_watermark', '1', 'switch', '0:关闭\r\n1:开启', '', '', '6', '1', '1573109710', '1573109779'),
  ('18', '1', 'upload', '图片水印图', 'image_watermark_pic', '', 'image', '', '', '', '7', '1', '1573109710', '1573109779'),
  ('19', '1', 'upload', '图片水印透明度', 'image_watermark_opacity', '50', 'input', '', '', '可设置值为0~100，数字越小，透明度越高', '8', '1', '1573109710', '1573109779'),
  ('20', '1', 'upload', '图片水印图位置', 'image_watermark_location', '9', 'select', '7:左下角\r\n1:左上角\r\n4:左居中\r\n9:右下角\r\n3:右上角\r\n6:右居中\r\n2:上居中\r\n8:下居中\r\n5:居中', '', '', '9', '1', '1573109710', '1573109779'),
  ('21', '1', 'upload', '文件上传大小限制', 'file_size', '0', 'input', '', '', '单位：KB，0表示不限制大小', '1', '1', '1573109710', '1573109779'),
  ('22', '1', 'upload', '允许上传文件格式', 'file_ext', 'doc,docx,xls,xlsx,ppt,pptx,pdf,wps,txt,rar,zip,xml,crt', 'input', '', '', '多个格式请用英文逗号（,）隔开', '2', '1', '1573109710', '1573109779'),
  ('22', '1', 'upload', '允许上传文件格式', 'file_ext', 'doc,docx,xls,xlsx,ppt,pptx,pdf,wps,txt,rar,zip,xml', 'input', '', '', '多个格式请用英文逗号（,）隔开', '2', '1', '1573109710', '1573109779'),
  ('23', '1', 'upload', '文字水印开关', 'text_watermark', '0', 'switch', '0:关闭\r\n1:开启', '', '', '10', '1', '1573109710', '1573109779'),
  ('24', '1', 'upload', '文字水印内容', 'text_watermark_content', '', 'input', '', '', '', '11', '1', '1573109710', '1573109779'),
  ('25', '1', 'upload', '文字水印字体', 'text_watermark_font', '', 'file', '', '', '不上传将使用系统默认字体', '12', '1', '1573109710', '1573109779'),
  ('26', '1', 'upload', '文字水印字体大小', 'text_watermark_size', '20', 'input', '', '', '单位：px(像素)', '13', '1', '1573109710', '1573109779'),
  ('27', '1', 'upload', '文字水印颜色', 'text_watermark_color', '#000000', 'input', '', '', '文字水印颜色，格式:#000000', '14', '1', '1573109710', '1573109779'),
  ('28', '1', 'upload', '文字水印位置', 'text_watermark_location', '7', 'select', '7:左下角\r\n1:左上角\r\n4:左居中\r\n9:右下角\r\n3:右上角\r\n6:右居中\r\n2:上居中\r\n8:下居中\r\n5:居中', '', '', '11', '1', '1573109710', '1573109779'),
  ('29', '1', 'upload', '缩略图尺寸', 'thumb_size', '300x300;500x500', 'input', '', '', '为空则不生成，生成 500x500 的缩略图，则填写 500x500，多个规格填写参考 300x300;500x500;800x800', '4', '1', '1573109710', '1573109779'),
  ('30', '1', 'system', '开发模式', 'app_debug', '1', 'switch', '0:关闭\r\n1:开启', '', '&lt;strong class=&quot;red&quot;&gt;生产环境下一定要关闭此配置&lt;/strong&gt;', '3', '1', '1573109710', '1573109779'),
  ('33', '1', 'system', '富文本编辑器', 'editor', 'umeditor', 'select', 'ueditor:UEditor\r\numeditor:UMEditor\r\nkindeditor:KindEditor\r\nckeditor:CKEditor', '', '', '0', '1', '1573109710', '1573109779'),
  ('35', '1', 'databases', '备份目录', 'backup_path', './backup/database/', 'input', '', '', '数据库备份路径,路径必须以 / 结尾', '0', '1', '1573109710', '1573109779'),
  ('36', '1', 'databases', '备份分卷大小', 'part_size', '20971520', 'input', '', '', '用于限制压缩后的分卷最大长度。单位：B；建议设置20M', '0', '1', '1573109710', '1573109779'),
  ('37', '1', 'databases', '备份压缩开关', 'compress', '1', 'switch', '0:关闭\r\n1:开启', '', '压缩备份文件需要PHP环境支持gzopen,gzwrite函数', '0', '1', '1573109710', '1573109779'),
  ('38', '1', 'databases', '备份压缩级别', 'compress_level', '4', 'radio', '1:最低\r\n4:一般\r\n9:最高', '', '数据库备份文件的压缩级别，该配置在开启压缩时生效', '0', '1', '1573109710', '1573109779'),
  ('39', '1', 'base', '网站状态', 'site_status', '1', 'switch', '0:关闭\r\n1:开启', '', '站点关闭后将不能访问，后台可正常登录', '1', '1', '1573109710', '1573109779'),
  ('40', '1', 'system', '后台管理路径', 'admin_path', 'admin.php', 'input', '', '', '必须以.php为后缀', '1', '1', '1573109710', '1573109779'),
  ('41', '1', 'base', '网站标题', 'site_title', 'HiPHP 开源后台管理框架', 'input', '', '', '网站标题是体现一个网站的主旨，要做到主题突出、标题简洁、连贯等特点，建议不超过28个字', '6', '1', '1573109710', '1573109779'),
  ('42', '1', 'base', '网站关键词', 'site_keywords', 'hiphp,hiphp框架,php开源框架', 'input', '', '', '网页内容所包含的核心搜索关键词，多个关键字请用英文逗号&quot;,&quot;分隔', '7', '1', '1573109710', '1573109779'),
  ('43', '1', 'base', '网站描述', 'site_description', 'hiphp网站开源平台', 'textarea', '', '', '网页的描述信息，搜索引擎采纳后，作为搜索结果中的页面摘要显示，建议不超过80个字', '8', '1', '1573109710', '1573109779'),
  ('44', '1', 'base', 'ICP备案信息', 'site_icp', '', 'input', '', '', '请填写ICP备案号，用于展示在网站底部，ICP备案官网：&lt;a href=&quot;http://www.miibeian.gov.cn&quot; target=&quot;_blank&quot;&gt;http://www.miibeian.gov.cn&lt;/a&gt;', '9', '1', '1573109710', '1573109779'),
  ('45', '1', 'base', '站点统计代码', 'site_statis', '', 'textarea', '', '', '第三方流量统计代码，前台调用时请先用 htmlspecialchars_decode函数转义输出', '10', '1', '1573109710', '1573109779'),
  ('46', '1', 'base', '网站名称', 'site_name', 'HiPHP', 'input', '', '', '将显示在浏览器窗口标题等位置', '3', '1', '1573109710', '1573109779'),
  ('47', '1', 'base', '网站LOGO', 'site_logo', '', 'image', '', '', '网站LOGO图片', '4', '1', '1573109710', '1573109779'),
  ('48', '1', 'base', '网站图标', 'site_favicon', '', 'image', '', '/system/annex/favicon', '又叫网站收藏夹图标，它显示位于浏览器的地址栏或者标题前面，&lt;strong class=&quot;red&quot;&gt;.ico格式&lt;/strong&gt;，&lt;a href=&quot;https://www.baidu.com/s?ie=UTF-8&amp;wd=favicon&quot; target=&quot;_blank&quot;&gt;点此了解网站图标&lt;/a&gt;', '5', '1', '1573109710', '1573109779'),
  ('49', '1', 'base', '手机网站', 'mobile_site_status', '0', 'switch', '0:关闭\r\n1:开启', '', '如果有手机网站，请设置为开启状态，否则只显示PC网站', '2', '1', '1573109710', '1573109779'),
  ('50', '1', 'clouds', '云端推送', 'cloud_push', '1', 'switch', '0:关闭\r\n1:开启', '', '关闭之后，无法通过云端推送安装扩展', '0', '1', '1573109710', '1573109779'),
  ('51', '1', 'clouds', '云端域名', 'cloud_push_domain', 'https://open.hiphp.net/', 'input', '', '', '没有官方公告通知, 不要修改此处, 否则无法安装和升级系统和应用！', '1', '1', '1573109710', '1573109779'),
  ('52', '1', 'clouds', '应用中心域名', 'store_push_domain', 'https://store.hiphp.net/', 'input', '', '', '没有官方公告通知, 不要修改此处, 否则无法安装和升级系统和应用！', '2', '1', '1573109710', '1573109779'),
  ('53', '1', 'base', '手机响应式', 'mobile_response', '', 'switch', '0:关闭\r\n1:开启', '', '开启后,手机主题模版与pc主题模版共用', '2', '1', '1573109710', '1573109779'),
  ('54', '1', 'base', '手机网站域名', 'mobile_domain', '', 'input', '', '', '手机访问将自动跳转至此域名，示例：http://m.domain.com', '3', '1', '1573109710', '1573109779'),
  ('55', '1', 'system', '后台白名单验证', 'admin_whitelist_verify', '0', 'switch', '0:禁用\r\n1:启用', '', '禁用后不存在的菜单节点将不在提示', '7', '1', '1573109710', '1573109779'),
  ('56', '1', 'system', '系统日志保留', 'log_retention', '30', 'input', '', '', '单位天，系统将自动清除 ? 天前的系统日志', '8', '1', '1573109710', '1573109779'),
  ('57', '1', 'upload', '上传驱动', 'driver', 'local', 'select', 'local:本地上传', '', '资源上传驱动设置', '0', '1', '1573109710', '1573109779'),
  ('58', '1', 'system', '扩展配置分组', 'config_group', '', 'array', '', '', '请按如下格式填写：&lt;br&gt;键值:键名&lt;br&gt;键值:键名&lt;br&gt;&lt;span style=&quot;color:#f00&quot;&gt;键值只能为英文、数字、下划线&lt;/span&gt;', '2', '0', '1573109710', '1573109779'),
  ('59', '1', 'system', '域名白名单', 'domain_whitelist', '', 'array', '', '', '请按如下格式填写：&lt;br&gt;www.aaa.com&lt;br&gt;www.bbb.cn&lt;br&gt;', '9', '1', '1573109710', '1573109779'),
  ('60', '1', 'system', '多应用域名绑定', 'domain_binds', '', 'array', '', '', '请按如下格式填写：&lt;br&gt;www.aaa.com:cms&lt;br&gt;www.bbb.cn:cms&lt;br&gt;test.bbb.cn:member&lt;br&gt;&lt;span style=&quot;color:#f00&quot;&gt;域名包括顶级和子域名&lt;/span&gt;', '10', '1', '1573109710', '1573109779'),
  ('61', '1', 'system', '同域登录配置', 'domain_cross', '', 'input', '', '', '一级域名下的二级域名能共享session数据，仅需配置如 .xxx.com即可', '11', '1', '1573109710', '1573109779'),
  ('62', '1', 'upload', '储存默认目录', 'save_dir', './uploads', 'input', '', '', '此处不要随意修改! 静态资源默认是在public目录下，如要自定义到根目录下，填入格式如: ../demo', '0', '1', '1573109710', '1573109779');
# Dump of table hi_system_hook

# ------------------------------------------------------------

DROP TABLE IF EXISTS `hi_system_hook`;

CREATE TABLE `hi_system_hook` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `system` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '系统插件',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '钩子名称',
  `source` varchar(50) NOT NULL DEFAULT '' COMMENT '钩子来源[plugin.插件名，module.模块名]',
  `intro` varchar(200) NOT NULL DEFAULT '' COMMENT '钩子简介',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='[系统] 钩子表';


INSERT INTO `hi_system_hook` (`id`, `system`, `name`, `source`, `intro`, `status`, `create_time`, `update_time`)
VALUES
  (1,1,'system_admin_index','','后台首页',1,1579153029,1579153029),
  (2,1,'system_admin_tips','','后台所有页面提示',1,1579153029,1579153029),
  (3,1,'system_annex_upload','','附件上传钩子，可扩展上传到第三方存储',1,1579153029,1579153029),
  (4,1,'cloud_push','','云平台推送',1,1579153029,1579153029),
  (5,1,'cloud_temp','','云平台推送',1,1579153029,1579153029),
  (6,0,'hi_builder','后台开发构建器','后台开发构建器',1,1579153029,1579153029);

# Dump of table hi_system_hook_plugin
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hi_system_hook_plugin`;

CREATE TABLE `hi_system_hook_plugin` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `hook` varchar(32) NOT NULL COMMENT '钩子id',
  `plugins` varchar(32) NOT NULL COMMENT '插件标识',
  `sort` int(11) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '1',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='[系统] 钩子-插件关系表';

INSERT INTO `hi_system_hook_plugin` (`id`, `hook`, `plugins`, `sort`, `status`, `create_time`, `update_time`)
VALUES
 (1, 'cloud_push', 'cloud', '0', '1', '1579141935', '1579141935'),
 (2, 'cloud_temp', 'cloud', '1', '1', '1579141935', '1579141935'),
 (3, 'system_builder', 'builder', '2', '1', '1579141935', '1579141935');



# Dump of table hi_system_log
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hi_system_log`;

CREATE TABLE `hi_system_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(100) DEFAULT '',
  `url` varchar(200) DEFAULT '',
  `param` text,
  `remark` varchar(255) DEFAULT '',
  `count` int(10) unsigned NOT NULL DEFAULT '1',
  `ip` varchar(128) DEFAULT '',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='[系统] 操作日志';

# Dump of table hi_system_menu
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hi_system_menu`;

CREATE TABLE `hi_system_menu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL DEFAULT '0',
  `module` varchar(50) NOT NULL COMMENT '模块名或插件名，插件名格式:plugin.插件名',
  `title` varchar(50) NOT NULL COMMENT '菜单标题',
  `icon` varchar(80) NOT NULL DEFAULT 'aicon ai-shezhi' COMMENT '菜单图标',
  `url` varchar(200) NOT NULL COMMENT '链接地址(模块/控制器/方法)',
  `param` varchar(200) NOT NULL DEFAULT '' COMMENT '扩展参数',
  `target` varchar(20) NOT NULL DEFAULT '_self' COMMENT '打开方式(_blank,_self)',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `debug` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '开发模式可见',
  `is_menu` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '菜单显示:1显示;0不显示',
  `system` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否为系统菜单，系统菜单不可删除',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态1显示，0隐藏',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=141 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='[系统] 管理菜单';


INSERT INTO `hi_system_menu` (`id`, `pid`, `module`, `title`, `icon`, `url`, `param`, `target`, `sort`, `debug`, `system`, `status`, `create_time`)
VALUES
('2', '0', 'system', '系统', 'icon iconfont iconwindow', 'system/system/index', '', '_self', '0', '0', '1', '1', '1573109779'),
('3', '0', 'system', '插件', 'icon iconfont iconmodel', 'system/plugins/index', '', '_self', '0', '0', '1', '1', '1573109779'),
('4', '2', 'system', '用户管理', 'icon iconfont iconpeople', '', '', '_self', '2', '0', '1', '1', '1573109779'),
('5', '4', 'system', '管理员', 'icon iconfont iconadmin', 'system/user/index', '', '_self', '0', '0', '1', '1', '1573109779'),
('6', '4', 'system', '角色管理', 'icon iconfont iconrole', 'system/role/index', '', '_self', '1', '0', '1', '1', '1573109779'),
('7', '2', 'system', '系统配置', 'icon iconfont iconsetting', 'system/config/index', '', '_self', '0', '0', '1', '1', '1573109779'),
('8', '2', 'system', '扩展管理', 'icon iconfont iconapps', '', '', '_self', '3', '0', '1', '1', '1573109779'),
('9', '2', 'system', '系统升级', 'icon iconfont icondownload', 'system/upgrade/index', '', '_self', '4', '0', '1', '1', '1573109779'),
('10', '6', 'system', '添加角色', '', 'system/role/add', '', '_self', '0', '0', '1', '1', '1573109779'),
('11', '6', 'system', '删除角色', '', 'system/role/remove', '', '_self', '1', '0', '1', '1', '1573109779'),
('12', '6', 'system', '修改角色', '', 'system/role/edit', '', '_self', '2', '0', '0', '1', '1573109779'),
('13', '5', 'system', '添加管理员', '', 'system/user/add', '', '_self', '0', '0', '1', '1', '1573109779'),
('14', '5', 'system', '修改管理员', '', 'system/user/edit', '', '_self', '1', '0', '1', '1', '1573109779'),
('15', '5', 'system', '删除管理员', '', 'system/user/remove', '', '_self', '2', '0', '1', '1', '1573109779'),
('16', '8', 'system', '钩子管理', 'icon iconfont iconlink', 'system/hook/index', '', '_self', '4', '0', '1', '1', '1573109779'),
('17', '8', 'system', '我的模块', 'icon iconfont iconmodule', 'system/module/index', '', '_self', '0', '0', '1', '1', '1573109779'),
('18', '17', 'system', '导入模块', '', 'system/module/import', '', '_self', '0', '0', '1', '1', '1573109779'),
('19', '8', 'system', '我的插件', 'icon iconfont iconplugin', 'system/plugin/index', '', '_self', '1', '0', '1', '1', '1573109779'),
('20', '17', 'system', '安装模块', '', 'system/module/install', '', '_self', '0', '0', '1', '1', '1573109779'),
('21', '17', 'system', '卸载模块', '', 'system/module/uninstall', '', '_self', '1', '0', '1', '1', '1573109779'),
('22', '17', 'system', '删除模块', '', 'system/module/del', '', '_self', '2', '0', '1', '1', '1573109779'),
('23', '17', 'system', '设置默认模块', '', 'system/module/setdefault', '', '_self', '3', '0', '1', '1', '1573109779'),
('24', '17', 'system', '开启模块', '', 'system/module/status', 'v=1', '_self', '4', '0', '1', '1', '1573109779'),
('25', '17', 'system', '禁用模块', '', 'system/module/status', 'v=0', '_self', '5', '0', '1', '1', '1573109779'),
('26', '17', 'system', '主题管理', '', 'system/module/theme', '', '_self', '6', '0', '1', '1', '1573109779'),
('27', '17', 'system', '设置默认主题', '', 'system/module/setdefaulttheme', '', '_self', '7', '0', '1', '1', '1573109779'),
('28', '17', 'system', '删除主题', '', 'system/module/deltheme', '', '_self', '8', '0', '1', '1', '1573109779'),
('29', '19', 'system', '导入插件', '', 'system/plugin/import', '', '_self', '0', '0', '1', '1', '1573109779'),
('30', '19', 'system', '安装插件', '', 'system/plugin/install', '', '_self', '1', '0', '1', '1', '1573109779'),
('31', '19', 'system', '卸载插件', '', 'system/plugin/uninstall', '', '_self', '2', '0', '1', '1', '1573109779'),
('32', '19', 'system', '删除插件', '', 'system/plugin/del', '', '_self', '3', '0', '1', '1', '1573109779'),
('33', '19', 'system', '开启插件', '', 'system/plugin/status', 'v=1', '_self', '4', '0', '1', '1', '1573109779'),
('34', '19', 'system', '禁用插件', '', 'system/plugin/status', 'v=0', '_self', '5', '0', '1', '1', '1573109779'),
('35', '2', 'system', '数据管理', 'icon iconfont icondatabase', 'system/database/index', '', '_self', '1', '0', '1', '1', '1573109779');


# Dump of table hi_system_module
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hi_system_module`;

CREATE TABLE `hi_system_module` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `system` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否系统模块',
  `name` varchar(50) NOT NULL COMMENT '模块名(英文)',
  `identifier` varchar(100) NOT NULL COMMENT '模块标识(模块名.[应用市场ID].module.[应用市场分支ID])',
  `title` varchar(50) NOT NULL COMMENT '模块标题',
  `intro` varchar(255) NOT NULL COMMENT '模块简介',
  `author` varchar(100) NOT NULL COMMENT '作者',
  `icon` varchar(80) NOT NULL DEFAULT '' COMMENT '图标',
  `version` varchar(16) NOT NULL DEFAULT '' COMMENT '版本号',
  `url` varchar(255) NOT NULL COMMENT '链接',
  `sort` int(5) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0未安装，1未启用，2已启用',
  `default` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '默认模块(只能有一个)',
  `app_id` varchar(30) NOT NULL DEFAULT '0' COMMENT '应用市场ID(0本地)',
  `app_keys` varchar(50) DEFAULT '' COMMENT '应用秘钥',
  `theme` varchar(50) NOT NULL DEFAULT 'default' COMMENT 'pc主题模板',
  `mobile_theme` varchar(50) NOT NULL COMMENT '手机web主题模板',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `identifier` (`identifier`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='[系统] 模块';

INSERT INTO `hi_system_module` (`id`, `system`, `name`, `identifier`, `title`, `intro`, `author`, `icon`, `version`, `url`, `sort`, `status`, `default`, `app_id`, `app_keys`, `theme`, `mobile_theme`, `create_time`, `update_time`)
VALUES
  (1, 1, 'system', 'system.hi.module', '系统管理模块', '系统核心模块，用于后台各项管理功能模块及功能拓展', 'HIPHP官方出品', '', '1.0.0', 'http://www.hiphp.net', 0, 2, 0, '0', '', 'default', '', 1573109779, 1573109779),
  (2, 1, 'index', 'index.hi.module', '默认模块', '推荐使用扩展模块作为默认首页。', 'HiPHP官方出品', '', '1.0.0', 'http://www.hiphp.net', 0, 2, 0, '', '0', 'default', '', 1573109779, 1573109779),
  (3, 1, 'install', 'install.hi.module', '系统安装模块', '系统安装模块，勿动。', 'HiPHP官方出品', '', '1.0.0', 'http://www.hiphp.net', 0, 2, 0, '', '0', 'default', '', 1573109779, 1573109779);


# Dump of table hi_system_plugin
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hi_system_plugin`;

CREATE TABLE `hi_system_plugin` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `system` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `name` varchar(32) NOT NULL COMMENT '插件名称(英文)',
  `title` varchar(32) NOT NULL COMMENT '插件标题',
  `icon` varchar(64) NOT NULL COMMENT '图标',
  `intro` text NOT NULL COMMENT '插件简介',
  `author` varchar(32) NOT NULL COMMENT '作者',
  `url` varchar(255) NOT NULL COMMENT '作者主页',
  `version` varchar(16) NOT NULL DEFAULT '' COMMENT '版本号',
  `identifier` varchar(64) NOT NULL DEFAULT '' COMMENT '插件唯一标识符',
  `app_id` varchar(30) NOT NULL DEFAULT '0' COMMENT '应用市场ID',
  `app_keys` varchar(50) DEFAULT '' COMMENT '应用秘钥',
  `theme` varchar(50) DEFAULT '' COMMENT '电脑端主题',
  `mobile_theme` varchar(50) DEFAULT '' '手机web主题',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='[系统] 插件表';

INSERT INTO `hi_system_plugin` (`id`, `system`, `name`, `title`, `icon`, `intro`, `author`, `url`, `version`, `identifier`, `app_id`, `app_keys`, `theme`, `mobile_theme`,`create_time`, `update_time`, `sort`, `status`)
VALUES
  (1, 1, 'cloud', '云平台推送', '', '云平台推送', 'HiPHP', 'http://www.hiphp.net', '1.0.0', 'cloud.plugin', '', '', '', '', 1579153755, 1579153755, 0, 2),
  (2, 0, 'builder', '构建器', '', 'HiPHP构建器', 'HiPHP', 'http://www.hiphp.net', '1.0.0', 'builder.plugin', '', '', '', '', 1579153755, 1579153755, 0, 2);


# Dump of table hi_system_component
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hi_system_component`;

CREATE TABLE `hi_system_component` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL COMMENT '应用名',
  `title` varchar(60) NOT NULL COMMENT '组件名称',
  `intro` varchar(255) NOT NULL COMMENT '组件简介',
  `author` varchar(100) NOT NULL COMMENT '作者',
  `version` varchar(20) NOT NULL COMMENT '版本号',
  `url` varchar(255) NOT NULL COMMENT '链接',
  `sort` int(5) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0未启用，1已启用',
  `app_type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '应用类型:0模块/1插件',
  `app_id` varchar(30) NOT NULL DEFAULT '0' COMMENT '应用市场ID',
  `app_keys` varchar(50) DEFAULT '' COMMENT '应用秘钥',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `app_id` (`app_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='[系统]应用组件表';


# Dump of table hi_system_theme
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hi_system_theme`;

CREATE TABLE `hi_system_theme` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL COMMENT '应用名',
  `theme_name` varchar(60) NOT NULL COMMENT '主题名',
  `title` varchar(50) NOT NULL COMMENT '主题名称',
  `intro` varchar(255) NOT NULL COMMENT '主题简介',
  `author` varchar(100) NOT NULL COMMENT '作者',
  `version` varchar(20) NOT NULL COMMENT '版本号',
  `url` varchar(255) NOT NULL COMMENT '链接',
  `sort` int(5) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0未启用，1已启用',
  `app_type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '应用类型:0模块/1插件',
  `app_id` varchar(30) NOT NULL DEFAULT '0' COMMENT '应用市场ID',
  `app_keys` varchar(50) DEFAULT '' COMMENT '应用秘钥',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `app_id` (`app_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='[系统]应用主题表';


# Dump of table hi_system_role
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hi_system_role`;

CREATE TABLE `hi_system_role` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '角色名称',
  `intro` varchar(200) NOT NULL COMMENT '角色简介',
  `auth` text NOT NULL COMMENT '角色权限',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='[系统] 管理角色';

INSERT INTO `hi_system_role` (`id`, `name`, `intro`, `auth`, `create_time`, `update_time`, `status`)
VALUES
 ('1', '超级管理员', '拥有系统最高权限', '0', '1573109779', '0', '1');


# Dump of table hi_system_user
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hi_system_user`;

CREATE TABLE `hi_system_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` varchar(100) NOT NULL DEFAULT '0' COMMENT '多个角色,分割',
  `username` varchar(50) NOT NULL COMMENT '用户名',
  `password` varchar(64) NOT NULL,
  `nick` varchar(50) NOT NULL COMMENT '昵称',
  `mobile` varchar(11) NOT NULL,
  `email` varchar(50) NOT NULL COMMENT '邮箱',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态',
  `last_login_ip` varchar(128) NOT NULL COMMENT '最后登陆IP',
  `last_login_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后登陆时间',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='[系统] 管理用户';


# Dump of table hi_system_lang
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hi_system_lang`;

CREATE TABLE `hi_system_lang` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `group` varchar(60) NOT NULL DEFAULT '' COMMENT '分组[应用名]',
  `pack` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '所属语言包',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '语言变量名',
  `langvar` varchar(255) NOT NULL DEFAULT '' COMMENT '语言变量',
  INDEX index_pack (`pack`),
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='语言数据';


# Dump of table hi_system_language
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hi_system_language`;

CREATE TABLE `hi_system_language` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `group` varchar(60) NOT NULL DEFAULT '' COMMENT '分组[应用名]',
  `name` varchar(60) NOT NULL DEFAULT '' COMMENT '语言包名',
  `default` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '语言变量名',
  INDEX index_group (`group`),
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='语言包';