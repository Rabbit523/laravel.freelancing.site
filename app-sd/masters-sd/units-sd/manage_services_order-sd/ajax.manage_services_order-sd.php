<?php
$content = '';
require_once("../../../requires-sd/config-sd.php");
if ($adminUserId == 0) {
    die('Invalid request');
}
include(DIR_ADMIN_CLASS."manage_services_order-sd.lib.php");

$module = 'manage_services_order-sd';
chkPermission($module);
$Permission = chkModulePermission($module);
$table = 'tbl_services_order';
$action = isset($_GET["action"]) ? trim($_GET["action"]) : (isset($_POST["action"]) ? trim($_POST["action"]) : 'datagrid');
$id = isset($_GET["id"]) ? trim($_GET["id"]) : (isset($_POST["id"]) ? trim($_POST["id"]) : 0);
$value = isset($_POST["value"]) ? trim($_POST["value"]) : isset($_GET["value"]) ? trim($_GET["value"]) : '';
$page_no = isset($_POST['iDisplayStart']) ? intval($_POST['iDisplayStart']) : 0;
$rows = isset($_POST['iDisplayLength']) ? intval($_POST['iDisplayLength']) : 25;
$sort = isset($_POST["iSortTitle_0"]) ? $_POST["iSortTitle_0"] : NULL;
$order = isset($_POST["sSortDir_0"]) ? $_POST["sSortDir_0"] : NULL;
$chr = isset($_POST["sSearch"]) ? $_POST["sSearch"] : NULL;
$sEcho = isset($_POST['sEcho']) ? $_POST['sEcho'] : 1;
$orderStatus = isset($_POST['orderStatus']) ? $_POST['orderStatus'] : '';

extract($_GET);
$searchArray = array("page" => $page_no, "rows" => $rows, "sort" => $sort, "order" => $order, "offset" => $page_no, "chr" => $chr, 'sEcho' => $sEcho,"orderStatus"=>$orderStatus);


