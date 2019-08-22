<?php
$content = '';
require_once("../../../requires-sd/config-sd.php");
if ($adminUserId == 0) {
    die('Invalid request');
}
include(DIR_ADMIN_CLASS."manage_service_request-sd.lib.php");

$module = 'manage_service_request-sd';
chkPermission($module);
$Permission = chkModulePermission($module);
$table = 'tbl_services';
$action = isset($_GET["action"]) ? trim($_GET["action"]) : (isset($_POST["action"]) ? trim($_POST["action"]) : 'datagrid');
$id = isset($_GET["id"]) ? trim($_GET["id"]) : (isset($_POST["id"]) ? trim($_POST["id"]) : 0);
$value = isset($_POST["value"]) ? trim($_POST["value"]) : isset($_GET["value"]) ? trim($_GET["value"]) : '';
$page_no = isset($_POST['iDisplayStart']) ? intval($_POST['iDisplayStart']) : 0;
$rows = isset($_POST['iDisplayLength']) ? intval($_POST['iDisplayLength']) : 25;
$sort = isset($_POST["iSortTitle_0"]) ? $_POST["iSortTitle_0"] : NULL;
$order = isset($_POST["sSortDir_0"]) ? $_POST["sSortDir_0"] : NULL;
$chr = isset($_POST["sSearch"]) ? $_POST["sSearch"] : NULL;
$sEcho = isset($_POST['sEcho']) ? $_POST['sEcho'] : 1;
$category = isset($_POST['category']) ? $_POST['category'] : '';
$subcategory = isset($_POST['subcategory']) ? $_POST['subcategory'] : '';
$skills = isset($_POST['skills']) ? $_POST['skills'] : '';
$filtering_type = isset($_POST['filtering_type']) ? $_POST['filtering_type'] : '';

extract($_GET);
$searchArray = array("page" => $page_no, "rows" => $rows, "sort" => $sort, "order" => $order, "offset" => $page_no, "chr" => $chr, 'sEcho' => $sEcho,"category"=>$category,"subcategory"=>$subcategory,"skills"=>$skills,"filtering_type"=>$filtering_type);

$mainObject = new servicesRequest($module, $id, NULL, $searchArray, $action);
if($action == "updateStatus" && in_array('status', $Permission)) 
{
    $setVal = array('isApproved' => 'a',"isActive"=>'y');
    $db->update($table, $setVal, array("id" => $id));
    
    echo json_encode(array('type' => 'success', 'message' => 'Services has been Approved successfully'));

    $serviceDetail = $db->pdoQuery("Select * from tbl_services where id=?",array($id))->result();
    if($serviceDetail['featured'] == 'y')
    {
        $msg = "Your service request has been accepted by ".SITE_NM." - Admin. Please pay to get your service active as featured.";
        $extra = "Please pay to get your service active as featured.";
    }
    else
    {
        $msg = "Your service request has been accepted by ".SITE_NM." - Admin.";
        $extra = "";
    }
    $link = SITE_URL.'service/'.$serviceDetail['servicesSlug'];
    $db->insert("tbl_notification",array("userId"=>$serviceDetail['freelanserId'],"message"=>$msg,"detail_link"=>$link,"isRead"=>'n',"notificationType"=>'f',"createdDate"=>date('Y-m-d H:i:s')));

    /*email notification*/
    
    $receiverDetail = getUser($serviceDetail['freelanserId']);
    $receiverDetail['NotifyServiceAcceptReject'];
    $service_link = "<a href='".$link."'>".ucfirst($serviceDetail['serviceTitle'])."</a>";
    if(notifyCheck('NotifyServiceAcceptReject',$serviceDetail['freelanserId'])==1)
    {
          $receiverNm = ucfirst(filtering($receiverDetail['firstName']))." ".ucfirst(filtering($receiverDetail['lastName']));
          $arrayCont = array('USERNM'=>$receiverNm,'SERVICE'=>$service_link,"EXTRA"=>$extra);
          $array = generateEmailTemplate('Service_request_approve',$arrayCont);
          sendEmailAddress($receiverDetail['email'],$array['subject'],$array['message']);
    }
    $activity_array = array("id" => $id, "module" => $module, "activity" => 'approv', "action" => $value);
    add_admin_activity($activity_array);
    
    exit;
}
else if($action == "getSubCategory") 
{
    $array = array();
    $array['subcat'] = $mainObject->get_subcategory($_REQUEST['cat_id']);
    echo json_encode($array);
    exit;
}
else if ($action == "delete" && in_array('delete', $Permission)) 
{
    $db->delete($table, array("id" => $id));
    echo json_encode(array('type' => 'success', 'message' => 'Services Request has been Deleted successfully'));

    $activity_array = array("id" => $id, "module" => $module, "activity" => 'status', "action" => $value);
    add_admin_activity($activity_array);
    exit;
} 
else if ($action == "reject" && in_array('status', $Permission)) 
{
    $setVal = array('isApproved' => 'r');
    $db->update($table, $setVal, array("id" => $id));
    echo json_encode(array('type' => 'success', 'message' => 'Services has been Rejected successfully'));

    $serviceDetail = $db->pdoQuery("Select * from tbl_services where id=?",array($id))->result();
    $msg = "Your service request has been rejected by ".SITE_NM." - Admin.";
    $link = SITE_URL.'service/'.$serviceDetail['servicesSlug'];
    $db->insert("tbl_notification",array("userId"=>$serviceDetail['freelanserId'],"message"=>$msg,"detail_link"=>$link,"isRead"=>'n',"notificationType"=>'f',"createdDate"=>date('Y-m-d H:i:s')));

    /*email notification*/
    $receiverDetail = getUser($serviceDetail['freelanserId']);
    $service_link = "<a href='".$link."'>".ucfirst($serviceDetail['serviceTitle'])."</a>";
    if(notifyCheck('NotifyServiceAcceptReject',$serviceDetail['freelanserId'])==1)
    {
          $receiverNm = ucfirst(filtering($receiverDetail['firstName']))." ".ucfirst(filtering($receiverDetail['lastName']));
          $arrayCont = array('USERNM'=>$receiverNm,'SERVICE'=>$service_link);
          $array = generateEmailTemplate('Service_request_reject',$arrayCont);
          sendEmailAddress($receiverDetail['email'],$array['subject'],$array['message']);
    }
    $activity_array = array("id" => $id, "module" => $module, "activity" => 'status', "action" => $value);
    add_admin_activity($activity_array);
    exit;
} 
else if($action == "undo")
{
    $aWhere = array("id" => $id);
    $affected_rows = $db->update($table,array("isDelete"=>'n',"isActive"=>'y'),$aWhere)->affectedRows();
    $activity_array = array("id" => $id, "module" => $module, "activity" => 'delete');
    add_admin_activity($activity_array);
    if ($affected_rows && $affected_rows > 0) {
        echo json_encode(array('type' => 'success', 'message' => "Services has been activated sucessfully"));
        exit;
    } 
    else{
        echo json_encode(array('type' => 'error', 'message' => "There seems to be an issue deleting user"));
        exit;
    }
}
else if($action == "load_sub")
{
    $main_cat = $_REQUEST['cat_id'];
    $sub_cat_list = $db->pdoQuery("select * from tbl_subcategory where maincat_id='".$main_cat."' and isActive='y' and isDelete='n' ")->results();
    $list = '<option>Select Sub Category</option>';
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


extract($mainObject->data);
echo ($content);
exit;