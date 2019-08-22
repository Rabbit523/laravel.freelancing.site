<?php

	require_once('config-sd.php');
	$url = '';
	$message = 'Something went wrong';

	if(!empty($_FILES['upload']) && !empty($_FILES['upload']['name']) && empty($_FILES['upload']['error'])) {
		$valid_array = array('jpg', 'jpge', 'png');
		$tmp_name = $_FILES['upload']['tmp_name'];
		$name = $_FILES['upload']['name'];
		$size = $_FILES['upload']['size'];

		$ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

		if(in_array($ext, $valid_array)) {
			if($size <= 4194304) {
				$url = uploadFile($_FILES['upload'], DIR_CONTENT, SITE_CONTENT);
				$url = $url['file_path'].$url['file_name'];
				$message = '';
			} else {
				$message = 'File size must be less than 4MB';
			}
		} else {
			$message = 'Only jpg and png image types are allowed only';
		}
	}

   $funcNum = $_GET['CKEditorFuncNum'] ;
   // Optional: instance name (might be used to load a specific configuration file or anything else).
   $CKEditor = $_GET['CKEditor'] ;
   // Optional: might be used to provide localized messages.
   $langCode = $_GET['langCode'] ;

   // Usually you will only assign something here if the file could not be uploaded.
   echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($funcNum, '".$url."', '$message');</script>";
?>