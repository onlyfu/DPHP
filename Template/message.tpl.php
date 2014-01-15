<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type">
    <title>系统提示</title>
    <style type="text/css">
        *{ padding: 0; margin: 0; }
        body{ background: #e1e1e1; font-family: '微软雅黑'; color: #333; font-size: 16px; }
        .msg{ width:800px;border-radius:5px;border:1px solid #ccc;background-color:#fff;
            margin:0 auto;margin-top:100px;margin-bottom:80px;padding:10px;}
        .txt{ border-bottom:1px solid #ccc;height:30px;font-weight:bold;margin-bottom:10px;}
        h1{ font-size: 24px; line-height: 1.5; }
        h1.succ{ color:#009900; }
        h1.error{ color:#d90000; }
        .msg .redirect{ padding-top: 10px}
        .msg .redirect a{color:#000099;text-decoration: underline;}
        #refresh{ color:#ff0000;}
        
        .copyright{border-top:1px solid #ccc;height:30px;line-height:30px;text-align:right;margin-top:10px;}
        .copyright a{ color: #666; text-decoration: none; }
    </style>
    <script type="text/javascript">
        var redirect='<?php echo $redirect;?>';
        var refresh=<?php echo $refresh;?>;
        window.onload=function(){
            if(redirect&&refresh){
                var timer=setInterval(function(){
                    refresh--;
                    document.getElementById('refresh').innerHTML=refresh;
                    if(refresh<=0){
                        location.href=redirect;
                    }
                },1000);
            }
        }
    </script>
</head>
<body>
<div class="msg">
    <p class="txt">系统提示</p>
    <h1 class="<?php echo $status;?>"><?php echo strip_tags($msg);?></h1>
    <div class="redirect">
        页面将在 <span id="refresh"><? echo $refresh;?></span> 后自动跳转 或 <a href="<?php echo $redirect;?>">点击跳转</a>
    </div>
    <div class="copyright">
        <p><a title="官方网站" href="http://www.dinlei.com/dphp" target="_blank">DPHP v2</a></p>
    </div>
</div>
</body>
</html>