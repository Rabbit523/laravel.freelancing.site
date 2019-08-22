<?php

$content = '';
require_once("../../../requires-sd/config-sd.php");
if ($adminUserId == 0) {
    die('Invalid request');
}
include(DIR_ADMIN_CLASS."language-sd.lib.php");
//echo "<pre>";print_r($_REQUEST);exit;
$module = 'language-sd';
chkPermission($module);
$Permission = chkModulePermission($module);
$table = 'tbl_language';
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
    $pageName = filtering($_REQUEST['specificAmount'], 'input');
    $id = filtering($_REQUEST['id'], 'input', 'int');
    if ($pageName != '' && $id != '') {
        if (getTotalRows("tbl_commision", "specificAmount='" . $pageName . "' AND id != '" . $id . "' ", 'id') >= 1) {
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
    echo json_encode(array('type' => 'success', 'Language ' . ($value == 'a' ? 'activated ' : 'deactivated ') . 'successfully'));
    $activity_array = array("id" => $id, "module" => $module, "activity" => 'status', "action" => $value);
    add_admin_activity($activity_array);
    exit;

} else if ($action == "delete" && in_array('delete', $Permission)) {
    $aWhere = array("id" => $id);
    $affected_rows = $db->delete($table, $aWhere)->affectedRows();

    if ($affected_rows && $affected_rows > 0) {
        
        $db->query(" ALTER TABLE `tbl_language_constant`  DROP `value_".$id."`" );
        $db->query(" ALTER TABLE `tbl_blog` DROP `blogTitle_".$id."`, DROP `blogDesc_".$id."` " );
        $db->query(" ALTER TABLE `tbl_blog_category` DROP `categoryName_".$id."` " );
        $db->query(" ALTER TABLE `tbl_category` DROP `category_name_".$id."` " );
        $db->query(" ALTER TABLE `tbl_credit_package` DROP `title_".$id."` " );
        $db->query(" ALTER TABLE `tbl_faq` DROP `question_".$id."`, DROP `ansDesc_".$id."` " );
        $db->query(" ALTER TABLE `tbl_faq_category` DROP `categoryName_".$id."` " );
        $db->query(" ALTER TABLE `tbl_question` DROP `question_".$id."` " );
        $db->query(" ALTER TABLE `tbl_skills` DROP `skill_name_".$id."` " );
        $db->query(" ALTER TABLE `tbl_subcategory` DROP `subcategory_name_".$id."` " );
        $db->query(" ALTER TABLE `tbl_content`  DROP `pageTitle_".$id."`,  DROP `pageDesc_".$id."`" );

        $db->query(" ALTER TABLE `tbl_slider`  DROP `title_".$id."`,  DROP `content_".$id."`" );
        $db->query(" ALTER TABLE `tbl_how_it_work`  DROP `title_2`,  DROP `content_2`" );
        $db->query(" ALTER TABLE `tbl_way_to_work` DROP `content_2`" );
        $db->query(" ALTER TABLE `tbl_way_to_work_service` DROP `content_2`" );
        $db->query(" ALTER TABLE `tbl_download_app`  DROP `tag_line_2`,  DROP `title_2`,  DROP `content_2`" );
        $db->query(" ALTER TABLE `tbl_search_section`  DROP `title_2`,  DROP `content_2`" );
        $db->query(" ALTER TABLE `tbl_hire_section`  DROP `title_2`,  DROP `content_2`" );



        $activity_array = array("id" => $id, "module" => $module, "activity" => 'delete');
        add_admin_activity($activity_array);
        
        echo json_encode(array('type' => 'success', 'message' => "Language deleted successfully"));
        exit;
    } else {
        echo json_encode(array('type' => 'error', 'message' => "There seems to be an issue deleting Language"));
        exit;
    }
} else if ($action == "view") {
    if(in_array('view', $Permission)) {
        $activity_array = array("id" => $id, "module" => $module, "activity" => 'view');
        add_admin_activity($activity_array);
    } else {
        die("You don't have sufficient permission to view record");
    }
}

$mainObject = new Language($module, $id, NULL, $searchArray, $action);
extract($mainObject->data);
echo ($content);
exit;
