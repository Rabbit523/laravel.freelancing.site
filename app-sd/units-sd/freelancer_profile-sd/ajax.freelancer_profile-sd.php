<?php
require_once("../../requires-sd/config-sd.php");
include(DIR_CLASS."freelancer_profile-sd.lib.php");

$action = isset($_GET["action"]) ? $_GET["action"] : (isset($_POST["action"]) ? $_POST["action"] : '');
$id=isset($_GET["id"]) ? $_GET["id"] : (isset($_POST["id"]) ? $_POST["id"] : '');
$affected_rows = array();
$return_array = array();
$module = 'freelancer_profile-sd';
$mainObj = new FreelancerProfile($module, '');

if($action  == "load_data")
{
	if($_REQUEST['entityType']=="add_portfolio")
	{
		$return_array['content'] = $mainObj->portfolioSection($_REQUEST['entityId']);
		echo json_encode($return_array);
		exit;
	}
	else if($_REQUEST['entityType']=="add_education")
	{
		$return_array['content'] = $mainObj->educationSection($_REQUEST['entityId']);
		echo json_encode($return_array);
		exit;
	}
	else if($_REQUEST['entityType']=="add_certificate")
	{
		$return_array['content'] = $mainObj->certificationSection($_REQUEST['entityId']);
		echo json_encode($return_array);
		exit;
	}
	else if($_REQUEST['entityType']=="add_experience")
	{
		$return_array['content'] = $mainObj->experienceSection($_REQUEST['entityId']);
		echo json_encode($return_array);
		exit;
	}
	
}

if($action == "delete_data")
{
	$user_detail = $db->pdoQuery("select skillList,langList,subCategoryList from tbl_users where id=?",array($sessUserId))->result();
	
	if($_REQUEST['type'] == 'lang')
	{
		$db->delete("tbl_user_language",array("userId"=>$sessUserId,"languageId"=>$_REQUEST['id']));
		$data = user_language_list($sessUserId);
		$div = 'lang_listing';
		$div_content = $mainObj->lang_list($data,'');
	}
	else
	{
		if($_REQUEST['type'] == 'skill')
		{
			$data = removeFromString($user_detail['skillList'],$_REQUEST['id']);
			$array = array("skillList"=>$data);
			$div = 'skill_listing';
			$div_content = ($data!='') ? $mainObj->skill_list($data,'') : '';
		}
		else if($_REQUEST['type'] == 'subcat')
		{
			$data = removeFromString($user_detail['subCategoryList'],$_REQUEST['id']);
			$array = array("subCategoryList"=>$data);
			$div = 'subcat_listing';


			$div_content = ($data!='') ? $mainObj->subCategoryList($data,'') : '';
		}
		$db->update("tbl_users",$array,array("id"=>$sessUserId));
	}
	$return_array['div'] = $div;
	$return_array['div_content'] = $div_content;
	echo json_encode($return_array);
	exit;
}

else if($action == "move_image")
{
	if(empty($_FILES['file']['error'])) 
	{
		$type = $_FILES['file']['type'];
		$type = explode('/', $type);
		$type = (!empty($type[1]) ? $type[1] : 'png');
		$path = $_FILES['file']['tmp_name'];
		$file_name = uploadFile($_FILES['file'], DIR_USER_PROFILE, SITE_USER_PROFILE);
		$db->update("tbl_users",array("profileImg"=>$file_name['file_name']),array("id"=>$sessUserId));

		$data = file_get_contents($path);
		$base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
		$return_array['content'] = $base64;
		echo json_encode($return_array);
		exit;			
	}
}else if($action == "remove_profile_image"){
	$db->update("tbl_users",array("profileImg"=>''),array("id"=>$sessUserId));
	echo json_encode(['url' => getUserImage($sessUserId)]);
	exit();
}	



if(!empty($_POST['avatar_data'])){

	$crop = new CropAvatar(
		isset($_POST['avatar_src']) ? $_POST['avatar_src'] : null,
		isset($_POST['avatar_data']) ? $_POST['avatar_data'] : null,
		isset($_FILES['avatar_file']) ? $_FILES['avatar_file'] : null
	);

	$response = array(
		'state'  => 200,
		'message' => $crop->getMsg(),
		'result' => $crop->getResult()
	);
	$email = getTableValue("tbl_users","email",array("id"=>$sessUserId));

	if(!empty($response['result'])){
		$file_name = str_replace(DIR_USER_PROFILE, '', $response['result']);
		$db->update("tbl_users",array("profileImg"=>$file_name),array("email"=>$email));
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
echo json_encode($return_array);
exit;

?>
