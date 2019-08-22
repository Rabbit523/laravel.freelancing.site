<?php
require_once("../../requires-sd/config-sd.php");
include(DIR_CLASS."freelancer_my_services-sd.lib.php");

$action = isset($_GET["action"]) ? $_GET["action"] : (isset($_POST["action"]) ? $_POST["action"] : '');
$id=isset($_GET["id"]) ? $_GET["id"] : (isset($_POST["id"]) ? $_POST["id"] : '');
$affected_rows = array();
$return_array = array();
$module = 'freelancer_my_services-sd';
$mainObj = new FreelancerMyServices($module, '');

$search_array = array();

if($action  == "load_search_data")
{
	$search_array['approval_status'] = (isset($_POST['approval_status']) ? $_POST['approval_status'] : '');
	$search_array['status'] = (isset($_POST['status']) ? $_POST['status'] : '');
    $search_array['keyword'] = (isset($_POST['keyword']) ? $_POST['keyword'] : '');

	$num_rec_per_page = 10;
    $start_from = load_more_pageNo($_REQUEST['page_no'],10);

    $where ="s.freelanserId='".$sessUserId."' and s.isDelete='n' ";
    if(isset($search_array['approval_status']) && $search_array['approval_status']!='')
    {
    	$where .= " AND s.isApproved='".$search_array['approval_status']."' ";
    }
    if(isset($search_array['status']) && $search_array['approval_status']!='')
    {
    	$where .= " AND s.isActive='".$search_array['status']."' ";
    }
    if(isset($search_array['keyword']) && $search_array['keyword']!='')
    {
        $where .= " AND s.serviceTitle LIKE '%".$search_array['keyword']."%' ";
    }

    $total_data = $db->pdoQuery("select s.*,c.".l_values('category_name')." as category_name,sub.".l_values('subcategory_name')." as subcategory_name from tbl_services As s 
        	LEFT JOIN tbl_category As c ON c.id = s.servicesCategory
        	LEFT JOIN tbl_subcategory As sub ON sub.id = s.servicesSubCategory
        	where ".$where." order by s.id DESC")->affectedRows();

    $query = $db->pdoQuery("select s.*,c.".l_values('category_name')." as category_name,sub.".l_values('subcategory_name')." as subcategory_name from tbl_services As s 
        	LEFT JOIN tbl_category As c ON c.id = s.servicesCategory
        	LEFT JOIN tbl_subcategory As sub ON sub.id = s.servicesSubCategory
        	where ".$where." order by s.id DESC LIMIT ".$start_from.",".$num_rec_per_page)->affectedRows();

    $load_data = load_more_data($total_data,'10',$query,$_REQUEST['page_no']); 
    $page = $load_data['page'];
    $return_array['btn'] = $load_data['btn'];
	$return_array['content'] = $mainObj->services_loop($search_array,$_REQUEST['page_no']);
	$return_array['pageno'] = $_REQUEST['page_no'];
    echo json_encode($return_array);
    exit;
}

if($action  == "load_more_data")
{
	$num_rec_per_page = 10;
    $start_from = load_more_pageNo($_REQUEST['page_no'],10);

    $total_data = $db->pdoQuery("select s.*,c.".l_values('category_name')." as category_name,sub.".l_values('subcategory_name')." as subcategory_name from tbl_services As s 
        	LEFT JOIN tbl_category As c ON c.id = s.servicesCategory
        	LEFT JOIN tbl_subcategory As sub ON sub.id = s.servicesSubCategory
        	where s.freelanserId = ? and s.isDelete='n' order by s.id DESC",array($sessUserId))->affectedRows();

    $query = $db->pdoQuery("select s.*,c.".l_values('category_name')." as category_name,sub.".l_values('subcategory_name')." as subcategory_name from tbl_services As s 
        	LEFT JOIN tbl_category As c ON c.id = s.servicesCategory
        	LEFT JOIN tbl_subcategory As sub ON sub.id = s.servicesSubCategory
        	where s.freelanserId = ? and s.isDelete='n' order by s.id DESC LIMIT ".$start_from.",".$num_rec_per_page,array($sessUserId))->affectedRows();

    $load_data = load_more_data($total_data,'10',$query,$_REQUEST['page_no']); 
    $page = $load_data['page'];
    $return_array['btn'] = $load_data['btn'];
	$return_array['content'] = $mainObj->services_loop($search_array,$_REQUEST['page_no']);
	echo json_encode($return_array);
	exit;
}
if($action=="delete_record")
{

    $serviceDetail2 = $db->pdoQuery("SELECT id,servicesId,serviceStatus FROM `tbl_services_order` WHERE serviceStatus != 'c' AND serviceStatus != 'cl' and  servicesId = ?",[$_REQUEST['id']])->affectedRows();

    $serviceDetail = $db->pdoQuery("select s.*,u.firstName,u.lastName,u.email from tbl_services As s 
        LEFT JOIN tbl_users As u ON u.id = s.freelanserId
        where s.id=? ",array($_REQUEST['id']))->result();

    if($serviceDetail2 > 0){
        $msgType = $_SESSION["msgType"] = disMessage(array('type' => 'err', 'var' => THIS_SERVICE_HAS_SOME_PENDING_ORDERS));
        echo SITE_URL.'post-services/'.$serviceDetail['servicesSlug'];
        exit;
    }

    $db->update("tbl_services",array("isDelete"=>'y'),array("id"=>$_REQUEST['id']));
    
    $user = ucfirst($serviceDetail['firstName'])." ".ucfirst($serviceDetail['lastName']);

    $service_link = SITE_URL."service/".$serviceDetail['servicesSlug'];
    $service_title = "<a href='".$service_link."'>".ucfirst($serviceDetail['serviceTitle'])."</a>";
    $arrayCont = array('ENTITY'=>"Service",'ENTITY_TITLE'=>$service_title,"USER_NAME"=>$user);
    $array = generateEmailTemplate('entity_delete_by_user',$arrayCont);
    sendEmailAddress($serviceDetail['email'],$array['subject'],$array['message']);
    //echo $service_link;
    echo SITE_URL.'my-services';
    $msgType = $_SESSION["msgType"] = disMessage(array('type' => 'suc', 'var' => YOUR_SERVICE_DELETED_SUCCESSFULLY));
    exit; 
    /*$db->delete("tbl_services",array("id"=>$_REQUEST['id']));*/
}
if($action == "payFeaturedFees")
{
    $featured_fees = SERVICE_FEATURED_FEES;
    $service_detail = $db->pdoQuery("select * from tbl_services where id=?",array($_REQUEST['id']))->result();
    $featured_amount = $service_detail['featured_days']*$featured_fees;
    $user_detail = $db->pdoQuery("select * from tbl_users where id=?",array($sessUserId))->result();
    $walletAmount = $user_detail['walletAmount'];
    
    if($featured_amount>$walletAmount)
    {
        $return_array['initial'] = "Opss!";
        $return_array['msg'] = YOU_HAVNT_SUFFICIANT_WALLET_AMOUNT_FOR_TRANSFER;
        $return_array['link'] = SITE_URL."financial-dashboard";
    }
    else
    {
        $final_wallet_amnt = $walletAmount-$featured_amount;
        $db->update("tbl_users",array("walletAmount"=>number_format($final_wallet_amnt,2)),array("email"=>$_SESSION['pickgeeks_email']));
        $featured_days = ($service_detail['featured_days']=='') ? '1' : $service_detail['featured_days'];
        $db->insert("tbl_wallet",array("userType"=>'f',"entity_id"=>$_REQUEST['id'],"entity_type"=>'s',"featured_days"=>$featured_days,"userId"=>$sessUserId,"amount"=>number_format($featured_amount,2),"paymentStatus"=>'c',"transactionType"=>'featuredFees',"createdDate"=>date("Y-m-d H:i:s"),"ipAddress"=>get_ip_address()));
        $db->update("tbl_services",array("featured_payment_status"=>'c'),array("id"=>$_REQUEST['id']));
        $return_array['initial'] = "Nice!";
        $return_array['msg'] = PAYMENT_DONE_SUCCESSFULLY;
        $return_array['link'] = SITE_URL."my-services";
    }
}

if($action == "makeFeatured"){
    $db->update("tbl_services",array("featured"=>'y',"featured_days"=>$_REQUEST["days"]),array("id"=>$_REQUEST['id']));
    $return_array['content'] = "0";
    echo json_encode($return_array);
    exit;
}
/*if($action == "makeFeatured")
{
    $amount = SERVICE_FEATURED_FEES * $_REQUEST['days'];
    $walletAmount = finalWalletAmount($sessUserId);
    if($walletAmount<$amount)
    {
        $return_array['content'] = "1";
    }
    else
    {
        $db->update("tbl_services",array("featured"=>'y'),array("id"=>$_REQUEST['id']));
        $db->insert("tbl_wallet",
            array(
                "userType"=>'f',
                "entity_id"=>$_REQUEST['id'],
                "entity_type"=>'s',
                "featured_days"=>$_REQUEST['days'],
                "userId"=>$sessUserId,
                "amount"=>$amount,
                "paymentStatus"=>'c',
                "transactionType"=>'featuredFees',
                "status"=>'completed',
                "createdDate"=>date('Y-m-d H:i:s'),"ipAddress"=>get_ip_address()
            )
        );
        updateWallet($sessUserId,$amount,'d');
        $return_array['content'] = "0";
    }
    echo json_encode($return_array);
    exit;
}*/

echo json_encode($return_array);
exit;
?>
