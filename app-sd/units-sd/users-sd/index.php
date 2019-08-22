<?php

	$reqAuth = false;
	$module = 'users-sd';
	$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;
	require_once "../../requires-sd/config-sd.php";
	require_once DIR_CLASS."users-sd.lib.php";
	require_once "../../../vender-sd/pagination/function.php";
	$table = "tbl_listing";

	extract($_REQUEST);
	$userId =(isset($uName) && $uName!='')?getTableValue('tbl_users','id',array('userName'=>$uName)):'';
	if($userId!=''){
		$winTitle = 'Profile of - ' .$uName.SITE_NM;
		$headTitle = 'Profile of - '.$uName;
		$metaTag = getMetaTags(array("description" => $winTitle, "keywords" => $headTitle, "author" => AUTHOR));

		$objPost = new stdClass();
		$mainObj = new Users($module);
			
		$lastInsertedId = '';	
		if(isset($_REQUEST['saleTypeId']) && $_REQUEST['saleTypeId']!='')		
		{
			$lastInsertedId = $mainObj->submitData($_REQUEST,$lastInsertedId);
			if($lastInsertedId != '')
			{
				//if(isset($_REQUEST['auction']) && $_REQUEST['auction']=='auction')
				redirectPage(SITE_URL.'sell_auction/'.$lastInsertedId);
				//else
					//redirectPage(SITE_URL.'sell_classified/'.$lastInsertedId);
			}
		}		

		$pageContent = $mainObj->getPageContent();
		require_once DIR_TMPL . "compiler-sd.skd";	
	}else{
		redirectPage(SITE_URL);
	}
	
?>