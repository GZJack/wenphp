<?php $title = '标题';$name = '姓名';$age = 18;$names = array (   0 => '张三',   1 => '李四',   2 => '王五', ); ?><!DOCTYPE html> <html lang="en"> <head>     <meta charset="UTF-8">     <meta name="viewport"           content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">     <meta http-equiv="X-UA-Compatible" content="ie=edge">     <title><?php echo $title; ?></title>     <link rel="stylesheet" type="text/css" href="./static/css/html.css">     <style>         .a{             width: 100%;             height: 50px;             background-color: aquamarine;         }     </style> </head> <body> <!--mix 会被混合到当前的页面里--> <?php include(APP_PATH.'user\view\test\..\header.html'); ?> <div>这是脚部</div>
<p><?php echo $name; ?></p> <div class="ccc"><?php echo $name; ?></div> <p><?php echo $age; ?></p> <p><?php echo $title; ?></p>  <?php foreach ($names as $name){     echo '<p>'.$name.'</p>'; } ?> </body> </html>