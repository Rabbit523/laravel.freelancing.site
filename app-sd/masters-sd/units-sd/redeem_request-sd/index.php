<?php

$reqAuth = true;
require_once("../../../requires-sd/config-sd.php");
include(DIR_ADMIN_CLASS."redeem_request-sd.lib.php");
require_once DIR_ADMIN_CLASS."paypal.class.php";

$paypal= new MyPayPal();

$module = "redeem_request-sd";
$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;
$table = "tbl_redeem_request";

$styles = array(array("data-tables/DT_bootstrap.css", SITE_ADM_PLUGIN));

$scripts = array("core/datatable.js",
    
    array("data-tables/jquery.dataTables.js", SITE_ADM_PLUGIN),
    array("data-tables/DT_bootstrap.js", SITE_ADM_PLUGIN)
    
    );

chkPermission($module);
$Permission = chkModulePermission($module);

$metaTag = getMetaTags(array("description" => "Admin Panel",
    "keywords" => 'Admin Panel',
    'author' => AUTHOR));
$breadcrumb = array("Manage Redeem Request");

$id = isset($_GET["id"]) ? (int) trim($_GET["id"]) : 0;
$postType = isset($_POST["type"]) ? trim($_POST["type"]) : '';
$type = isset($_GET["type"]) ? trim($_GET["type"]) : $postType;

$headTitle = $type == 'add' ? 'Add' : ($type == 'edit' ? 'Edit' : 'Manage') . ' Redeem Request';
$winTitle = $headTitle . ' - ' . SITE_NM;

if(isset($_GET['token']) && isset($_GET['PayerID'])){
    $paypal->DoExpressCheckoutPayment();
}


if(isset($_REQUEST['action']) && $_REQUEST['action'] == "pay_user")
{
    $response=array();
   // printr($_REQUEST);exit;
    $pay_detail = $db->pdoQuery("select r.*,u.paypal_email from tbl_redeem_request As r JOIN tbl_users As u ON r.userId = u.id where r.id  = '".$_REQUEST['wid']."' ")->result();
    if($pay_detail['paypal_email']!=''){
        
        $cancel_url = SITE_ADM_MOD."redeem_request-sd/";
        $paypal_email = $pay_detail['paypal_email'];
        $item_amount = $pay_detail['amount'];
        $item_name = "Transfer money";
        
        $paypalmode = (PPL_MODE=='sandbox') ? '.sandbox' : '';
            
        //Redirect user to PayPal store with Token received.
        
        $url_paypal ='https://www'.$paypalmode.'.paypal.com/cgi-bin/webscr';
        $url_paypal .= "?cmd=".urlencode("_xclick");
        $url_paypal .= "&business=".urlencode($paypal_email);
        $url_paypal .= "&item_name=".urlencode($item_name);
        $url_paypal .= "&item_number=".urlencode("1");
        $url_paypal .= "&custom=".urlencode($_REQUEST['wid']);
        $url_paypal .= "&currency_code=".urlencode(PPL_CURRENCY_CODE);
        $url_paypal .= "&rm=".urlencode("2");
        $url_paypal .= "&amount=".urlencode($item_amount);
        $url_paypal .= "&notify_url=".urlencode(SITE_ADM_MOD."redeem_request-sd/notify.php");
        $url_paypal .= "&return=".urlencode(SITE_ADM_MOD."redeem_request-sd/return.php");
        $url_paypal .= "&cancel_return=".urlencode($cancel_url);

        $activity_array = array("id" => $id, "module" => $module, "activity" => 'pay');
        add_admin_activity($activity_array);

        // echo $url_paypal;
        // exit;
        header("location:".$url_paypal);
        exit();
        
        /*$products = [];
        $item_amount = $pay_detail['amount'];
        $products[0]['ItemName'] = "Transfer money"; //Item Name
        $products[0]['ItemPrice'] = urlencode($item_amount); //Item Price
        $products[0]['ItemNumber'] =urlencode("1"); //Item Number
        $products[0]['ItemQty'] = 1; // Item Quantity
        $products[0]['ItemDesc'] = "Transfer money"; //Item Number
        $products[0]['PAYMENTREQUEST_0_CUSTOM'] = urlencode($_REQUEST['wid']);

        //-------------------- prepare charges -------------------------

        $charges = [];

        //Other important variables like tax, shipping cost
        $charges['TotalTaxAmount'] = 0;  //Sum of tax for all items in this order.
        $charges['HandalingCost'] = 0;  //Handling cost for this order.
        $charges['InsuranceCost'] = 0;  //shipping insurance cost for this order.
        $charges['ShippinDiscount'] = 0; //Shipping discount for this order. Specify this as negative number.
        $charges['ShippinCost'] = 0; //Although you may change the value later, try to pass in a shipping amount that is reasonably accurate.

        $paypal->SetExpressCheckOut($products, $charges);*/
    }else{
        $response['error'] = "User not provided the Paypal email id";
        $msgType = $_SESSION["msgType"] = disMessage(array('type' => 'suc', 'var' => 'Dear User, You have successfully switched as '.$_SESSION["pickgeeks_userType"]));
        echo json_encode($response);
        exit;
    }
}
$objContent = new RedeemRequest($module);
$pageContent = $objContent->getPageContent();
require_once(DIR_ADMIN_TMPL . "compiler-sd.skd");
