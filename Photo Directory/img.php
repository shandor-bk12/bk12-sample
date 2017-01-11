<?php
 
# Requires https://github.com/Nimrod007/PHP_image_resize
# http://www.nimrodstech.com/php-image-resize/
 
include_once('php_image_resize.php');
 
$imgURL = $_GET["url"];
$resizeResult = smart_resize_image($imgURL,'',180,170,false,'browser',flase,false,100,false);
 
?>