<?php
require_once("../../requires-sd/config-sd.php");
include(DIR_CLASS."job_workroom-sd.lib.php");

$action = isset($_GET["action"]) ? $_GET["action"] : (isset($_POST["action"]) ? $_POST["action"] : '');
$id=isset($_GET["id"]) ? $_GET["id"] : (isset($_POST["id"]) ? $_POST["id"] : '');
$affected_rows = array();
$return_array = array();
$module = "job_workroom-sd";
$mainObj = new JobWorkroom($module,'');
$search_array = array();


if($action == "send_message")
{

    
    $content = $_POST['msg'];
    $msg = $content[0]['value'];
    $winnerId = $content[1]['value'];
    $jobId = $content[2]['value'];
    $freelanserId = $winnerId;
    
    $jobs = $db->select('tbl_jobs',array('posterId,jobSlug'),array('id'=>$jobId))->result();
    $posterId = $jobs['posterId'];
    
    if($posterId == $sessUserId){
        $receiverId = $winnerId;
        $senderId = $sessUserId;
    } else {
        $receiverId = $posterId;
        $senderId = $sessUserId;
    }
         
   /* echo "Sender Id = ".$senderId." Receiver Id = ".$receiverId;     
    exit;*/
  
     
    $db->insert("tbl_messages",
        array(
            "entityId"=>$jobId,
            "senderId"=>$senderId,
            "receiverId"=>$receiverId,
            "entityType"=>'J',
            "message"=>$msg,
            "readStatus"=>'UR',
            "createdDate"=>date('Y-m-d H:i:s'),
            "messageType"=>'text',
            "ipAddress"=>get_ip_address()
        ));

    //Send notification to other user for alert, per day once.
    $res = $db->pdoQuery("SELECT id FROM tbl_messages WHERE DATE(createdDate) = CURRENT_DATE() AND entityId=? AND senderId=? AND entityType=?",array($jobId,$senderId,"J"))->affectedRows();
     
    if($res==1){
        $msg = NEW_MESSAGE_ON_JOB_WORKROOM;
        $type = ($sessUserType=="Freelancer"?"c":"f");
        $detail_link = SITE_URL."job/workroom/".$jobs['jobSlug'];
        $db->insert("tbl_notification",array("userId"=>$receiverId,"message"=>$msg,"detail_link"=>$detail_link,"isRead"=>'n',"notificationType"=>$type,"createdDate"=>date('Y-m-d H:i:s')));
    }

    $return_array['content'] = $mainObj->messages_list($jobId);
    echo json_encode($return_array);
    exit;
}
if($action == "check_report"){
    if(isset($sessUserId) && $sessUserId>0)
    {
        $return_array['content'] = 'modal';
    }
    else
    {
        $return_array['content'] = 'link';
        $_SESSION['last_page'] ="job/workroom/".$_REQUEST['s'];
    }
    echo json_encode($return_array);
    exit;
}

if($action == "delte_message")
{
    $serviceId = getServiceId($_POST['slug']);
    $userDetail = $db->pdoQuery("select deleteUser,senderId,receiverId from tbl_messages where entityId=?",array($serviceId))->result();
    if($userDetail['deleteUser'] == '')
    {
        $deleteuser = $sessUserId;
    }
    else
    {
        $deleteuser = $userDetail['senderId'].",".$userDetail['receiverId'];
    }
    $db->update("tbl_messages",array("deleteUser"=>$deleteuser),array("entityId"=>$serviceId));
    $return_array['content'] = $mainObj->messages_list($serviceId);
    echo json_encode($return_array);
    exit;
}
if($action == "delete_single_msg")
{
    $db->update("tbl_messages",array("deleteUser"=>$sessUserId),array("id"=>$_REQUEST['msg_id']));
    $return_array['content'] = $mainObj->messages_list($_REQUEST['oId']);
    echo json_encode($return_array);
    exit;
}
if($action == "load_review")
{
    $return_array['content'] = $mainObj->reviewRating($_REQUEST['id'],$_REQUEST['type']);
    echo json_encode($return_array);
    exit;
}
if($action == 'delete_milestone'){
    $db->delete('tbl_milestones',array("id" => $_POST['id']));
    $return_array['message'] = 'success';
    echo json_encode($return_array);
    exit();
}
if($action=="move_file")
{
    
    if(empty($_FILES['file']['error'])) 
    {
        $file_name = uploadFile($_FILES['file'], DIR_WORKROOM,SITE_WORKROOM);

        $job_detail = $db->pdoQuery("select * from tbl_jobs where id=?",array($_REQUEST['id']))->result();

        if($sessUserId == $job_detail['posterId'])
        {
            $receiverId = $sessUserId;
        }
        else
        {
            $receiverId = $job_detail['posterId'];
        }
        $db->insert("tbl_messages",array(
            "entityId"=>$_REQUEST['id'],
            "senderId"=>$sessUserId,
            "receiverId"=>$receiverId,
            "entityType"=> 'J',
            "fileName"=>$file_name['file_name'],
            "readStatus"=>'UR',
            "createdDate"=>date('Y-m-d H:i:s'),
            "messageType"=>'file',
            "ipAddress"=>get_ip_address())
        );

        $return_array['content'] = $mainObj->messages_list($_REQUEST['id']);
        echo json_encode($return_array);
        exit;           
    }
}

