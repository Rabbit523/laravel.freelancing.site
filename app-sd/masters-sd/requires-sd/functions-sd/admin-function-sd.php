<?php
function getLoggedinName() {
	global $db, $adminUserId;
	$qrysel = $db->select("uName","id=".$adminUserId."");
	$fetchUser = mysql_fetch_object($qrysel);
	return trim(addslashes(ucwords($fetchUser->uName)));
}

//check Admin Permission
function chkPermission($module){
	global $db, $adminUserId;
	//"permissions",
	$admSl = $db->select("tbl_admin", array("adminType"), array("id ="=>(int)$adminUserId))->result();
	if(!empty($admSl)){
		$adm = $admSl;
		//echo $adm['adminType']; exit; 
		if($adm['adminType'] == 'g'){
			$moduleId = $db->select("tbl_adminrole", array("id"), array("pagenm ="=>(string)$module))->result();
			$chkPermssion = $db->select("tbl_admin_permission", array("permission"), array("admin_id"=>(int)$adminUserId,"page_id"=>$moduleId['id']))->result();
			if(empty($chkPermssion['permission'])){
				$toastr_message = $_SESSION["toastr_message"] = disMessage(array('type'=>'err','var'=>'NoPermission'));
				redirectPage(SITE_ADM_MOD.'home-sd/');
			}
		}
	}
}

function add_admin_activity($activity_array=array()){
	global $db,$adminUserId;
	$admSl = $db->select("tbl_admin", array("adminType"), array("id ="=>(int)$adminUserId))->result();
	if($admSl['adminType'] == 'g'){
		$activity_array['id'] = (isset($activity_array['id']))?$activity_array['id']:0;
		$activity_array['module'] = (isset($activity_array['module']))?getTableValue('tbl_adminrole','id',array("pagenm"=>$activity_array['module'])):0;
		$activity_array['activity'] = (isset($activity_array['activity']))?getTableValue('tbl_subadmin_action','id',array("constant"=>$activity_array['activity'])):0;
		$activity_array['action'] = (isset($activity_array['action']))?$activity_array['action']:'';
		$activity_array['created_date'] = date('Y-m-d H:i:s');
		$activity_array['updated_date'] = date('Y-m-d H:i:s');
		
		
		$val_array = array("activity_type"=>$activity_array['activity'],"page_id"=>$activity_array['module'],"admin_id"=>$adminUserId,"entity_id"=>$activity_array['id'],"entity_action"=>$activity_array['action'],"created_date"=>$activity_array['created_date'],"updated_date"=>$activity_array['updated_date']);
		$db->insert('tbl_admin_activity',$val_array);
	}
}


function chkModulePermission($module){
	global $db, $adminUserId;
	//"permissions",
	$admSl = $db->select("tbl_admin", array("adminType"), array("id ="=>(int)$adminUserId))->result();
	if(!empty($admSl)){
		$adm = $admSl;
		//echo $adm['adminType']; exit; 
		if($adm['adminType'] == 'g'){
			$moduleId = $db->select("tbl_adminrole", array("id"), array("pagenm ="=>(string)$module))->result();
			$chkPermssion = $db->select("tbl_admin_permission", array("permission"), array("admin_id"=>(int)$adminUserId,"page_id"=>$moduleId['id'],"and permission !="=>""))->result();
			if(!empty($chkPermssion['permission'])){
				$qryRes = $db->pdoQuery("select id,constant from tbl_subadmin_action where id in (".$chkPermssion['permission'].")")->results();
				foreach($qryRes as $fetchRes){
					$permissions[] = $fetchRes["constant"];
				}
			}
		}else{
			$qryRes = $db->select("tbl_subadmin_action", array("id,constant"), array())->results();
			foreach($qryRes as $fetchRes){
				$permissions[] = $fetchRes["constant"];
			}
		}
	}
	return $permissions;
}


