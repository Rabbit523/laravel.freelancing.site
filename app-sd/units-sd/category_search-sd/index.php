<?php

	$reqAuth = false;
	$module = 'category_search-sd';
	$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;
	require_once "../../requires-sd/config-sd.php";
	require_once DIR_CLASS."category_search-sd.lib.php";
	extract($_REQUEST);

	$winTitle = 'All Categories - ' . SITE_NM;
	$headTitle = 'All Categories';
	$metaTag = getMetaTags(array("description" => $winTitle, "keywords" => $headTitle, "author" => AUTHOR));

	$objPost = new stdClass();
	$action = (isset($_REQUEST['action']) && $_REQUEST['action']!='') ? $_REQUEST['action'] : '';
	$jobType = (isset($_REQUEST['type']) && $_REQUEST['type']!='') ? $_REQUEST['type'] : '';

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
	

	$mainObj = new categorySearch($module,$jobType,'','',$query_string);
	$pageContent = $mainObj->getPageContent();
	require_once DIR_TMPL . "compiler-sd.skd";	
?>