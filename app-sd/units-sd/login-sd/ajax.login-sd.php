<?php
	require_once("../../requires-sd/config-sd.php");
	include(DIR_CLASS."login-sd.lib.php");

	$action = isset($_GET["action"]) ? $_GET["action"] : (isset($_POST["action"]) ? $_POST["action"] : '');

	if(!empty($_GET['forgot_email'])) {
		$email = strtolower(trim($_GET['forgot_email']));
		$exist = $db->pdoQuery('SELECT id FROM tbl_users WHERE LOWER(email)=?', array($email))->result();
		echo (!empty($exist) ? 'true' : 'false');
		exit;
	}
?>