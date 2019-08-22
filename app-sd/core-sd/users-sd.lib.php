<?php
class Users{
	function __construct($module = "", $id = 0) {
		global $fields,$memberId;
		$this->fields = $fields;
		$this->memberId = $memberId;
		foreach ($GLOBALS as $key => $values) {
			$this->$key = $values;
		}
		$this->module = $module;
		$this->id = $id;
		$userName = (isset($_REQUEST['uName']) && $_REQUEST['uName']!='')?$_REQUEST['uName']:'';
		$userId = $this->userId = (isset($userName) && $userName!='')?getTableValue('tbl_users','id',array('userName'=>$userName)):'';
	}
	public function submitData($data){
		extract($data);
		$uniqueCodeDomain = generateRandString(25);				
		$lastInsertedId = $this->db->insert("tbl_listing",array("userId"=>$_SESSION['pickgeeks_userId'],"listingTypeId"=>$saleTypeId,"listingUrl"=>$website_url,'listingSlug'=>Slug(displaySiteUrl($website_url)),"createdDate"=>date('Y-m-d H:i:s'),'isActive'=>'n','saleType'=>$auction,'domainCode'=>$uniqueCodeDomain,"ipAddress"=>get_ip_address()))->getLastInsertId();	
		$_SESSION['siteId'] = $lastInsertedId ;
		return $lastInsertedId ;
	}
	public function getPageContent() {		
		$html = new MainTemplater(DIR_TMPL . "{$this->module}/{$this->module}.skd");
		$html = $html->compile();		
		$totalUsers = $this->db->pdoQuery("SELECT * FROM `tbl_users` WHERE `id` = ".$this->userId)->result();
		$totalCommentPlaced = $this->db->pdoQuery("SELECT count(*) as totalComment FROM `tbl_comments` WHERE `userId` = ".$this->userId." AND `isActive` = 'Y'")->result();
		$totalBidsPlaced = $this->db->pdoQuery("SELECT count(*) AS totalBids FROM `tbl_bids` WHERE isBuyNow='n' AND  `buyerId` = ".$this->userId)->result();			
		$totalListingPlaced = $this->db->pdoQuery("SELECT count(*) as totalListing FROM `tbl_listing` WHERE `userId` = ".$this->userId." AND `isActive` = 'y' AND isAdminApproved = 'approved' AND isDraft = 'n'")->result();
		$avrListingPlaced = $this->db->pdoQuery("SELECT count(*) as avrListing FROM `tbl_listing` tl WHERE tl.userId = ".$this->userId." AND tl.isActive = 'Y' AND tl.listStatus='sold'")->result();
		$profile=($totalUsers['profile_img'] != '')?SITE_APP_UPD.'profile/'.$totalUsers['profile_img']:SITE_APP_UPD.'profile/th2_no_user_image.png';
		$userName = $totalUsers['userName'];	
		$HomePageUrl=$totalUsers['HomePageUrl'];
        $user_full_name = $totalUsers['firstName'].' '.$totalUsers['lastName'];
        $location = $totalUsers['location'];
       
        $lastLogin = getRemainingDays($totalUsers['lastLogin'],date('Y-m-d H:i:s'),'remainDays');
        $memberSinse = getRemainingDays($totalUsers['createdDate'],date('Y-m-d H:i:s'),'remainDays');        
        $totalCommentPlaced = $totalCommentPlaced['totalComment'];        
        $totalBidsPlaced =($totalBidsPlaced['totalBids']==0 ||$totalBidsPlaced['totalBids']==1)?$totalBidsPlaced['totalBids'].' bid':$totalBidsPlaced['totalBids'].' bids';
        $totalListingPlaced = $totalListingPlaced['totalListing'];
        $avrListingSold = ($avrListingPlaced['avrListing'] != 0)?(int)(($avrListingPlaced['avrListing']*100)/$totalListingPlaced):0;
        $PhoneVeriStatus = ($totalUsers['PhoneVeriStatus'] == 'y')?'fa-check-square-o':'fa-square-o';
		$emailVeriStatus = ($totalUsers['emailVeriStatus'] == 'y')?'fa-check-square-o':'fa-square-o';
        $fbVeriStatus = ($totalUsers['fbVeriStatus'] == 'y')?'fa-check-square-o':'fa-square-o';
        $twitterVeriStatus = ($totalUsers['twitterVeriStatus'] == 'y')?'fa-check-square-o':'fa-square-o';
        $linkedInVeriStatus = ($totalUsers['linkedInVeriStatus'] == 'y')?'fa-check-square-o':'fa-square-o';
        $fbUserProfileVal = ($totalUsers['isFbDisplay'] == 'y')?'<a href="http://facebook.com/'.$totalUsers['fbUserProfile'].'" target="_blank">Face book</a>':'Facebook';

        $twitterUserProfileVal = ($totalUsers['isTwitterDisplay'] == 'y')?'<a href="https://twitter.com/'.$totalUsers['twitterUserProfile'].'" target="_blank">Twitter</a>':'Twitter';
        $linkedInUserProfileVal = ($totalUsers['isLinkedInDisplay'] == 'y')?'<a href="'.$totalUsers['linkedInUserProfile'].'" target="_blank">LinkedIn</a>':'LinkedIn';

		$watchSellerdataToggle = $watchSellerdataTarget = '';
		if($totalUsers['isDeleted']=='y')
		{
			$watchSellerClass = 'hide';
			$watchSellerText = '';
		}
        elseif(!isset($_SESSION['pickgeeks_userId']))
        {
			$watchSellerClass = 'btn-primary';
	        $watchSellerText = 'Watch Seller';
	        $watchSellerdataToggle = 'modal';
			$watchSellerdataTarget = '#loginModal';
        }
        else
        {
	        $totalwatchedPlaced = $this->db->pdoQuery("SELECT count(*) as totalWatchedSeller FROM `tbl_watchlist` WHERE `userId` = ".$_SESSION['pickgeeks_userId']." AND watchListType = 's' AND sellId = ".$this->userId)->result();
			if($_SESSION['pickgeeks_userId'] == $this->userId)
			{
				$watchSellerClass = 'hide';
				$watchSellerText = '';
			}
	        elseif($totalwatchedPlaced['totalWatchedSeller'] > 0)
	        {
	        	$watchSellerClass = 'btn-success';
	        	$watchSellerText = 'Watched Seller';
	        }
	        else        	
	        {
	        	$watchSellerClass = 'btn-primary';
	        	$watchSellerText = 'Watch Seller';
	        }        
	    }
        $listingDetails = '';
        $allListings = $this->db->pdoQuery("SELECT * FROM `tbl_listing` WHERE `userId` = ".$this->userId." AND isAdminApproved = 'approved' AND isDraft = 'n' AND `isActive` = 'y' order by listingId desc")->results();   
       
        if(count($allListings) > 0)
        {
        	foreach($allListings as $allListing)
        	{
        		extract($allListing);        		
				$totalListCommentPlaced = $this->db->pdoQuery("SELECT count(*) as totalComment FROM `tbl_comments` WHERE `userId` = ".$this->userId." AND listingId = ".$listingId." AND `isActive` = 'Y'")->result();
        		$html_listing = new MainTemplater(DIR_TMPL . "{$this->module}/listing-sd.skd");
				$html_listing = $html_listing->compile();
				$bidCount=getTotalBids($listingId);				
				if($saleType == 'a')
				{
					$bidPrice = '$'.getHighPrice($listingId);
					$totalBids = ($bidCount==0 || $bidCount==1)?$bidCount.' bid':$bidCount.' bids';						
				} 
				elseif($saleType == 'c')
				{
					$bidPrice =($reservePrice != 0)?'$'.$reservePrice:'$1';
					$totalBids = '-';	
				}
				$avrMonthlyRev = '';
				if($isRevenueGenerate == 'y' && $saleType!='3')
				{
					$avrMonthlyRev = MONTHLY_REVENUE.' <span>$0</span>';
					$grossRevenueArr = json_decode($grossRevenue);
					$i = 1;
					$grossRevenueValSum = '';
					foreach($grossRevenueArr as $grossRevenueKey=>$grossRevenueVal)
					{
						if($grossRevenueVal != '')
						{
							$grossRevenueValSum = $grossRevenueValSum + $grossRevenueVal;
							$i++;
						}
					}
					$avrMonthlyRev = MONTHLY_REVENUE.' <span> $'.number_format($grossRevenueValSum /$i,2).'</span>';
				}
				$dataToggle = $dataTarget = '';
				$listingDeletedLable = 'hide';
				if($isDeleted == 'y')
				{
					$watchedListClass = 'hide';
					$watchedListText = '';
					$listingDeletedLable = '';
				}
				elseif(!isset($_SESSION['pickgeeks_userId']))
		        {
					$watchedListClass = 'btn-default';
					$watchedListText = 'Watch';
					$dataToggle = 'modal';
					$dataTarget = '#loginModal';
		        }
		        else
		        {
					$totalwatchedList = $this->db->pdoQuery("SELECT count(*) as totalWatchedListing FROM `tbl_watchlist` WHERE `userId` = ".$_SESSION['pickgeeks_userId']." AND watchListType = 'l' AND listingId = ".$listingId)->result();
					if($_SESSION['pickgeeks_userId'] == $this->userId)
					{
						$watchedListClass = 'hide';
						$watchedListText = '';
					}
					elseif($totalwatchedList['totalWatchedListing'] > 0)
					{
						$watchedListClass = 'btn-success';
						$watchedListText = 'Watched';
					}
					else
					{
						$watchedListClass = 'btn-default';
						$watchedListText = 'Watch';
					}
				}
				$placeBidOROffer =($listStatus=='open')?(($saleType == 'a')?'<a href="'.SITE_URL.'site_details/'.$listingSlug.'">Place Bid</a>':'<a href="'.SITE_URL.'site_details/'.$listingSlug.'">Make an offer</a>'):"<b>".ucfirst($listStatus)."</b>";
				$fieldListArr = array('%SITE_URL%'=>SITE_URL.'site_details/'.$listingSlug,
									'%SITE_TITLE%'=>($listingTypeId=='4')?$appName:displaySiteUrl($listingUrl),
									'%SELLER_NAME%'=>$totalUsers['userName'],
									'%LISTING_ADDED_DATE%'=>getRemainingDays($createdDate,date('Y-m-d H:i:s'),'remainDays'),
									'%TOTAL_PLACED_COMMENTS%'=>$totalListCommentPlaced['totalComment'].' Comments',
									"%LISTING_TYPE%" => getListingType($listingTypeId),
									'%TAGLINE%'=>$tagline,
									'%ESTABLISHED_DATE%'=>date('M Y',strtotime($liveDate)),
									'%MONTHLY_REVENUE%'=>$avrMonthlyRev,
									'%BID_PRICE%'=>$bidPrice,
									'%TOTAL_BID_PLACES%'=>$totalBids,
									'%WATCH_LIST_CLASS%'=>$watchedListClass,
									'%WATCH_LIST_TEXT%'=>$watchedListText,
									'%LISTING_ID%'=>$listingId,
									'%DATA_TOGGLE%'=>$dataToggle,
									'%DATA_TARGET%'=>$dataTarget,
									'%PLACE_BID_OR_OFFER%'=>$placeBidOROffer,
									'%LISTING_DELETED_LABLE%'=>$listingDeletedLable);
				$listingDetails .= str_replace(array_keys($fieldListArr), array_values($fieldListArr), $html_listing);
        	}
        }
        else
		{
			$html_listing = new MainTemplater(DIR_TMPL . "{$this->module}/no_feedbacks-sd.skd");
			$html_listing = $html_listing->compile();
			$listingDetails = "<div class='no-records'><i class='fa fa-exclamation-triangle'></i> ".NO_RECORDS_FOUND." !</div>";
		}
        $feedbacks = '';
        $allfeedbacks = $this->db->pdoQuery("SELECT *,tlr.userId as usersId,tlr.createdDate as feedbackCreatedDate,tl.createdDate as listingCreatedDate,tl.listingTypeId FROM `tbl_listing_rating` tlr
											LEFT JOIN tbl_listing tl ON tl.listingId = tlr.listingId
											LEFT JOIN tbl_users tu ON tlr.userId = tu.id
											WHERE tlr.userId = ".$this->userId)->results();
		$listingFeedback = $feedback_hide = '';
		$totalListingRating = $totalListingRatingCount = 0;
        if(count($allfeedbacks) > 0)
        {
        	foreach($allfeedbacks as $allfeedback)
        	{
        		$html_feedback = new MainTemplater(DIR_TMPL . "{$this->module}/feedbacks-sd.skd");
				$feedbacks = $html_feedback->compile();
        		if($allfeedback['isPossitive']=='p')
        		{
        			$isPossitive = 'plus';
        			$feedback_hide = '';
        		}
        		else
        		{
        			$isPossitive = 'minus';
        			$feedback_hide = 'hide';
        		}
        		$listingRating = $allfeedback['listingRating']*20;
        		if($listingRating!=0)
        		{
        			$totalListingRating = $totalListingRating + $listingRating;
        			$totalListingRatingCount ++;
        		}
        		$soldPrice = getTableValue('tbl_manage_order','price',array('listingId'=>$allfeedback['listingId']));
        		if($soldPrice == '')
        			$soldPrice = 0;
        		$purchaseDate = getTableValue('tbl_manage_order','purchaseDate',array('listingId'=>$allfeedback['listingId']));        		
        		$createdDate = date('Y-m-d H:i:s');
				$soldAgo = getRemainingDays($allfeedback['listDurationDate'],$allfeedback['listingCreatedDate']);
				$usersLink = SITE_URL.'users/'.$allfeedback['userName'];
				$buyerprofile =($allfeedback['profile_img'] != '')?SITE_APP_UPD.'profile/'.$allfeedback['profile_img']:SITE_APP_UPD.'profile/th2_no_user_image.png';
				$marginDeleteButton = $hideFeedbackUser = '';
				if($allfeedback['isDeleted']=='n'){
					$hideFeedbackUser = 'hide';
				}
				else
					$marginDeleteButton = 'marginDeleteButton';

				if($allfeedback['listingTypeId'] == 1 || $allfeedback['listingTypeId'] == 2)
					$lisitngType ='Website';
				elseif ($allfeedback['listingTypeId'] == 3)
					$lisitngType ='Domain';
				elseif ($allfeedback['listingTypeId'] == 4)
					$lisitngType ='App';

				$fieldfeedback = array('%LISTING_URL%'=>displaySiteUrl($allfeedback['listingUrl']),
										'%LISTING_LINK%'=>SITE_URL.'site_details/'.$allfeedback['listingSlug'],
										'%RATING_COMMENT%'=>filtering($allfeedback['listingRatingDesc']),
										'%IS_POSSITIVE%'=>$isPossitive,
										'%LISTING_RATTING%'=>$listingRating,
										'%FEEDBACK_HIDE%'=>$feedback_hide,
										'%SOLD_PRICE%'=>$soldPrice,
										'%SOLD_AGO%'=>$soldAgo,
										'%USER_NAME%'=>ucfirst($allfeedback['userName']),
										'%USER_LINK%'=>$usersLink,
										'%BUYER_PROFILE_IMAGE%'=>$buyerprofile,
										'%CREATED_DATE%'=>date(DATE_FORMAT,strtotime($allfeedback['feedbackCreatedDate'])),
										'%HIDE_FEEDBACK_USER%'=>$hideFeedbackUser,
										'%MARGIN_DELETE_BUTTON%'=>$marginDeleteButton,
										'%LISTING_TYPE%'=>$lisitngType);
				$listingFeedback .= str_replace(array_keys($fieldfeedback), array_values($fieldfeedback), $feedbacks);
			}
			$totalFeedbackReceive = '';
			if($totalListingRatingCount != 0)
				$totalFeedbackReceive = '(Recently '.number_format($totalListingRating/$totalListingRatingCount,2).'% positive)';
		} 
		else
		{
			$html_feedback = new MainTemplater(DIR_TMPL . "{$this->module}/no_feedbacks-sd.skd");
			$listingFeedback = $html_feedback->compile();
			$totalFeedbackReceive = NO_FEEDBACK_RECEIVED;
		}
		$totalPositiveResp = '';

		
		$Home_Page_Url=($HomePageUrl!='')?'<p><span class="sitepoint-datetime loc-icon" title="Sun, 16 Oct 2016 14:52:19 UTC"><i class="fa fa-calendar-times-o" aria-hidden="true"></i></span> <span>Home Page URL: <a href='.$HomePageUrl.' target="_blank">'.$HomePageUrl.'</a></span></p>':'';

		$hideStatus = ($totalUsers['isDeleted'] != 'y')?'hide':'';
		$userName=($HomePageUrl!='')?"<a href='".$HomePageUrl."' target='_blank'>".$userName."</a>":$userName;
		
		$fieldArr = array("%USER_PROFILE_IMAGE%","%USER_NAME%","%USER_FULL_NAME%",'%LOCATION%','%LAST_LOGIN%','%MEMBER_SINSE%','%TOTAL_COMMENT_PLACED%','%TOTAL_BIDS_PLACED%','%TOTAL_LISTIGN_PLACED%','%AVR_LISTING_SOLD%','%PHONE_VERIFY_STATUS%','%EMAIL_VERIFY_STATUS%','%FB_VERIFY_STATUS%','%TWITTER_VERIFY_STATUS%','%LINKED_IN_VERIFY_STATUS%','%FACEBOOK_LINK%','%TWITTER_LINK%','%LINKED_IN_LINK%','%WATCH_SELLER_CLASS%','%WATCH_SELLER_TEXT%','%SELLER_ID%','%LISTING_DETAILS%','%FEEDBACKS%','%TOTAL_FEEDBACK_RECEIVE%','%TOTAL_POSITIVE_RESP%','%SELLER_DATA_TARGET%','%SELLER_DATA_TOGGLE%','%HIDE_DELETE_USER%','%ABOUT_ME%','%HOME_PAGE_URL%');
		$replaceArr = array($profile,$userName,$user_full_name,$location,$lastLogin,$memberSinse,$totalCommentPlaced,$totalBidsPlaced,$totalListingPlaced,$avrListingSold,$PhoneVeriStatus,$emailVeriStatus,$fbVeriStatus,$twitterVeriStatus,$linkedInVeriStatus,$fbUserProfileVal,$twitterUserProfileVal,$linkedInUserProfileVal,$watchSellerClass,$watchSellerText,$this->userId,$listingDetails,$listingFeedback,$totalFeedbackReceive,$totalFeedbackReceive,$watchSellerdataTarget,$watchSellerdataToggle,$hideStatus,$totalUsers['aboutme'],$Home_Page_Url);
		$html = str_replace($fieldArr, $replaceArr, $html);		
		return $html;		
	}	
}
?>
