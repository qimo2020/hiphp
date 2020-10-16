<?php /*a:3:{s:76:"D:\panchuming\phpstudy\WWW\hiphp_up\app\system/view/default/index\index.html";i:1596778860;s:75:"D:\panchuming\phpstudy\WWW\hiphp_up\app\system/view/default/block\base.html";i:1595577783;s:76:"D:\panchuming\phpstudy\WWW\hiphp_up\app\system/view/default/block\layui.html";i:1589178961;}*/ ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title><?php echo config('hiphp.name'); ?></title>
    <meta name="keywords" content="">
    <meta name="description" content="">
    <link rel="stylesheet" href="/pack/layui/css/layui.css?v=<?php echo config('hiphp.version'); ?>">
    <link rel="stylesheet" href="/static/m_system/css/base.css?v=<?php echo config('hiphp.version'); ?>">
</head>
<body id="main-body">
<?php if(isset($tabType)): switch($tabType): case "1": ?>

<div class="layui-card main-box">
    <div class="layui-tab layui-tab-brief">
        <ul class="layui-tab-title">
            <li class="layui-this">
                <a href="javascript:;" id="curTitle"><?php echo $currMenu['title']; ?></a>
            </li>
        </ul>
        <div class="layui-tab-content page-tab-content">
            <div class="layui-tab-item layui-show">
                <!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>LayuiHi</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta http-equiv="Access-Control-Allow-Origin" content="*">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link rel="stylesheet" href="/pack/layui/css/layui.css?v=<?php echo config('hiphp.version'); ?>" media="all">
    <link rel="stylesheet" href="/static\m_system/css/frame.css?v=<?php echo config('hiphp.version'); ?>" media="all">
    <link rel="stylesheet" href="/static\m_system/fonts/iconfont.css?v=<?php echo config('hiphp.version'); ?>" media="all">
    <?php if(isset($fontItems) && !empty($fontItems)): foreach($fontItems as $v): ?>
    <link rel="stylesheet" href="/<?php echo htmlentities($v['link']); ?>?v=<?php echo htmlentities($v['version']); ?>" media="all">
    <?php endforeach; ?>
    <?php endif; ?>
    <style id="layuihi-bg-color"></style>
</head>
<body class="layui-layout-body layuihi-all">
<div class="layui-layout layui-layout-admin">
    <div class="layui-header header">
        <div class="layui-logo"><a href=""> <img src="/static\m_system/images/logo.png" alt="logo">
            <h1>HiPHP</h1>
        </a></div>
        <a>
            <div class="layuihi-tool"><i title="展开" class="icon iconfont iconmenuclose" data-side-fold="1"></i></div>
        </a>
        <ul class="layui-nav layui-layout-left layui-header-menu layui-header-pc-menu mobile layui-hide-xs">

        </ul>
        <ul class="layui-nav layui-layout-left layui-header-menu mobile layui-hide-sm">
            <li class="layui-nav-item"> <a href="javascript:;"><i class="fa fa-list-ul"></i> 选择模块<span class="layui-nav-more"></span></a>
                <dl class="layui-nav-child layui-header-hi-menu">

                </dl>
            </li>
        </ul>
        <ul class="layui-nav layui-layout-right">
            <li class="layui-nav-item"> <a href="javascript:;" data-refresh="刷新"><i class="icon iconfont iconreflesh"></i></a> </li>
            <li class="layui-nav-item"> <a href="javascript:;" data-clear="清理" class="layuihi-clear"><i class="icon iconfont iconlaji"></i></a> </li>
            <li class="layui-nav-item layuihi-setting"> <a href="javascript:;"><?php echo htmlentities($login['nick']); ?></a>
                <dl class="layui-nav-child">
                    <dd> <a href="javascript:;" data-iframe-tab="page/user-setting.html" data-title="基本资料"
                            data-icon="fa fa-gears">基本资料</a> </dd>
                    <dd> <a href="javascript:;" data-iframe-tab="page/user-password.html" data-title="修改密码"
                            data-icon="fa fa-gears">修改密码</a> </dd>
                    <dd> <a href="javascript:;" class="login-out">退出登录</a> </dd>
                </dl>
            </li>
            <li class="layui-nav-item layuihi-select-bgcolor mobile layui-hide-xs"> <a href="javascript:;" data-bgcolor="配色方案"><i class="icon iconfont icongengduo"></i></a> </li>
        </ul>
    </div>
    <div class="layui-side layui-bg-black">
        <div class="layui-side-scroll layui-left-menu">

        </div>
    </div>
    <div class="layui-body">
        <div class="layui-tab" lay-filter="layuihiTab" id="top_tabs_box">
            <ul class="layui-tab-title" id="top_tabs">
                <li class="layui-this" id="layuihiHomeTabId" lay-id=""><i class="fa fa-home"></i> <span>首页</span></li>
            </ul>
            <ul class="layui-nav closeBox">
                <li class="layui-nav-item"> <a href="javascript:;"> <i class="fa fa-dot-circle-o"></i> 页面操作</a>
                    <dl class="layui-nav-child">
                        <dd><a href="javascript:;" data-page-close="other"><i class="fa fa-window-close"></i> 关闭其他</a></dd>
                        <dd><a href="javascript:;" data-page-close="all"><i class="fa fa-window-close-o"></i> 关闭全部</a></dd>
                    </dl>
                </li>
            </ul>
            <div class="layui-tab-content clildFrame">
                <div id="layuihiHomeTabIframe" class="layui-tab-item layui-show"> </div>
            </div>
        </div>
    </div>