if($action == "startWork"){
    $mainObj->startWork($_REQUEST);
    $return_array['type'] = "true";
    echo json_encode($return_array);
    exit;
}
if($action == "endJob"){
    $mainObj->endWork($_REQUEST);
    $return_array['type'] = "true";
    echo json_encode($return_array);
    exit;    
}
if($action == "disputeAccJob"){
    $mainObj->disputeAccJob($_REQUEST);
    $return_array['type'] = "true";
    echo json_encode($return_array);
    exit;    
}

if($action == "disputeDenyJob"){

    $mainObj->disputeDenyJob($_REQUEST);
    $return_array['type'] = "true";
    echo json_encode($return_array);
    exit;    
}

if($action == "hire_freelancer"){
    $returnVal=$mainObj->hireFreelancer($_REQUEST['bid_id']);
    $return_array['type'] = $returnVal;
    echo json_encode($return_array);
    exit;    
}

if($action == "delete_fid"){
	$aWhere = array("id" => $_POST['fid_id']);
    $affected_rows = $db->delete('tbl_saved_freelancer',$aWhere)->affectedRows();
    $return_array['type'] = "true";
    echo json_encode($return_array);
    exit;
}
if($action == "delete_all"){
    $aWhere = array('customerId'=>$_SESSION['pickgeeks_userId']);
    $affected_rows = $db->delete('tbl_saved_freelancer',$aWhere)->affectedRows();
    $return_array['type'] = "true";
    echo json_encode($return_array);
    exit;
}
if($action == "bid_accept"){
    $bid_id = $_POST['bid_id'];
    $job_id = $_POST['job_id'];
    $return_array['status'] = $mainObj->acceptMilestones($bid_id,$job_id);
    echo json_encode($return_array);
    exit;
}
if($action == "start_work"){
    $job_id = $_POST['job_id'];
    $aWhere = array('id'=>$job_id);
    $up = array('jobStatus'=>"ip");

    $result = $db->update('tbl_jobs',$up,$aWhere)->affectedRows();
    $return_array['status'] = 'true';
    echo json_encode($return_array);
    exit;
}
if($action == "ask_for_payment"){
    $ml_id = $_REQUEST['mls_id']; 
    $return_array['data'] = $mainObj->askForPayment($ml_id);   
    echo json_encode($return_array);
    exit;
}
if($action == "milestone_payment"){
    $ml_id = $_REQUEST['mls_id']; 
    $return_array['status'] = $mainObj->milestonePayment($ml_id);
    echo json_encode($return_array);
    exit;
}
if($action == "get_milestone_amount"){
    $ml_id = $_REQUEST['mls_id']; 
    $mls = $db->select('tbl_milestones',array('amount'),array('id'=>$ml_id))->result();
    $amount = $mls['amount'];
    echo json_encode($amount);
    exit;   
}
if($action == "message_read"){
    $job_id = $_REQUEST['job_id'];
    $userid = $sessUserId;
    $aWhere = array('entityType'=>'J','entityId'=>$job_id,'receiverId'=>$userid);
    $update = array('readStatus' => 'R');
    //$db->update('tbl_messages',$update,$aWhere);
    $return_array['status'] = 'true';
    echo json_encode($return_array);
    exit; 
}

echo json_encode($return_array);
exit;
?>
