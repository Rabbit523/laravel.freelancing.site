<?php

$content = '';
require_once("../../../requires-sd/config-sd.php");
if ($adminUserId == 0) {
    die('Invalid request');
}
include(DIR_ADMIN_CLASS."listing-sd.lib.php");
//echo "<pre>";print_r($_REQUEST);exit;
$module = 'listing-sd';
chkPermission($module);
$Permission = chkModulePermission($module);
$table = 'tbl_listing';
$action = isset($_GET["action"]) ? trim($_GET["action"]) : (isset($_POST["action"]) ? trim($_POST["action"]) : 'datagrid');
$id = isset($_GET["id"]) ? trim($_GET["id"]) : (isset($_POST["id"]) ? trim($_POST["id"]) : 0);
$listingTypeId = isset($_GET["listingTypeId"]) ? trim($_GET["listingTypeId"]) : (isset($_POST["listingTypeId"]) ? trim($_POST["listingTypeId"]) : 0);

$value = isset($_POST["value"]) ? trim($_POST["value"]) : isset($_GET["value"]) ? trim($_GET["value"]) : '';
$page_no = isset($_POST['iDisplayStart']) ? intval($_POST['iDisplayStart']) : 0;
$rows = isset($_POST['iDisplayLength']) ? intval($_POST['iDisplayLength']) : 25;
$sort = isset($_POST["iSortTitle_0"]) ? $_POST["iSortTitle_0"] : NULL;
$order = isset($_POST["sSortDir_0"]) ? $_POST["sSortDir_0"] : NULL;
$chr = isset($_POST["sSearch"]) ? $_POST["sSearch"] : NULL;
$sEcho = isset($_POST['sEcho']) ? $_POST['sEcho'] : 1;
$filtering_type = isset($_POST['filtering_type']) ? $_POST['filtering_type'] : '';
$status_type = isset($_POST['status_type']) ? $_POST['status_type'] : '';
extract($_GET);
$searchArray = array("page" => $page_no, "rows" => $rows, "sort" => $sort, "order" => $order, "offset" => $page_no, "chr" => $chr, 'sEcho' => $sEcho,'filtering_type' => $filtering_type,'status_type' => $status_type);