</div>
<script>
    var admin_path = '<?php echo config("system.admin_path"); ?>';
</script>
<script src="/pack/layui/layui.js?v=<?php echo config('hiphp.version'); ?>"></script>
<script>
    layui.use(['element', 'form'], function() {
        var element = layui.element;
        var form = layui.form;
    });
    layui.config({
    	base: '/pack/layui_admin/',
        version: '<?php echo config("hiphp.version"); ?>'
    }).extend({
        layuihi: "layuihi",
        base: "base",
        func: "func",
        md5: "md5",
    }).use(['base']);
</script>
<script>
    layui.use(['element', 'layer', 'layuihi'], function () {
        var $ = layui.jquery, element = layui.element, layer = layui.layer;
        layuihi.init('<?php echo url("index"); ?>');
        $('.login-out').on("click", function () {
            $.ajax({
                url:"<?php echo url('entry/logout'); ?>",
                type:"post",
                dataType:"json",
                success:function(data){
                    layer.msg('退出成功', function () {
                        window.location.href = '/'+admin_path;
                    });
                },
                error:function(data){
                    $.messager.alert('错误',data.msg);
                }
            });
        });
    });
</script>
</body>
</html>
            </div>
        </div>
    </div>
</div>
<?php break; case "2": ?>

<div class="layui-card main-box">
    <div class="layui-tab layui-tab-brief" lay-filter="form-block-tab">
        <ul class="layui-tab-title">
            <?php if(is_array($tabData['tab']) || $tabData['tab'] instanceof \think\Collection || $tabData['tab'] instanceof \think\Paginator): $k = 0; $__LIST__ = $tabData['tab'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($k % 2 );++$k;if(($k == 1)): ?>
            <li class="layui-this">
                <?php else: ?>
            <li>
                <?php endif; ?>
                <a href="javascript:;" class="<?php if((isset($vo['class']))): ?><?php echo htmlentities($vo['class']); ?><?php endif; ?>" id="<?php if((isset($vo['id']))): ?><?php echo htmlentities($vo['id']); ?><?php endif; ?>"><?php echo $vo['title']; ?></a>
            </li>
            <?php endforeach; endif; else: echo "" ;endif; ?>
        </ul>
        <div class="layui-tab-content page-tab-content">
            <!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>LayuiHi</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta http-equiv="Access-Control-Allow-Origin" content="*">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link rel="stylesheet" href="/pack/layui/css/layui.css?v=<?php echo config('hiphp.version'); ?>" media="all">
    <link rel="stylesheet" href="/static\m_system/css/frame.css?v=<?php echo config('hiphp.version'); ?>" media="all">
    <link rel="stylesheet" href="/static\m_system/fonts/iconfont.css?v=<?php echo config('hiphp.version'); ?>" media="all">
    <?php if(isset($fontItems) && !empty($fontItems)): foreach($fontItems as $v): ?>
    <link rel="stylesheet" href="/<?php echo htmlentities($v['link']); ?>?v=<?php echo htmlentities($v['version']); ?>" media="all">
    <?php endforeach; ?>
    <?php endif; ?>
    <style id="layuihi-bg-color"></style>
</head>
<body class="layui-layout-body layuihi-all">
<div class="layui-layout layui-layout-admin">
    <div class="layui-header header">
        <div class="layui-logo"><a href=""> <img src="/static\m_system/images/logo.png" alt="logo">
            <h1>HiPHP</h1>
        </a></div>
        <a>
            <div class="layuihi-tool"><i title="展开" class="icon iconfont iconmenuclose" data-side-fold="1"></i></div>
        </a>
        <ul class="layui-nav layui-layout-left layui-header-menu layui-header-pc-menu mobile layui-hide-xs">

        </ul>
        <ul class="layui-nav layui-layout-left layui-header-menu mobile layui-hide-sm">
            <li class="layui-nav-item"> <a href="javascript:;"><i class="fa fa-list-ul"></i> 选择模块<span class="layui-nav-more"></span></a>
                <dl class="layui-nav-child layui-header-hi-menu">

                </dl>
            </li>
        </ul>
        <ul class="layui-nav layui-layout-right">
            <li class="layui-nav-item"> <a href="javascript:;" data-refresh="刷新"><i class="icon iconfont iconreflesh"></i></a> </li>
            <li class="layui-nav-item"> <a href="javascript:;" data-clear="清理" class="layuihi-clear"><i class="icon iconfont iconlaji"></i></a> </li>
            <li class="layui-nav-item layuihi-setting"> <a href="javascript:;"><?php echo htmlentities($login['nick']); ?></a>
                <dl class="layui-nav-child">
                    <dd> <a href="javascript:;" data-iframe-tab="page/user-setting.html" data-title="基本资料"
                            data-icon="fa fa-gears">基本资料</a> </dd>
                    <dd> <a href="javascript:;" data-iframe-tab="page/user-password.html" data-title="修改密码"
                            data-icon="fa fa-gears">修改密码</a> </dd>
                    <dd> <a href="javascript:;" class="login-out">退出登录</a> </dd>
                </dl>
            </li>
            <li class="layui-nav-item layuihi-select-bgcolor mobile layui-hide-xs"> <a href="javascript:;" data-bgcolor="配色方案"><i class="icon iconfont icongengduo"></i></a> </li>
        </ul>
    </div>
    <div class="layui-side layui-bg-black">
        <div class="layui-side-scroll layui-left-menu">

        </div>
    </div>
    <div class="layui-body">
        <div class="layui-tab" lay-filter="layuihiTab" id="top_tabs_box">
            <ul class="layui-tab-title" id="top_tabs">
                <li class="layui-this" id="layuihiHomeTabId" lay-id=""><i class="fa fa-home"></i> <span>首页</span></li>
            </ul>
            <ul class="layui-nav closeBox">
                <li class="layui-nav-item"> <a href="javascript:;"> <i class="fa fa-dot-circle-o"></i> 页面操作</a>
                    <dl class="layui-nav-child">
                        <dd><a href="javascript:;" data-page-close="other"><i class="fa fa-window-close"></i> 关闭其他</a></dd>
                        <dd><a href="javascript:;" data-page-close="all"><i class="fa fa-window-close-o"></i> 关闭全部</a></dd>
                    </dl>
                </li>
            </ul>
            <div class="layui-tab-content clildFrame">
                <div id="layuihiHomeTabIframe" class="layui-tab-item layui-show"> </div>
            </div>
        </div>
    </div>
</div>
<script>
    var admin_path = '<?php echo config("system.admin_path"); ?>';
</script>
<script src="/pack/layui/layui.js?v=<?php echo config('hiphp.version'); ?>"></script>
<script>
    layui.use(['element', 'form'], function() {
        var element = layui.element;
        var form = layui.form;
    });
    layui.config({
    	base: '/pack/layui_admin/',
        version: '<?php echo config("hiphp.version"); ?>'
    }).extend({
        layuihi: "layuihi",
        base: "base",
        func: "func",
        md5: "md5",
    }).use(['base']);
</script>
<script>
    layui.use(['element', 'layer', 'layuihi'], function () {
        var $ = layui.jquery, element = layui.element, layer = layui.layer;
        layuihi.init('<?php echo url("index"); ?>');
        $('.login-out').on("click", function () {
            $.ajax({
                url:"<?php echo url('entry/logout'); ?>",
                type:"post",
                dataType:"json",
                success:function(data){
                    layer.msg('退出成功', function () {
                        window.location.href = '/'+admin_path;
                    });
                },
                error:function(data){
                    $.messager.alert('错误',data.msg);
                }
            });
        });
    });
</script>
</body>
</html>
        </div>
    </div>
</div>
<?php break; case "3": ?>

<div class="layui-card main-box">
    <div class="layui-tab layui-tab-brief">
        <ul class="layui-tab-title">-
            <?php if(is_array($tabData['tab']) || $tabData['tab'] instanceof \think\Collection || $tabData['tab'] instanceof \think\Paginator): $i = 0; $__LIST__ = $tabData['tab'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;
            $tabData['current'] = isset($tabData['current']) ? $tabData['current'] : '';
             if(strtolower(trim($vo['url'])) == strtolower(trim($hiCurrentMenu['url'])) or strtolower(trim($vo['url'])) == strtolower(trim($tabData['current']))): ?>
            <li class="layui-this">
                <?php else: ?>
            <li>
                <?php endif; if((strpos($vo['url'], 'http'))): ?>
                <a href="<?php echo htmlentities($vo['url']); ?>" target="_blank"><?php echo $vo['title']; ?></a>
                <?php elseif((strpos($vo['url'], config('system.admin_path')) !== false)): ?>
                <a href="<?php echo htmlentities($vo['url']); ?>" id="<?php if((isset($vo['id']))): ?><?php echo htmlentities($vo['id']); ?><?php endif; ?>" class="<?php if((isset($vo['class']))): ?><?php echo htmlentities($vo['class']); ?><?php endif; ?>"><?php echo $vo['title']; ?></a>
                <?php else: ?>
                <a href="<?php echo url($vo['url']); ?>" class="<?php if((isset($vo['class']))): ?><?php echo htmlentities($vo['class']); ?><?php endif; ?>" id="<?php if((isset($vo['id']))): ?><?php echo htmlentities($vo['id']); ?><?php endif; ?>"><?php echo $vo['title']; ?></a>
                <?php endif; ?>
            </li>
            <?php endforeach; endif; else: echo "" ;endif; ?>
        </ul>
        <div class="layui-tab-content page-tab-content">
            <div class="layui-tab-item layui-show">
                <!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>LayuiHi</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta http-equiv="Access-Control-Allow-Origin" content="*">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link rel="stylesheet" href="/pack/layui/css/layui.css?v=<?php echo config('hiphp.version'); ?>" media="all">
    <link rel="stylesheet" href="/static\m_system/css/frame.css?v=<?php echo config('hiphp.version'); ?>" media="all">
    <link rel="stylesheet" href="/static\m_system/fonts/iconfont.css?v=<?php echo config('hiphp.version'); ?>" media="all">
    <?php if(isset($fontItems) && !empty($fontItems)): foreach($fontItems as $v): ?>
    <link rel="stylesheet" href="/<?php echo htmlentities($v['link']); ?>?v=<?php echo htmlentities($v['version']); ?>" media="all">
    <?php endforeach; ?>
    <?php endif; ?>
    <style id="layuihi-bg-color"></style>
</head>
<body class="layui-layout-body layuihi-all">
<div class="layui-layout layui-layout-admin">
    <div class="layui-header header">
        <div class="layui-logo"><a href=""> <img src="/static\m_system/images/logo.png" alt="logo">
            <h1>HiPHP</h1>
        </a></div>
        <a>
            <div class="layuihi-tool"><i title="展开" class="icon iconfont iconmenuclose" data-side-fold="1"></i></div>
        </a>
        <ul class="layui-nav layui-layout-left layui-header-menu layui-header-pc-menu mobile layui-hide-xs">

        </ul>
        <ul class="layui-nav layui-layout-left layui-header-menu mobile layui-hide-sm">
            <li class="layui-nav-item"> <a href="javascript:;"><i class="fa fa-list-ul"></i> 选择模块<span class="layui-nav-more"></span></a>
                <dl class="layui-nav-child layui-header-hi-menu">

                </dl>
            </li>
        </ul>
        <ul class="layui-nav layui-layout-right">
            <li class="layui-nav-item"> <a href="javascript:;" data-refresh="刷新"><i class="icon iconfont iconreflesh"></i></a> </li>
            <li class="layui-nav-item"> <a href="javascript:;" data-clear="清理" class="layuihi-clear"><i class="icon iconfont iconlaji"></i></a> </li>
            <li class="layui-nav-item layuihi-setting"> <a href="javascript:;"><?php echo htmlentities($login['nick']); ?></a>
                <dl class="layui-nav-child">
                    <dd> <a href="javascript:;" data-iframe-tab="page/user-setting.html" data-title="基本资料"
                            data-icon="fa fa-gears">基本资料</a> </dd>
                    <dd> <a href="javascript:;" data-iframe-tab="page/user-password.html" data-title="修改密码"
                            data-icon="fa fa-gears">修改密码</a> </dd>
                    <dd> <a href="javascript:;" class="login-out">退出登录</a> </dd>
                </dl>
            </li>
            <li class="layui-nav-item layuihi-select-bgcolor mobile layui-hide-xs"> <a href="javascript:;" data-bgcolor="配色方案"><i class="icon iconfont icongengduo"></i></a> </li>
        </ul>
    </div>
    <div class="layui-side layui-bg-black">
        <div class="layui-side-scroll layui-left-menu">

        </div>
    </div>
    <div class="layui-body">
        <div class="layui-tab" lay-filter="layuihiTab" id="top_tabs_box">
            <ul class="layui-tab-title" id="top_tabs">
                <li class="layui-this" id="layuihiHomeTabId" lay-id=""><i class="fa fa-home"></i> <span>首页</span></li>
            </ul>
            <ul class="layui-nav closeBox">
                <li class="layui-nav-item"> <a href="javascript:;"> <i class="fa fa-dot-circle-o"></i> 页面操作</a>
                    <dl class="layui-nav-child">
                        <dd><a href="javascript:;" data-page-close="other"><i class="fa fa-window-close"></i> 关闭其他</a></dd>
                        <dd><a href="javascript:;" data-page-close="all"><i class="fa fa-window-close-o"></i> 关闭全部</a></dd>
                    </dl>
                </li>
            </ul>
            <div class="layui-tab-content clildFrame">
                <div id="layuihiHomeTabIframe" class="layui-tab-item layui-show"> </div>
            </div>
        </div>
    </div>
</div>
<script>
    var admin_path = '<?php echo config("system.admin_path"); ?>';
</script>
<script src="/pack/layui/layui.js?v=<?php echo config('hiphp.version'); ?>"></script>
<script>
    layui.use(['element', 'form'], function() {
        var element = layui.element;
        var form = layui.form;
    });
    layui.config({
    	base: '/pack/layui_admin/',
        version: '<?php echo config("hiphp.version"); ?>'
    }).extend({
        layuihi: "layuihi",
        base: "base",
        func: "func",
        md5: "md5",
    }).use(['base']);
</script>
<script>
    layui.use(['element', 'layer', 'layuihi'], function () {
        var $ = layui.jquery, element = layui.element, layer = layui.layer;
        layuihi.init('<?php echo url("index"); ?>');
        $('.login-out').on("click", function () {
            $.ajax({
                url:"<?php echo url('entry/logout'); ?>",
                type:"post",
                dataType:"json",
                success:function(data){
                    layer.msg('退出成功', function () {
                        window.location.href = '/'+admin_path;
                    });
                },
                error:function(data){
                    $.messager.alert('错误',data.msg);
                }
            });
        });
    });
</script>
</body>
</html>
            </div>
        </div>
    </div>
</div>
<?php break; default: ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>LayuiHi</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta http-equiv="Access-Control-Allow-Origin" content="*">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link rel="stylesheet" href="/pack/layui/css/layui.css?v=<?php echo config('hiphp.version'); ?>" media="all">
    <link rel="stylesheet" href="/static\m_system/css/frame.css?v=<?php echo config('hiphp.version'); ?>" media="all">
    <link rel="stylesheet" href="/static\m_system/fonts/iconfont.css?v=<?php echo config('hiphp.version'); ?>" media="all">
    <?php if(isset($fontItems) && !empty($fontItems)): foreach($fontItems as $v): ?>
    <link rel="stylesheet" href="/<?php echo htmlentities($v['link']); ?>?v=<?php echo htmlentities($v['version']); ?>" media="all">
    <?php endforeach; ?>
    <?php endif; ?>
    <style id="layuihi-bg-color"></style>
</head>
<body class="layui-layout-body layuihi-all">
<div class="layui-layout layui-layout-admin">
    <div class="layui-header header">
        <div class="layui-logo"><a href=""> <img src="/static\m_system/images/logo.png" alt="logo">
            <h1>HiPHP</h1>
        </a></div>
        <a>
            <div class="layuihi-tool"><i title="展开" class="icon iconfont iconmenuclose" data-side-fold="1"></i></div>
        </a>
        <ul class="layui-nav layui-layout-left layui-header-menu layui-header-pc-menu mobile layui-hide-xs">

        </ul>
        <ul class="layui-nav layui-layout-left layui-header-menu mobile layui-hide-sm">
            <li class="layui-nav-item"> <a href="javascript:;"><i class="fa fa-list-ul"></i> 选择模块<span class="layui-nav-more"></span></a>
                <dl class="layui-nav-child layui-header-hi-menu">

                </dl>
            </li>
        </ul>
        <ul class="layui-nav layui-layout-right">
            <li class="layui-nav-item"> <a href="javascript:;" data-refresh="刷新"><i class="icon iconfont iconreflesh"></i></a> </li>
            <li class="layui-nav-item"> <a href="javascript:;" data-clear="清理" class="layuihi-clear"><i class="icon iconfont iconlaji"></i></a> </li>
            <li class="layui-nav-item layuihi-setting"> <a href="javascript:;"><?php echo htmlentities($login['nick']); ?></a>
                <dl class="layui-nav-child">
                    <dd> <a href="javascript:;" data-iframe-tab="page/user-setting.html" data-title="基本资料"
                            data-icon="fa fa-gears">基本资料</a> </dd>
                    <dd> <a href="javascript:;" data-iframe-tab="page/user-password.html" data-title="修改密码"
                            data-icon="fa fa-gears">修改密码</a> </dd>
                    <dd> <a href="javascript:;" class="login-out">退出登录</a> </dd>
                </dl>
            </li>
            <li class="layui-nav-item layuihi-select-bgcolor mobile layui-hide-xs"> <a href="javascript:;" data-bgcolor="配色方案"><i class="icon iconfont icongengduo"></i></a> </li>
        </ul>
    </div>
    <div class="layui-side layui-bg-black">
        <div class="layui-side-scroll layui-left-menu">

        </div>
    </div>
    <div class="layui-body">
        <div class="layui-tab" lay-filter="layuihiTab" id="top_tabs_box">
            <ul class="layui-tab-title" id="top_tabs">
                <li class="layui-this" id="layuihiHomeTabId" lay-id=""><i class="fa fa-home"></i> <span>首页</span></li>
            </ul>
            <ul class="layui-nav closeBox">
                <li class="layui-nav-item"> <a href="javascript:;"> <i class="fa fa-dot-circle-o"></i> 页面操作</a>
                    <dl class="layui-nav-child">
                        <dd><a href="javascript:;" data-page-close="other"><i class="fa fa-window-close"></i> 关闭其他</a></dd>
                        <dd><a href="javascript:;" data-page-close="all"><i class="fa fa-window-close-o"></i> 关闭全部</a></dd>
                    </dl>
                </li>
            </ul>
            <div class="layui-tab-content clildFrame">
                <div id="layuihiHomeTabIframe" class="layui-tab-item layui-show"> </div>
            </div>
        </div>
    </div>
</div>
<script>
    var admin_path = '<?php echo config("system.admin_path"); ?>';
</script>
<script src="/pack/layui/layui.js?v=<?php echo config('hiphp.version'); ?>"></script>
<script>
    layui.use(['element', 'form'], function() {
        var element = layui.element;
        var form = layui.form;
    });
    layui.config({
    	base: '/pack/layui_admin/',
        version: '<?php echo config("hiphp.version"); ?>'
    }).extend({
        layuihi: "layuihi",
        base: "base",
        func: "func",
        md5: "md5",
    }).use(['base']);
</script>
<script>
    layui.use(['element', 'layer', 'layuihi'], function () {
        var $ = layui.jquery, element = layui.element, layer = layui.layer;
        layuihi.init('<?php echo url("index"); ?>');
        $('.login-out').on("click", function () {
            $.ajax({
                url:"<?php echo url('entry/logout'); ?>",
                type:"post",
                dataType:"json",
                success:function(data){
                    layer.msg('退出成功', function () {
                        window.location.href = '/'+admin_path;
                    });
                },
                error:function(data){
                    $.messager.alert('错误',data.msg);
                }
            });
        });
    });
</script>
</body>
</html>
<?php endswitch; ?>
<?php endif; ?>
</body>
</html>