<?php

	$reqAuth = true;
	$module = 'freelancer_post_services-sd';
	$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;
	require_once "../../requires-sd/config-sd.php";
	require_once DIR_CLASS."freelancer_post_services-sd.lib.php";
	extract($_REQUEST);
        if($sessUserId<=0)
{
    $_SESSION['last_page'] ="post-services";
    $msgType = $_SESSION["msgType"] = disMessage(array('type' => 'info', 'var' => PLEASE_LOGIN_FOR_REPORT_THIS_SERVICE));
        redirectPage(SITE_URL . "SignIn");
}

	$winTitle = 'Post Services - ' . SITE_NM;
	$headTitle = 'Post Services';
	$metaTag = getMetaTags(array("description" => $winTitle, "keywords" => $headTitle, "author" => AUTHOR));

	$objPost = new stdClass();
	$action = (isset($_REQUEST['action']) && $_REQUEST['action']!='') ? $_REQUEST['action'] : '';
	$slug = (isset($_REQUEST['slug']) && $_REQUEST['slug']!='') ? $_REQUEST['slug'] : '';
	
	$mainObj = new FreelancerPostServices($module,'','',$slug);
	if($action == 'saveData' || $action == 'editData')
	{
		if(CheckRepeatEntry('tbl_services','servicesPostDate','ipAddress','id'))
		{
			$mainObj->saveData($_POST);
		}
		else
		{
                        $_SESSION['last_page'] = "post-services".$this->slug;
			$_SESSION["msgType"] = disMessage(array('type'=>'err','var'=>SOMETHING_WENT_WRONG));
			redirectPage(SITE_URL.'post-services');
		}
	}
	
	$pageContent = $mainObj->getPageContent();
	require_once DIR_TMPL . "compiler-sd.skd";	
?>