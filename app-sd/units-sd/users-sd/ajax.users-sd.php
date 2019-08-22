<?php
require_once("../../requires-sd/config-sd.php");
include(DIR_CLASS."sell-sd.lib.php");

$action = isset($_GET["action"]) ? $_GET["action"] : (isset($_POST["action"]) ? $_POST["action"] : '');
$return_array = array();

if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'watchSeller')
{
	$sellerId = $_REQUEST['sellerId'];

	$isSeller = $db->pdoQuery("SELECT count(*) as totalWatch FROM `tbl_watchlist` WHERE `userId` = ".$_SESSION['pickgeeks_userId']." AND `sellId` = ".$sellerId." AND watchListType = 's'")->result();

	if($isSeller['totalWatch'] > 0)
	{
		$db->delete("tbl_watchlist",array('userId'=>$_SESSION['pickgeeks_userId'],'sellId'=>$sellerId,'watchListType'=>'s'));
		$return_array['data'] = 'deleted';
	}
	else
	{
		$db->insert("tbl_watchlist",array('userId'=>$_SESSION['pickgeeks_userId'],'sellId'=>$sellerId,'watchListType'=>'s','createdDate'=>date('Y-m-d H:i:s')));
		$return_array['data'] = 'inserted';	
	}
	echo json_encode($return_array);
	exit;
}
elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == 'watchListing')
{
	$listingId = $_REQUEST['listingId'];

	$isSeller = $db->pdoQuery("SELECT count(*) as totalWatch FROM `tbl_watchlist` WHERE `userId` = ".$_SESSION['pickgeeks_userId']." AND `listingId` = ".$listingId." AND watchListType = 'l'")->result();

	if($isSeller['totalWatch'] > 0)
	{
		$db->delete("tbl_watchlist",array('userId'=>$_SESSION['pickgeeks_userId'],'listingId'=>$listingId,'watchListType'=>'l'));
		$return_array['data'] = 'deleted';
	}
	else
	{
		$db->insert("tbl_watchlist",array('userId'=>$_SESSION['pickgeeks_userId'],'listingId'=>$listingId,'watchListType'=>'l','createdDate'=>date('Y-m-d H:i:s')));
		$return_array['data'] = 'inserted';	
	}
	echo json_encode($return_array);
	exit;
}
?>