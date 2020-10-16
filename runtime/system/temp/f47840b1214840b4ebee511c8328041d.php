<?php /*a:2:{s:78:"D:\panchuming\phpstudy\WWW\hiphp_up\app\system/view/default/index\welcome.html";i:1595227730;s:75:"D:\panchuming\phpstudy\WWW\hiphp_up\app\system/view/default/block\base.html";i:1595577783;}*/ ?>
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
                <link rel="stylesheet" href="/static/p_builder/css/table.css?v=<?php echo config('builder.version'); ?>?v=<?php echo config('hiphp.version'); ?>">
<div class="table-box">
    <div class="page-tab-content">
        <fieldset class="layui-elem-field red">
            <legend>欢迎使用HiPHP</legend>
            <div class="layui-field-box">
                特别说明：你可用于学习或商用，但必须保留版权信息的正常显示。
            </div>
        </fieldset>
        <table class="layui-table" lay-skin="line">
            <colgroup>
                <col width="160">
                <col>
            </colgroup>
            <thead>
            <tr>
                <th colspan="2">系统基础信息</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td align="right">HiPHP版本</td>
                <td>V <?php echo config('hiphp.version'); ?> <a href="<?php echo url('upgrade/index'); ?>" class="mcolor">检查新版本</a></td>
            </tr>
            <tr>
                <td align="right">MySql版本</td>
                <td>MySql <?php echo db('mysql')->query('select version() as version')[0]['version']; ?></td>
            </tr>
            <tr>
                <td align="right">PHP版本</td>
                <td>PHP <?php echo htmlentities(PHP_VERSION); ?> </td>
            </tr>
            <tr>
                <td align="right">ThinkPHP版本</td>
                <td><?php echo \think\facade\App::version(); ?></td>
            </tr>
            <tr>
                <td align="right">服务器环境</td>
                <td><?php echo htmlentities(PHP_OS); ?> / <?php echo htmlentities($_SERVER["SERVER_SOFTWARE"]); ?></td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
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
            <link rel="stylesheet" href="/static/p_builder/css/table.css?v=<?php echo config('builder.version'); ?>?v=<?php echo config('hiphp.version'); ?>">
<div class="table-box">
    <div class="page-tab-content">
        <fieldset class="layui-elem-field red">
            <legend>欢迎使用HiPHP</legend>
            <div class="layui-field-box">
                特别说明：你可用于学习或商用，但必须保留版权信息的正常显示。
            </div>
        </fieldset>
        <table class="layui-table" lay-skin="line">
            <colgroup>
                <col width="160">
                <col>
            </colgroup>
            <thead>
            <tr>
                <th colspan="2">系统基础信息</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td align="right">HiPHP版本</td>
                <td>V <?php echo config('hiphp.version'); ?> <a href="<?php echo url('upgrade/index'); ?>" class="mcolor">检查新版本</a></td>
            </tr>
            <tr>
                <td align="right">MySql版本</td>
                <td>MySql <?php echo db('mysql')->query('select version() as version')[0]['version']; ?></td>
            </tr>
            <tr>
                <td align="right">PHP版本</td>
                <td>PHP <?php echo htmlentities(PHP_VERSION); ?> </td>
            </tr>
            <tr>
                <td align="right">ThinkPHP版本</td>
                <td><?php echo \think\facade\App::version(); ?></td>
            </tr>
            <tr>
                <td align="right">服务器环境</td>
                <td><?php echo htmlentities(PHP_OS); ?> / <?php echo htmlentities($_SERVER["SERVER_SOFTWARE"]); ?></td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
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
                <link rel="stylesheet" href="/static/p_builder/css/table.css?v=<?php echo config('builder.version'); ?>?v=<?php echo config('hiphp.version'); ?>">
<div class="table-box">
    <div class="page-tab-content">
        <fieldset class="layui-elem-field red">
            <legend>欢迎使用HiPHP</legend>
            <div class="layui-field-box">
                特别说明：你可用于学习或商用，但必须保留版权信息的正常显示。
            </div>
        </fieldset>
        <table class="layui-table" lay-skin="line">
            <colgroup>
                <col width="160">
                <col>
            </colgroup>
            <thead>
            <tr>
                <th colspan="2">系统基础信息</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td align="right">HiPHP版本</td>
                <td>V <?php echo config('hiphp.version'); ?> <a href="<?php echo url('upgrade/index'); ?>" class="mcolor">检查新版本</a></td>
            </tr>
            <tr>
                <td align="right">MySql版本</td>
                <td>MySql <?php echo db('mysql')->query('select version() as version')[0]['version']; ?></td>
            </tr>
            <tr>
                <td align="right">PHP版本</td>
                <td>PHP <?php echo htmlentities(PHP_VERSION); ?> </td>
            </tr>
            <tr>
                <td align="right">ThinkPHP版本</td>
                <td><?php echo \think\facade\App::version(); ?></td>
            </tr>
            <tr>
                <td align="right">服务器环境</td>
                <td><?php echo htmlentities(PHP_OS); ?> / <?php echo htmlentities($_SERVER["SERVER_SOFTWARE"]); ?></td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
            </div>
        </div>
    </div>
</div>
<?php break; default: ?>
<link rel="stylesheet" href="/static/p_builder/css/table.css?v=<?php echo config('builder.version'); ?>?v=<?php echo config('hiphp.version'); ?>">
<div class="table-box">
    <div class="page-tab-content">
        <fieldset class="layui-elem-field red">
            <legend>欢迎使用HiPHP</legend>
            <div class="layui-field-box">
                特别说明：你可用于学习或商用，但必须保留版权信息的正常显示。
            </div>
        </fieldset>
        <table class="layui-table" lay-skin="line">
            <colgroup>
                <col width="160">
                <col>
            </colgroup>
            <thead>
            <tr>
                <th colspan="2">系统基础信息</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td align="right">HiPHP版本</td>
                <td>V <?php echo config('hiphp.version'); ?> <a href="<?php echo url('upgrade/index'); ?>" class="mcolor">检查新版本</a></td>
            </tr>
            <tr>
                <td align="right">MySql版本</td>
                <td>MySql <?php echo db('mysql')->query('select version() as version')[0]['version']; ?></td>
            </tr>
            <tr>
                <td align="right">PHP版本</td>
                <td>PHP <?php echo htmlentities(PHP_VERSION); ?> </td>
            </tr>
            <tr>
                <td align="right">ThinkPHP版本</td>
                <td><?php echo \think\facade\App::version(); ?></td>
            </tr>
            <tr>
                <td align="right">服务器环境</td>
                <td><?php echo htmlentities(PHP_OS); ?> / <?php echo htmlentities($_SERVER["SERVER_SOFTWARE"]); ?></td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
<?php endswitch; ?>
<?php endif; ?>
</body>
</html>