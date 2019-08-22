<?php
	$reqAuth = true;
	$module = 'freelancer_payment_history-sd';
	$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;
	require_once "../../requires-sd/config-sd.php";
	require_once DIR_CLASS."freelancer_payment_history-sd.lib.php";
	extract($_REQUEST);

	$winTitle = 'Payment History - ' . SITE_NM;
	$headTitle = 'Payment History';
	$metaTag = getMetaTags(array("description" => $winTitle, "keywords" => $headTitle, "author" => AUTHOR));
	$mainObj = new FreelancerPaymentHistory($module,'','');
	if($sessUserType!='Freelancer')
	{
		redirectPage(SITE_URL);
	}
	$pageContent = $mainObj->getPageContent();
	require_once DIR_TMPL . "compiler-sd.skd";	
?>