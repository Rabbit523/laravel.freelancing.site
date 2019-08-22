<?php
require_once("../../requires-sd/config-sd.php");
include(DIR_CLASS."category_search-sd.lib.php");

$action = isset($_GET["action"]) ? $_GET["action"] : (isset($_POST["action"]) ? $_POST["action"] : '');
$id=isset($_GET["id"]) ? $_GET["id"] : (isset($_POST["id"]) ? $_POST["id"] : '');
$affected_rows = array();
$return_array = array();
$module = 'category_search-sd';
$mainObj = new categorySearch($module);

$search_array = array();
if($action  == "load_seach_data")
{
	$search_array['keyword'] = (isset($_POST['keyword']) ? $_POST['keyword'] : '');
    
	$return_array['content'] = $mainObj->loop_data($search_array['keyword']);
	echo json_encode($return_array);
	exit;
}


echo json_encode($return_array);
exit;
?>
