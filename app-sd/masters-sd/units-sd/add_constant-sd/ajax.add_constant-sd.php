<?php

$content = '';
require_once("../../../requires-sd/config-sd.php");
if ($adminUserId == 0) 
{
    die('Invalid request');
}
include(DIR_ADMIN_CLASS."add_constant-sd.lib.php");
$module = 'add_constant-sd';
chkPermission($module);
$Permission = chkModulePermission($module);
$table = 'tbl_language_constant';

extract($_POST);
$flag=0;
if($action=="chkConstant" && !empty($constantVal)){

    $selectAllConstant=$db->pdoQuery("SELECT constant from tbl_language_constant")->results();

    foreach ($selectAllConstant as $key => $value) {
        if($value['constant']==strtoupper($constantVal)){
            $flag=1;
        }
    }

    if($flag==1){
        $data= 200;
        echo json_encode($data);
        exit;
    }
}


?>
