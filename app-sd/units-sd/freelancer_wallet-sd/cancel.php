<?php
	$reqAuth = true;
	require_once "../../requires-sd/config-sd.php";
	require_once DIR_CLASS."freelancer_wallet-sd.lib.php";

	$mail_data = '';
	foreach ($_POST as $key => $value) {
		$mail_data .= "$key => $value <br />";
	}
	
	redirectPage(SITE_URL);
?>