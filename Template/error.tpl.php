<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type">
    <title>貌似出错了</title>
    <style type="text/css">
        *{ padding: 0; margin: 0; }
        body{ background: #e1e1e1; font-family: '微软雅黑'; color: #333; font-size: 16px; }
        img{ border: 0; }
        .error{ width:800px;border-radius:5px;border:1px solid #ccc;background-color:#fff;
            margin:0 auto;margin-top:100px;margin-bottom:80px;padding:10px;}
        .txt{ border-bottom:1px solid #ccc;height:30px;font-weight:bold;margin-bottom:10px;}
        h1{ font-size: 24px; line-height: 1.5; }
        .error .content{ padding-top: 10px}
        .error .info{ margin-bottom: 12px; }
        .error .info .title{ margin-bottom: 3px; }
        .error .info .title h3{ color: #000; font-weight: 700; font-size: 16px; }
        .error .info .text{ line-height: 24px; }
        .copyright{border-top:1px solid #ccc;height:30px;line-height:30px;text-align:right;margin-top:10px;}
        .copyright a{ color: #666; text-decoration: none; }
    </style>
</head>
<body>
<div class="error">
    <p class="txt">貌似出错了</p>
    <h1><?php echo strip_tags($e['message']);?></h1>
    <div class="content">
        <?php if(isset($e['file'])) {?>
            <div class="info">
                <div class="title">
                    <h3>错误位置</h3>
                </div>
                <div class="text">
                    <p>FILE: <?php echo $e['file'] ;?> &#12288;<?php if(isset($e['line'])){?>LINE: <?php echo $e['line'];?><?php }?></p>
                </div>
            </div>
        <?php }?>
        <?php if(isset($e['trace'])) {?>
            <div class="info">
                <div class="title">
                    <h3>TRACE</h3>
                </div>
                <div class="text">
                    <p><?php echo nl2br($e['trace']);?></p>
                </div>
            </div>
        <?php }?>
    </div>
    <div class="copyright">
        <p><a title="官方网站" href="http://www.dinlei.com/dphp" target="_blank">DPHP v2</a></p>
    </div>
</div>

</body>
</html>