<?php

$content = '';
require_once("../../../requires-sd/config-sd.php");
if ($adminUserId == 0) {
    die('Invalid request');
}
include(DIR_ADMIN_CLASS."redeem_request-sd.lib.php");
//echo "<pre>";print_r($_REQUEST);exit;
$module = 'redeem_request-sd';
chkPermission($module);
$Permission = chkModulePermission($module);
$table = 'tbl_redeem_request';
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
$user_type = isset($_POST['user_type']) ? $_POST['user_type'] : '';
$date = isset($_POST['date']) ? $_POST['date'] : '';
extract($_GET);
$searchArray = array("page" => $page_no, "rows" => $rows, "sort" => $sort, "order" => $order, "offset" => $page_no, "chr" => $chr, 'sEcho' => $sEcho,'filtering_type' => $filtering_type,'user_type' => $user_type,'date' => $date);

if (isset($_REQUEST['ajaxvalidate']) && $_REQUEST['ajaxvalidate']) {
    $pageName = filtering($_REQUEST['pageName'], 'input');
    $id = filtering($_REQUEST['id'], 'input', 'int');
    if ($pageName != '' && $id != '') {
        if (getTotalRows("tbl_project_category", "pageName='" . $pageName . "' AND id != '" . $id . "' ", 'id') >= 1) {
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
} else if ($action == "updateStatus" && in_array('status', $Permission)) {
    $setVal = array('isActive' => ($value == 'a' ? 'y' : 'n'));

    $db->update($table, $setVal, array("id" => $id));

    echo json_encode(array('type' => 'success', 'Project category has been ' . ($value == 'a' ? 'activated ' : 'deactivated ') . 'successfully'));

    $activity_array = array("id" => $id, "module" => $module, "activity" => 'status', "action" => $value);

    add_admin_activity($activity_array);

    exit;
} else if ($action == "delete" && in_array('delete', $Permission)) {
    $aWhere = array("id" => $id);
    $affected_rows = $db->delete($table, $aWhere)->affectedRows();

    if ($affected_rows && $affected_rows > 0) {
        $activity_array = array("id" => $id, "module" => $module, "activity" => 'delete');
        add_admin_activity($activity_array);
        
        echo json_encode(array('type' => 'success', 'message' => "Project Category has been deleted successfully"));
        exit;
    } else {
        echo json_encode(array('type' => 'error', 'message' => "There seems to be an issue deleting project category"));
        exit;
    }
} else if ($action == "view") {
    if(in_array('view', $Permission)) {
        $activity_array = array("id" => $id, "module" => $module, "activity" => 'view');
        add_admin_activity($activity_array);
    } else {
        die("You don't have sufficient permission to view record");
    }
}else if ($action == "decline_payment") {
    $request = $db->pdoQuery('SELECT * FROM `tbl_redeem_request` WHERE id = ?',[$_GET['wid']])->result();

    if(!empty($request)){
        $aWhere = array("id" => $_GET['wid']);
        $affected_rows = $db->delete('tbl_redeem_request', $aWhere)->affectedRows();
        if ($affected_rows && $affected_rows > 0) {
            $activity_array = array("id" => $id, "module" => $module, "activity" => 'delete');
            add_admin_activity($activity_array);
            $user_details = getUser($request['userId']);
            $msg = "Your redeem request has been declined by ".SITE_NM." - Admin.";
            $db->insert("tbl_notification",array("userId"=>$user_details['id'],"message"=>$msg,"isRead"=>'n',"notificationType"=>$user_details['userType'],"createdDate"=>date('Y-m-d H:i:s')));

            $users = $db->pdoQuery('SELECT * FROM `tbl_users` WHERE email = ?',[$user_details['email']])->results();

            foreach ($users as $key => $value) {
                $uarr = ['walletAmount' => $user_details['walletAmount'] + $request['amount']];
                $db->update('tbl_users', $uarr, array("id" => $value['id']));
            }

            echo json_encode(array('type' => 'success', 'message' => "Request declined successfully"));
            exit;
        } else {
            echo json_encode(array('type' => 'error', 'message' => "There seems to be an issue Request declining"));
            exit;
        }
    }else{
        echo json_encode(array('type' => 'error', 'message' => "no record found"));
        exit;  
    }

}
else if ($action == "export_excel") {
    $content_array=array();
    $heading_array[] = array("Sr No.",'User Name','Amount','Payment Status','Date');
    /*$qrySel = $db->pdoQuery("SELECT w.*,w.id As wid,w.createdDate As walletDate,u.*,u.id As uId FROM tbl_wallet As w JOIN tbl_users As u ON w.userId = u.id WHERE w.status='reqRedeem' ")->results();*/

    $qrySel = $db->pdoQuery("Select r.amount,r.createdDate as walletDate,u.firstName,u.lastName,u.userType,r.paymentStatus from tbl_redeem_request As r 
        LEFT JOIN tbl_users As u ON u.id = r.userId")->results();
    $i=1;
    if(!empty($qrySel)) {
        foreach($qrySel as $qryRes){
            $constantArr = array(
                $i,
                filtering(ucfirst($qryRes["firstName"]))." ".filtering(ucfirst($qryRes["lastName"])),
                CURRENCY_SYMBOL.$qryRes['amount'],
                ($qryRes['paymentStatus']='p')?'Pending':'Completed',
                date(DATE_FORMAT_ADMIN,strtotime($qryRes['walletDate'])));
            $final_result[] = $constantArr;
            $i++;
        }
        $activity_array = array("id" => $id, "module" => $module, "activity" => 'excel');
        add_admin_activity($activity_array);
        export_to_excel($final_result, $module,$heading_array);
    }
    else{
        $_SESSION["toastr_message"] = disMessage(array('type' => 'err', 'var' => 'No data found for export to excel!'));
        redirectPage(SITE_ADM_MOD.$module."/");
    }
} else if ($action == "export_csv") {
    $user_data = array();
    $usertype_array=array("Sr No.",'User Name','Amount','Payment Status','Date');

    $getUserData = $db->pdoQuery("Select r.amount,r.createdDate as walletDate,u.firstName,u.lastName,u.userType,r.paymentStatus from tbl_redeem_request As r 
        LEFT JOIN tbl_users As u ON u.id = r.userId")->results();
    /* $getUserData = $db->pdoQuery("SELECT w.*,w.id As wid,w.createdDate As walletDate,u.*,u.id As uId FROM tbl_wallet As w JOIN tbl_users As u ON w.userId = u.id WHERE w.status='reqRedeem' ")->results();*/
    $i=1;
    if(!empty($getUserData)){
       foreach($getUserData AS $keys => $values) {
        $user_data[$keys][] = $i;
        $user_data[$keys][] = filtering(ucfirst($values["firstName"]))." ".filtering(ucfirst($values["lastName"]));
        $user_data[$keys][] = CURRENCY_SYMBOL.$values['amount'];
        $user_data[$keys][]=($values['paymentStatus']='p')?'Pending':'Completed';
        $user_data[$keys][] = date(DATE_FORMAT_ADMIN,strtotime($values['walletDate']));
        $i++;
    }
    $final_result = array($usertype_array);
    foreach($user_data as $k=>$v){
        $final_result = array_merge($final_result,array($v));
    }
    $activity_array = array("id" => $id, "module" => $module, "activity" => 'csv');
    add_admin_activity($activity_array);
    convert_to_csv($final_result,"reeedemRequestInfoCSV.csv",",");
    exit;
}
else{
    $_SESSION["toastr_message"] = disMessage(array('type' => 'err', 'var' => 'No data found for export to csv!'));
    redirectPage(SITE_ADM_MOD.$module."/");
}    
}
$mainObject = new RedeemRequest($module, $id, NULL, $searchArray, $action);
extract($mainObject->data);
echo ($content);
exit;
