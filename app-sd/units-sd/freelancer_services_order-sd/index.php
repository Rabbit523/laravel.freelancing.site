<?php
	$reqAuth = true;
	$module = 'freelancer_services_order-sd';
	$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;
	require_once "../../requires-sd/config-sd.php";
	require_once DIR_CLASS."freelancer_services_order-sd.lib.php";
	extract($_REQUEST);

	$winTitle = 'Services Order - ' . SITE_NM;
	$headTitle = 'Services Order';
	$metaTag = getMetaTags(array("description" => $winTitle, "keywords" => $headTitle, "author" => AUTHOR));

	$objPost = new stdClass();
	$actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $keyword = $_SERVER['QUERY_STRING'];
	$url = explode("?",$actual_link);

	if(!empty($url[1]))
	{
		$a = explode("&",$url[1]);
		$array1 = $array2 = array();
		foreach ($a as $key => $value) {
			$x = explode("=",$a[$key]);
			array_push($array1,$x[0]);
			array_push($array2,$x[1]);
		}
		$query_string = array_combine($array1, $array2);
	}
	else
	{
		$query_string = array();
	}
	$mainObj = new FreelancerServiceOrder($module,'','',$query_string);

	if($sessUserType!='Freelancer')
	{
		redirectPage(SITE_URL);
	}
	
	$action = (isset($_REQUEST['action']) && $_REQUEST['action']!='') ? $_REQUEST['action'] : '';
	
	
	if($action == "saveDeadline")
	{
		$mainObj->saveDeadline($_POST);
	}
	if($action == "saveMsg")
	{
		$mainObj->saveMsg($_POST);
	}
	$pageContent = $mainObj->getPageContent();
	require_once DIR_TMPL . "compiler-sd.skd";	
?>