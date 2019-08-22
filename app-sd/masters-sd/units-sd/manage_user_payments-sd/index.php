<?php

$reqAuth = true;
require_once("../../../requires-sd/config-sd.php");
include(DIR_ADMIN_CLASS."manage_user_payments-sd.lib.php");
$module = "manage_user_payments-sd";
$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;
$table = "tbl_refund_payment";

$styles = array(array("data-tables/DT_bootstrap.css", SITE_ADM_PLUGIN));

$scripts = array("core/datatable.js",
    array("data-tables/jquery.dataTables.js", SITE_ADM_PLUGIN),
    array("data-tables/DT_bootstrap.js", SITE_ADM_PLUGIN));

chkPermission($module);
$Permission = chkModulePermission($module);

$metaTag = getMetaTags(array("description" => "Admin Panel",
    "keywords" => 'Admin Panel',
    'author' => AUTHOR));
$breadcrumb = array("Manage User Payments");

$id = isset($_GET["id"]) ? (int) trim($_GET["id"]) :(isset($_POST["id"]) ? (int) trim($_POST["id"]):0);
$postType = isset($_POST["type"]) ? trim($_POST["type"]) : '';
$type = isset($_GET["type"]) ? trim($_GET["type"]) :(isset($_POST["type"]) ? trim($_POST["type"]) :$postType);
$headTitle = $type == 'add' ? 'Add' : ($type == 'edit' ? 'Edit' : 'Manage') . ' User Payments';
$winTitle = $headTitle . ' - ' . SITE_NM;
$objContent = new user_payments($module);
if ($_SERVER["REQUEST_METHOD"] == "POST" && $_REQUEST['type']=="edit") {
    $objContent->contentSubmit($_POST,$Permission);
}
if(isset($_REQUEST['action']) && $_REQUEST['action'] == "pay_refund"){
    $response=array();
    $pay_detail = $db->pdoQuery("select w.*,u.paypalAccount from tbl_refund_payment As w JOIN tbl_users As u ON w.userId = u.id where w.refundId  = '".$_REQUEST['wid']."' ")->result();
    if($pay_detail['paypalAccount']!=''){
        $paypalmode = (PPL_MODE=='sandbox') ? '.sandbox' : '';

        $cancel_url = SITE_ADM_MOD."manage_user_payments-sd/";
        $paypal_email = $pay_detail['paypalAccount'];
        $item_amount = $pay_detail['amount'];
        $item_name = "Transfer money";
        $url_paypal = 'https://www'.$paypalmode.'.paypal.com/cgi-bin/webscr';
        $url_paypal .= "?cmd=".urlencode("_xclick");
        $url_paypal .= "&business=".urlencode($paypal_email);
        $url_paypal .= "&item_name=".urlencode($item_name);
        $url_paypal .= "&item_number=".urlencode("1");
        $url_paypal .= "&custom=".urlencode($_REQUEST['wid']);
        $url_paypal .= "&currency_code=".urlencode(PPL_CURRENCY_CODE);
        $url_paypal .= "&rm=".urlencode("2");
        $url_paypal .= "&amount=".urlencode($item_amount);
        $url_paypal .= "&notify_url=".urlencode(SITE_ADM_MOD."manage_user_payments-sd/notify.php");
        $url_paypal .= "&return=".urlencode(SITE_ADM_MOD."manage_user_payments-sd/return.php");
        $url_paypal .= "&cancel_return=".urlencode($cancel_url);
       
        header("location:".$url_paypal);
        exit();
    }else{
        $response['error'] = "User not provided the palypal email id";
        echo json_encode($response);
        exit;
    }
}
$pageContent = $objContent->getPageContent();
require_once(DIR_ADMIN_TMPL . "compiler-sd.skd");