if (isset($_REQUEST['ajaxvalidate']) && $_REQUEST['ajaxvalidate']) {
    $pageName = filtering($_REQUEST['pageName'], 'input');
    $pId = filtering($_REQUEST['id'], 'input', 'int');
    if ($pageName != '' && $pId != '') {
        if (getTotalRows("tbl_listing", "listingUrl='" . $pageName . "' AND listingId != '" . $pId . "' ", 'pId') >= 1) {
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
} else if(isset($_REQUEST['choice_id'])){
    $setVal = array('editor_choice' => ($value == 'a' ? 'y' : 'n'));

    $db->update($table, $setVal, array("listingId" => $choice_id));

    echo json_encode(array('type' => 'success', 'Listing ' . ($value == 'a' ? 'added as Expert Choice  ' : 'removed from Expert Choice') . 'successfully'));
    exit;
} else if ($action == "updateStatus" && in_array('status', $Permission)) {
    $setVal = array('isActive' => ($value == 'a' ? 'y' : 'n'),'deactivatedBy' => ($value == 'a' ? 'n' : 'a'));

    $db->update($table, $setVal, array("listingId" => $id));

    echo json_encode(array('type' => 'success', 'Listing ' . ($value == 'a' ? 'activated ' : 'deactivated ') . 'successfully'));
    $activity_array = array("id" => $id, "module" => $module, "activity" => 'status', "action" => $value);
    add_admin_activity($activity_array);
    exit;
} else if ($action == "delete" && in_array('delete', $Permission)) {
    //if buyer not accept/reject files and admin delete listing
    $list1=$db->pdoQuery('select tl.listingId,tl.isBuyerPay,tw.amount,tw.userId as sellerId from tbl_listing as tl join tbl_wallet as tw on (tl.listingId=tw.projectId)  where tl.isBuyerPay!=0 and tl.listingId='.$id.' and tl.file_transfer_status="transferred" and tl.file_accept_status="pending" and tw.earnFrom="fromSeller" and tw.status="onHold"')->results();
    if(count($list1) > 0){
        foreach ($list1 as $value) {
            $exist_entry=$db->select('tbl_refund_payment',array('refundId'),array('listingId' => $value['listingId'],'userId' => $value['sellerId'],'isSellerTransfer' => 'y','amount' => $value['amount'],'isBuyerPaid' => 'y'))->result();
            if($exist_entry==''){
                $db->insert('tbl_refund_payment',array('listingId' => $value['listingId'],'userId' => $value['sellerId'],'isSellerTransfer' => 'y','islistingDeleted' => 'y','amount' => $value['amount'],'isBuyerPaid' => 'y','createdDate'=>date('Y-m-d H:i:s')));
            }
        }
    }
     //if buyer paid for listing and admin delete the listing then refund all money to buyer
    $list2=$db->pdoQuery('select tl.listingId,tl.isBuyerPay,tw.amount,tw.userId as sellerId from tbl_listing as tl join tbl_wallet as tw on (tl.listingId=tw.projectId)  where tl.isBuyerPay!=0 and tl.listingId='.$id.' and tl.file_transfer_status="pending" and tl.file_accept_status="pending" and tw.earnFrom="fromBuyer" and tw.status="onHold"')->results();

    if(count($list2) > 0){
        foreach ($list2 as $value) {
            $exist_entry=$db->select('tbl_refund_payment',array('refundId'),array('listingId' => $value['listingId'],'userId' => $value['isBuyerPay'],'amount' => $value['amount'],'isBuyerPaid' => 'y'))->result();
            if($exist_entry==''){
                $db->insert('tbl_refund_payment',array('listingId' => $value['listingId'],'userId' => $value['isBuyerPay'],'amount' => $value['amount'],'isBuyerPaid' => 'y','islistingDeleted' => 'y','createdDate'=>date('Y-m-d H:i:s')));
            }
        }
    }
    $isDeleted = array("isDeleted"=>'y');
    $aWhere = array("listingId" => $id);
    $affected_rows = $db->update($table, $isDeleted, $aWhere)->affectedRows();
    if ($affected_rows && $affected_rows > 0) {
        $activity_array = array("id" => $id, "module" => $module, "activity" => 'delete');
        add_admin_activity($activity_array);
        
        echo json_encode(array('type' => 'success', 'message' => "Listing deleted successfully"));
        exit;
    } else {
        echo json_encode(array('type' => 'error', 'message' => "There seems to be an issue deleting Listing"));
        exit;
    }
} 
else if ($action == "undo" && in_array('undo', $Permission)) {
    $isDeleted = array("isDeleted"=>'n');
    $aWhere = array("listingId" => $id);
    $affected_rows = $db->update($table, $isDeleted, $aWhere)->affectedRows();
    if ($affected_rows && $affected_rows > 0) {
        $activity_array = array("id" => $id, "module" => $module, "activity" => 'undo');
        add_admin_activity($activity_array);
        
        echo json_encode(array('type' => 'success', 'message' => "Listing Activated successfully"));
        exit;
    } else {
        echo json_encode(array('type' => 'error', 'message' => "There seems to be an issue undo Listing"));
        exit;
    }
} 
else if ($action == "view") {
    if(in_array('view', $Permission)) {
        $activity_array = array("id" => $id, "module" => $module, "activity" => 'view');
        add_admin_activity($activity_array);
    } else {
        die("You don't have sufficient permission to view record");
    }
}
else if ($action == "edit" && in_array('edit', $Permission)) {
    $activity_array = array("id" => $id, "module" => $module, "activity" => 'edit');
    add_admin_activity($activity_array);
}
else if($action == 'getChildCat')
{
    $parId = $_REQUEST['parId'];

    $childCats = $db->pdoQuery("SELECT * FROM `tbl_listing_category` WHERE `parent_id` = ".$parId." AND `isActive` =  'y'")->results();
                   
    $html = ''; 
    $html .= '<option>--- Select sub-types ---</option>';                           
    foreach($childCats as $childCat)
    {
        $catId = $childCat['id'];
        $categoryName = $childCat['categoryName'];

        //$html .= '<div class="checkbox"><label><input type="checkbox" name="childCat[]" value="'.$catId.'">'.$categoryName.'</label></div>';
        $html .= '<option value="'.$catId.'">'.$categoryName.'</option>';
    }
    $return_array['html'] = $html;
    echo json_encode($return_array);
    exit;
}
else if($action == 'getChildNiche')
{
    $parId = $_REQUEST['parId'];

    $childCats = $db->pdoQuery("SELECT * FROM `tbl_listing_niche` WHERE `parent_id` = ".$parId." AND `isActive` =  'y'")->results();
                   
    $html = ''; 
    $html .= '<option>--- Select sub-categories ---</option>';    
    foreach($childCats as $childCat)
    {
        $catId = $childCat['id'];
        $categoryName = $childCat['nicheName'];

        //$html .= '<div class="checkbox"><label><input type="checkbox" name="childCat[]" value="'.$catId.'">'.$categoryName.'</label></div>';
        $html .= '<option value="'.$catId.'">'.$categoryName.'</option>';
    }
    $return_array['html'] = $html;
    echo json_encode($return_array);
    exit;
}
else if($action=='check_date'){
    $year=(isset($_REQUEST['year']))?$_REQUEST['year']:date('Y');
    $month=(isset($_REQUEST['month']))?$_REQUEST['month']:date('n');
    $saleType=(isset($_REQUEST['saleType']))?$_REQUEST['saleType']:0;
    $date_live=strtotime($year."-".$month);
    $now=strtotime(date('Y-m'));
    if($saleType==1)
        echo (((date('Y') -$year) * 12) + (date('n') - $month) > 3)?'true':'false';
    else if($saleType==4)
        echo ($date_live >= $now)?'false':'true';
    else
        echo (((date('Y') -$year) * 12) + (date('n') - $month) < 3)?'true':'false';
    exit;
}
else if ($action == 'upload') {
    
    $a = 'file';
    $type = $_FILES[$a]["type"];
    $fileName = $_FILES[$a]["name"];
        
    $TmpName = $_FILES[$a]["tmp_name"];
    $size = $_FILES[$a]["size"];
    $listingId = $_REQUEST["listingId"];
    $image_type_array=array("image/jpeg","image/png","image/gif","image/x-png","image/jpg","image/x-png","image/x-jpeg","image/pjpeg","image/x-icon");
    
    $doc_type_array=array("text/plain","application/wps-office.xlsx","application/xls","application/pdf","application/msword","application/vnd.openxmlformats-officedocument.wordprocessingml.document","application/docx","application/doc","application/ppt","application/pptx","application/pps","application/ppsx");
    $file_attachment=$db->pdoQuery('SELECT attachments FROM tbl_listing WHERE listingId='. $listingId)->result();
    if($size<=FILE_SIZE){
        $upload_dir=DIR_UPD.'product/';
        $newName = mt_rand(100000, 999999)."--".(pathinfo($fileName, PATHINFO_FILENAME));
        $ext = '.'.strtoupper(getExt($fileName));
        $fileName = $newName.$ext;
        if (in_array($type,$image_type_array)) {
            $th_arr[0] = array('width' => '324', 'height' => '155');  
            $th_arr[1] = array('width' => '480', 'height' => '332');
            $th_arr[2] = array('width' => '600', 'height' => '400');                        
            for($j=0;$j<count($th_arr);$j++)
            {           
                $isUploadImage = $updimage = GenerateThumbnail($fileName,$upload_dir,$TmpName,$th_arr,$newName,true);
            }
        }
        $fileNameArr = pathinfo($fileName);
        $onlyFileName = $fileNameArr['basename'];
        if(in_array($type,$doc_type_array)){
            move_uploaded_file($TmpName, $upload_dir.$onlyFileName);
        }
        if(file_exists($upload_dir.$onlyFileName)){
            $attachments_temp=($file_attachment['attachments']!='')?$file_attachment['attachments'].",".$onlyFileName:$onlyFileName;
            $db->update('tbl_listing', array("attachments"=>$attachments_temp),array('listingId' => $listingId))->showQuery();  
        }
        exit;
    }
}
else if($action=='delete_attach'){
    //$id=(isset($_REQUEST['id']))?$_REQUEST['id']:'';
    $listingId = $_REQUEST["listingId"];
    $file_name = $_REQUEST["file_name"];
    $file_attachment=$db->pdoQuery('SELECT attachments FROM tbl_listing WHERE listingId='. $listingId)->result();
    $attachments=($file_attachment['attachments']!='')?explode(",",$file_attachment['attachments']):'';
    if(in_array($file_name, $attachments)){
        unset($attachments[array_search($file_name,$attachments)]);
        if(file_exists(DIR_UPD.'product/'.$file_name)){
            unlink(DIR_UPD.'product/'.$file_name);
        }
    }
    $attachments=implode(",", $attachments);
    $affect=$db->update('tbl_listing', array("attachments"=>$attachments),array('listingId' => $listingId))->affectedRows();
    echo ($affect>0)?'true':'false';
    exit;
}else if ($action == "export_excel") {
    // Function for Exporting Data to Excel
    $heading_array[] = array("Sr No.","User Name","URL","Listing Type","Status");
    $qrySel = $db->pdoQuery("SELECT userName,listingUrl,listingTypeName,tbl_listing.isActive as status  from tbl_listing join tbl_users on (tbl_listing.userId=tbl_users.id) join tbl_listing_type on(tbl_listing_type.listingTypeId=tbl_listing.listingTypeId) WHERE tbl_listing.isActive!='d' and tbl_listing.isAdminApproved='approved' and tbl_listing.isPaid='y' ")->results();
    $i=1;

    if(!empty($qrySel)) {
        foreach($qrySel as $qryRes){
                $constantArr = array(
                            $i,
                            $qryRes['userName'],
                            $qryRes['listingUrl'],
                            trim($qryRes['listingTypeName']),
                            ($qryRes['status']== 'y' ? 'Active' : 'Deactive'));
                $final_result[] = $constantArr;
                $i++;
            }
            $activity_array = array("id" => $id, "module" => $module, "activity" => 'excel');
            add_admin_activity($activity_array);
            export_to_excel($final_result, $module,$heading_array);
    } else {
        $_SESSION["toastr_message"] = disMessage(array('type' => 'err', 'var' => 'No data found for export to excel!'));
       redirectPage(SITE_ADM_MOD.$module."/");
    }
}else if ($action == "export_csv") {
    $user_data = array();
    $usertype_array = array("Sr No.","User Name","URL","Listing Type","Status");
    $getUserData = $db->pdoQuery("SELECT userName,listingUrl,listingTypeName,tbl_listing.isActive as status 
        from tbl_listing join 
        tbl_users on (tbl_listing.userId=tbl_users.id) join tbl_listing_type 
        on(tbl_listing_type.listingTypeId=tbl_listing.listingTypeId) 
        WHERE tbl_listing.isActive!='d' and 
        tbl_listing.isAdminApproved='approved' 
        and tbl_listing.isPaid='y' ")->results();

    $i=1;
    
    if(!empty($getUserData)) {
        foreach($getUserData AS $keys => $values) {
            $user_data[$keys][] = $i;
            $user_data[$keys][] = ucfirst($values['userName']);
            $user_data[$keys][] = $values['listingUrl'];
            $user_data[$keys][] = trim($values['listingTypeName']);
            $user_data[$keys][] = $values['status']== 'y' ? 'Active' : 'Deactive';
            $i++;
        }
        
        $final_result = array($usertype_array);
        foreach($user_data as $k => $v){
            $final_result = array_merge($final_result,array($v));
        }
        $activity_array = array("id" => $id, "module" => $module, "activity" => 'csv');
        add_admin_activity($activity_array);
        convert_to_csv($final_result,"listingInfoCSV.csv",",");
        exit;
    }
    else{
        $_SESSION["toastr_message"] = disMessage(array('type' => 'err', 'var' => 'No data found for export to csv!'));
       redirectPage(SITE_ADM_MOD.$module."/");
    }
}
$mainObject = new Listing($module, $id, NULL, $searchArray, $action,$listingTypeId);
extract($mainObject->data);
echo ($content);
exit;
