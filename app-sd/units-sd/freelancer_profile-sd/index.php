<?php
$reqAuth = true;
$module = 'freelancer_profile-sd';
$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;
require_once "../../requires-sd/config-sd.php";
require_once DIR_CLASS."freelancer_profile-sd.lib.php";
extract($_REQUEST);

$winTitle = 'Profile - ' . SITE_NM;
$headTitle = 'Profile';
$metaTag = getMetaTags(array("description" => $winTitle, "keywords" => $headTitle, "author" => AUTHOR));

$objPost = new stdClass();
$mainObj = new FreelancerProfile($module);

if($sessUserType!='Freelancer')
{
	redirectPage(SITE_URL);
}

$action = (isset($_REQUEST['action']) && $_REQUEST['action']!='') ? $_REQUEST['action'] : '';

if($action!='' && $action=='skillAdd')
{
	$mainObj->data_add($_REQUEST,"skills");
}
if($action!='' && $action=='langAdd')
{
	$mainObj->languageAdd($_REQUEST);
}
if($action!='' && $action=='addUserName')
{
	$mainObj->data_add($_REQUEST,"profile");
}
if($action!='' && $action=='addLocation')
{
	$mainObj->data_add($_REQUEST,"location");
}
if($action!='' && $action=='videoAdd')
{
	$mainObj->data_add($_REQUEST,"videoUrl");
}
if($action!='' && $action=='addPortfolio')
{
	$mainObj->portfolioAdd($_REQUEST);
}
if($action!='' && $action == 'deleteRecord')
{
	$mainObj->DeleteRecord($_REQUEST);
}
if($action!='' && $action=='addEducation')
{
	$mainObj->educationAdd($_REQUEST);
}
if($action!='' && $action=='addCertification')
{
	$mainObj->certificateAdd($_REQUEST);
}
if($action!='' && $action=='addExp')
{
	$mainObj->expAdd($_REQUEST);
}
if($action!='' && $action=='addCategory')
{
	$mainObj->data_add($_REQUEST,"subCategoryList");
}
if($action!='' && $action=='addlink')
{
	$mainObj->data_add($_REQUEST,"profileUrl");
}
if($action!='' && $action=='addOverview')
{
	$mainObj->data_add($_REQUEST,"overView");
}
if($action!='' && $action=='addTitle')
{
	$mainObj->data_add($_REQUEST,"proTitle");
}
if($action!='' && $action=='addExpLvl')
{
	$mainObj->data_add($_REQUEST,"expLvl");
}

$pageContent = $mainObj->getPageContent();
require_once DIR_TMPL . "compiler-sd.skd";	
?>