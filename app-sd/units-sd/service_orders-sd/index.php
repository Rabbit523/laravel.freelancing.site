<?php

	$reqAuth = true;
      
	$module = 'service_orders-sd';
	$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;
	require_once "../../requires-sd/config-sd.php";
        
	require_once DIR_CLASS."service_orders-sd.lib.php";

	extract($_REQUEST);
        
	$winTitle = 'Service Orders- ' . SITE_NM;
                
	$headTitle = 'Service Orders';

	$metaTag = getMetaTags(array("description" => $winTitle, "keywords" => $headTitle, "author" => AUTHOR));

	$objPost = new stdClass();
	$mainObj = new ServiceOrders($module);
	
	$pageContent = $mainObj->getPageContent();
	require_once DIR_TMPL . "compiler-sd.skd";	
?>