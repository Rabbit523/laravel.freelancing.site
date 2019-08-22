<?php
require_once "config-sd.php";
$captchanumber = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890abcdefghijklmnopqrstuvwxyz'; // Initializing PHP variable with string
$captchanumber = substr(str_shuffle($captchanumber), 0, 6); // Getting first 6 word after shuffle.
$_SESSION["code"] = $captchanumber; // Initializing session variable with above generated sub-string
$image = imagecreatefrompng(SITE_IMG . "captcha_bg.png"); // Generating CAPTCHA 
$foreground = imagecolorallocate($image, 255, 255, 255); // Font Color
imagettftext($image, 18, 0, 10, 27, $foreground, DIR_FONT . 'raleway-medium-webfont.ttf', $captchanumber);
header('Content-type: image/png');
imagepng($image);
?>