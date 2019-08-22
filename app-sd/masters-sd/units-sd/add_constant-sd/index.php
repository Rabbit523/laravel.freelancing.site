<?php

$reqAuth = true;
require_once("../../../requires-sd/config-sd.php");
include(DIR_ADMIN_CLASS."add_constant-sd.lib.php");

$objPost = new stdClass();

$winTitle = 'Add Constant - ' . SITE_NM;
$headTitle = 'Add Constant';
$metaTag = getMetaTags(array("description" => "Admin Panel",
    "keywords" => 'Admin Panel',
    'author' => AUTHOR));

$module = 'add_constant-sd';
$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;
$breadcrumb = array($headTitle);
chkPermission($module);

extract($_POST);

/*$objPost->opasswd = isset($opasswd) ? filtering($opasswd, 'input') : '';
$objPost->passwd = isset($passwd) ? filtering($passwd, 'input') : '';
$objPost->cpasswd = isset($cpasswd) ? filtering($cpasswd, 'input') : '';
$objPost->passvalue = isset($passvalue) ? filtering($passvalue, 'input') : '';*/

$objUser = new cPass($module);


if (isset($_POST["submitChange"])) {
   $changeReturn=$objUser->submitProcedure($_POST);
    
        switch ($changeReturn) {
            case 'success' : {
                    $_SESSION["toastr_message"] = disMessage(array('from' => 'admin', 'type' => 'suc', 'var' => 'Your response has been submited'));
                    redirectPage(SITE_ADM_MOD . $module);
                    break;
                }
        }
   
}

$pageContent = $objUser->getPageContent();

require_once(DIR_ADMIN_TMPL . "compiler-sd.skd");

?>