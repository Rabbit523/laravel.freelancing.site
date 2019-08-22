<?php

$reqAuth = false;
$module = 'post_job-sd';

$reqAuthXml = $_SERVER["SERVER_NAME"] . '##' . $module;
require_once "../../requires-sd/config-sd.php";
require_once DIR_CLASS . "post_job-sd.lib.php";
$table = "tbl_jobs";
extract($_REQUEST);

if($sessUserId<=0)
{
    if($sessUserType == "c"){
    $_SESSION['last_page'] ="c/post-job";
    $msgType = $_SESSION["msgType"] = disMessage(array('type' => 'info', 'var' => PLEASE_LOGIN_FOR_REPORT_THIS_SERVICE));
        redirectPage(SITE_URL . "SignIn");    
    }
    else{
        $_SESSION['last_page'] ="post-services";
    $msgType = $_SESSION["msgType"] = disMessage(array('type' => 'info', 'var' => PLEASE_LOGIN_FOR_REPORT_THIS_SERVICE));
        redirectPage(SITE_URL . "SignIn");
    }
    
}

$winTitle = 'Post Job - ' . SITE_NM;
$headTitle = 'Post Job';

$metaTag = getMetaTags(array("description" => $winTitle, "keywords" => $headTitle, "author" => AUTHOR));
$slug = (isset($_REQUEST['slug']) && $_REQUEST['slug'] != '') ? $_REQUEST['slug'] : '';



$objPost = new stdClass();

$mainObj = new PostJob($module, '', '', $slug);

	if(isset($_POST['action'])){
		$mainObj->submitJobContent($_POST);
	}


$pageContent = $mainObj->getPageContent();

require_once DIR_TMPL . "compiler-sd.skd";
?>