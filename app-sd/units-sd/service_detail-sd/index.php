<?php

	$reqAuth = false;
	$module = 'service_detail-sd';
	$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;
	require_once "../../requires-sd/config-sd.php";
	require_once DIR_CLASS."service_detail-sd.lib.php";
	extract($_REQUEST);

	$winTitle = 'Service Detail - ' . SITE_NM;
	$headTitle = 'Service Detail';
	$metaTag = getMetaTags(array("description" => $winTitle, "keywords" => $headTitle, "author" => AUTHOR));

	$objPost = new stdClass();
	$slug = (isset($_REQUEST['slug']) && $_REQUEST['slug']!='') ? $_REQUEST['slug'] : '';
	$mainObj = new ServiceDetail($module,$slug);


	if(serviceSlugCheck($slug)==0)
	{
		$msgType = $_SESSION["msgType"] = disMessage(array('type' => 'info', 'var' => SOMETHING_WENT_WRONG));
		redirectPage(SITE_URL);
	}
	$action = (isset($_REQUEST['action']) && $_REQUEST['action']!='') ? $_REQUEST['action'] : '';
	if($action == "report_service")
	{
		if(isset($sessUserId) && $sessUserId > 0)
		{
			$mainObj->reportService($_REQUEST);
		}
		else
		{
			$_SESSION['last_page'] = "service/".$slug;
			$msgType = $_SESSION["msgType"] = disMessage(array('type' => 'info', 'var' => PLEASE_LOGIN_FOR_REPORT_THIS_SERVICE));
			redirectPage(SITE_URL."SignIn");
		}
	}
	else if($action == "submitData")
	{
		if(isset($sessUserId) && $sessUserId>0)
		{
			$mainObj->serviceOrder($_REQUEST);
		}
		else
		{
			$_SESSION['last_page'] = "service/".$slug;
			$msgType = $_SESSION["msgType"] = disMessage(array('type' => 'info', 'var' => PLEASE_LOGIN_FOR_PROCEED_TO_ORDER));
			redirectPage(SITE_URL."SignIn");
		}
	}

	if($action == "send_msg")
	{
		$mainObj->send_message($_POST);
	}


	$title = "Service - ".$mainObj->serviceId($slug,'serviceTitle');
	$site_url = SITE_URL."service/".$slug;
	$serviceId = $mainObj->serviceId($slug,'id');

	$service_image = $db->pdoQuery("select * from tbl_services_files where servicesId=? LIMIT 1",array($serviceId))->result();
	$site_img = SITE_SERVICES_FILE.$service_image['fileName'];

	$metaTag = getOGMetaTags(array("og:site_name" => SITE_NM, "og:title" => $title,"og:type" => "article","og:url"=> $site_url,"og:image"=>$site_img,"og:locale"=>"en_US"));


	$serviceId = ($slug!='') ? getServiceId($slug) : '';
	$mainObj->serviceView($serviceId);
	
	$pageContent = $mainObj->getPageContent();
	require_once DIR_TMPL . "compiler-sd.skd";	
?>