<?php

$content = '';
require_once("../../../requires-sd/config-sd.php");
if ($adminUserId == 0) {
    die('Invalid request');
}
include(DIR_ADMIN_CLASS."constant-sd.lib.php");
//echo "<pre>";print_r($_REQUEST);exit;
$module = 'constant-sd';
chkPermission($module);
$Permission = chkModulePermission($module);
$table = 'tbl_language_constant';
$action = isset($_GET["action"]) ? trim($_GET["action"]) : (isset($_POST["action"]) ? trim($_POST["action"]) : 'datagrid');
$id = isset($_GET["id"]) ? trim($_GET["id"]) : (isset($_POST["id"]) ? trim($_POST["id"]) : 0);
$value = isset($_POST["value"]) ? trim($_POST["value"]) : isset($_GET["value"]) ? trim($_GET["value"]) : '';
$page_no = isset($_POST['iDisplayStart']) ? intval($_POST['iDisplayStart']) : 0;
$rows = isset($_POST['iDisplayLength']) ? intval($_POST['iDisplayLength']) : 25;
$sort = isset($_POST["iSortTitle_0"]) ? $_POST["iSortTitle_0"] : NULL;
$order = isset($_POST["sSortDir_0"]) ? $_POST["sSortDir_0"] : NULL;
$chr = isset($_POST["sSearch"]) ? $_POST["sSearch"] : NULL;
$sEcho = isset($_POST['sEcho']) ? $_POST['sEcho'] : 1;

extract($_GET);
$searchArray = array("page" => $page_no, "rows" => $rows, "sort" => $sort, "order" => $order, "offset" => $page_no, "chr" => $chr, 'sEcho' => $sEcho);

if (isset($_REQUEST['ajaxvalidate']) && $_REQUEST['ajaxvalidate']) {
    $categoryName = strtolower(filtering($_REQUEST['categoryName'], 'input'));
    $id = filtering($_REQUEST['id'], 'input', 'int');
    if ($categoryName != '' && $id != '') {
        if (getTotalRows("tbl_language_constant", "LOWER(categoryName)='".$categoryName."' AND  id != '" . $id . "' ", 'id') >= 1) {
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

    echo json_encode(array('type' => 'success', 'Constant ' . ($value == 'a' ? 'activated ' : 'deactivated ') . 'successfully'));

    $activity_array = array("id" => $id, "module" => $module, "activity" => 'status', "action" => $value);

    add_admin_activity($activity_array);

    exit;
} else if ($action == "delete" && in_array('delete', $Permission)) {
    $aWhere = array("id" => $id);
    $affected_rows = $db->delete($table, $aWhere)->affectedRows();

    $Where = array("faqCategoryId" => $id);
    $delete_faq = $db->delete('tbl_faq',$Where);

    if ($affected_rows && $affected_rows > 0) {


        $activity_array = array("id" => $id, "module" => $module, "activity" => 'delete');
        add_admin_activity($activity_array);
        
        echo json_encode(array('type' => 'success', 'message' => "Constant deleted successfully"));
        exit;
    } else {
        echo json_encode(array('type' => 'error', 'message' => "There seems to be an issue deleting Constant"));
        exit;
    }
} else if ($action == "view") {
    if(in_array('view', $Permission)) {
        $activity_array = array("id" => $id, "module" => $module, "activity" => 'view');
        add_admin_activity($activity_array);
    } else {
        die("You don't have sufficient permission to view record");
    }
}else if ($action == "export_excel") {
    $lang = $db->pdoQuery("SELECT * from tbl_language")->results();
    $heading_array[0] = array("constant","value");
    foreach ($lang as $key => $value) {
        array_push($heading_array[0],"value_".$value['id']);    
    }
    $final_result[] = $heading_array[0];
    $qrySel = $db->pdoQuery("SELECT * from tbl_language_constant")->results();
    $i=1;
    if(!empty($qrySel)) {
        foreach($qrySel as $qryRes){
            $constantArr = [];
            array_push($constantArr, $qryRes['constant']);
            array_push($constantArr, $qryRes['value']);
            foreach ($lang as $key => $value) {
                array_push($constantArr,$qryRes["value_".$value['id']]);    
            }
            $final_result[] = $constantArr;
            $i++;
        }
        $activity_array = array("id" => $id, "module" => $module, "activity" => 'excel');
        add_admin_activity($activity_array);
        //export_to_excel($final_result, $module,$heading_array);
        convert_to_excel($final_result,$module.'.xls');
    }else{
        $_SESSION["toastr_message"] = disMessage(array('type' => 'err', 'var' => 'No data found for export to excel!'));
        redirectPage(SITE_ADM_MOD.$module."/");
    }
}else if ($action == "export_csv") {
   
    $user_data = array();
    $lang = $db->pdoQuery("SELECT * from tbl_language")->results();
    $heading_array = array("Sr No.","Constant","Value");
    foreach ($lang as $key => $value) {
        array_push($heading_array,"value_".$value['id']);    
    }
    array_push($heading_array,"Registered On");

    $qrySel = $db->pdoQuery("SELECT * from tbl_language_constant")->results();
    $i=1;
    if(!empty($qrySel)) {
        foreach($qrySel AS $keys => $values) {
            $type = $values['userType']== 'C' ? 'Customer' : 'Provider';
            $user_data[$keys][] = $i;
            $user_data[$keys][] = $values['constant'];
            $user_data[$keys][] = $values['value'];
            foreach ($lang as $key => $value) {
                $user_data[$keys][] = $values["value_".$value['id']];
            }
            $user_data[$keys][] = date("d-m-Y",strtotime($values['createdDate']));
            $i++;
        }
        $final_result = array($heading_array);
        foreach($user_data as $k=>$v){
            $final_result = array_merge($final_result,array($v));
        }
        $activity_array = array("id" => $id, "module" => $module, "activity" => 'csv');
        add_admin_activity($activity_array);
        convert_to_csv($final_result,"userInfoCSV.csv",",");
        exit;
    }
    else{
        $_SESSION["toastr_message"] = disMessage(array('type' => 'err', 'var' => 'No data found for export to csv!'));
        redirectPage(SITE_ADM_MOD.$module."/");
    }
}

$mainObject = new Constant($module, $id, NULL, $searchArray, $action);
extract($mainObject->data);
echo ($content);
exit;