if($action == "updateStatus" && in_array('status', $Permission)) 
{
    $setVal = array('isActive' => ($value == 'a' ? 'y' : 'n'));
    

    $db->update($table, $setVal, array("id" => $id));
    echo json_encode(array('type' => 'success', 'Job has been ' . ($value == 'a' ? 'activated ' : 'deactivated ') . 'successfully'));

    $activity_array = array("id" => $id, "module" => $module, "activity" => 'status', "action" => $value);

    add_admin_activity($activity_array);

    exit;
}
else if ($action == "delete" && in_array('delete', $Permission)) 
{
    $aWhere = array("id" => $id);
    /*$affected_rows = $db->update($table,array("isDelete"=>'y',"isActive"=>'n'),$aWhere)->affectedRows();
*/
    $affected_rows = $db->delete($table,$aWhere);
    if($affected_rows && $affected_rows > 0) 
    {
        $activity_array = array("id" => $id, "module" => $module, "activity" => 'delete');
        add_admin_activity($activity_array);
        
        echo json_encode(array('type' => 'success', 'message' => "Job has been deleted successfully"));
        exit;
    }
    else 
    {
        echo json_encode(array('type' => 'error', 'message' => "There seems to be an issue deleting Job"));
        exit;
    }
} 
else if($action == "undo")
{
    $aWhere = array("id" => $id);
    $affected_rows = $db->update($table,array("isDelete"=>'n',"isActive"=>'y'),$aWhere)->affectedRows();
    $activity_array = array("id" => $id, "module" => $module, "activity" => 'delete');
    add_admin_activity($activity_array);
    if ($affected_rows && $affected_rows > 0) {
        echo json_encode(array('type' => 'success', 'message' => "Job has been activated sucessfully"));
        exit;
    } 
    else{
        echo json_encode(array('type' => 'error', 'message' => "There seems to be an issue deleting user"));
        exit;
    }
}
else if ($action == "export_excel") {
    $content_array=array();
    $heading_array[] = array("Sr No.",'Services Title','Category','Freelancer Name','Customer Name','Total Payment','Order Date','Order Status');
   
    $qrySel = $db->pdoQuery("SELECT s.serviceTitle,jc.category_name,CONCAT(f.firstName,' ',f.lastName) As freelanserName,CONCAT(c.firstName,' ',c.lastName) As customerName,so.totalPayment,so.orderDate,so.serviceStatus FROM tbl_services_order As so
            LEFT JOIN tbl_services As s ON s.id = so.servicesId
            LEFT JOIN tbl_category As jc ON jc.id = s.servicesCategory
            LEFT JOIN tbl_users As f ON f.id = so.freelanserId
            LEFT JOIN tbl_users As c ON c.id = so.customerId
            ")->results();

    $i=1;
    if(!empty($qrySel)) {
        foreach($qrySel as $qryRes){
            if($qryRes['serviceStatus']=='no')
            {
                $status = "New Order";
            }
            else if($qryRes['serviceStatus']=='ip')
            {
                $status = "In Progress";
            }
            else if($qryRes['serviceStatus']=='ar')
            {
                $status = "Ask for Refund";
            }
            else if($qryRes['serviceStatus']=='c')
            {
                $status = "Closed";
            }
            else if($qryRes['serviceStatus']=='p')
            {
                $status = "Payment Pending";
            }
            else if($qryRes['serviceStatus']=='ud')
            {
                $status = "Under Dispute";
            }
            else if($qryRes['serviceStatus']=='ds')
            {
                $status = "Dispute Solved";
            }
           
            $constantArr = array(
                $i,
                $qryRes['serviceTitle'],
                $qryRes['category_name'],
                ucfirst($qryRes['freelanserName']),
                ucfirst($qryRes['customerName']),
                CURRENCY_SYMBOL.$qryRes['totalPayment'],
                date('d-m-Y H:i:s',strtotime($qryRes['orderDate'])),
                $status
                );
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
    $usertype_array=array("Sr No.",'Services Title','Category','Freelancer Name','Customer Name','Total Payment','Order Date','Order Status');
    /*$getUserData = $db->pdoQuery("SELECT w.*,w.id As wid,w.createdDate As walletDate,u.*,u.id As uId FROM tbl_wallet As w JOIN tbl_users As u ON w.userId = u.id WHERE w.status='reqRedeem' ")->results();*/
    $getUserData = $db->pdoQuery("SELECT s.serviceTitle,jc.category_name,f.userName As freelanserName,c.userName As customerName,so.totalPayment,so.orderDate,so.serviceStatus FROM tbl_services_order As so
            LEFT JOIN tbl_services As s ON s.id = so.servicesId
            LEFT JOIN tbl_category As jc ON jc.id = s.servicesCategory
            LEFT JOIN tbl_users As f ON f.id = so.freelanserId
            LEFT JOIN tbl_users As c ON c.id = so.customerId
            ")->results();

   // printr($getUserData,1);
    $i=1;
    if(!empty($getUserData)){
       foreach($getUserData AS $keys => $values) {
            if($values['serviceStatus']=='no')
            {
                $status1 = "New Order";
            }
            else if($values['serviceStatus']=='ip')
            {
                $status1 = "In Progress";
            }
            else if($values['serviceStatus']=='ar')
            {
                $status1 = "Ask for Refund";
            }
            else if($values['serviceStatus']=='c')
            {
                $status1 = "Closed";
            }
            else if($values['serviceStatus']=='p')
            {
                $status1 = "Payment Pending";
            }
            else if($values['serviceStatus']=='ud')
            {
                $status1 = "Under Dispute";
            }
            else if($values['serviceStatus']=='ds')
            {
                $status1 = "Dispute Solved";
            }

            $user_data[$keys][] = $i;
            $user_data[$keys][] = ucfirst($values['serviceTitle']);
            $user_data[$keys][] = ucfirst($values['category_name']);
            $user_data[$keys][] = ucfirst($values['freelanserName']);
            $user_data[$keys][] = ucfirst($values['customerName']);
            $user_data[$keys][] = $values['totalPayment'];
            $user_data[$keys][] = date('d-m-Y H:i:s',strtotime($values['orderDate']));
            $user_data[$keys][] = $status1;
            $i++;
        }
       

        $final_result = array($usertype_array);
        foreach($user_data as $k=>$v){
            $final_result = array_merge($final_result,array($v));
        }
        $activity_array = array("id" => $id, "module" => $module, "activity" => 'csv');
        add_admin_activity($activity_array);
        convert_to_csv($final_result,"ManagePost.csv",",");
        exit;
    }
    else{
        $_SESSION["toastr_message"] = disMessage(array('type' => 'err', 'var' => 'No data found for export to csv!'));
       redirectPage(SITE_ADM_MOD.$module."/");
    }    
}
else if($action == "load_sub")
{
    $main_cat = $_REQUEST['cat_id'];
    $sub_cat_list = $db->pdoQuery("select * from tbl_subcategory where maincat_id='".$main_cat."' and isActive='y' and isDelete='n' ")->results();
    $list = '';
    $array = array();

    foreach ($sub_cat_list as $value) 
    {
        $list .= "<option value='".$value['id']."' >".$value['subcategory_name']."</option>";
    }
    echo $list;exit;
   
}
else if($action == "view" && in_array('view', $Permission)) 
{
    $activity_array = array("id" => $id, "module" => $module, "activity" => 'view');
    add_admin_activity($activity_array);
}
else if($action == "disputeView" && in_array('view', $Permission)) 
{
    $activity_array = array("id" => $id, "module" => $module, "activity" => 'view');
    add_admin_activity($activity_array);
}


$mainObject = new servicesOrder($module, $id, NULL, $searchArray, $action);
extract($mainObject->data);
echo ($content);
exit;