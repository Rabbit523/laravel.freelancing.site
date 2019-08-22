<?php
require_once("../../requires-sd/config-sd.php");
include(DIR_CLASS."post_job-sd.lib.php");

$action = isset($_GET["action"]) ? $_GET["action"] : (isset($_POST["action"]) ? $_POST["action"] : '');
$id = isset($_GET["id"]) ? $_GET["id"] : (isset($_POST["id"]) ? $_POST["id"] : '');
$affected_rows = array();
$return_array = array();
$module = 'post_job-sd';
$obj= new PostJob($module,'');

$search_array = array();
$response = array();
$response['status'] = false;

if($action == "load_sub"){
	$main_cat = $_REQUEST['cat_id'];
	$job_id = !empty($_REQUEST['job_id']) ? $_REQUEST['job_id'] : 0;
    $sub_cat = $db->pdoQuery("select * from tbl_subcategory where maincat_id='".$main_cat."' and isActive='y' and isDelete='n' ")->results();

    $selected_SubCategory = 0;
    if(!empty($job_id)){
    	$selected_list = $db->pdoQuery('SELECT id,jobSubCategory FROM `tbl_jobs` WHERE id = ?',[$job_id])->result();
    	$selected_SubCategory = $selected_list['jobSubCategory'];
    }


    $list .="<option value=''>--".SELECT_SUB_CATEGORY."--</option>";
    if(count($sub_cat) > 0){

	    foreach ($sub_cat as $value)
	    {
	        $list .= "<option value='".$value['id']."' ".($value['id'] == $selected_SubCategory ? 'selected="selected"' : '')." >".$value[l_values('subcategory_name')]."</option>";
	    }
	} else {
		$list .="<option value='0' >--".NO_SUBCATEGORY_AVAILABLE."--</option>";
	}
    echo $list;exit;
}
if($action == "load_skills"){
	$main_cat = $_REQUEST['cat_id'];
	$job_id = !empty($_REQUEST['job_id']) ? $_REQUEST['job_id'] : 0;
    $sub_cat = $db->pdoQuery('SELECT * FROM `tbl_skills` WHERE category_ids LIKE ? and isActive="y" and isApproved = "y" and isDelete = "n" ',['%"'.$main_cat.'"%'])->results();
    $selected_skill = [];
    if(!empty($job_id)){
    	$selected_list = $db->pdoQuery('SELECT id,skills FROM `tbl_jobs` WHERE id = ?',[$job_id])->result();
    	$selected_skill = explode(',',$selected_list['skills']);
    }
    $list = '';
    if(count($sub_cat) > 0){
	    foreach ($sub_cat as $value)
	    {
	        $list .= "<option value='".$value['id']."' ".(in_array($value['id'],$selected_skill) ? 'selected="selected"' : '')." >".$value['skill_name']."</option>";
	    }
	} else {
		$list ="<option value='0' disabled='disabled' >--".NO_SKILLS_AVAILABLE."--</option>";
	}
    echo $list;exit;
    exit;
}
if($action == "move_image"){
	extract($_POST);

	if(empty($_FILES['job_files']['error']))
	{
	  $obj->submitFiles($_FILES,$token,$extention);
	  $return_array['content'] = 'success';
	  echo json_encode($return_array);
	  exit;
	}
}
if($action == "file_delete"){
	$aWhere = array("id" => $id);
    $affected_rows = $db->delete('tbl_temp_files', $aWhere)->affectedRows();

    if($affected_rows && $affected_rows > 0) {
       $response['status'] = 'true';
    } else {
		$affected_rows = $db->delete('tbl_job_files', $aWhere)->affectedRows();
		$response['status'] = 'true';
    }
    echo json_encode($response);
    exit();
}
if($action == "sel_question"){
	extract($_POST);
	$response['result'] = $obj->getSelectedQuestions($que_id);
	echo json_encode($response);
	exit();
}
if($action == "sel_question_edit"){
	extract($_POST);
	$response['result'] = $obj->getSelectedQuestions($que_id,'edit',$job_id);
	echo json_encode($response);
	exit();
}
if($action == "sel_invitations"){
	extract($_POST);
	$response['result'] = $obj->getSelectedUsers($_POST['user_id']);
	echo json_encode($response);
	exit();
}
if($action == "get_invitations"){
    $response['status'] = TRUE;
    $expLevel = !empty($_POST["expLevel"])?$_POST["expLevel"]:"b";
    $response['result'] = $obj->getInvitationSuggestion($expLevel);
    echo json_encode($response);
	exit();
}
?>
