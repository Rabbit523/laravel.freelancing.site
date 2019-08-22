<?php

$reqAuth = true;
require_once("../../../requires-sd/config-sd.php");
include(DIR_ADMIN_CLASS."cPass-sd.lib.php");

$objPost = new stdClass();

$winTitle = 'Change Password - ' . SITE_NM;
$headTitle = 'Change Password';
$metaTag = getMetaTags(array("description" => "Admin Panel",
    "keywords" => 'Admin Panel',
    'author' => AUTHOR));

$module = 'cPass-sd';
$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;
$breadcrumb = array($headTitle);
chkPermission($module);

extract($_POST);

$objPost->opasswd = isset($opasswd) ? filtering($opasswd, 'input') : '';
$objPost->passwd = isset($passwd) ? filtering($passwd, 'input') : '';
$objPost->cpasswd = isset($cpasswd) ? filtering($cpasswd, 'input') : '';
$objPost->passvalue = isset($passvalue) ? filtering($passvalue, 'input') : '';

$objUser = new cPass();

if (isset($_POST["submitChange"])) {
    
    if ($objPost->opasswd != "" && $objPost->passwd != "" && $objPost->cpasswd != "") {
        $changeReturn = $objUser->submitProcedure();
        switch ($changeReturn) {
            case 'wrongPass' : $toastr_message = disMessage(array('from' => 'admin', 'type' => 'error', 'var' => 'Your Current Password is wrong, Please try again'));
                break;
            case 'passNotmatch' : $toastr_message = disMessage(array('from' => 'admin', 'type' => 'error', 'var' => 'Password does not match the Confirm Password, Please try again'));
                break;
            case 'succChangePass' : {
                    $_SESSION["toastr_message"] = disMessage(array('from' => 'admin', 'type' => 'suc', 'var' => 'Your Password updated successfully'));
                    redirectPage(SITE_ADM_MOD . $module);
                    break;
                }
        }
    }
}

$pageContent = $objUser->getPageContent();
require_once(DIR_ADMIN_TMPL . "compiler-sd.skd");

?>