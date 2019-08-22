<?php
$content = '';
require_once("../../../requires-sd/config-sd.php");
if ($adminUserId == 0) {
    die('Invalid request');
}
include(DIR_ADMIN_CLASS."users-sd.lib.php");
//echo "<pre>";print_r($_REQUEST);exit;
$module = 'users-sd';
chkPermission($module);
$Permission = chkModulePermission($module);
$table = 'tbl_users';
$action = isset($_GET["action"]) ? trim($_GET["action"]) : (isset($_POST["action"]) ? trim($_POST["action"]) : 'datagrid');
$type = isset($_GET["type"]) ? $_GET['type'] : '';
$id = isset($_GET["id"]) && !is_array($_GET["id"]) ? trim($_GET["id"]) : (isset($_POST["id"]) && !is_array($_POST["id"]) ? trim($_POST["id"]) : 0);
$value = isset($_POST["value"]) ? trim($_POST["value"]) : isset($_GET["value"]) ? trim($_GET["value"]) : '';
$page_no = isset($_POST['iDisplayStart']) ? intval($_POST['iDisplayStart']) : 0;
$rows = isset($_POST['iDisplayLength']) ? intval($_POST['iDisplayLength']) : 25;
$sort = isset($_POST["iSortTitle_0"]) ? $_POST["iSortTitle_0"] : NULL;
$order = isset($_POST["sSortDir_0"]) ? $_POST["sSortDir_0"] : NULL;
$chr = isset($_POST["sSearch"]) ? $_POST["sSearch"] : NULL;
$sEcho = isset($_POST['sEcho']) ? $_POST['sEcho'] : 1;
$filterLocation = isset($_POST['filterLocation']) ? $_POST['filterLocation'] : '';
$filterCompany = isset($_POST['filterCompany']) ? $_POST['filterCompany'] : '';
extract($_GET);
$searchArray = array("page" => $page_no, "rows" => $rows, "sort" => $sort, "order" => $order, "offset" => $page_no, "chr" => $chr, 'sEcho' => $sEcho,'filterLocation' => $filterLocation,'filterCompany' =>$filterCompany);
if (isset($_REQUEST['ajaxvalidate']) && $_REQUEST['ajaxvalidate']) {
    $pageName = filtering($_REQUEST['comments'], 'input');
    $pId = filtering($_REQUEST['id'], 'input', 'int');
    if ($pageName != '' && $pId != '') {
        if (getTotalRows("tbl_users", "userName='" . $pageName . "' AND id != '" . $pId . "' ", 'id') >= 1) {
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
} else if($type == 'F'){
    $users = $db->select('tbl_users',array('*'),array('id'=>$id))->result();
    $slug = $users['userSlug']; 
    redirectPage(SITE_URL.'f/profile/'.$slug);
} else if ($action == "updateStatus" && in_array('status', $Permission)) {
    $setVal = array('isActive' => ($value == 'a' ? 'y' : 'd'));
    $user = $db->select("tbl_users","*",array("id"=>$id))->result();
    $affected_rows = $db->update($table, $setVal, array("email" => $user["email"]))->affectedRows();
    
    if($value != 'a'){
        if($affected_rows && $affected_rows > 0){
            /*$arrayCont = array("greetings"=>$user['userName']);
            $to = $user['email'];
            $array = generateEmailTemplate('user_deactive',$arrayCont);
            sendEmailAddress($to,$array['subject'],$array['message']);*/
            $activity_array = array("id" => $id, "module" => $module, "activity" => 'isActive', "action" => $value);
            add_admin_activity($activity_array);
            echo json_encode(array('type' => 'success', 'User ' . ($value == 'a' ? 'activated ' : 'deactivated ') . 'successfully'));
            exit;
        } else {
            echo json_encode(array('type' => 'error', 'message' => "There seems to be an issue deactivating User"));
            exit;
        }    
    }
    exit;
} else if ($action == "delete" && in_array('delete', $Permission)) {
    
    $isDeleted = array("isDeleted"=>'y');
    $aWhere = array("id" => $id);
    $user = $db->select("tbl_users","*",array("id"=>$id))->result();
    $affected_rows = $db->update($table, $isDeleted, $aWhere)->affectedRows();

    if ($affected_rows && $affected_rows > 0) {
        $activity_array = array("id" => $id, "module" => $module, "activity" => 'delete');
        add_admin_activity($activity_array);
        
        $arrayCont = array("USERNAME"=>$user['userName']);
        $to = $user['email'];
        $array = generateEmailTemplate('delete_user_alert',$arrayCont);
        sendEmailAddress($to,$array['subject'],$array['message']);
        echo json_encode(array('type' => 'success', 'message' => "User deleted successfully"));
        exit;
    } else {
        echo json_encode(array('type' => 'error', 'message' => "There seems to be an issue deleting User"));
        exit;
    }
} else if ($action == "perDelete" && in_array('delete', $Permission)){
    $aWhere = array("id" => $id);
    $affected_rows = $db->delete($table,$aWhere)->affectedRows();
    if($affected_rows && $affected_rows > 0) 
    {
        $activity_array = array("id" => $id, "module" => $module, "activity" => 'delete');
        add_admin_activity($activity_array);
        echo json_encode(array('type' => 'success', 'message' => "User has been deleted successfully"));
        exit;
    }
    else 
    {
        echo json_encode(array('type' => 'error', 'message' => "There seems to be an issue deleting User"));
        exit;
    }
}else if ($action == "deleteAll" && in_array('delete', $Permission)){
    extract($_POST);
    foreach ($id as $key => $value) {
        $aWhere = array("id" => $value);
        $affected_rows = $db->delete($table,$aWhere)->affectedRows();
    }
    if($affected_rows && $affected_rows > 0) 
    {
        $activity_array = array("id" => $id, "module" => $module, "activity" => 'delete');
        add_admin_activity($activity_array);
        echo json_encode(array('type' => 'success', 'message' => "User has been deleted successfully"));
        exit;
    }
    else 
    {
        echo json_encode(array('type' => 'error', 'message' => "There seems to be an issue deleting User"));
        exit;
    }
}
else if ($action == "undo" && in_array('undo', $Permission)) {
    $isDeleted = array("isDeleted"=>'n');
    $aWhere = array("id" => $id);
    $user = $db->select("tbl_users","*",array("id"=>$id))->result();
    $affected_rows = $db->update($table,    $isDeleted, $aWhere)->affectedRows();

    if ($affected_rows && $affected_rows > 0) {
        $activity_array = array("id" => $id, "module" => $module, "activity" => 'undo');
        add_admin_activity($activity_array);
        
        $arrayCont = array("USERNAME"=>$user['userName']);
        $to = $user['email'];
        $array = generateEmailTemplate('undo_user_alert',$arrayCont);
        // printr($array,1);
        sendEmailAddress($to,$array['subject'],$array['message']);

        echo json_encode(array('type' => 'success', 'message' => "User activated successfully"));
        exit;
    } else {
        echo json_encode(array('type' => 'error', 'message' => "There seems to be an issue undo User"));
        exit;
    }
}
/*else if ($action == "view") {
    if(in_array('view', $Permission)) {
        $activity_array = array("id" => $id, "module" => $module, "activity" => 'view');
        add_admin_activity($activity_array);
    } else {
        die("You don't have sufficient permission to view record");
    }
}*/
else if ($action == "view") {
    if(in_array('view', $Permission)) {
        $activity_array = array("id" => $id, "module" => $module, "activity" => 'view');
        add_admin_activity($activity_array);
    } else {
        die("You don't have sufficient permission to view record");
    }
}
else if ($action == "view_counter") {
    if(in_array('view', $Permission)) {
        $activity_array = array("id" => $id, "module" => $module, "activity" => 'view_counter');
        add_admin_activity($activity_array);
    } else {
        die("You don't have sufficient permission to view record");
    }
}
else if ($action == "edit" && in_array('edit', $Permission)) {
    $activity_array = array("id" => $id, "module" => $module, "activity" => 'edit');
    add_admin_activity($activity_array);
}else if ($action == "export_excel") {
    // Function for Exporting Data to Excel
    $heading_array[] = array("Sr No.","User Type","First Name","Last Name","Location","Registered On","Status");
    
        $qrySel = $db->pdoQuery("SELECT * from tbl_users")->results();
        $i=1;

        if(!empty($qrySel)) {
            foreach($qrySel as $qryRes){
                $type = $qryRes['userType']== 'C' ? 'Customer' : 'Provider';
                $constantArr = array(
                            $i,
                            $type,
                            $qryRes['firstName'],
                            $qryRes['lastName'],
                            $qryRes['location'],
                            date("d-m-Y",strtotime($qryRes['createdDate'])),
                            ($qryRes['status']=='a'?'Active':'Deactive'));
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
}else if ($action == "export_csv") {
    $user_data = array();
    $getUserData = $db->pdoQuery("SELECT * FROM tbl_users")->results();
    $usertype_array = array("Sr No.","User Type","First Name","Last Name","Location","Registered On","Status");
    $i=1;
    if(!empty($getUserData)) {
        foreach($getUserData AS $keys => $values) {
            $type = $values['userType']== 'C' ? 'Customer' : 'Provider';
            $user_data[$keys][] = $i;
            $user_data[$keys][] = $type;
            $user_data[$keys][] = $values['firstName'];
            $user_data[$keys][] = $values['lastName'];
            $user_data[$keys][] = $values['location'];
            $user_data[$keys][] = date("d-m-Y",strtotime($values['createdDate']));
            $user_data[$keys][] = $values['status']=='a'?'Active':'Deactive';
            $i++;
        }
        $final_result = array($usertype_array);
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

$mainObject = new Users($module, $id, NULL, $searchArray, $action);
extract($mainObject->data);
echo ($content);
exit;
