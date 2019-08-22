<?php
require_once("../../requires-sd/config-sd.php");
include(DIR_CLASS."customer_wallet-sd.lib.php");

$action = isset($_GET["action"]) ? $_GET["action"] : (isset($_POST["action"]) ? $_POST["action"] : '');
$id=isset($_GET["id"]) ? $_GET["id"] : (isset($_POST["id"]) ? $_POST["id"] : '');
$affected_rows = array();
$return_array = array();
$module = 'customer_wallet-sd';
$mainObj = new CustomerWallet($module);


if(isset($_GET['amountRedeem']) && (!empty($_GET['amountRedeem']))){
    $amount=trim($_GET['amountRedeem']);
   
    $qry=$db->pdoQuery("SELECT walletAmount FROM tbl_users where id = ?",array($sessUserId))->result();

    $pending_request_detail = $db->pdoQuery("select * from tbl_redeem_request where userId=?",array($sessUserId));
    $pending_redeem_request = $pending_request_detail->results();
    $rowsAffected = $pending_request_detail->affectedRows();
    $total_redeem_amount = 0;
    if($rowsAffected>0)
    {
    	foreach ($pending_redeem_request as $value) 
    	{
    		$total_redeem_amount += $value['amount'];
    	}
    }
    $final_amount = $qry['walletAmount'] - $total_redeem_amount;
    $response = ($final_amount>=$amount) ? 'true' : 'false';
    echo $response;
    exit;
}
if(isset($_POST['action']) && $_POST['action'] == "send_reminder")
{
	$user_detail = $db->pdoQuery("select * from tbl_users where id=?",array($sessUserId))->result();
	$username = ucfirst($user_detail['first_name']." ".$user_detail['last_name']);

	$redeem_detail  = $db->pdoQuery("select * from tbl_redeem_request where id=?",array($_REQUEST['id']))->result();

	$arrayCont = array('HEADING'=>"Reminder of Redeem Request",'USERNAME'=>$username,'AMOUNT'=>CURRENCY_SYMBOL.$redeem_detail['amount'] ,'PAYPAL_EMAIL'=>$user_detail['paypal_email'],'DATE'=>date('d F,Y',strtotime($redeem_detail['request_date'])));
	$array = generateEmailTemplate('user_redeem_request',$arrayCont);
	sendEmailAddress(ADMIN_EMAIL,$array['subject'],$array['message']);
	echo json_encode($return_array);
	exit;
}
echo json_encode($return_array);
exit;
?>
