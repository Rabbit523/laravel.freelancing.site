<?php
class Details{
	function __construct($module = "", $id = 0) {
		global $fields,$memberId;
		$this->fields = $fields;
		$this->memberId = $memberId;

		foreach ($GLOBALS as $key => $values) {
			$this->$key = $values;
		}
		$this->module = $module;
		$this->id = $id;
	}
	public function getPageContent() {
		if(isset($_REQUEST['id']) && ($_REQUEST['id'] > 0)){
			$qrySel=$this->db->pdoQuery('SELECT listingId,listingUrl,listStatus,saleType,reservePrice,listingSlug,isBuyerPay,DATE_ADD(createdDate,INTERVAL listDurationDate day) as endDate,file_transfer_status,isDeleted,listingTypeId,appName FROM tbl_listing where listingId= ?',array($_REQUEST['id']))->result();
			if($qrySel['isDeleted']=='y')
			{
				$disabled_class = "disabled";
			}
			else
			{
				$disabled_class = "";
			}
			$accept=($qrySel['listStatus'] =='sold' || $qrySel['isBuyerPay'] > 0)?'disabled':(((date('Y-m-d H:i:s', strtotime($qrySel["endDate"] . " +48 hours"))) < date('Y-m-d H:i:s'))?'disabled' : (($qrySel['endDate'] > date('Y-m-d H:i:s')) ? 'disabled' : (($qrySel['file_transfer_status']) != 'pending' )?'disabled':''));
			$saleType=(isset($_REQUEST['sale']) && ($_REQUEST['sale']=='c'))?"Offer" : "Bid";
			$statement='tbl_users.userName,tbl_users.profile_img from tbl_bids join tbl_users on (tbl_users.id=tbl_bids.buyerId) where tbl_bids.isBuyNow="n" AND tbl_bids.listingId='.$qrySel["listingId"].' and tbl_bids.status="y"';
    		$subqry=$this->db->pdoQuery('SELECT tbl_bids.*,'.$statement." order by amount desc")->results();
    		if(count($subqry) > 0){
    			$html = new MainTemplater(DIR_TMPL . "{$this->module}/{$this->module}.skd");
				$html = $html->compile();
				$left_panel = new MainTemplater(DIR_TMPL . "dashboard_left_panel-sd.skd");
				$left_panel = $left_panel->compile();
				$main_content = new MainTemplater(DIR_TMPL . "{$this->module}/loop_data-sd.skd");
				$main_content = $main_content->compile();	
				$content='';
		    	//FOR PAGINATION START
				$totalRecord = count($subqry);
		        $page = (int) (((!isset($_REQUEST["page"])) || ($_REQUEST["page"]==0)) ? 1 : $_REQUEST["page"]);
		        $limit = (int) (!isset($_REQUEST["per_page"]) ? 50: $_REQUEST["per_page"]);
		    	$lastpage = ceil($totalRecord/$limit);
		    	$startpoint = ($page -1) *$limit;
		    	$pagination = "<div id='pagingg' class='contex_listing_pagination'>";
				$pagination .= pagination($statement,$limit,$page,$totalRecord);
				$pagination .= "</div>";
				$result_count = min($totalRecord, $page * $limit);
				$replace_page=($totalRecord==0)?0:($startpoint+1);

		    	//FOR PAGINATION END
				$subqry1=$this->db->pdoQuery("SELECT tbl_bids.*,".$statement." order by tbl_bids.amount desc LIMIT ".$startpoint." , ".$limit)->results();
		    	$flag=0;
		    	foreach ($subqry1 as $value) {
		    		$flag=(($value['isWon']=='y') && ($value['approveStatus']=='accepted'))?1:$flag;
		    	}
				foreach ($subqry as $value) {
					 if($value['profile_img']!=""){
			            $img=SITE_UPD."profile/".$value['profile_img'];
			        }else{
			            $img=SITE_UPD."th2_no_user_image.png";
			        }
			        $button_accept_class=(($value['isWon']=='y') && ($value['approveStatus']=='accepted'))?"btn-danger":(($value['approveStatus']=='rejected')?"btn-warning":"btn-success");
			        $button_accept_icon=(($value['isWon']=='y') && ($value['approveStatus']=='accepted'))?"fa-times":(($value['approveStatus']=='rejected')?"fa-ban":"fa-check");
			        $button_title=(($value['isWon']=='y') && ($value['approveStatus']=='accepted'))?"Reject":(($value['approveStatus']=='rejected')?"Already Rejected":"Accept");
		    		$button_disable=((($flag==1) && ($button_accept_class)=='btn-success') || ($value['approveStatus']=='rejected'))?'disabled':'';
			        $suggested=($value['isReserve']=='y' && $saleType=='Bids' && $qrySel['reservePrice'] > 0)?'Suggested':'';

	    			$fields_content=array(
		    			"%ID%" => $value['bidId'],
		    			"%DISABLE_CLASS%" => $disabled_class,
		        		'%USER_NAME%' => $value['userName'],
		        		'%PROFILE_IMAGE%' => $img,
		        		'%TYPE%' => ($qrySel['saleType']=='c')?'Offer':'Bid',
		        		'%CREATED_DATE%' => date(DATE_FORMAT, strtotime($value['biddedDate'])),
		        		'%AMOUNT%' => CURRENCY_SYMBOL.$value['amount'],
		        		'%HIGH_AMOUNT%' => CURRENCY_SYMBOL.getHighPrice($value['listingId']),
		        		"%BID_END_BUTTON%" => $accept,
		        		"%ACCEPT_LINK%" => SITE_MOD.$this->module."/ajax." . $this->module . ".php?action=accept&bidId=" . $value['bidId'],
		        		"%BUTTON_CLASS%" => $button_accept_class,
		        		"%BUTTON_TITLE%" => $button_title,
		        		"%BUTTON_ICON%" => $button_accept_icon,
		        		"%BUTTON_DISABLE%" => $button_disable,
		        		"%SUGGESTED%" => $suggested
		    		);
		    		// printr($fields_content,1);
		    		// exit();
	    			$content.=str_replace(array_keys($fields_content),array_values($fields_content),$main_content);
	    		}

	    		$listingUrl = ($qrySel['listingTypeId'] == 4)?$qrySel['appName']:displaySiteUrl($qrySel['listingUrl']);

				$menu_field = array('%DASHBOARD_LEFT_PANEL%',"%DETAILS_LIST%","%LISTING_URL%","%WEBSITE_LINK%","%BIDS_OFFERS%","%TOTAL_RECORDS%",'%PAGINATION%','%CURRENT_PAGE%','%TOTAL_PAGES%','%ID%','%SALE%');
				$menu_replace = array($left_panel,$content,$listingUrl,SITE_DETAILS_URL.$qrySel['listingSlug'],$saleType,$totalRecord,$pagination,$replace_page,$result_count,$_GET['id'],$_GET['sale']);
				$html = str_replace($menu_field,$menu_replace,$html);
				return $html;
			}else{
			redirectPage(SITE_URL."listings",'refresh');
			}
		}else{
			redirectPage(SITE_URL."listings",'refresh');
		}
	}
}
?>
