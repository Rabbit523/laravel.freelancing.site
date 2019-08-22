<?php
$content = '';
require_once("../../../requires-sd/config-sd.php");
if ($adminUserId == 0) {
    die('Invalid request');
}
include(DIR_ADMIN_CLASS."report-sd.lib.php");
//echo "<pre>";print_r($_REQUEST);exit;
$module = 'report-sd';
chkPermission($module);
$Permission = chkModulePermission($module);
$table = 'tbl_report';
$action = isset($_GET["action"]) ? trim($_GET["action"]) : (isset($_POST["action"]) ? trim($_POST["action"]) : 'datagrid');
$id = isset($_GET["id"]) ? trim($_GET["id"]) : (isset($_POST["id"]) ? trim($_POST["id"]) : 0);
$value = isset($_POST["value"]) ? trim($_POST["value"]) : isset($_GET["value"]) ? trim($_GET["value"]) : '';
$page_no = isset($_POST['iDisplayStart']) ? intval($_POST['iDisplayStart']) : 0;
$rows = isset($_POST['iDisplayLength']) ? intval($_POST['iDisplayLength']) : 25;
$sort = isset($_POST["iSortTitle_0"]) ? $_POST["iSortTitle_0"] : NULL;
$order = isset($_POST["sSortDir_0"]) ? $_POST["sSortDir_0"] : NULL;
$chr = isset($_POST["sSearch"]) ? $_POST["sSearch"] : NULL;
$sEcho = isset($_POST['sEcho']) ? $_POST['sEcho'] : 1;
$filtering_type = isset($_POST['filtering_type']) ? $_POST['filtering_type'] : '';
$filtering_status = isset($_POST['filtering_status']) ? $_POST['filtering_status'] : '';
extract($_GET);
$searchArray = array("page" => $page_no, "rows" => $rows, "sort" => $sort, "order" => $order, "offset" => $page_no, "chr" => $chr, 'sEcho' => $sEcho,'filtering_type' => $filtering_type,"filtering_status" => $filtering_status);

if (isset($_REQUEST['ajaxvalidate']) && $_REQUEST['ajaxvalidate']) {
    $pageName = filtering($_REQUEST['userId'], 'input');
    $pId = filtering($_REQUEST['id'], 'input', 'int');
    if ($pageName != '' && $pId != '') {
        if (getTotalRows("tbl_listing_reported", "userId ='" . $pageName . "' AND id != '" . $pId . "' ", 'id') >= 1) {
            echo 'false';
            exit;
        } else {
            echo 'true';
            exit;
        }
    } else {
        echo 'false';
        exit;
    }
} else if($action == "approve"){
    $setVal = array('status' => ($value = 'Acc'));
    $db->update($table, $setVal, array("id" => $id));
    echo json_encode(array('type' => 'success','message'=> 'Reported Accepted successfully'));
    $activity_array = array("id" => $id, "module" => $module, "activity" => 'Report status', "action" => $value);
    add_admin_activity($activity_array);
    exit;
} else if($action == "reject"){
    $setVal = array('status' => ($value = 'Rej'));
    $db->update($table, $setVal, array("id" => $id));
    echo json_encode(array('type' => 'success','message'=> 'Reported Rejected successfully'));
    $activity_array = array("id" => $id, "module" => $module, "activity" => 'Report status', "action" => $value);
    add_admin_activity($activity_array);
    exit;
} else if ($action == "updateStatus" && in_array('status', $Permission)) {
    $setVal = array('isActive' => ($value == 'a' ? 'y' : 'n'));
    $db->update($table, $setVal, array("id" => $id));
    echo json_encode(array('type' => 'success', 'Reported Listing ' . ($value == 'a' ? 'activated ' : 'deactivated ') . 'successfully'));
    $activity_array = array("id" => $id, "module" => $module, "activity" => 'status', "action" => $value);
    add_admin_activity($activity_array);
    exit;
} else if ($action == "delete" && in_array('delete', $Permission)) {
    $aWhere = array("id" => $id);
    $affected_rows = $db->delete($table, $aWhere)->affectedRows();
    if ($affected_rows && $affected_rows > 0) {
        $activity_array = array("id" => $id, "module" => $module, "activity" => 'delete');
        add_admin_activity($activity_array);
        echo json_encode(array('type' => 'success', 'message' => "Reported Listing deleted successfully"));
        exit;
    } else {
        echo json_encode(array('type' => 'error', 'message' => "There seems to be an issue deleting Reported Listing"));
        exit;
    }
}else if ($action == "view") {
    if(in_array('view', $Permission)) {
        $activity_array = array("id" => $id, "module" => $module, "activity" => 'view');
        add_admin_activity($activity_array);
    } else {
        die("You don't have sufficient permission to view record");
    }
}else if ($action == "sendmailtoseller" || $action== "sendmailtouser") {
    if($semail!="" && $uemail!=""){
        if($action=="sendmailtouser"){
            $to=trim($uemail);
            $toName = getTableValue("tbl_users",'userName',array('email'=>$to));
            $sName = getTableValue("tbl_users",'userName',array('email'=>$semail));

            $arrayCont = array('greetings'=>trim($toName,"'"),'reported_details'=>"You reported the Listing item of user ".trim($sName,"'"));
            $array = generateEmailTemplate('report_listing',$arrayCont);
            sendEmailAddress($to,$array['subject'],$array['message']);
        }
        else{
            $to=trim($semail);
            $toName = getTableValue("tbl_users",'userName',array('email'=>$to));
            $uName = getTableValue("tbl_users",'userName',array('email'=>$uemail));

            $arrayCont = array('greetings'=>trim($toName,"'"),'reported_details'=>"Your Listing item has been reported by the user ".trim($uName,"'"));
            $array = generateEmailTemplate('report_listing',$arrayCont);
            sendEmailAddress($to,$array['subject'],$array['message']);
        }
        
        echo json_encode(array('type' => 'success', 'message' => "Mail has been sent successfully"));
        exit;
    }
    else{
        echo json_encode(array('type' => 'success', 'message' => "There seems to be an issue to sending mail, Please try again"));
        exit; 
    }
}
$mainObject = new Report($module, $id, NULL, $searchArray, $action);
extract($mainObject->data);
echo ($content);
exit;
