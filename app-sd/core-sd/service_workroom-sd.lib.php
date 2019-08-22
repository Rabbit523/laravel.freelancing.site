<?php

class serviceWorkroom extends Home{
    function __construct($module = "", $slug = 0, $id="",$token = "") {
        foreach ($GLOBALS as $key => $values) {
            $this->$key = $values;
        }
        $this->module = $module;
        $this->slug = $slug;
        $this->id = $id;
    }

    public function getPageContent()
    {
        $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/service_workroom-sd.skd");
        $sub_content = $sub_content->compile();

        $service_detail = $this->db->pdoQuery("select cat.".l_values('category_name')." as category_name,s.*,o.service_order_data,o.deadline_accept_status,o.customerId As customer,o.freelanserId As freelancer,o.servicesId As sId,f.firstName AS freelancerFirstNm,f.lastName AS freelancerLastNm,c.firstName AS customerFirstNm,c.lastName AS customerLastNm,f.profileImg As freelancerImg,c.profileImg As customerImg,AVG(fr.startratings) As freelancerRate,AVG(cr.customerStarRating) As customerRate,f.location As freelancerLocation,c.location As customerLocation,o.totalDuration,o.quantity,o.serviceStatus,o.work_start_date,o.work_end_date,o.actual_work_start_date,o.actual_work_end_date,o.submitWork,o.paymentStatus,o.id As oId,o.addOn,o.totalPayment
            from tbl_services As s
            LEFT JOIN tbl_services_order As o ON o.servicesId = s.id
            LEFT JOIN tbl_users As f ON f.id = o.freelanserId
            LEFT JOIN tbl_users As c ON c.id = o.customerId
            LEFT JOIN tbl_reviews As fr ON fr.freelancerId = f.id
            LEFT JOIN tbl_reviews As cr ON cr.customerId = c.id
            LEFT JOIN tbl_category As cat ON cat.id = s.servicesCategory
            where o.id = '".$this->id."'
            ")->result();
        $sImg = getserviceImages($service_detail['sId'],1);
        $oservice = $service_detail['service_order_data'] =  json_decode($service_detail['service_order_data'],true);
        $service_detail['serviceTitle'] = !empty($oservice['serviceTitle']) ? $oservice['serviceTitle'] : $service_detail['serviceTitle'];

        $arr = ['serviceTitle','noDayDelivery','servicesPrice'];

        foreach ($arr as $tkey => $tvalue) {
            $service_detail[$tvalue] = !empty($oservice['service_data'][$tvalue]) ? $oservice['service_data'][$tvalue] : $service_detail[$tvalue];
        }

        $receiverId = $this->customerDetail($service_detail['oId']);
        $receiverDetail = getUser($receiverId);

        $review_query = $this->db->pdoQuery("select * from tbl_reviews where entityType=? and entityId=?",array('S',$this->id));
        $review_detail = $review_query->result();
        //printr($review_detail,1);
        $review_data = $review_query->affectedRows();

        $feedback_class = "hide";
        $receive_feedback_class = "hide";
        $given_feedback_class = "hide";

        if($service_detail['serviceStatus']=='c' && $service_detail['paymentStatus'] =='c')
        {
            if($this->sessUserType=="Freelancer")
            {
                $feedback_class = ($review_data>0 && $review_detail['custReview']!='') ? 'hide' : '';
                $receive_feedback_class = ($review_data>0 && $review_detail['review'] !='') ? '' : 'hide';
                $given_feedback_class = ($review_data>0 && $review_detail['custReview']!='') ? '' : 'hide';
            }
            else
            {
                $feedback_class = ($review_data>0 && $review_detail['review']!='') ? 'hide' : '';
                $receive_feedback_class = ($review_data>0 && $review_detail['custReview']!='') ? '' : 'hide';
                $given_feedback_class = ($review_data>0 && $review_detail['review']!='') ? '' : 'hide';
            }
        }
        $freelancername = filtering(ucfirst($service_detail['freelancerFirstNm']))." ".filtering(ucfirst($service_detail['freelancerLastNm']));
        $freelancer_img = getUserImage($service_detail['freelancer']);
        $freelancer_rate = $service_detail['freelancerRate']*20;
        $freelancer_location = $service_detail['freelancerLocation'];

        if($_SESSION['pickgeeks_userType'] == 'Freelancer'){
            $freelancername = filtering(ucfirst($service_detail['customerFirstNm']))." ".filtering(ucfirst($service_detail['customerLastNm']));
            $freelancer_img = getUserImage($service_detail['customer']);
            $freelancer_rate = $service_detail['customerRate']*20;
            $freelancer_location =  $service_detail['customerLocation'];
        }
        $hide_dispute_btn='hide';
        $dis_res = $this->dispute_rights($this->id);        
        if($dis_res==0 && $service_detail['serviceStatus']!='c' && $service_detail['serviceStatus']!='dsc'){
            $hide_dispute_btn="";
        }
        $status = service_status($service_detail['serviceStatus']);
        $array = array(
            "%CATEGORY%" => filtering(ucfirst($service_detail['category_name'])),
            "%FREELANCER_IMG%" => $freelancer_img,
            "%FREELANCER_NAME%" => $freelancername,
            "%FREELANCER_RATE%" => $freelancer_rate,
            "%FREELANCER_LOCATION%" => $freelancer_location,
            "%CUSTOMER_IMG%" => getUserImage($service_detail['customer']),
            "%CUSTOMER_NAME%" => filtering(ucfirst($service_detail['customerFirstNm']))." ".filtering(ucfirst($service_detail['customerLastNm'])),
            "%CUSTOMER_RATE%" => $service_detail['customerRate']*20,
            "%CUSTOMER_LOCATION%" => $service_detail['customerLocation'],
            "%SERVICES_IMG%" => $sImg[0],
            "%SERVICES_TITLE%" => filtering(ucfirst($service_detail['serviceTitle'])),
            "%FEATURED_CLASS%" => ($service_detail['featured'] == 'y') ? '': 'hide',
            "%DELIVERY_DAYS%" => $service_detail['noDayDelivery']." Days",
            "%DURATION%" => $service_detail['totalDuration'],
            "%SOLD_SERVICES%" => $this->sold_service($service_detail['sId']),
            "%QTY%" => $service_detail['quantity'],
            "%ORDER_STATUS%" => $status["status"],
            "%ORDER_CLASS%" => $status["class"],
            "%ADDON_DETAIL%" => $this->addOnDetail2($service_detail['oId']),
            "%ADDON_CLASS%" => ($this->addOnDetail2($service_detail['oId'])=='') ? 'hide' : '',
            "%DEADLINE_DATE_CLASS%" => ($service_detail['deadline_accept_status']=='') ? 'hide' : '',
            "%DEADLINE_DATE%" => ($service_detail['deadline_accept_status']=='') ? '' : date('dS F, Y',strtotime($service_detail['work_start_date'])) ." - ".date('dS F, Y',strtotime($service_detail['work_end_date'])),
            "%MESSAGES%" => $this->messages_list($this->id),
            "%SLUG%" => $this->slug,
            "%RECEIVER_NAME%" => filtering(ucfirst($receiverDetail['firstName']))." ".filtering(ucfirst($receiverDetail['lastName'])),
            "%RECEIVER_IMG%" => getUserImage($receiverId),
            "%DISPUTE_LIST%" => $this->dispute_list($this->id),
            "%RATING_LOOP%" => $this->review_loop(),
            "%REVIEW_ID%" => $review_detail['id'],
            "%ORDER_ID%" => $this->id,
            "%GIVEN_FEEDBACK_CLASS%" => $given_feedback_class,
            "%DISPUTE_RIGHTS%" => $hide_dispute_btn,
            "%HIDE_DISPUTE_BTN%" => $hide_dispute_btn,
            "%FILES%" => $this->file_list(),
            "%FEEDBACK_CLASS%" =>  $feedback_class,
            '%RECEIVE_FEEDBACK_CLASS%' => $receive_feedback_class,
            "%DISPUTE_CLASS%" => ($service_detail['serviceStatus'] == 'no' || $service_detail['submitWork'] == 'n') ? 'hide' : '',
            "%BUTTON%" =>$this->button_rights(),
            "%WORK_START_DATE%"=>  date('dS F, Y',strtotime($service_detail['actual_work_start_date'])),
            "%START_DATE%"=> ($service_detail['actual_work_start_date']=='0000-00-00 00:00:00') ? 'hide' : '',
            "%WORK_END_DATE%" => date('dS F, Y',strtotime($service_detail['actual_work_end_date'])),
            "%END_DATE%"=> ($service_detail['actual_work_end_date']=='0000-00-00 00:00:00') ? 'hide' : '',
            "%MESSAGE_RIGHTS%" => $this->message_rights(),
            "%SERVICE_CHARGE%" => CURRENCY_SYMBOL.$service_detail['totalPayment'],
            "%ORDER_ID%" => $service_detail['oId']
        );

        return str_replace(array_keys($array),array_values($array),$sub_content);
    }

    public function message_rights()
    {
        $order_detail = $this->orderDetail($this->id);
        if($order_detail['serviceStatus'] == 'c')
        {
            return 'hide';
        }
        else
        {
            return '';
        }
    }

    public function orderDetail($id)
    {
        $order_detail = $this->db->pdoQuery("select * from tbl_services_order where id=?",array($id))->result();
        return $order_detail;
    }

    public function button_rights()
    {
        $order_detail = $this->orderDetail($this->id);

        $button = '';
        if($this->sessUserType == "Freelancer")
        {

            $date = ($order_detail['actual_work_start_date']=='0000-00-00 00:00:00') ? '' : date('Y-m-d',strtotime($order_detail['actual_work_start_date']));
            $deadline_date = ($date!='') ? date('Y-m-d', strtotime($date. ' + '.$order_detail['totalDuration'].' days')) : '';

            if($order_detail['serviceStatus'] == 'no' && $order_detail['actual_work_start_date'] == '0000-00-00 00:00:00' && $order_detail['deadline_accept_status'] == 'a')
            {
                $button = "<button class='btn btn-system start_work' data-id='".$order_detail['id']."'>".START_SERVICE."</button>";
            }
            else if($deadline_date!='' &&  $order_detail['submitWork']=='n' && $order_detail['serviceStatus']=='ip')
            {
                $button = "<button class='btn btn-system' data-toggle='modal' data-target='#submit_work'>".SUBMIT_WORK."</button>";
            }

            else if($order_detail['submitWork']=='y' && $order_detail['paymentStatus']=='p' && $order_detail['serviceStatus']=='ip')
            {
                $button = "<button class='btn btn-system ask_payment'>".ASK_FOR_PAYMENT."</button>";
            }
            else if(date('Y-m-d')>$deadline_date && $order_detail['submitWork']=='y' && $order_detail['paymentStatus']=='p' && $order_detail['serviceStatus']=='p')
            {
                $button = '<div class="alert alert-info">'.PAYMENT_REQUEST_PENDING.'</div>';
            }

        }
        else
        {
            $days = $order_detail['totalDuration']+2;
            $dis_res = $this->dispute_rights($this->id);
            if($order_detail['serviceStatus']=='no')
            {
                $date = ($order_detail['orderDate']=='0000-00-00 00:00:00') ? '' : date('Y-m-d',strtotime($order_detail['orderDate']));
            }
            else
            {
                $date = ($order_detail['actual_work_start_date']=='0000-00-00 00:00:00') ? '' : date('Y-m-d',strtotime($order_detail['actual_work_start_date']));
            }
            $deadline_date = ($date!='') ? date('Y-m-d', strtotime($date. ' + '.$order_detail['totalDuration'].' days')) : '';


            if($deadline_date!='' && date('Y-m-d')>$deadline_date && $order_detail['submitWork']=='n' && $order_detail['serviceStatus']!='ar')
            {
                $button = "<button class='btn btn-system ask_refund'>".ASK_FOR_REFUND."</button>";
            }
            if($deadline_date!='' && date('Y-m-d')>$deadline_date && $order_detail['submitWork']=='n' && $order_detail['serviceStatus']=='ar' && $order_detail['paymentStatus']=='p')
            {
                $button = '<div class="alert alert-info">'.REFUND_REQUEST_PENDING.'</div>';
            }
            else if(($order_detail['serviceStatus'] == 'ip' || $order_detail['serviceStatus'] == 'p') && $order_detail['paymentStatus']=='p' && $order_detail['submitWork']=='y' && $dis_res==0)
            {
                $button = "<button class='btn btn-system pay_to_freelancer'>".PAY_TO_FREELANCER."</button>";
            }
        }

        return $button;
    }

    public function dispute_rights($serviceID)
    {
        $data = $this->db->pdoQuery("select * from tbl_dispute where type='S' AND entityId=?",array($serviceID))->affectedRows();
        return $data;
    }
    public function review_loop()
    {
        $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/give_review_loop-sd.skd");
        $sub_content = $sub_content->compile();
        if($this->sessUserType == "Customer")
        {
            $array = array(
                "%RATE1%" => PUNCTUALITY,
                "%RATE2%" => WORK_CLARIFICATION,
                "%RATE3%" => EXPERTISE,
                "%RATE4%" => COMMUNICATION,
                "%RATE5%" => WORK_QUALITY,
                "%RATE5_CLASS%" => '',
                "%USER_TYPE%" => 'F'
            );
        }
        else
        {
            $array = array(
                "%RATE1%" => REQUIREMENT_CLARIFICATION,
                "%RATE2%" => ON_TIME_PAYMENT,
                "%RATE3%" => ON_TIME_RESPONSE,
                "%RATE4%" => COMMUNICATION,
                "%RATE5%" => AVERAGE_STAR_RATING,
                "%RATE5_CLASS%" => 'hide',
                "%USER_TYPE%" => 'C'
            );
        }
        return str_replace(array_keys($array), array_replace($array), $sub_content);
    }

    public function dispute_list($id)
    {
        $query = $this->db->pdoQuery("select d.*,u.firstName,u.lastName,u.userType from tbl_dispute As d
            LEFT JOIN tbl_users As u ON u.id = d.disputerId
            where d.type=? and d.entityId=? and (d.disputerId = ? OR d.disputedId=?)",array('S',$id,$this->sessUserId,$this->sessUserId))->results();
        $data = '';
        if(count($query)>0)
        {
            $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/service_dispute-sd.skd");
            $sub_content = $sub_content->compile();
            foreach ($query as $key => $value) {
                $array = array(
                    "%DISPUTER_NAME%" => filtering(ucfirst($value['firstName']))." ".filtering(ucfirst($value['lastName'])),
                    "%DISPUTER_TYPE%" => ($value['userType'] == 'F') ? 'Freelancer' : 'Customer',
                    "%REASON%" => filtering($value['disputeReason']),
                    "%DESC%" => filtering($value['disputeDesc']),
                    "%DISPUTE_STATUS%" => ($value['status'] == 'P') ? 'Pending':'Solved',
                    "%DISPUTE_DATE%"=> date('dS F,Y H:i:s',strtotime($value['insertedDate']))
                );
                $data .= str_replace(array_keys($array), array_replace($array), $sub_content);
            }
        }
        else
        {
            $data .= "<span class='no-records'><i class='fa fa-exclamation-triangle'></i>".NO_DISPUTE_RECORD."</span>";
        }
        return $data;
    }
    public function customerDetail($id)
    {
        $service = $this->db->pdoQuery("select * from tbl_services_order where id=?",array($id))->result();
        if($service['freelanserId'] == $this->sessUserId)
        {
            $receiverId = $service['customerId'];
        }
        else
        {
            $receiverId = $service['freelanserId'];
        }
        return $receiverId;
    }

    public function file_list()
    {
        $query = $this->db->pdoQuery("select * from tbl_messages where entityId=? and entityType=? and messageType=?",array($this->id,'S','file'))->results();
        $data = '';
        foreach ($query as $value)
        {
            $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/files_loop-sd.skd");
            $sub_content = $sub_content->compile();
            $ext = explode(".",$value['fileName']);
            $type = (string)(end($ext));

            if ($type=='pdf' || $type=='PDF')
            {
                $img_src = SITE_UPD."pdf.png";
            }
            else if($type=='doc' || $type=='docx' || $type=='DOC'|| $type=='DOCX')
            {
                $img_src = SITE_UPD."doc.png";
                $type = 'doc';
            }
            else
            {
                $img_src = SITE_WORKROOM.$value['fileName'];
            }
            $array = array(
                "%EXT%"=> $type,
                "%IS_WORK%"=> '',
                "%LINK%"=> SITE_WORKROOM.$value['fileName'],
                "%IMG%"=> "<img src='".$img_src."'>",
                "%IMAGE_NAME%"=> $value['fileName'],
                "%TIME%"=> date('dS F,Y',strtotime($value['createdDate']))
            );
            $data .= str_replace(array_keys($array), array_values($array), $sub_content);
        }

        $order_detail = $this->orderDetail($this->id);
        if($order_detail['submitWork']=='y')
        {
            $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/files_loop-sd.skd");
            $sub_content = $sub_content->compile();
            

            $ext = explode(".",$order_detail['submitWorkFile']);
            $type = (string)(end($ext));

            if ($type=='pdf' || $type=='PDF')
            {
                $img_src = SITE_UPD."pdf.png";
            }
            else if($type=='doc' || $type=='docx' || $type=='DOC'|| $type=='DOCX')
            {
                $img_src = SITE_UPD."doc.png";
                $type = 'doc';
            }
            else
            {
                $img_src = SITE_WORK_FILES.$order_detail['submitWorkFile'];
            }

            $array1 = array(
                "%EXT%"=> $type,
                "%IS_WORK%"=> 'work_file',
                "%LINK%"=> SITE_WORK_FILES.$order_detail['submitWorkFile'],
                "%IMG%"=> "<img src='".$img_src."'>",
                "%IMAGE_NAME%"=> $order_detail['submitWorkFile']."<p>".filtering($order_detail['submitWorkMessage'])."</p>",
                "%TIME%"=> date('dS F,Y',strtotime($order_detail['submitWorkDate']))
            );
            $data .= str_replace(array_keys($array1), array_values($array1), $sub_content);
        }

        return $data;
    }

    public function messages_list($id)
    {
        $data = $this->db->pdoQuery("select * from tbl_messages where entityId=? and entityType=? and (deleteUser IS NULL OR (NOT FIND_IN_SET(".$this->sessUserId.",deleteUser))) ",array($id,'S'))->results();
        $loop_data = '';

        $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/service_messages-sd.skd");
        $sub_content = $sub_content->compile();
        if($_SESSION['pickgeeks_userType'] == 'Freelancer'){
            $msg = YOU_CAN_CONTACT_USER ;
        }else{
            $msg = PLEASE_SUBMIT_DOCUMENT_RELATED_TO_THIS_ORDER;
        }

        $array = array
        (
            "%MESSAGE%" => $msg,
            "%TIME%" => '',
            "%USER_CLASS%" => 'detail_notice user',
            "%MSG_ID%" => '123',
            '%NO_DELETE%' => 'hide'
        );
        $loop_data .= str_replace(array_keys($array), array_replace($array), $sub_content);

        if(count($data)>0)
        {

            foreach ($data as $value)
            {
                $img_src = '';
                if($value['messageType']=='file')
                {
                    $ext = explode(".", $value['fileName']);
                    $type = (string)$ext[1];

                    if ($type=='pdf' || $type == 'PDF')
                    {
                        $img_src = SITE_UPD."pdf.png";
                    }
                    else if($type=='doc' || $type=='docx' || $type=='DOC' || $type=='DOCX')
                    {
                        $img_src = SITE_UPD."doc.png";
                    }
                    else
                    {
                        $img_src = SITE_WORKROOM.$value['fileName'];
                    }
                    $link = SITE_WORKROOM.$value['fileName'];
                    $msg = "<a href='".$link."' download class='attch-img'><img src='".$img_src."'></a>";
                }
                else
                {
                    $msg = filtering($value['message']);
                }
                $array = array
                (
                    "%MESSAGE%" => $msg,
                    "%TIME%" => (date('Y-m-d') == date('Y-m-d',strtotime($value['createdDate']))) ? date('H:i:s',strtotime($value['createdDate'])) : date('dS F,Y H:i:s',strtotime($value['createdDate'])),
                    "%USER_CLASS%" => ($this->sessUserId == $value['senderId']) ? 'user' : 'customer',
                    "%MSG_ID%" => $value['id'],
                    '%NO_DELETE%' => ''
                );

                $loop_data .= str_replace(array_keys($array), array_replace($array), $sub_content);
            }
        }
        else
        {
           
        }

        return $loop_data;
    }
    public function get_addon($id)
    {
        if($id!='')
        {
            $data = $this->db->pdoQuery("select * from tbl_services_addon where id IN(".$id.") ")->results();

            foreach ($data as $value) {

                $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/service_addOn-sd.skd");
                $sub_content = $sub_content->compile();

                $array = array
                (
                    "%ADDON_TITLE%" => filtering(ucfirst($value['addonTitle'])),
                    "%DAYS%" => $value['addonDayRequired'],
                    "%ADDON_PRICE%" => $value['addonPrice']."<span>".CURRENCY_SYMBOL."</span>"
                );
                $content = str_replace(array_keys($array), array_values($array), $sub_content);
            }
        }
        else
        {
            $content = '';
        }
        return $content;
    }


    public function addOnDetail2($orderId,$type = '')
    {
        $data = '';
        $query = $this->db->pdoQuery("select * from tbl_services_order where id = ?",[$orderId])->result();
        $oaddons = json_decode($query['service_order_data'],true);
        $oaddons = !empty($oaddons['service_addons_data']) ? $oaddons['service_addons_data'] : [];

        if($type == 'counter'){
            return count($oaddons);
        }else{

            $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/service_addOn-sd.skd");
            $sub_content = $sub_content->compile();
            foreach ($oaddons as $value) {
                $array = array(
                    "%ADDON_TITLE%" => $value['addonTitle'],
                    "%DAYS%" => $value['addonDayRequired'],
                    "%ADDON_PRICE%" => $value['addonPrice']."<span>".CURRENCY_SYMBOL."</span>"
                );
                $data .= str_replace(array_keys($array), array_values($array), $sub_content);
            }
        }
        return $data;
    }


    public function sold_service($id)
    {

        $data = $this->db->pdoQuery("select count(id) As soldServices from tbl_services_order where paymentStatus='c' and serviceStatus='c' and servicesId='".$id."' ")->result();

        return $data['soldServices'];
    }

    public function saveDisputeData($data)
    {
        extract($data);
        $userDetail = $this->orderDetail($this->id);
        $disputedId = ($userDetail['freelanserId'] == $this->sessUserId) ? $userDetail['customerId'] : $userDetail['freelanserId'];
        $this->db->insert("tbl_dispute",array("disputerId"=>$this->sessUserId,"disputedId"=>$disputedId,"type"=>'S',"entityId"=>$this->id,"disputeReason"=>$reason,"disputeDesc"=>$description,"insertedDate"=>date('Y-m-d H:i:s'),"status"=>'P',"ipAddress"=>get_ip_address()));

        $this->db->update("tbl_services_order",array("serviceStatus"=>'ud'),array("id"=>$this->id));


        $disputerDetail = getUser($this->sessUserId);
        $disputedDetail = getUser($disputedId);
        $serviceDetail = $this->db->pdoQuery("select s.serviceTitle,s.freelanserId,s.servicesSlug, s.id as sID,o.id As orderId from tbl_services_order As o
            LEFT JOIN tbl_services As s ON s.id = o.servicesId
            where o.id=?",array($this->id))->result();

        $diputername = filtering(ucfirst($disputerDetail['firstName']))." ".filtering(ucfirst($disputerDetail['lastName']));
        $diputedname = filtering(ucfirst($disputedDetail['firstName']))." ".filtering(ucfirst($disputedDetail['lastName']));
        $link1 = SITE_URL."f/profile/".$disputerDetail['userSlug'];
        $link2 = SITE_URL."c/profile/".$disputedDetail['userSlug'];
        $link3 = SITE_URL."service/".filtering($serviceDetail['serviceTitle']);

        $msg1 = $disputerDetail['userName'].' has raised a dispute against you.';
        $nlink = SITE_URL.'service/workroom/'.base64_encode($serviceDetail['orderId']).'/'.$serviceD.htetail['servicesSlug'];
        $tp = $_SESSION['pickgeeks_userType'] == 'Customer' ? 'f' : 'c';
        notify($tp,$disputedId,$msg1,$nlink);

        $msg2 = $disputerDetail['userName'].' has created a dispute.';
        $nlink2 = SITE_ADMIN_URL.'units-sd/dispute_service-sd/';
        notify('a',0,$msg2,$nlink2);
        //die;

        $diputer_link = "<a href='".$link1."'>".$diputername."</a>";
        $diputed_link = "<a href='".$link2."'>".$diputedname."</a>";
        $service_link = "<a href='".$link3."'>".filtering($serviceDetail['serviceTitle'])."</a>";
        $admin_link = SITE_ADMIN_URL."dispute_service-sd";
        $admin_link = "<a href='".$admin_link."'>Admin Link</a>";

        $disputerTyp = ($disputerDetail['userType'] == 'f') ? 'Freelancer' : 'Customer';
        $disputedType = ($disputedDetail['userType'] == 'f') ? 'Freelancer' : 'Customer';

        /*send mail to disputed person*/
        $arrayCont = array('greetings'=>"There!",'USER'=>$diputedname,"SERVICE"=>filtering($serviceDetail['serviceTitle']),"CREATOR_NM"=>$diputername,"REASON"=>$reason,"DESC"=>$description);
        $array = generateEmailTemplate('user_raise_dipute',$arrayCont);
        sendEmailAddress($disputedDetail['email'],$array['subject'],$array['message']);

        /*send mail to admin*/
        if(notifyCheck('NotifyDisputeCreateCustomer',$serviceDetail['freelanserId'])==1)
        {
            $arrayCont = array('greetings'=>"There!",'DISPUTER'=>$diputedname,"USERTYPE"=>$disputerType,"DISPUTEDUSER"=>$diputername,"DISPUTEDTYPE"=>$disputedType,"SERVICE_NM"=>filtering($serviceDetail['serviceTitle']),"DISPUTER_EMAIL"=>$disputerDetail['email'],"DISPUTED_EMAIL"=>$disputedDetail['email'],"REASON"=>$reason,"DESCRIPTION"=>$description,"LINK"=>$admin_link);
            $array = generateEmailTemplate('dipute_generate_initimate_to_admin',$arrayCont);
            sendEmailAddress(ADMIN_EMAIL,$array['subject'],$array['message']);
        }

        $msgType = $_SESSION["msgType"] = disMessage(array('type'=>'suc','var'=>DISPUTE_HAS_BEEN_GENERATED_SUCCESSFULLY));
        redirectPage(SITE_URL."service/workroom/".base64_encode($this->id)."/".$this->slug);

    }
    public function saveReviewData($data)
    {
        extract($data);

        $orderDetail = $this->orderDetail($this->id);
        $review_query = $this->db->pdoQuery("select * from tbl_reviews where entityId=? and entityType=? and customerId=? and freelancerId=?",array($this->id,'S',$orderDetail['customerId'],$orderDetail['freelanserId']));
        $review_record = $review_query->affectedRows();
        $review_row = $review_query->result();

        if($userType == 'F')
        {
            $avg_rate = ROUND(($rate1Val+$rate2Val+$rate3Val+$rate4Val+$rate5Val)/5);
            $array = array("entityId"=>$this->id,"customerId"=>$orderDetail['customerId'],"freelancerId"=>$orderDetail['freelanserId'],"review" =>filtering($review),"punctality"=>$rate1Val,"workClarification"=>$rate2Val,"expertise"=>$rate3Val,"communication"=>$rate4Val,"workQuality"=>$rate5Val,"createdDate"=>date('Y-m-d H:i:s'),"startratings"=>(int)$avg_rate);
            $customerDetail = getUser($orderDetail['customerId']);
            $user = filtering(ucfirst($customerDetail['firstName']));
            $userId = $orderDetail['customerId'];
            $notificationType = 'f';
        }
        else
        {
            $avg_rate = ROUND(($rate1Val+$rate2Val+$rate3Val+$rate4Val)/4);
            $array = array("entityId"=>$this->id,"customerId"=>$orderDetail['customerId'],"freelancerId"=>$orderDetail['freelanserId'],"custReview"=>filtering($review),"reqClarification"=>$rate1Val,"onTimePayment"=>$rate2Val,"onTimeResponse"=>$rate3Val,"custComm"=>$rate4Val,"customerCreatedDate"=>date('Y-m-d H:i:s'),"customerStarRating"=>(int)$avg_rate);
            $freelancerDetail = getUser($orderDetail['freelanserId']);
            $user = filtering(ucfirst($customerDetail['firstName']));
            $userId = $orderDetail['freelanserId'];
            $notificationType = 'c';
        }
        if($review_record>0)
        {
            $this->db->update("tbl_reviews",$array,array("id"=>$review_row['id']));
        }
        else
        {
            $this->db->insert("tbl_reviews",$array);
        }
        $serviceDetail = $this->db->pdoQuery("select * from tbl_services where id=?",array($orderDetail['servicesId']))->result();
        $msg = $user." has given review for service - ".filtering(ucfirst($serviceDetail['serviceTitle']));
        $link = SITE_URL."service/workroom/".base64_encode($this->id)."/".$this->slug;
        $this->db->insert("tbl_notification",array("userId"=>$userId,"message"=>$msg,"detail_link"=>$link,"isRead"=>'n',"notificationType"=>$notificationType,"createdDate"=>date('Y-m-d H:i:s')));

        /*send email notification*/

        if(notifyCheck('NotifyCustomerReview',$serviceDetail['freelanserId'])==1)
        {
            $receiverDetail = getUser($serviceDetail['freelanserId']);

            $receiverNm = ucfirst(filtering($receiverDetail['firstName']))." ".ucfirst(filtering($receiverDetail['lastName']));
            $arrayCont = array('USERNM'=>$receiverNm,'SERVICE'=>filtering(ucfirst($serviceDetail['serviceTitle'])),"CUSTOMER"=>$user);
            $array = generateEmailTemplate('customer_leaves_review',$arrayCont);
            sendEmailAddress($receiverDetail['email'],$array['subject'],$array['message']);
        }

        $msgType = $_SESSION["msgType"] = disMessage(array('type'=>'suc','var'=>YOUR_REVIEW_HAS_BEEN_SUBMITTED_SUCCESSFULLY));
        redirectPage(SITE_URL."service/workroom/".base64_encode($this->id)."/".$this->slug);

    }
    public function reviewRating($id,$type)
    {
        $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/given_review_loop-sd.skd");
        $sub_content = $sub_content->compile();

        $review_query = $this->db->pdoQuery("select * from tbl_reviews where id=?",array($id));
        $value = $review_query->result();

        $review_rows = $review_query->affectedRows();
        $data = '';
        if($review_rows>0)
        {
            if($this->sessUserType == 'Customer')
            {
                if($type == 'login')
                {
                    $user = $value['freelancerId'];
                }
                else
                {
                    $user = $value['customerId'];
                }
            }
            else
            {
                if($type == 'login')
                {
                    $user = $value['customerId'];
                }
                else
                {
                    $user = $value['freelancerId'];
                }
            }

            if($value['freelancerId'] == $user)
            {
                $array = array(
                    "%RATE1%" => PUNCTUALITY,
                    "%RATE2%" => WORK_CLARIFICATION,
                    "%RATE3%" => EXPERTISE,
                    "%RATE4%" => COMMUNICATION,
                    "%RATE5%" => WORK_QUALITY,
                    "%RATE5_CLASS%" => '',
                    "%USER_TYPE%" => 'F',
                    "%RATE1_VAL%" => $value['punctality']*20,
                    "%RATE2_VAL%" => $value['workClarification']*20,
                    "%RATE3_VAL%" => $value['expertise']*20,
                    "%RATE4_VAL%" => $value['communication']*20,
                    "%RATE5_VAL%" => $value['workQuality']*20,
                    "%REVIEW%" => ($value['review'] == '') ? REVIEW_NOT_GIVEN : $value['review'],
                    "%AVG%" => $value['startratings']*20,
                    "%REVIEW_TIME%" => ($value['createdDate']=='0000-00-00 00:00:00') ? '' : date('dS F,Y',strtotime($value['createdDate']))
                );
            }
            else
            {
                $array = array(
                    "%RATE1%" => REQUIREMENT_CLARIFICATION,
                    "%RATE2%" => ON_TIME_PAYMENT,
                    "%RATE3%" => ON_TIME_RESPONSE,
                    "%RATE4%" => COMMUNICATION,
                    "%RATE5%" => AVERAGE_STAR_RATING,
                    "%RATE5_CLASS%" => 'hide',
                    "%USER_TYPE%" => 'C',
                    "%RATE1_VAL%" => $value['reqClarification']*20,
                    "%RATE2_VAL%" => $value['onTimePayment']*20,
                    "%RATE3_VAL%" => $value['onTimeResponse']*20,
                    "%RATE4_VAL%" => $value['custComm']*20,
                    "%AVG%" => $value['customerStarRating']*20,
                    "%REVIEW%" => ($value['custReview'] == '') ? REVIEW_NOT_GIVEN : $value['custReview'],
                    "%REVIEW_TIME%" => ($value['customerCreatedDate']=='0000-00-00 00:00:00') ? '' : date('dS F,Y',strtotime($value['customerCreatedDate']))
                );
            }
            $data .= str_replace(array_keys($array), array_values($array), $sub_content);
        }
        else
        {
            $data .= '';
        }
        return $data;
    }

    public function saveSubmitWork($data,$files)
    {
        extract($data);
        $file_name = uploadFile($_FILES['workFile'], DIR_WORK_FILES,SITE_WORK_FILES);

        $id = base64_decode($id);
        $this->db->update("tbl_services_order",array("submitWork"=>'y',"submitWorkMessage"=>filtering($submitWorkMsg),"submitWorkFile"=>$file_name['file_name'],"submitWorkDate"=>date('Y-m-d H:i:s')),array("id"=>$id));

        $service_detail = $this->db->pdoQuery("select s.serviceTitle,s.servicesSlug,f.userSlug,f.firstName,f.lastName,o.totalDuration,c.email,c.id from tbl_services_order As o
            LEFT JOIN tbl_users As f ON f.id = o.freelanserId
            LEFT JOIN tbl_users As c ON c.id = o.customerId
            LEFT JOIN tbl_services As s ON s.id = o.servicesId
            where o.id=?
            ",array($id))->result();



        /*freelancer mail*/

        $user_link = SITE_URL."f/profile/".$service_detail['userSlug'];
        $service_link = SITE_URL."service/".$service_detail['servicesSlug'];
        $freelancerNm = filtering(ucfirst($service_detail['firstName']))." ".filtering(ucfirst($service_detail['lastName']));
        $freelancerLink = "<a href='".$user_link."'>".$freelancerNm."</a>";
        $service_title = "<a href='".$service_link."'>".$service_detail['serviceTitle']."</a>";
        $login_link = "<a href='".SITE_URL.'SignIn'."'>Login</a>";

        $arrayCont = array('greetings'=>"There!",'FREELANCER_NM'=>$freelancerLink,"SERVICE_NM"=>$service_title,"LOGIN"=>$login_link);

        $array = generateEmailTemplate('freelancer_submitted_work',$arrayCont);
        sendEmailAddress($service_detail['email'],$array['subject'],$array['message']);

        $workroom_link = SITE_URL."service/workroom/".base64_encode($this->id)."/".$this->slug;
        $msg = $freelancerNm ." has submitted the work for service - ".$service_detail['serviceTitle'];

        //print_r($msg);
        //die;
        $this->db->insert("tbl_notification",array("userId"=>$service_detail['id'],"message"=>$msg,"detail_link"=> $workroom_link ,"isRead"=>'n',"notificationType"=>'c',"createdDate"=>date('Y-m-d H:i:s')));
        $msgType = $_SESSION["msgType"] = disMessage(array('type'=>'suc','var'=>YOU_WORK_HAS_BEEN_SUBMITTED_SUCCESSFULLY));
        redirectPage(SITE_URL."service/workroom/".base64_encode($this->id)."/".$this->slug);


    }

}


?>


