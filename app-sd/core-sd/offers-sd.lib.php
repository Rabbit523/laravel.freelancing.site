<?php
class Offers {
	function __construct($module = "", $id = 0) {
		foreach ($GLOBALS as $key => $values) {
			$this->$key = $values;
		}
		$this->module = $module;
		$this->id = $id;
	}
	public function getPageContent() {
		$html = new MainTemplater(DIR_TMPL . "{$this->module}/{$this->module}.skd");
		$html = $html->compile();
		
		$left_panel = new MainTemplater(DIR_TMPL . "dashboard_left_panel-sd.skd");
		$left_panel = $left_panel->compile();
		$offer_list = new MainTemplater(DIR_TMPL . "{$this->module}/offer_list_loop-sd.skd");
		$offer_list = $offer_list->compile();
		$offer_list_data = $approvedAmount = '';
		$query=$this->db->pdoQuery('SELECT tbl_bids.amount as amount,tbl_bids.approveStatus,tbl_bids.biddedDate,tbl_listing.listingId,tbl_listing.listingUrl,tbl_listing.buyNowPrice,tbl_listing.createdDate,tbl_listing.listDurationDate,tbl_listing.listingSlug,tbl_listing.isDeleted ,tbl_listing.listingTypeId,tbl_listing.appName
			from tbl_bids 
			join tbl_listing on (tbl_listing.listingId=tbl_bids.listingId) 
			where tbl_bids.isBuyNow="n" 
			AND buyerId='.$this->sessUserId.' 
			AND tbl_listing.saleType="c" 
			order by tbl_bids.biddedDate')->results();

		if(count($query) > 0){
			foreach ($query as $value) {
				
				$listingDeleted = ($value['isDeleted'] == 'n')?'hide':'';

				$field_replace_array=array(
						'%LISTING_URL%' => ($value['listingTypeId']=='4')?$value['appName']:displaySiteUrl($value['listingUrl']),
						"%LIST_TYPE%" => getListingType($value['listingTypeId']),
						'%WEBSITE_LINK%' => SITE_DETAILS_URL.$value['listingSlug'],
						'%BUY_NOW%' => ($value['buyNowPrice']=='')?0:$value['buyNowPrice'],
						'%BIDDED_DATE%' => date(DATE_FORMAT,strtotime($value['biddedDate'])),
						'%REMAINING_DAYS%' => getRemainingDays($value['listDurationDate'],$value['createdDate']),
						'%BID_STATUS%' => $value['approveStatus'],
						"%HREF%" => SITE_MOD.$this->module."/ajax." . $this->module . ".php?action=view_offers&listingId=".$value['listingId']."&module=".$this->module,
						'%ISLISTING_DELETED%'=>$listingDeleted
					);
				$offer_list_data.=str_replace(array_keys($field_replace_array), array_values($field_replace_array), $offer_list);

				if($value['approveStatus'] == 'accepted')
				{
					$approvedAmount = $value['amount'];
				}
			}
		}else{
			$offer_list_data="<tr><td colspan='7'>".YOU_HAVE_NOT_PLACED_ANY_OFFER."</td></tr>";
		}

		$inWalletAmount = 0;
		if(isset($_SESSION['pickgeeks_userId']))
		{
			$inWalletAmount = checkUserWalletAmount($_SESSION['pickgeeks_userId']);
		}

		$menu_field = array('%DASHBOARD_LEFT_PANEL%','%OFFER_LIST%','%inWalletAmount%','%APPROVED_AMOUNT%');
		$menu_replace = array($left_panel,$offer_list_data,$inWalletAmount,$approvedAmount);
		$html = str_replace($menu_field,$menu_replace,$html);
		return $html;

	}
	public function InsertData($data){
		extract($data);
		if(!empty($chkpoint) && checkToken($chkpoint, 'frmSubmit'))
		{
			if($message!=''){
				$array=array(
	            "ownerId" => $this->sessUserId,
	            "senderId" => $this->sessUserId,
	            "receiverId" => $receiverId,
	            "messageDesc" => $message,
	            "delete_user" => '',
	            "createdDate" => date('Y-m-d H:i:s'),
	            "ipAddress"=>get_ip_address()
	            );
	            $isUsreContact = $this->db->pdoQuery("SELECT * FROM tbl_user_contacts WHERE (userId = '".$_SESSION['pickgeeks_userId']."' AND contactuserId = '".$receiverId."') OR (contactuserId = '".$_SESSION['pickgeeks_userId']."' AND userId= '".$receiverId."')")->results();

				if(count($isUsreContact) <= 0)
					$this->db->insert("tbl_user_contacts",array("userId"=>$_SESSION['pickgeeks_userId'],"contactuserId"=>$receiverId,"createdDate"=>date('Y-m-d H:i:s'),"ipAddress"=>get_ip_address()));
	        	$query=$this->db->insert('tbl_messages',$array)->getLastInsertId();
	        	$msgType = $_SESSION["msgType"] = ($query>0)?disMessage(array('type'=>'suc','var'=>YOUR_MESSAGE_SENT_SUCCESSFULLY)):disMessage(array('type'=>'err','var'=>SOMETHING_WENT_WRONG));
			}
			redirectPage($_SERVER['HTTP_REFERER']);
		} else {
			$msgType =$_SESSION["msgType"]=disMessage(array('type' => 'err', 'var' => SECURITY_TOKEN_MISMATCH));
			redirectPage($_SERVER['HTTP_REFERER'],'refresh');
		}  
	}	
}

?>
