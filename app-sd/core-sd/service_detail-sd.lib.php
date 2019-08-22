<?php

class ServiceDetail {
	function __construct($module = "", $slug = 0, $token = "") {
		foreach ($GLOBALS as $key => $values) {
			$this->$key = $values;
		}
		$this->module = $module;
		$this->slug = $slug;
	}

  	public function getPageContent()
  	{
      global $sessUserId;
  		  $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/service_detail-sd.skd");
        $sub_content = $sub_content->compile();

        $query = $this->db->pdoQuery("SELECT s.*,c.".l_values('category_name')." as category_name,sub.".l_values('subcategory_name')." as subcategory_name,u.id as fid,u.profileImg,u.firstName,u.lastName,u.userSlug,u.professionalTitle,u.professionalOverview,u.freelancerLvl,u.location,s.featured 
          FROM tbl_services As s
          LEFT JOIN tbl_category As c ON c.id = s.servicesCategory
          LEFT JOIN tbl_subcategory As sub ON sub.id = s.servicesSubCategory
          LEFT JOIN tbl_users As u ON u.id = s.freelanserId
          where servicesSlug=? and s.isActive='y' and s.isDelete='n' and u.isActive='y' and u.isDeleted ='n'",array($this->slug))->result();
        if(empty($query)){
          $msgType = $_SESSION["msgType"] = disMessage(array('type' => 'err', 'var' => SERVICE_NOT_AVAILABLE));
          redirectPage(SITE_URL);
        }
        if($query['isApproved']!='a' || $query['isActive']!='y'){
          $msgType = $_SESSION["msgType"] = disMessage(array('type' => 'err', 'var' => SOMETHING_WENT_WRONG));
          redirectPage(SITE_URL."search/service/");
        }

        $saved_service_record = $this->db->pdoQuery("select * from tbl_saved_services where serviceId=? and userId=?",array($query['id'],$this->sessUserId))->affectedRows();
        $fav_service_record = $this->db->pdoQuery("select * from tbl_favorite_services where serviceId=? and customerId=?",array($query['id'],$this->sessUserId))->affectedRows();
        $report_status = $this->db->pdoQuery("select * from tbl_report where reportedId=? and reporterId=? and userId=?",array($query['id'],$this->sessUserId,$query['freelanserId']))->affectedRows();

        $serviceId = $this->serviceId($this->slug,'id');
        $service_image = $this->db->pdoQuery("select * from tbl_services_files where servicesId=? LIMIT 1",array($serviceId))->result();
        $service_img = SITE_SERVICES_FILE.$service_image['fileName'];

        $deleted_class = "";
        $deleted_css = "hide";
        if($query['isDelete']=="y"){
          $deleted_class = "hide";
          $deleted_css = "";
        }
        $service_res =  $this->db->pdoQuery("SELECT COUNT(DISTINCT(s.id)) as posted,
                                            COUNT(DISTINCT(so.id)) as orders,
                                            SUM(so.totalPayment) as amount,
                                            COUNT(DISTINCT (case when isActive='y' then s.id end)) as live
                                            FROM tbl_services as s 
                                            LEFT JOIN tbl_services_order as so ON(so.servicesId=s.id) 
                                            WHERE s.freelanserId=".$query['freelanserId'])->result();

        $date = date('M, Y',strtotime($query['servicesPostDate']));
        if($_SESSION["pickgeeks_userType"]=="Freelancer"){
          $review_link = SITE_URL."c/review";
        }else{
          $review_link = SITE_URL."f/review/".$query['userSlug'];
        }
        $chat_btn = ($sessUserId==$query['freelanserId'])?"hide":"";

        $array = array(
          "%ADDON_CLASS%" => ($this->addonDetail($query['id'])=='') ? 'hide':'',
          "%SERVICE_TITLE%" => filtering(ucfirst($query['serviceTitle'])),
        	"%SLIDER_IMG%" => $this->sliderImg($query['id'],filtering(ucfirst($query['serviceTitle']))),
          "%CATEGORY%" => $query['category_name'],
          "%SUB_CATEGORY%" => $query['subcategory_name'],
          "%NO_DELIVERY%" => $query['noDayDelivery'],
          "%NO_VIEWS%" => $this->views($query['id']),
          "%FREELANCER_IMG%" => getUserImage($query['freelanserId']),
          "%FREELANCER_NAME%" => (filtering(ucfirst($query['firstName']))." ".filtering(ucfirst($query['lastName']))),
          "%FREELANCER_PROFESSIONAL_TITLE%" => filtering($query['professionalTitle']),
          "%FREELANCER_ID%" => $query['freelanserId'],
          "%LEVEL%" => getUserExpLevel($query['freelancerLvl']),
          "%LOCATION%" => $query['location'],
          "%SHOW_CHAT_BTN%" => $chat_btn,
          "%RATING%" => getAvgUserReview($query['freelanserId'],'freelancer')*20,
          "%PROFESSIONAL_VIEW%" => filtering($query['professionalOverview']),
          "%SERVICE_REQ_DETAIL%"=> nl2br($query['requiredDetails']),
          "%DESC%"=> nl2br($query['description']),
          "%ADDON_DETAIL%" => $this->addonDetail($query['id']),
          "%JOINED_DATE%" => $date,
          "%POSTED_SRERVICE%" => $service_res["posted"],
          "%SRERVICE_ORDER%" => $service_res["orders"],
          "%TOTAL_AMOUNT%" => $service_res["amount"],
          "%SERVICE_PRICE%" => $query['servicesPrice'],
          "%SERVICE_PRICE_J%" => $query['servicesPrice'],
          "%SAVED_SERVICES%" => $this->saved_services($query['id']),
          "%SOLD_SERVICES%" => $this->sold_services($query['id']),
          "%FAVOURITE_SERVICES%" => $this->fav_services($query['id']),
          "%RESPONSE_TIME%" => $this->responseTime($query['freelanserId']),
          "%SAVED_SERVICES_ICON_CLASS%" => ($saved_service_record==0) ? 'fa fa-heart' : 'fa fa-heart',
          "%FAV_CLASS%" => ($fav_service_record==0) ? 'fa fa-heart-o' : 'fa fa-heart',
          "%SLUG%" => $this->slug,
          "%REPORT_CLASS%" => ($report_status==0) ? 'fa fa-flag-o' : 'fa fa-flag',
          "%BUY_NOW_BTN_CLASS%" => $this->buynowButton($query['freelanserId']),
          "%DISABLE_CLASS%" => ($this->sessUserId == $query['freelanserId']) ? 'not-active' : '',
          "%HIDE_CLASS%" => ($this->sessUserId == $query['freelanserId']) ? 'hide' : '',
          "%DELETED_CLASS%" => $deleted_class,
          "%DELETED_CSS%" => $deleted_css,
          "%SERVICE_IMG%" => $service_img,
          "%LINK%" => SITE_URL."service/".$this->slug,
          "%FEATURED_TAG_CLASS%" => ($query['featured'] == 'y' && $query['featured_payment_status'] == 'c') ? '' : 'hide',
          "%MSG_RIGHTS%" => (isset($this->sessUserId) && $this->sessUserId>0) ? (($this->sessUserType == 'Customer') ? '' : 'hide') : 'hide',
          "%REVIEW_LINK%" => $review_link,
          "%RELATED_SERVICES%" => $this->related_services($query['freelanserId'],$query['id']),
          "%FAQ_LIST%" => $this->faq_list()
      	);
        return str_replace(array_keys($array), array_replace($array), $sub_content);
    }
    public function buynowButton($freelanserId)
    {
      if(isset($this->sessUserId) && $this->sessUserId>0)
      {
        if($this->sessUserId == $freelanserId)
        {
          $btn = 'hide';
        }
        else
        {
          $btn = '';
        }
      }
      else
      {
        $btn = '';
      }
      return $btn;
    }
    public function sold_services($id)
    {
      $sold_services = $this->db->pdoQuery("select * from tbl_services_order where servicesId=? and paymentStatus=?  and serviceStatus=?",array($id,'c','c'))->affectedRows();
      return $sold_services;
    }
    public function saved_services($id)
    {
      $saved_services = $this->db->pdoQuery("select * from tbl_saved_services where serviceId=?",array($id))->affectedRows();
      return $saved_services;
    }
    public function fav_services($id)
    {
      $fav_services = $this->db->pdoQuery("select * from tbl_favorite_services where serviceId=?",array($id))->affectedRows();
      return $fav_services;
    }

    public function faq_list(){
      $data = "";
      $faq_res = $this->db->pdoQuery("select * from tbl_faq where isActive='y'")->results();
      if(!empty($faq_res)){
        foreach ($faq_res as $res) {
          $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/faq.php");
          $sub_content = $sub_content->compile();
          $array = array(
                  "%FAX_ID%" => $res["id"],
                  "%FAX_TITLE%" => $res[l_values('question')],
                  "%FAX_DESC%" => $res[l_values('ansDesc')]
                );
          $data .= str_replace(array_keys($array),array_replace($array), $sub_content);
        }
      }
      return $data;
    }
    public function related_services($id,$ser_id){
        $query = $this->db->pdoQuery("SELECT s.id as ser_id,s.serviceTitle as service_name,s.servicesSlug, s.services_image,s.servicesPrice,s.description,c.".l_values('category_name')." as category_name,u.id as fid, u.profileImg, u.firstName,u.lastName,u.userSlug,u.professionalTitle,u.professionalOverview,u.freelancerLvl,u.location,s.featured 
          FROM tbl_services As s
          JOIN tbl_category As c ON c.id = s.servicesCategory
          JOIN tbl_users As u ON u.id = s.freelanserId
          where freelanserId=? AND s.id!=? and s.isActive='y' and s.isDelete='n' and u.isActive='y' and u.isDeleted ='n' GROUP BY s.id",array($id,$ser_id))->results();
        
        $data = '';
        if(!empty($query))
        {
          foreach ($query as $value)
          {
            $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/related_services-sd.skd");
            $sub_content = $sub_content->compile();
            $image = getTableValue("tbl_services_files","fileName",array("servicesId"=>$value["ser_id"]));

            $url = SITE_URL."service/".$value["servicesSlug"];
            $service_res =  $this->db->pdoQuery("SELECT COUNT(DISTINCT(id)) as orders FROM tbl_services_order WHERE servicesId=".$value['ser_id'])->result();
            $fav_service_record = $this->db->pdoQuery("select * from tbl_favorite_services where serviceId=? and customerId=?",array($value['ser_id'],$this->sessUserId))->affectedRows();

            $array = array(
                  "%SERVICE_IMG%" => SITE_SERVICES_FILE.$image,
                  "%SLUG%" => filtering($value['servicesSlug']),
                  "%FAV_CLASS%" => ($fav_service_record==0) ? 'fa fa-heart-o' : 'fa fa-heart',
                  "%SERVICE_NAME%" => filtering($value['service_name']),
                  "%SERVICE_URL%" => $url,
                  "%CATEGORY_NAME%" => $value["category_name"],
                  "%SERVICE_PRICE%" => $value["servicesPrice"],
                  "%SERVICE_ORDER%" => $service_res["orders"],
                  "%FREELANCER_URL%" => SITE_URL."f/profile/".$value["userSlug"],
                  "%FREELANCER_IMG%" => getUserImage($value['fid']),
                  "%FREELANCER_NAME%" => (filtering(ucfirst($value['firstName']))." ".filtering(ucfirst($value['lastName']))),
                  "%FREELANCER_PROFESSIONAL_TITLE%" => filtering($value['professionalTitle']),
                  "%FREELANCER_ID%" => $value['fid'],
                  "%LEVEL%" => getUserExpLevel($value['freelancerLvl']),
                  "%LOCATION%" => $value['location'],
                  "%RATING%" => ROUND(getAvgUserReview($value['fid'],'freelancer')),
              );
            $data .= str_replace(array_keys($array),array_replace($array), $sub_content);
          }
        }
        else
        {
          $data .= '';
        }
        return $data;
    }

    public function responseTime($id)
    {

      $job_detail = $this->db->pdoQuery("SELECT * FROM `tbl_job_bids` WHERE userId = '".$id."' ")->results();

      $d = 0;$i=0;
      foreach ($job_detail as $value)
      {
        $now = date_create($value['createdDate']);
        $date = date_create($value['accept_reject_date']);
        $interval = $date->diff($now);
        //printr($interval,1);
        $days = $interval->format('%i');
        $d += $days;
        $i++;
      }
      $final = ($i!=0) ? ceil($d/$i) : 0;
      $final_result = '';
      if($final<1440)
      {
        if($final>60)
          $final_result = $final/60 ." minutes";
        else
          $final_result = $final." minutes";
      }
      else if($final>1440 && $final<43200)
      {
          $final_result = floor($final/1440)." days";
      }
      else if($final>43200)
      {
          $final_result = floor($final/1440)." Month";
      }
      //printr($final_result,1);
      return $final_result;
    }


    public function addonDetail($id)
    {
      //echo $id;exit;
      $query_data = $this->db->pdoQuery("select * from tbl_services_addon where services_id=?",array($id));
      $query = $query_data->results();
      $data = '';
      if($query_data->affectedRows()>0)
      {
        foreach ($query as $value)
        {
              $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/addOn_detail-sd.skd");
              $sub_content = $sub_content->compile();

              $array = array(
                    "%ADDON_TITLE%" => filtering($value['addonTitle']),
                    "%ADDON_DESC%" => filtering($value['addonDesc']),
                    "%DAYS%" => filtering($value['addonDayRequired']),
                    "%ADDON_PRICE%" => $value['addonPrice'],
                    "%ADDON_ID%" => $value['id'],
                    "%ADDON_PRICE_A%" => $value['addonPrice']
                );
             $data .= str_replace(array_keys($array),array_replace($array), $sub_content);
        }

      }
      else
      {
        $data .= '';
      }
      return $data;
    }
    public function sliderImg($id,$title)
    {
        $query = $this->db->pdoQuery("select * from tbl_services_files where servicesId=?",array($id))->results();
        $img = '';
        foreach ($query as $value)
        {
  	    	  $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/slider_img-sd.skd");
  	        $sub_content = $sub_content->compile();

            $array = array(
              "%IMG%" => SITE_SERVICES_FILE.$value['fileName'],
              "%TITLE%" => $title
              );
            $img .= str_replace(array_keys($array), array_replace($array), $sub_content);
        }
        return $img;
    }
    public function serviceView($id)
    {
        $query = $this->db->pdoQuery("select * from tbl_services_view where serviceId=? and userId=? and ipAddress=?",array($id,$this->sessUserId,get_ip_address()))->affectedRows();
        if($query==0)
        {
            $this->db->insert("tbl_services_view",array("serviceId"=>$id,"userId"=>$this->sessUserId,"ipAddress"=>get_ip_address(),"createdDate"=>date('Y-m-d H:i:s')));
        }
    }
    public function views($id)
    {
       $views = $this->db->pdoQuery("select * from tbl_services_view where serviceId=?",array($id))->affectedRows();
       return $views;
    }
    public function serviceId($slug,$field="id")
    {
      $service_detail = $this->db->pdoQuery("select ".$field." from tbl_services where servicesSlug=?",array($slug))->result();

      return $service_detail[$field];
    }
    public function reportService($data)
    {
        extract($data);
        $reportedId = $this->serviceId($slug);
        $report_check = $this->db->pdoQuery("select * from tbl_report where reportedId=? and reporterId=? and reportType=? and userId=?",array($reportedId,$this->sessUserId,'Service',$freelancerId))->affectedRows();
        if($report_check>0)
        {
            $msgType = $_SESSION["msgType"] = disMessage(array('type' => 'err', 'var' => YOUR_HAVE_ALREADY_REPORTED_TO_THIS_SERVICE));
                     
            redirectPage(SITE_URL."service/".$this->slug);
        }
        else
        {
            $this->db->insert("tbl_report",array("reportedId"=>$reportedId,"reportType"=>'Service',"userId"=>$freelancerId,"reporterId"=>$this->sessUserId,"reportMessage"=>$report_reason,"status"=>'Pen',"createdDate"=>date('Y-m-d H:i:s'),"ipAddress"=>get_ip_address()));
            $query = $this->db->pdoQuery("SELECT s.*,c.".l_values('category_name')." as category_name,sub.".l_values('subcategory_name')." as subcategory_name,u.id as fid,u.profileImg,u.firstName,u.lastName,u.userSlug,u.professionalTitle,u.professionalOverview,u.freelancerLvl,u.location,s.featured 
          FROM tbl_services As s
          LEFT JOIN tbl_category As c ON c.id = s.servicesCategory
          LEFT JOIN tbl_subcategory As sub ON sub.id = s.servicesSubCategory
          LEFT JOIN tbl_users As u ON u.id = s.freelanserId
          where servicesSlug=? and s.isActive='y' and s.isDelete='n' and u.isActive='y' and u.isDeleted ='n'",array($this->slug))->result();
            $msgType = $_SESSION["msgType"] = disMessage(array('type' => 'suc', 'var' => YOUR_REPORT_HAS_BEEN_SENT_SUCCESSFULLY));
                        $msg =  $query['firstName']." ".$query['lastName']." ".'Your service report has been sent successfully';

                $nm =  $this->db->insert("tbl_notification",array("userId"=>0,"message"=>$msg,"isRead"=>'y',"notificationType"=>'a',"createdDate"=>date('Y-m-d H:i:s')))->showQuery();



            redirectPage(SITE_URL."service/".$this->slug);
        }

    }

    public function serviceOrder($data)
    {
      if(isset($this->sessUserId) && $this->sessUserId>0)
      {
        $temp_order_check = $this->db->pdoQuery("select * from tbl_services_order_temp where customerId=?",array($this->sessUserId))->affectedRows();
        if($temp_order_check>0)
        {
          $this->db->delete("tbl_services_order_temp",array("customerId"=>$this->sessUserId));
        }

        extract($data);
        $serviceId = $this->serviceId($slug,"id");
        $serviceName = filtering($this->serviceId($slug,"serviceTitle"));
        $freelanserId = $this->serviceId($slug,"freelanserId");
        $customerId = $this->sessUserId;
        $addOn = trim(implode(",", $addOn),',');

        $addOnPrice = ($addOn !='') ? filtering($this->addOnAddDetail($addOn,'addonPrice')) : '0';
        $addOnDay = ($addOn !='') ? filtering($this->addOnAddDetail($addOn,'addonDayRequired')) : '0';
        $service_price = filtering($this->serviceId($slug,'servicesPrice'));
        $service_day = filtering($this->serviceId($slug,'noDayDelivery'));
        $totalPayment =  $addOnPrice + ($service_price*$quantity);
        $total_day = $service_day + $addOnDay;



        $this->db->insert("tbl_services_order_temp",array("servicesId"=>$serviceId,"freelanserId"=>$freelanserId,"customerId"=>$customerId,"orderDate"=>date('Y-m-d H:i:s'),"addOn"=>$addOn,"accept_status"=>'p',"quantity"=>$quantity,"totalPayment"=>$totalPayment,"totalDuration"=>$total_day,"serviceStatus"=>'no',"orderStatus"=>'p'));

        redirectPage(SITE_URL."confirm-order");

      }
      else
      {
          $_SESSION['last_page'] = "service/".$this->slug;
          $msgType = $_SESSION["msgType"] = disMessage(array('type' => 'suc', 'var' => PLEASE_LOGIN_TO_PUT_A_SERVICE_ORDER));
          redirectPage(SITE_URL."SignIn");
      }

    }
    public function addOnAddDetail($id,$field="addonPrice")
    {
      $query = $this->db->pdoQuery("select ".$field." from tbl_services_addon where id IN(".$id.")")->results();
      $data = 0;
      foreach ($query as $value)
      {
        $data += $value[$field];
      }
      return $data;
    }

    public function send_message($data)
    {

        extract($data);
        $this->db->insert("tbl_pmb",array("senderId"=>$this->sessUserId,"ReceiverId"=>$freelancerId,"message"=>$msg,"readStatus"=>'n',"createdDate"=>date('Y-m-d H:i:s'),"ipAddress"=>get_ip_address()));
        $msgType = $_SESSION["msgType"] = disMessage(array('type' => 'suc', 'var' => YOUR_MESSAGE_HAS_BEEN_SENT_SUCCESSFULLY));
        $senderDetail = getUser($this->sessUserId);
        $receiverDetail = getUser($freelancerId);
        /*site notification*/
        $senderNm = filtering(ucfirst($senderDetail['firstName']))." ".filtering(ucfirst($senderDetail['lastName']));
        $receiverNm = filtering(ucfirst($receiverDetail['firstName']))." ".filtering(ucfirst($receiverDetail['lastName']));
        $msg = "You have received new message from ".$senderNm;
        $detail_link = SITE_URL."pmb/".base64_encode($this->sessUserId);
        $this->db->insert("tbl_notification",array("userId"=>$freelancerId,"message"=>$msg,"detail_link"=>$detail_link,"isRead"=>'n',"notificationType"=>'f',"createdDate"=>date('Y-m-d H:i:s')));

        /*email notification*/
        if(notifyCheck('Notifymessage',$freelancerId)==1)
        {
              $arrayCont = array('USERNM'=>$receiverNm,'CUST_NM'=>$senderNm);
              $array = generateEmailTemplate('New_Message_From_Customer',$arrayCont);
              sendEmailAddress($receiverDetail['email'],$array['subject'],$array['message']);
        }
        redirectPage(SITE_URL."pmb/".base64_encode($freelancerId));
    }
}
 ?>


