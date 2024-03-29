<?php

$content = '';
require_once("../../../requires-sd/config-sd.php");
if ($adminUserId == 0) {
    die('Invalid request');
}
include(DIR_ADMIN_CLASS."manage_payments-sd.lib.php");
//echo "<pre>";print_r($_REQUEST);exit;
$module = 'manage_payments-sd';
chkPermission($module);
$Permission = chkModulePermission($module);
$table = 'tbl_wallet';
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

extract($_GET);
$searchArray = array("page" => $page_no, "rows" => $rows, "sort" => $sort, "order" => $order, "offset" => $page_no, "chr" => $chr, 'sEcho' => $sEcho,'filtering_type' => $filtering_type);

if ($action == "view") {
    if(in_array('view', $Permission)) {
        $activity_array = array("id" => $id, "module" => $module, "activity" => 'view');
        add_admin_activity($activity_array);
    } else {
        die("You don't have sufficient permission to view record");
    }
}
else if ($action == "export_excel") {
    $content_array=array();
    $heading_array[] = array("Sr No.",'User Name','Amount','Earn From','Payment Status','Date');
    $qrySel = $db->pdoQuery("SELECT w.id,u.userName,w.amount,w.earnFrom,w.paymentStatus,w.createdDate As walletDate FROM tbl_wallet As w LEFT JOIN tbl_users As u ON w.userId = u.id ORDER BY w.id DESC")->results();
    $i=1;
    if(!empty($qrySel)) {
        foreach($qrySel as $qryRes){
            $constantArr = array(
                        $i,
                        ($qryRes['userName'] == '')?'admin':ucfirst($qryRes['userName']),
                        CURRENCY_SYMBOL.$qryRes['amount'],
                        $qryRes['earnFrom'],
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
}

else if ($action == "export_csv") {
    $user_data = array();
    $usertype_array=array("Sr No.",'User Name','Amount','Earn From','Payment Status','Date');
    $getUserData = $db->pdoQuery("SELECT w.id,u.userName,w.amount,w.earnFrom,w.paymentStatus,w.createdDate As walletDate FROM tbl_wallet As w LEFT JOIN tbl_users As u ON w.userId = u.id ORDER BY w.id DESC")->results();
    $i=1;
   if(!empty($getUserData)){
       foreach($getUserData AS $keys => $values) {
            $user_data[$keys][] = $i;
            $user_data[$keys][] = ($values['userName'] == '')?'admin':ucfirst($values['userName']);
            $user_data[$keys][] = CURRENCY_SYMBOL.$values['amount'];
            $user_data[$keys][] = $values['earnFrom'];
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
        convert_to_csv($final_result,"paymentInfoCSV.csv",",");
        exit;
    }
    else{
        $_SESSION["toastr_message"] = disMessage(array('type' => 'err', 'var' => 'No data found for export to csv!'));
       redirectPage(SITE_ADM_MOD.$module."/");
    } 
}

$mainObject = new Payments($module, $id, NULL, $searchArray, $action);
extract($mainObject->data);
echo ($content);
exit;
