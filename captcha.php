<?php
session_start();

$captcha_value = substr(md5(rand()), 0, 5);

$_SESSION['captcha'] = $captcha_value;

$image = imagecreatetruecolor(100, 40);
$background_color = imagecolorallocate($image, 255, 255, 255);
$text_color = imagecolorallocate($image, 0, 0, 0);

imagefilledrectangle($image, 0, 0, 100, 40, $background_color);
imagettftext($image, 20, 0, 10, 30, $text_color, 'Alcohole.ttf', $captcha_value);

header('Content-Type: image/png');
imagepng($image);
imagedestroy($image);
?>
