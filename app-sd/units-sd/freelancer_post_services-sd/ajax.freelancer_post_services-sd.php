
<?php
require_once("../../requires-sd/config-sd.php");
include(DIR_CLASS."freelancer_post_services-sd.lib.php");

$action = isset($_GET["action"]) ? $_GET["action"] : (isset($_POST["action"]) ? $_POST["action"] : '');
$id=isset($_GET["id"]) ? $_GET["id"] : (isset($_POST["id"]) ? $_POST["id"] : '');
$affected_rows = array();
$return_array = array();
$module = 'freelancer_post_services-sd';
$mainObj = new FreelancerPostServices($module, '');

$search_array = array();


if($action == "load_sub_category")
{
	$return_array['content'] = getSubcategory($_REQUEST['maincat_id']);
	echo json_encode($return_array);
	exit;
}
if($action == "move_image"){
	extract($_POST);

	if(empty($_FILES['services_file']['error'])) 
	{
		$mainObj->submitFiles($_FILES,$token,$extention);
		$return_array['content'] = 'success';
		echo json_encode($return_array);
		exit;     
	}
}


if(!empty($_POST['avatar_data'])){
	extract($_POST);

	$crop = new CropAvatar(
		isset($_POST['avatar_src']) ? $_POST['avatar_src'] : null,
		isset($_POST['avatar_data']) ? $_POST['avatar_data'] : null,
		isset($_FILES['avatar_file']) ? $_FILES['avatar_file'] : null,
		['width' => 600,'height' => 300]
	);

	//print_r($_FILES['avatar_file']);

	$response = array(
		'state'  => 200,
		'message' => $crop->getMsg(),
		'result' => $crop->getResult()
	);

	if(!empty($response['result'])){
		$file_name = str_replace(DIR_SERVICES_FILE, '', $response['result']);
		$response['result'] =  $mainObj->submitFiles2($token,$file_name);
	}else{
		$response = array(
			'state'  => 4040,
			'message' => '',
			'result' => ''
		);		
	}
	echo json_encode($response);
	exit();
}

if($action == "file_delete"){
	if($_REQUEST['type'] == 'edit')
	{
		$data = $db->pdoQuery("select * from tbl_services_files where id=?",array($id))->result();
		$aWhere = array("id" => $id);
		$image = $data['fileName'];
		$affected_rows = $db->delete('tbl_services_files', $aWhere)->affectedRows();
		
	}
	else
	{
		$data = $db->pdoQuery("select * from tbl_temp_files where id=?",array($id))->result();
		$aWhere = array("id" => $id);
		$image = $data['fileName'];
		$affected_rows = $db->delete('tbl_temp_files', $aWhere)->affectedRows();
	}

	if ($affected_rows && $affected_rows > 0) {
		$response['status'] = 'true';
		unlink(DIR_SERVICES_FILE.$image);
	} else 
	{
		$data = $db->pdoQuery("select * from tbl_temp_files where id=?",array($id))->result();
		$aWhere = array("id" => $id);
		$image = $data['fileName'];
		$affected_rows = $db->delete('tbl_temp_files', $aWhere)->affectedRows();
		$response['status'] = 'true';
	}
	echo json_encode($response);
	exit();
}
if($action == "remove_addOn")
{
	$id = trim(str_replace("remove_div", "", $_POST['id']));
	$service_query = $db->pdoQuery("select * from tbl_services_addon where id=?",array($id)); 
	$service_detail = $service_query->result();
	$service_id = $_REQUEST['serviceId'];
	//echo $service_id['services_id'];exit;
	if($service_query->affectedRows()>0)
	{
		$db->delete("tbl_services_addon",array("id"=>$id));
	}

	if($_REQUEST['index'] == 1)
	{
		$return_array['content'] = $mainObj->getAddOnDetail($service_id);
	}
	else
	{
		$return_array['content'] = '';
	}
	echo json_encode($return_array);
	exit();
}


echo json_encode($return_array);
exit;
?>
