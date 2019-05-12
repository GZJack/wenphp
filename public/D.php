<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/4/19
 * Time: 12:33
 */

//ob_start();
//
//for ($i=10; $i>0; $i--) {
//    echo $i;
//    ob_end_clean();//修改部分
//    flush();
//    sleep(1);
//}

$data = '你很帅';
?>
<?php //for ($i=10; $i>0; $i--) { echo $i; flush(); sleep(1); } ?>

<?php //for ($i=10; $i>0; $i--) { echo $i.'<br>'; ob_flush();flush(); sleep(1); } ?>

<?php
echo 'who are you?'.$data;
?>
