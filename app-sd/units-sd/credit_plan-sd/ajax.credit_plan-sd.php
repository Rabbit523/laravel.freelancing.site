<?php
require_once("../../requires-sd/config-sd.php");
include(DIR_CLASS."credit_plan-sd.lib.php");

$action = isset($_GET["action"]) ? $_GET["action"] : (isset($_POST["action"]) ? $_POST["action"] : '');
$id=isset($_GET["id"]) ? $_GET["id"] : (isset($_POST["id"]) ? $_POST["id"] : '');
$affected_rows = array();
$return_array = array();
$module = 'credit_plan-sd';
$mainObj = new CreditPlan($module);

if($action == "subscribePlan")
{
	$plan_detail = $db->pdoQuery("select * from tbl_credit_package where id=?",array($_REQUEST['planId']))->result();
	$wallet_amount = checkUserWalletAmount($sessUserId);

	if($plan_detail['price']==0)
	{
		$chkuserFreeplan = checkUserFreePlan($sessUserId);
		if($chkuserFreeplan>0)
		{
			$return_array['type'] = "error";
			$return_array['msg'] = "You have already used your free plan";
			$return_array['url'] = '';
			echo json_encode($return_array);
			exit;
		}
		else
		{
			goto packageProceed;
		}
	}
	else
	{
		packageProceed:
		if($wallet_amount<$plan_detail['price'])
		{
			$return_array['type'] = "error";
			$return_array['msg'] = "You don't have enough credits to purchase. Please credit up your wallet. Redirecting over there....";
			$return_array['url'] = SITE_URL."financial-dashboard";

			echo json_encode($return_array);
			exit;
		}
		else
		{
			$query = $db->pdoQuery("select p.*,u.email,u.userType from tbl_user_plan As p
				LEFT JOIN tbl_users As u ON u.id = p.userId
				where p.userId=? and p.isCurrent=? ORDER BY p.id DESC",array($sessUserId,'y'));

			$detail = $query->result();
			$total_record = $query->affectedRows();


			if($total_record>0)
			{
				$remain_credit = $detail['no_credit'] - $detail['used_credit'];
			}
			else
			{
				$remain_credit = 0;
			}
			$last_credit = $remain_credit;

			$db->update("tbl_user_plan",array("isCurrent"=>'n'),array("userId"=>$sessUserId));
			$db->insert("tbl_user_plan",array("userId"=>$sessUserId,"planId"=>$_REQUEST['planId'],"no_credit"=>($plan_detail['noCredits']+$last_credit),"used_credit"=>'0',"last_credit"=>$last_credit,"subscribedDate"=>date('Y-m-d H:i:s'),"isCurrent"=>'y'));

			$query = $db->pdoQuery("select p.*,u.email,u.userType from tbl_user_plan As p
				LEFT JOIN tbl_users As u ON u.id = p.userId
				where p.userId=? and p.isCurrent=? ORDER BY p.id DESC",array($sessUserId,'y'));

			$detail = $query->result();


			$db->pdoQuery("update tbl_users set walletAmount=walletAmount-'".$plan_detail['price']."' where email=? ",array($_SESSION['pickgeeks_email']));

			/*send email start*/
			$arrayCont = array('greetings'=>"There!",'mp_name'=>filtering($plan_detail['title']),"mp_price"=>CURRENCY_SYMBOL.$plan_detail['price'],"CREDIT"=>$plan_detail['noCredits']);
	    	$array = generateEmailTemplate('user_plan_purchase',$arrayCont);
	    	sendEmailAddress($detail['email'],$array['subject'],$array['message']);


	    	$db->insert("tbl_wallet",array('entity_id' => $_REQUEST['planId'],'entity_type' => $_REQUEST['planId'],"userType"=>$detail['userType'],"userId"=>$sessUserId,"amount"=>$plan_detail['price'],"paymentStatus"=>'c',"transactionType"=>'creaditPurchase',"status"=>'Completed',"createdDate"=>date('Y-m-d H:i:s'),"ipAddress"=>get_ip_address()));

			$return_array['type'] = "success";
			$return_array['msg'] = "Your purchase for ".$plan_detail['title']." has been completed";
			$return_array['url'] = SITE_URL."creditPlan";
			echo json_encode($return_array);
			exit;
		}
		echo json_encode($return_array);
		exit;
	}
}
echo json_encode($return_array);
exit;
?>