// Get Section wise Role Array
function getSectionRoleArray($flag=false) {
	global $db, $adminUserId;
	$arr[]=array();
	$type = '';
	$res1=$db->select('tbl_admin','id,adminType,permissions','id='.$adminUserId, NULL, NULL);
	$res1Fetch = mysql_fetch_object($res1);
	$permission = $res1Fetch->permissions!='' ? $res1Fetch->permissions : 0;

	$res=$db->select('tbl_adminsection','id,type,section_name', NULL, NULL, '`order` ASC');
	if(mysql_num_rows($res)>0) {
		$i=0;
		while($row=mysql_fetch_array($res)) {
			$per_wh_con = '';
			if($res1Fetch->adminType == 'g')
				$per_wh_con=($permission!='0')?(' AND id IN('.str_replace('|',',',$permission.')')):'';
			$status_wh=($res1Fetch->adminType == 's' && $flag == false) ?  " status IN ('a','s')":"status='a'";
			$qry_role="sectionid='".$row['id']."' AND ".$status_wh.$per_wh_con;
			$res_role=$db->select('tbl_adminrole','id,title,pagenm,image', $qry_role, NULL, '`seq` ASC', 0);
			if($tot=mysql_num_rows($res_role)>0) {
				$temp=$j=0;
				while($row_role=mysql_fetch_array($res_role)) {
					$arr[$i]['id']=$row_role['id'];
					$arr[$i]['text']=$row_role['title'];
					$arr[$i]['pagenm']=$row_role['pagenm'];
					$arr[$i]['image']=$row_role['image'];
					if($j==0) {
						$arr[$i]['optlbl']=$row['section_name'];
						$temp=$row['id'];$j++;
					} else if($j==($tot-1)) {
						$j=0;
					}
					$i++;
				}
			}
		}
	}
	return $arr;
}	
function makeConstantFile()
{
	global $db, $adminUserId;
	
	$files = glob(DIR_INC.'language/*'); // get all file names
	foreach($files as $file){ // iterate files
	  if(is_file($file))
		unlink($file); // delete file
	}
	$qrysel1= $db->select("tbl_language", "*",array("status"=>"a"),"", "", 0)->results();
		
	foreach($qrysel1 as $fetchSel)
	{
		$fp = fopen(DIR_INC. "language/".$fetchSel['id'].".php","wb");
		$content = '';
		
		$qsel1 = $db->select("tbl_constant","*",array("languageId"=>$fetchSel['id']))->results();
		
		$content.='<?php ';
		foreach($qsel1 as $fetchSel1)
		{
			$content.= ' define("'.$fetchSel1['constantName'].'","'.$fetchSel1['constantValue'].'"); ';
		}
		$content.=' ?>';
		fwrite($fp,$content);
		fclose($fp);
	}
}

function mysql_get_prim_key($table){
	global $db;
	$sql = "SHOW INDEX FROM $table WHERE Key_name = 'PRIMARY'";
	$gp = mysql_query($sql);
	$cgp = mysql_num_rows($gp);
	$cgp=$db->pdoQuery($sql)->result();	
	if(count($cgp) > 0){
		$Column_name=$cgp['Column_name'];
//	extract($agp);
		return($Column_name);
	}else{
	//return(false);
		return '';
	}
}
function searchInMultidimensionalArray($array, $key, $value) {

    $response = array();
    $response['status'] = false;

    foreach ($array as $main_key => $val) {
        if ($val[$key] == $value) {
            $response['status'] = true;
            $response['key'] = $main_key;

            return $response;
        }
    }

    return $response;
}

/*function filtering($value = '', $type = 'output', $valType = 'string', $funcArray = '') {
    global $abuse_array, $abuse_array_value;

    if ($valType != 'int' && $type == 'output') {
        $value = str_ireplace($abuse_array, $abuse_array_value, $value);
    }

    if ($type == 'input' && $valType == 'string') {
        $value = str_replace('<', '< ', $value);
    }

    $content = $filterValues = '';
    if ($valType == 'int')
        $filterValues = (isset($value) ? (int) strip_tags(trim($value)) : 0);
    if ($valType == 'float')
        $filterValues = (isset($value) ? (float) strip_tags(trim($value)) : 0);
    else if ($valType == 'string')
        $filterValues = (isset($value) ? (string) strip_tags(trim($value)) : NULL);
    else if ($valType == 'text')
        $filterValues = (isset($value) ? (string) trim($value) : NULL);
    else
        $filterValues = (isset($value) ? trim($value) : NULL);

    if ($type == 'input') {
        //$content = mysql_real_escape_string($filterValues);
        //$content = $filterValues;
        //$value = str_replace('<', '< ', $filterValues);
        $content = addslashes($filterValues);
    } else if ($type == 'output') {
        if ($valType == 'string')
            $filterValues = html_entity_decode($filterValues);

        $value = str_replace(array('\r', '\n', ''), array('', '', ''), $filterValues);
        $content = stripslashes($value);
    }
    else {
        $content = $filterValues;
    }

    if ($funcArray != '') {
        $funcArray = explode(',', $funcArray);
        foreach ($funcArray as $functions) {
            if ($functions != '' && $functions != ' ') {
                if (function_exists($functions)) {
                    $content = $functions($content);
                }
            }
        }
    }

    return $content;
}*/

function getlistingUrl_admin($listingId) 
{
    global $db;  
    $qSel = "SELECT listingUrl from tbl_listing WHERE listingId = '".$listingId."'";
    $qrysel = $db->select('tbl_listing',array('listingUrl'),array('listingId'=>$listingId))->result();
    return $qrysel['listingUrl']; 
}  


?>
