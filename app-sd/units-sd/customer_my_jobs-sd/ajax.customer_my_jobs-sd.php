<?php
require_once("../../requires-sd/config-sd.php");
include(DIR_CLASS."customer_my_jobs-sd.lib.php");

$action = isset($_GET["action"]) ? $_GET["action"] : (isset($_POST["action"]) ? $_POST["action"] : '');
$id=isset($_GET["id"]) ? $_GET["id"] : (isset($_POST["id"]) ? $_POST["id"] : '');
$affected_rows = array();
$return_array = array();
$module = "customer_my_jobs-sd";
$mainObj = new CustomerMyJobs($module,'');
$search_array = array();

if($action == "load_search_data")
{
    $num_rec_per_page = 10;
    $start_from = load_more_pageNo($_REQUEST['page_no'],10);

    $search_array['appStatus'] = (isset($_POST['appStatus']) ? $_POST['appStatus'] :'');
    $search_array['levelStatus'] = (isset($_POST['levelStatus']) ? $_POST['levelStatus'] :'');
    $search_array['typeStatus'] = (isset($_POST['typeStatus']) ? $_POST['typeStatus'] :'');
    $search_array['status'] = (isset($_POST['status']) ? $_POST['status'] :'');
    $search_array['keyword'] = (isset($_POST['keyword']) ? $_POST['keyword'] :'');
    $where = "j.posterId = ".$sessUserId;
    
    if(isset($search_array['appStatus']) && $search_array['appStatus']!='')
    {
        $where .= " AND j.isApproved='".$search_array['appStatus']."' ";
    }
    if(isset($search_array['levelStatus']) && $search_array['levelStatus']!='')
    {
        $where .= " AND j.expLevel='".$search_array['levelStatus']."' ";
    }
    if(isset($search_array['typeStatus']) && $search_array['typeStatus']!='')
    {
        $where .= " AND j.jobType='".$search_array['typeStatus']."' ";
    }
    if(isset($search_array['keyword']) && $search_array['keyword']!='')
    {
        $where .= " AND j.jobTitle LIKE '%".$search_array['keyword']."%' ";
    }

    $countRecord = $db->pdoQuery("select j.*,c.".l_values('category_name')." as category_name,s.".l_values('subcategory_name')." as subcategory_name,jb.isHired,jb.userId
            from tbl_jobs as j
            LEFT JOIN tbl_category AS c ON j.jobCategory = c.id
            LEFT JOIN tbl_subcategory AS s ON j.jobSubCategory = s.id
            LEFT JOIN tbl_job_bids AS jb ON jb.jobid = j.id
            where ".$where)->affectedRows();

    $jobs = $db->pdoQuery("select j.*,c.".l_values('category_name')." as category_name,s.".l_values('subcategory_name')." as subcategory_name,jb.isHired,jb.userId
            from tbl_jobs as j
            LEFT JOIN tbl_category AS c ON j.jobCategory = c.id
            LEFT JOIN tbl_subcategory AS s ON j.jobSubCategory = s.id
            LEFT JOIN tbl_job_bids AS jb ON jb.jobid = j.id
            where ".$where." LIMIT ".$start_from.",".$num_rec_per_page)->results();

    $load_data = load_more_data($countRecord,'10',$jobs,$_REQUEST['page_no']);

    $page = $load_data['page'];
    $return_array['btn'] = $load_data['btn'];
    $return_array['content'] = $mainObj->getJobs($search_array,$_REQUEST['page_no']);
    $return_array['pageno'] = $_REQUEST['page_no'];
    echo json_encode($return_array);
    exit;

}

if($action == "load_more_data")
{
	$num_rec_per_page = 10;
    $start_from = load_more_pageNo($_REQUEST['page_no'],10);
    $search_array['appStatus'] = (isset($_POST['appStatus']) ? $_POST['appStatus'] :'');
    $search_array['levelStatus'] = (isset($_POST['levelStatus']) ? $_POST['levelStatus'] :'');
    $search_array['typeStatus'] = (isset($_POST['typeStatus']) ? $_POST['typeStatus'] :'');
    $search_array['status'] = (isset($_POST['status']) ? $_POST['status'] :'');

    $countRecord = $db->pdoQuery("select j.*,c.".l_values('category_name')." as category_name,s.".l_values('subcategory_name')." as subcategory_name,jb.isHired,jb.userId
            from tbl_jobs as j
            LEFT JOIN tbl_category AS c ON j.jobCategory = c.id
            LEFT JOIN tbl_subcategory AS s ON j.jobSubCategory = s.id
            LEFT JOIN tbl_job_bids AS jb ON jb.jobid = j.id
            where j.posterId = ".$sessUserId)->affectedRows();

    $jobs = $db->pdoQuery("select j.*,c.".l_values('category_name')." as category_name,s.".l_values('subcategory_name')." as subcategory_name,jb.isHired,jb.userId
            from tbl_jobs as j
            LEFT JOIN tbl_category AS c ON j.jobCategory = c.id
            LEFT JOIN tbl_subcategory AS s ON j.jobSubCategory = s.id
            LEFT JOIN tbl_job_bids AS jb ON jb.jobid = j.id
            where j.posterId = ".$sessUserId." LIMIT ".$start_from.",".$num_rec_per_page)->results();

    $load_data = load_more_data($countRecord,'10',$jobs,$_REQUEST['page_no']);
    $page = $load_data['page'];
    $return_array['btn'] = $load_data['btn'];
    $return_array['content'] = $mainObj->getJobs($search_array,$_REQUEST['page_no']);
    $return_array['pageno'] = $_REQUEST['page_no'];
    echo json_encode($return_array);
    exit;
}
if($action == "delete_jobs")
{
	$aWhere = array("id" => $_POST['job_id']);
    $db->update("tbl_jobs",array("isDelete"=>'y'),$aWhere);


    $jobDetail = $db->pdoQuery("select j.*,u.firstName,u.lastName,u.email from tbl_jobs As j
        LEFT JOIN tbl_users As u ON u.id = j.posterId
        where j.id ='".$_REQUEST['job_id']."'
        ")->result();

    $joblink = SITE_URL."job/".$jobDetail['jobSlug'];
    $jobDetailLink = "<a href='".$joblink."'>".ucfirst($jobDetail['jobTitle'])."</a>";
    $user  = ucfirst($jobDetail['firstName'])." ".ucfirst($jobDetail['lastName']);
    $arrayCont = array('ENTITY'=>"Job",'ENTITY_TITLE'=>$jobDetailLink,"USER_NAME"=>$user);
    $array = generateEmailTemplate('entity_delete_by_user',$arrayCont);
    sendEmailAddress($jobDetail['email'],$array['subject'],$array['message']);

    $return_array['type'] = "true";
    echo json_encode($return_array);
    exit;
}
if($action == "payFeaturedFees"){
    $service_detail = $db->pdoQuery("select * from tbl_jobs where id=?",array($_REQUEST['id']))->result();
    $featured_fees = $service_detail['featuredDuration']*JOB_FEATURED_FEES;

    $user_detail = $db->pdoQuery("select * from tbl_users where id=?",array($sessUserId))->result();
    $walletAmount = $user_detail['walletAmount'];

    if($featured_fees > $walletAmount)
    {
        $return_array['initial'] = "Opss!";
        $return_array['msg'] = YOU_HAVNT_SUFFICIANT_WALLET_AMOUNT_FOR_TRANSFER;
        $return_array['link'] = SITE_URL."c/financial-dashboard";
    }
    else
    {
        $final_wallet_amnt = $walletAmount-$featured_fees;
        $db->update("tbl_users",array("walletAmount"=>$final_wallet_amnt),array("email"=>$_SESSION['pickgeeks_email']));
        $db->insert("tbl_wallet",array("userType"=>'c',"entity_id"=>$_REQUEST['id'],"entity_type"=>'j',"userId"=>$sessUserId,"amount"=>$featured_fees,"paymentStatus"=>'c',"transactionType"=>'featuredFees',"createdDate"=>date("Y-m-d H:i:s"),"ipAddress"=>get_ip_address()));
        $db->update("tbl_jobs",array("featured"=>'y',"featuredPayment"=>"y","featuredPaymentDate"=>date("Y-m-d H:i:s")),array("id"=>$_REQUEST['id']));
        $return_array['initial'] = "Nice!";
        $return_array['msg'] = PAYMENT_DONE_SUCCESSFULLY;
    }
}

if($action == "makeFeaturedPayment"){
    $f_duration = rtrim($_REQUEST['f_dur'],".");
    $featured_fees = $f_duration*JOB_FEATURED_FEES;
    $user_detail = $db->pdoQuery("select * from tbl_users where email=?",array($_SESSION['pickgeeks_email']))->result();
    $walletAmount = $user_detail['walletAmount'];

    if($featured_fees > $walletAmount)
    {
        $return_array['initial'] = "Opss!";
        $return_array['msg'] = YOU_HAVNT_SUFFICIANT_WALLET_AMOUNT_FOR_TRANSFER;
        $return_array['link'] = SITE_URL."c/financial-dashboard";
    }
    else
    {
        $final_wallet_amnt = $walletAmount-$featured_fees;
        $db->update("tbl_users",array("walletAmount"=>$final_wallet_amnt),array("email"=>$_SESSION['pickgeeks_email']));
        $db->insert("tbl_wallet",array("userType"=>'c',"entity_id"=>$_REQUEST['id'],"entity_type"=>'j',"userId"=>$sessUserId,"amount"=>$featured_fees,"paymentStatus"=>'c',"transactionType"=>'featuredFees',"createdDate"=>date("Y-m-d H:i:s"),"ipAddress"=>get_ip_address()));
         // $db->update("tbl_jobs",array("featured"=>'y',"featuredDate"=>date("Y-m-d H:i:s"),"featuredDuration"=>$f_duration,"featuredPayment"=>"y","featuredPaymentDate"=>date("Y-m-d H:i:s")),array("id"=>$_REQUEST['id']));
        $db->update("tbl_jobs",array("featured"=>'y',"featuredDate"=>date("Y-m-d H:i:s"),"featuredDuration"=>$f_duration),array("id"=>$_REQUEST['id']));
        $return_array['initial'] = "Nice!";
        $return_array['msg'] = PAYMENT_DONE_SUCCESSFULLY;
    }
}

echo json_encode($return_array);
exit;
?>
