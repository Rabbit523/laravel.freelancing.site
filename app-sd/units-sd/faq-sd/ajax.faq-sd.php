<?php
require_once("../../requires-sd/config-sd.php");
include(DIR_CLASS."faq-sd.lib.php");

$module = 'faq-sd';

$action = isset($_GET["action"]) ? $_GET["action"] : (isset($_POST["action"]) ? $_POST["action"] : '');
$return_array = array();

$mainObj = new Faq($module, '');

if($action == 'get-faq')
{   
    $return_array['content'] = $mainObj->faq_load($_REQUEST['faq_category'],$_REQUEST['keyword']);
    echo json_encode($return_array);
    exit;
}
if($action == 'search-faq')
{
    if($_REQUEST['keywrd'] == "")
    {
        $query_data = $db->select("tbl_faq_category","*",array("isActive"=>'y'))->result();
        $return_array['content'] = $mainObj->faq_load($query_data['id']);
    }
    else
    {
        $return_array['content'] = $mainObj->search_faq($_REQUEST['keywrd'],$_REQUEST['cat']);   
    }   
    echo json_encode($return_array);
    exit;
}
echo json_encode($return_array);
exit;
?>