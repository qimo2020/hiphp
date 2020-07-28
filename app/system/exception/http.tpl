<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $msg ?></title>
    <link rel="stylesheet" href="/pack/bootstrap4/css/bootstrap.min.css">
    <link rel="stylesheet" href="/static/m_system/css/exception.css">
</head>
<body>
<div class="container">
    <div class="main">
        <h2><?php echo $msg; ?></h2>
        <h1>
            <?php
                if(isset($httpCode)){
                    echo $httpCode;
                }else{
                    echo 'Error';
                }
            ?>
        </h1>
        <div class="link-item">
            <a href="/">
                返回首页
            </a>
            <a href="javascript:;" onclick="window.history.go(-1)">
                返回上一页
            </a>
        </div>
    </div>
</div>
</body>
</html>