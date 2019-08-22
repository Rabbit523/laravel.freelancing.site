<?php

class FreelancerServiceOrder {
	function __construct($module = "", $id = 0, $token = "",$search_array= array()) {
		foreach ($GLOBALS as $key => $values) {
			$this->$key = $values;
		}
		$this->module = $module;
		$this->id = $id;
        $this->search_array = $search_array;
	}

  	public function getPageContent()
  	{
        $status = (array_key_exists('status',$this->search_array)) ? urldecode($this->search_array['status']) : '';
  		$sub_content = new MainTemplater(DIR_TMPL . $this->module . "/freelancer_services_order-sd.skd");
        $sub_content = $sub_content->compile();

        $new_order_status = ($status == 'new_order' ? 'checked' : '');
        $in_progress_status = ($status == 'in_progress' ? 'checked' : '');
        $ask_refund_status = ($status == 'ask_refund' ? 'checked' : '');
        $closed_status = ($status == 'closed' ? 'checked' : '');
        $payment_pending_status = ($status == 'payment_pending' ? 'checked' : '');
        $completed_another_status = ($status == 'completed' ? 'checked' : '');
        $under_dispute_status = ($status == 'under_dispute' ? 'checked' : '');
        $dispute_solved_status = ($status == 'dispute_solved' ? 'checked' : '');
        $dispute_solved_closed_status = ($status == 'dispute_solved_closed' ? 'checked' : '');

        $where = "so.freelanserId ='".$this->sessUserId."' ";
        if(isset($this->search_array['status']))
        {
            $where .= $this->condition($this->search_array['status']);
        }
        $loda_data = $this->db->pdoQuery("select s.*,so.*,c.".l_values('category_name')." as category_name,sub.".l_values('subcategory_name')." as subcategory_name,u.firstName,u.lastName from tbl_services_order As so
            JOIN tbl_services As s ON s.id = so.servicesId
            JOIN  tbl_category As c ON c.id = s.servicesCategory
            JOIN tbl_subcategory As sub ON sub.id = s.servicesSubCategory
            JOIN tbl_users As u ON u.id = so.customerId
            where ".$where)->affectedRows();

        $load_class = ($loda_data>10) ? '' : 'hide';

        return str_replace(array("%SUB_HEADER_CONTENT%","%LOOP_DATA%","%LOAD_CLASS%","%NEWORDER_STATUS%","%INPRO_STATUS%","%AR_STATUS%","%CLOSED_STATUS%","%PENDING_STATUS%","%COMP_STATUS%","%UD_STATUS%","%DS_STATUS%","%DSC_STATUS%"), array(subHeaderContent("f/services-order"),$this->services_order_loop($this->search_array),$load_class,$new_order_status,$in_progress_status,$ask_refund_status,$closed_status,$payment_pending_status,$completed_another_status,$under_dispute_status,$dispute_solved_status,$dispute_solved_closed_status), $sub_content);
    }

    public function services_order_loop($search_array='',$pageNo=1)
    {
    	$num_rec_per_page=10;
	    $start_from = ($pageNo-1) * $num_rec_per_page;

	    $where = "so.freelanserId ='".$this->sessUserId."' ";
        
        if(isset($search_array['status']))
        {
            $where .= $this->condition($search_array['status']);
            
        }
	    $query = $this->db->pdoQuery("select s.*,so.*,so.id As orderId,c.".l_values('category_name')." as category_name,sub.".l_values('subcategory_name')." as subcategory_name,u.firstName,u.lastName 
            FROM tbl_services_order As so
	    	JOIN tbl_services As s ON s.id = so.servicesId
	    	JOIN  tbl_category As c ON c.id = s.servicesCategory
	    	JOIN tbl_subcategory As sub ON sub.id = s.servicesSubCategory
	    	JOIN tbl_users As u ON u.id = so.customerId
            where ".$where." ORDER BY so.id DESC LIMIT ".$start_from.",".$num_rec_per_page)->results();
        $data = '';
        if(count($query)>0)
        {   
    	    foreach ($query as $value)
    	    {
    	    	$sub_content = new MainTemplater(DIR_TMPL .$this->module . "/services_data-sd.skd");
    		    $sub_content = $sub_content->compile();

                $deadline_add_button = ($value['work_start_date'] == '0000-00-00 00:00:00' && $value['work_end_date'] == '0000-00-00 00:00:00' && $value['serviceStatus']!='ip') ? '' : 'hide';
                $deadline_edit_button = (($value['work_start_date'] != '0000-00-00 00:00:00' && $value['work_end_date'] != '0000-00-00 00:00:00' && $value['deadline_accept_status']=='r')) ? '' : 'hide';
                $deadline_detail_button = ($value['work_start_date'] == '0000-00-00 00:00:00' && $value['work_end_date'] == '0000-00-00 00:00:00') ? 'hide' : '';
                $work_start_button = ($value['actual_work_start_date'] == '0000-00-00 00:00:00' && $value['deadline_accept_status']=='a' && $value['serviceStatus']=='no') ? '' : 'hide';
                $deadline_btn_class = ($value['work_start_date'] == '0000-00-00 00:00:00' && $value['work_end_date'] == '0000-00-00 00:00:00' && $value['deadline_accept_status'] == '') ? 'hide' : '';
                $delivery_days = floor((strtotime($value['work_end_date']) - strtotime($value['work_start_date']))/(60 * 60 * 24)) ;
                $addon_res =  $this->db->pdoQuery("SELECT COUNT(id) as total FROM tbl_services_addon WHERE services_id=".$value['id'])->result();
                $addon = ($value['addOn']=='') ? '0' : $value['addOn'];
                $status = service_status($value['serviceStatus']);

    		    $array = array
                (
                    "%FEATURED_LBL_CLASS%" => checkClass($value['featured'],$value['isDelete']),
                    "%FEATURED_LBL%" => (checkClass($value['featured'],$value['isDelete'])!='') ? ((checkClass($value['featured'],$value['isDelete'])=='deleted-class') ? 'Deleted' : 'Featured') : '' ,
			    	"%SERVICE_TITLE%" => filtering(ucfirst($value['serviceTitle'])),
                    "%SERVICE_SLUG%" => $value['servicesSlug'],
			    	"%SERVICE_CAT%" => filtering(ucfirst($value['category_name'])),
			    	"%SERVICE_SUBCAT%" => filtering(ucfirst($value['subcategory_name'])),
			    	"%SERVICE_QUANTITY%" => $value['quantity'],
			    	"%SERVICE_PAID_AMNT%" => $value['totalPayment']."<span>".CURRENCY_SYMBOL."</span>",
			    	"%SERVICE_DURATION%" => $value['noDayDelivery']." Day(s)&lrm;",
			    	"%CUST_NAME%" => filtering(ucfirst($value['firstName']))." ".filtering(ucfirst($value['lastName'])),
			    	"%ORDER_PLACED%" => date('dS, F Y',strtotime($value['orderDate'])),
                    "%ORDER_STATUS%" => $status["status"],
			    	"%ORDER_CLASS%" => $status["class"],
			    	"%DELIVERY_DAYS%" => $value['totalDuration']." Days",
                    "%TOTAL_ADDONS%" => $addon_res["total"],
                     "%ADD_ON_DETAIL%" => ($addon!=0) ? $this->addOnDetail2($value['orderId'],"counter") : '',
                    "%ADD_ON_DETAIL_CLASS%" => ($addon!=0) ? (($this->addOnDetail2($value['orderId']) == '0') ? 'hide' : '') : 'hide',
                    "%SERVICES_ID%" => $value['orderId'],
                    "%DEADLINE_ADD_CLASS%" => $deadline_add_button,
                    "%DEADLINE_EDIT_CLASS%" => $deadline_edit_button,
                    "%DEADLINE_DETAIL_CLASS" => $deadline_detail_button,
                    "%WORK_START_CLASS%" => $work_start_button,
                    /*"%DELIVERY_DAYS%" => $delivery_days	." Days",*/
                    "%ORDER_ID%" => base64_encode($value['orderId']),
                    "%DEADLINE_VIEW_CLASS%" => $deadline_btn_class,
                    "%DELETED_SERVICE_MAIN_CLASS%" => ($value['isDelete'] == 'y') ? 'deleted_post' : '',
                    "%DELETED_DIV%" => ($value['isDelete'] == 'y') ? '<div class="deleted-post"><span>Deleted</span></div>' : '',
                    "%DETAIL_LINK%" => ($value['isDelete'] == 'y') ? 'javascript:void(0)' : SITE_URL."service/".$value['servicesSlug'],

		    	);
    		    $data .= str_replace(array_keys($array), array_replace($array), $sub_content);
    	    }
        }
        else
        {
            $data .= "<span class='no-records'><i class='fa fa-exclamation-triangle'></i>".NO_RECORDS_FOUND."</span>";
        }
	    return $data;
    }
    public function condition($data)
    {
        if($data == 'new_order')
        {
            $where = " AND so.serviceStatus='no'";
        }
        else if($data == 'in_progress')
        {
            $where = " AND so.serviceStatus ='ip' ";
        }
        else if($data == 'ask_refund')
        {
           $where = " AND so.serviceStatus ='ar' ";
        }
        else if($data == 'closed')
        {
           $where = " AND so.serviceStatus ='cl' ";
        }
        else if($data == 'payment_pending')
        {
           $where = " AND so.serviceStatus ='p' ";
        }
        else if($data == 'completed')
        {
            $where = " AND so.serviceStatus ='c' ";
        }
        else if($data == 'under_dispute')
        {
           $where = " AND so.serviceStatus ='ud' ";
        }
        else if($data == 'dispute_solved')
        {
           $where = " AND so.serviceStatus ='ds' ";
        }
        else if($data == 'dispute_solved_closed')
        {
           $where = " AND so.serviceStatus ='dsc' ";
        }
        else
        {
            $where = "";
        }
        return $where;
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

            $sub_content = new MainTemplater(DIR_TMPL .$this->module . "/addOn_data-sd.skd");
            $sub_content = $sub_content->compile();
            foreach ($oaddons as $value) {
                $array = array(
                    "%ADDON_TITLE%" => $value['addonTitle'],
                    "%DAY_REQUIRED%" => $value['addonDayRequired'],
                    "%ADDONPRICE%" => CURRENCY_SYMBOL.$value['addonPrice']
                );
                $data .= str_replace(array_keys($array), array_values($array), $sub_content);
            }
        }
        return $data;
    }
    public function addOnDetail($serviceId,$record,$addOnId)
    {
        $where = ($addOnId==0) ? "services_id='".$serviceId."'" : "services_id='".$serviceId."' and id IN(".$addOnId.")";
    	if($record=='data')
    	{
	    	$query = $this->db->pdoQuery("select * from tbl_services_addon where ".$where)->results();
	    	$data = "";
	    	foreach ($query as $value) {
	    		$sub_content = new MainTemplater(DIR_TMPL .$this->module . "/addOn_data-sd.skd");
			    $sub_content = $sub_content->compile();
	    		$array = array(
	    			"%ADDON_TITLE%" => $value['addonTitle'],
	    			"%DAY_REQUIRED%" => $value['addonDayRequired'],
                    "%ADDONPRICE%" => $value['addonPrice']
	    			);
	    		$data .= str_replace(array_keys($array), array_values($array), $sub_content);
	    	}
    	}
    	else
    	{
    		$query = $this->db->pdoQuery("select * from tbl_services_addon where ".$where)->affectedRows();
    		$data = $query;
    	}
    	return $data;
    }

    public function saveDeadline($data)
    {
        extract($data);

        $this->db->update("tbl_services_order",array('deadline_requested_date' => date('Y-m-d H:i:s'), "work_start_date"=>date('Y-m-d',strtotime($start_date)),"work_end_date"=>date('Y-m-d',strtotime($end_date)),"deadline_accept_status"=>'p'),array("id"=>$sId));
        $job_res=$this->db->select("tbl_services_order",array("customerId","servicesId","freelanserId"),array("id"=>$sId))->result();
        $service_data=$this->db->select("tbl_services",array("serviceTitle"),array("id"=>$job_res['servicesId']))->result();
        $user_data=getUser($job_res['freelanserId']);
        $msg = $user_data['userName']." has defined timline for the order on service - ".$service_data['serviceTitle'];

        $link=SITE_URL."c/service-order";
        notify('c',$job_res['customerId'],$msg,$link);

        $msgType = $_SESSION["msgType"] = disMessage(array('type' => 'suc', 'var' => "Deadline saved successfully"));
        redirectPage(SITE_URL."f/services-order");
    }
    public function deadlineDetail($id,$btn_record='')
    {
        $data = $this->db->pdoQuery("select * from tbl_services_order where id=?",array($id))->result();
        $sub_content = new MainTemplater(DIR_TMPL .$this->module . "/deadline_detail-sd.skd");
        $sub_content = $sub_content->compile();
        if($btn_record!='')
        {
            $submit_class = (($data['deadline_accept_status']=='r')) ? '' : 'hide';
            return $submit_class;
        }
        else
        {
            $array =
                array(
                    "%START_DATE%" => date('dS, M Y',strtotime($data['work_start_date'])),
                    "%END_DATE%" => date('dS, M Y',strtotime($data['work_end_date'])),
                    "%STATUS%" => ($data['deadline_accept_status']=='p') ? 'Pending' : (($data['deadline_accept_status']=='a') ? 'Accepted': 'Rejected'),
                    "%MESSAGE%" => !empty($this->rejectReason($id)) ? filtering($this->rejectReason($id)) : 'N/A',
                    "%MESSAGE_CLASS%" => ($data['deadline_accept_status']=='a') ? 'hide' : '',
                    "%REPLY_BTN%" => ($data['deadline_accept_status']=='r') ? '' : 'hide'
                );
            return str_replace(array_keys($array), array_values($array), $sub_content);

        }
    }
    public function rejectReason($id)
    {
        $reject = $this->db->pdoQuery("select * from tbl_reject_reason where entityId=? order by id DESC",array($id))->result();
        return $reject['reason'];
    }
    public function saveMsg($data)
    {
        extract($data);
        $data = $this->db->pdoQuery("select * from tbl_services_order where id=?",array($orderId))->result();
        $this->db->insert("tbl_messages",array("entityId"=>$orderId,"senderId"=>$this->sessUserId,"receiverId"=>$data['customerId'],"entityType"=>'S',"message"=>$reply,"readStatus"=>'UR',"createdDate"=>date('Y-m-d H:i:s'),"messageType"=>'text',"ipAddress"=>get_ip_address()));

        $msgType = $_SESSION["msgType"] = disMessage(array('type' => 'suc', 'var' => MESSAGE_HAS_BEEN_SUBMITTED_SUCCESSFULLY));
        redirectPage(SITE_URL."f/services-order");

    }

}
 ?>


