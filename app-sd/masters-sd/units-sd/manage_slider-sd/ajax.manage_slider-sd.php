<?php
    $content = '';
    require_once("../../../requires-sd/config-sd.php");
    if ($adminUserId == 0) { die('Invalid request'); }
    include(DIR_ADMIN_CLASS."manage_slider-sd.lib.php");
    $module = 'manage_slider-sd';
    chkPermission($module);
    $Permission = chkModulePermission($module);
    $table = 'tbl_slider';
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

    if ($action == "updateStatus" && in_array('status', $Permission)) {
        $setVal = array('isActive' => ($value == 'a' ? 'y' : 'n'));

        $db->update($table, $setVal, array("id" => $id));
        $vide_type_check = $db->pdoQuery("select * from tbl_slider where id=? ",array($id))->result();
        if($vide_type_check['slider_type'] == 'v')
        {
            $db->pdoQuery("UPDATE tbl_slider set isActive='n' where id!=?",array($id));
        }
        else
        {
            $db->pdoQuery("UPDATE tbl_slider set isActive='n' where slider_type=?",array('v'));
        }
        echo json_encode(array('type' => 'success', 'Slider is ' . ($value == 'a' ? 'activated ' : 'deactivated ') . 'successfully'));
        $activity_array = array("id" => $id, "module" => $module, "activity" => 'status', "action" => $value);
        add_admin_activity($activity_array);
        exit;
    } else if ($action == "delete" && in_array('delete', $Permission)) {
        $slider = getTableValue($table, 'file_name', array("id" => $id));
        $affected_rows = $db->delete($table, array("id" => $id))->affectedRows();
        if ($affected_rows && $affected_rows > 0) {
            if(file_exists(DIR_SLIDER_IMAGE.$slider)) {
                unlink(DIR_SLIDER_IMAGE.$slider);
            }

            $activity_array = array("id" => $id, "module" => $module, "activity" => 'delete');
            add_admin_activity($activity_array);
            echo json_encode(array('type' => 'success', 'message' => "Slider page has been deleted successfully"));
            exit;
        } else {
            echo json_encode(array('type' => 'error', 'message' => "There seems to be an issue deleting content page"));
            exit;
        }
    } else if ($action == "view" && in_array('view', $Permission)) {
        $activity_array = array("id" => $id, "module" => $module, "activity" => 'view');
        add_admin_activity($activity_array);
    } else if ($action == "getContent") {
        extract($_POST);
        if (isset($id) && isset($language_id)) {
            $whereCond = ["id" => $id];
            if (empty($language_id)) {
                $selectArr[]= 'title'.' as title ';
                $selectArr[]= 'content'.' as content ';
            } else {
                $titleFld = "title_".$language_id.' as title ';
                $contentFld = "content_".$language_id.' as content ';
                $selectArr[] = $titleFld;
                $selectArr[] = $contentFld;
            }
            $selectArr = implode(",", $selectArr);
            $qrySel = $db->pdoQuery("select ".$selectArr." from tbl_slider where id=? ",array($id))->result();
            //$qrySel = $db->select($table,$selectArr , $whereCond)->result();
            echo json_encode($qrySel);exit;
        } else {
            echo json_encode(['code'=>100]);exit;

        }
        
    }


    $mainObject = new Slider($module, $id, NULL, $searchArray, $action);
    extract($mainObject->data);
    echo ($content);
    exit;