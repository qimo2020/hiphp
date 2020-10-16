<?php /*a:2:{s:76:"D:\panchuming\phpstudy\WWW\hiphp_up\app\system/view/default/entry\index.html";i:1589179041;s:76:"D:\panchuming\phpstudy\WWW\hiphp_up\app\system/view/default/block\layui.html";i:1589178961;}*/ ?>
<!DOCTYPE html>
<html>
<head>
    <title>后台管理登录 -  Powered by <?php echo config('hiphp.name'); ?></title>
    <meta http-equiv="Access-Control-Allow-Origin" content="*">
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="/pack/layui/css/layui.css">
    <style type="text/css">
        html, body {width: 100%;height: 100%;overflow: hidden}
        body {background: #20222A; }
        .bg-black{background-color: #2f4056;}
        body:after {content:'';background-repeat:no-repeat;background-size:cover;-webkit-filter:blur(3px);-moz-filter:blur(3px);-o-filter:blur(3px);-ms-filter:blur(3px);filter:blur(3px);position:absolute;top:0;left:0;right:0;bottom:0;z-index:-1;}
        #layui-container {width: 100%;height: 100%;overflow: hidden}
        .login-box{box-shadow: 0 0 20px 0 rgba(0, 0, 0, 0.2), 0 5px 5px 0 rgba(0, 0, 0, 0.24);padding:25px 40px; background-color: #fff;width:280px;position:fixed;left:50%;top:50%;z-index:999;margin:-200px 0 0 -180px;}
        .layui-form-pane .layui-form-label{width:50px;background-color:rgba(255,255,255, 0.5);color:#fff;}
        .layui-form-pane .layui-input-block{margin-left:50px;}
        .login-box .layui-input{
            font-family: "Roboto", sans-serif;
            outline: 0;
            background: #f2f2f2;
            width: 100%;
            border: 0;
            margin: 0 0 15px;
            padding: 25px 15px;
            box-sizing: border-box;
            font-size: 15px;
        }
        .login-box input[name="password"]{}
        .login-box input[type="submit"]{letter-spacing:5px; font-size:15px; padding: 12px 0; height: auto;line-height: normal}
        .login-box input[name="captcha"]{width:105px;float: left;}
        .captcha{float:right;border-radius:3px;width:150px;height:50px;overflow:hidden;}
        .login-box .layui-btn{width:100%;}
        .login-box .copyright{text-align:center;height:50px;line-height:50px;font-size:12px;color:#aaa}
        .login-box .copyright a{color:#aaa;}
        @media only screen and (min-width:750px){
            .login-box{width:335px;margin:-200px 0 0 -200px!important}
            .login-box input[name="captcha"]{width:165px;}
        }
    </style>
</head>
<body>
<div id="layui-container">
    <div class="login-box">
        <form action="<?php echo url(); ?>" method="post" class="layui-form layui-form-pane">
            <fieldset class="layui-elem-field layui-field-title">
                <legend style="color:#333;"><?php echo config('hiphp.name'); ?> 管理后台登录</legend>
            </fieldset>
            <div class="layui-form-item">
                <input type="text" name="username" class="layui-input" lay-verify="required" placeholder="账号" autofocus="autofocus" />
            </div>
            <div class="layui-form-item">

                <input type="password" name="password" class="layui-input" lay-verify="required" placeholder="密码" />
            </div>

            <div class="layui-form-item" <?php if(($loginError < 3)): ?>style="display:none;"<?php endif; ?>>
            <input type="text" name="captcha" class="layui-input" placeholder="验证码" /><a href="javascript:;" class="captcha"><img <?php if(($loginError >= 3)): ?>src="<?php echo url('entry/captcha'); ?>"<?php endif; ?> height="48" width="150" id="captchaImg" alt="验证码" /></a>
            </div>
            <?php echo token_field(); ?>
            <input type="submit" value="登录" lay-submit="" lay-filter="formLogin" class="layui-btn bg-black">
        </form>
        <div class="copyright">
            ©<a href="<?php echo config('hiphp.url'); ?>" target="_blank"><?php echo config('hiphp.copyright'); ?></a> All Rights Reserved.
        </div>
    </div>
</div>
<script src="/pack/jquery/jquery-3.4.1.min.js" charset="utf-8"></script>
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
<script src="/pack/particleground/jquery.particleground.min.js" charset="utf-8"></script>
<script type="text/javascript">
window.sessionStorage.clear();
layui.use(['form', 'layer', 'md5', 'jquery'], function() {
    var $ = layui.jquery, layer = layui.layer, form = layui.form, md5 = layui.md5, captchaUrl = '<?php echo url("entry/captcha", [], false); ?>';
    form.on('submit(formLogin)', function(data) {
        var that = $(this), _form = that.parents('form'),
            account = $('input[name="username"]').val(),
            pwd = $('input[name="password"]').val(),
            token = $('input[name="__token__"]').val(),
            captcha = $('input[name="captcha"]').val();
        layer.msg('数据提交中...',{time:500000});
        that.prop('disabled', true);
        $.ajax({
            type: "POST",
            url: _form.attr('action'),
            data: {'username': account, 'password': md5.exec(pwd), '__token__' : token, captcha: captcha},
            success: function(res) {
                $('#captchaImg').attr('src', captchaUrl+'/rand/'+Math.random());
                if (res.data.token) {
                    $('input[name="__token__"]').val(res.data.token);
                }
                layer.msg(res.msg, {}, function() {
                    if (res.code == 1) {
                        location.href = res.url;
                    } else {
                        that.prop('disabled', false);
                    }
                });
            }
        });
        return false;
    });
    $(document).on('click', '#captchaImg', function(){
        $(this).attr('src', captchaUrl+'/rand/'+Math.random());
    });
    // 粒子线条背景
    $(document).ready(function(){
        $('#layui-container').particleground({
            dotColor:'#5cbdaa',
            lineColor:'#fff'
        });
    });
});
</script>
</body>
</html>