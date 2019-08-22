<?php

class CustomerServiceOrder {
    function __construct($module = "", $id = 0, $token = "",$search_array = array()) {
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
        $keyword = (array_key_exists('keyword',$this->search_array)) ? urldecode($this->search_array['keyword']) : '';
        $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/".$this->module.".skd");
        $sub_content = $sub_content->compile();

        $new_order_status = ($status == 'new_order' ? 'checked' : '');
        $ask_refund_status = ($status == 'ask_refund' ? 'checked' : '');
        $closed_status = ($status == 'closed' ? 'checked' : '');
        $in_progress_status = ($status == 'in_progress' ? 'checked' : '');
        $payment_pending_status = ($status == 'payment_pending' ? 'checked' : '');
        $completed_another_status = ($status == 'completed' ? 'checked' : '');
        $under_dispute_status = ($status == 'under_dispute' ? 'checked' : '');
        $dispute_solved_status = ($status == 'dispute_solved' ? 'checked' : '');

        $where = "so.customerId ='".$this->sessUserId."' ";
        if(isset($this->search_array['status']))
        {
            $where .= $this->condition($this->search_array['status']);
        }
        if(isset($this->search_array['keyword']) && $this->search_array['keyword']!='')
        {
            $where .= " AND s.serviceTitle LIKE '%".$this->search_array['keyword']."%' ";
        }
        $load_data = $this->db->pdoQuery("select s.*,so.*,c.".l_values('category_name')." as category_name,sub.".l_values('subcategory_name')." as subcategory_name,u.firstName,u.lastName from tbl_services_order As so
            LEFT JOIN tbl_services As s ON s.id = so.servicesId
            LEFT JOIN  tbl_category As c ON c.id = s.servicesCategory
            LEFT JOIN tbl_subcategory As sub ON sub.id = s.servicesSubCategory
            LEFT JOIN tbl_users As u ON u.id = so.freelanserId
            where ".$where)->affectedRows();

        $load_class = ($load_data > 10) ? '' : 'hide';

        $data_array = array(
            "%SUB_HEADER_CONTENT%" => customerSubHeaderContent("ser_order"),
            "%LOOP_DATA%" => $this->services_order_loop(1),
            "%LOAD_CLASS%" => $load_class,
            "%NEWORDER_STATUS%" => $new_order_status,
            "%INPRO_STATUS%" => $in_progress_status,
            "%AR_STATUS%" => $ask_refund_status,
            "%CLOSED_STATUS%" => $closed_status,
            "%PENDING_STATUS%" => $payment_pending_status,
            "%COMP_STATUS%" => $completed_another_status,
            "%UD_STATUS%" => $under_dispute_status,
            "%DS_STATUS%" => $dispute_solved_status
        );

        return str_replace(
            array_keys($data_array),array_values($data_array), $sub_content);
    }

    public function services_order_loop($search_array='',$pageNo=1)
    {
        $num_rec_per_page=10;
        $start_from = ($pageNo-1) * $num_rec_per_page;

        $where = "so.customerId ='".$this->sessUserId."' ";
        if(isset($search_array['status']))
        {
            $where .= $this->condition($search_array['status']);
        }
        if(isset($search_array['keyword']) && $search_array['keyword']!='')
        {
            $where .= " AND s.serviceTitle LIKE '%".$search_array['keyword']."%' ";
        }

        $query = $this->db->pdoQuery("select s.*,so.*,so.id As orderId,c.".l_values('category_name')." as category_name,sub.".l_values('subcategory_name')." as subcategory_name,u.id as uid,u.firstName,u.lastName from tbl_services_order As so
            LEFT JOIN tbl_services As s ON s.id = so.servicesId
            LEFT JOIN  tbl_category As c ON c.id = s.servicesCategory
            LEFT JOIN tbl_subcategory As sub ON sub.id = s.servicesSubCategory
            LEFT JOIN tbl_users As u ON u.id = so.freelanserId
            where ".$where." ORDER by so.id DESC LIMIT ".$start_from.",".$num_rec_per_page)->results();

        $data = '';
        if(count($query)>0)
        {

            foreach ($query as $value)
            {
                $value['service_order_data'] = !empty($value['service_order_data']) ? json_decode($value['service_order_data'],true) : [];
                $oservice = !empty($value['service_order_data']['service_data']) ? $value['service_order_data']['service_data'] : [];


                $refundHide = 'hide';
                if($value['serviceStatus'] == 'ar')
                {
                    $refundHide = $this->ask_refund_status($value['orderId']);
                }
                $askRefund = $this->ask_for_refund($value['orderId']);

                $sub_content = new MainTemplater(DIR_TMPL .$this->module . "/services_data-sd.skd");
                $sub_content = $sub_content->compile();

                $deadline_add_button = ($value['work_start_date'] == '0000-00-00 00:00:00' && $value['work_end_date'] == '0000-00-00 00:00:00' && $value['serviceStatus']!='ip') ? '' : 'hide';
                $deadline_edit_button = (($value['work_start_date'] == '0000-00-00 00:00:00' && $value['work_end_date'] == '0000-00-00 00:00:00') || ($value['work_start_date'] != '0000-00-00 00:00:00' && $value['work_end_date'] != '0000-00-00 00:00:00' && $value['deadline_accept_status']=='a' && $value['serviceStatus']=='ip')) ? 'hide' : '';
                $deadline_detail_button = ($value['work_start_date'] == '0000-00-00 00:00:00' && $value['work_end_date'] == '0000-00-00 00:00:00') ? 'hide' : '';
                $work_start_button = ($value['work_start_date'] != '0000-00-00 00:00:00' && $value['work_end_date'] != '0000-00-00 00:00:00' && $value['deadline_accept_status']=='a' && $value['serviceStatus']!='ip') ? '' : 'hide';
                $delivery_days = floor((strtotime($value['work_end_date']) - strtotime($value['work_start_date']))/(60 * 60 * 24));

                $addon = ($value['addOn']=='') ? '0' : $value['addOn'];
                $rdata = $this->db->pdoQuery("select reason from tbl_reject_reason where entityId=".$value['orderId']." order by id desc limit 1")->result();

                $deadline_lbl = DEADLINE_DETAILS;

                if($value['deadline_accept_status'] == 'p' && $value["work_start_date"]!="0000-00-00 00:00:00"){
                    $deadline_lbl = ACCEPT_DETAILS;
                }


                $array = array
                (
                    "%FEATURED_LBL_CLASS%" => checkClass($value['featured'],$value['isDelete']),
                    "%FEATURED_LBL%" => (checkClass($value['featured'],$value['isDelete'])!='') ? ((checkClass($value['featured'],$value['isDelete'])=='deleted-class') ? 'Deleted' : 'Featured') : '' ,
                    "%SERVICE_TITLE%" => !empty($oservice['serviceTitle']) ? $oservice['serviceTitle'] : $value['serviceTitle'],
                    "%SERVICE_CAT%" => $value['category_name'],
                    "%SERVICE_SUBCAT%" => $value['subcategory_name'],
                    "%SERVICE_QUANTITY%" => $value['quantity'],
                    "%SERVICE_PAID_AMNT%" => $value['totalPayment']."<span>".CURRENCY_SYMBOL."</span>",
                    "%SERVICE_PRICE%" => (!empty($oservice['servicesPrice']) ? $oservice['servicesPrice'] : $value['servicesPrice'])."<span>".CURRENCY_SYMBOL."</span>",
                    "%SERVICE_DURATION%" => (!empty($oservice['noDayDelivery']) ? $oservice['noDayDelivery'] : $value['noDayDelivery'])." Days",
                    "%ORDER_PLACED%" => date('dS, F Y',strtotime($value['orderDate'])),
                    "%ORDER_STATUS%" => $this->service_status($value['serviceStatus']),

                    "%ADD_ON_DETAIL%" => ($addon!=0) ? $this->addOnDetail2($value['orderId'],"counter") : '',
                    "%ADD_ON_DETAIL_CLASS%" => ($addon!=0) ? (($this->addOnDetail2($value['orderId']) == '0') ? 'hide' : '') : 'hide',
                    "%SERVICES_ID%" => $value['orderId'],
                    "%DEADLINE_ADD_CLASS%" => $deadline_add_button,
                    "%DEADLINE_EDIT_CLASS%" => $deadline_edit_button,
                    "%DEADLINE_DETAIL_CLASS%" => $deadline_detail_button,
                    "%WORK_START_CLASS%" => $work_start_button,
                    "%REFUND_HIDE%" => $refundHide,
                    "%DELIVERY_DAYS%" => $value['totalDuration']." Day(s)",
                    "%ASK_REFUND_HIDE%" => $askRefund,
                    "%U_ID%" => $value['uid'],
                    "%DEADLINE_DETAILS_LBL%" => $deadline_lbl,
                    "%REJECT_STATUS%" => $rdata['reason'],
                    "%ORDER_ID%" => base64_encode($value['orderId']),
                    "%SERVICE_SLUG%" => $value['servicesSlug'],
                    "%DELETED_SERVICE_MAIN_CLASS%" => ($value['isDelete'] == 'y') ? 'deleted_post' : '',
                    "%DELETED_DIV%" => ($value['isDelete'] == 'y') ? '<div class="deleted-post"><span>Deleted</span></div>' : '',
                    "%DETAIL_LINK%" => ($value['isDelete'] == 'y') ? 'javascript:void(0)' : SITE_URL.'service/'.$value['servicesSlug']
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



    public function ask_for_refund($id){
        $result = $this->db->select('tbl_services_order',array('*'),array('id'=>$id))->result();

        $currentDate = date_create(date('Y-m-d H:i:s'));
        $interval = date_diff($currentDate, date_create($result['orderDate']));
        $soldDate = $interval->format("%d");
        $refundHide = $this->ask_refund_status($id);
        if($soldDate >= 7 && $result['actual_work_start_date'] =="0000-00-00 00:00:00"){
            if($refundHide == ""){
                return "hide";
            }
            return "";
        } else {
            return "hide";
        }
    }

    public function ask_refund_status($id){
        $af = $this->db->select('tbl_refund_request',array('*'),array('serviceOrderId'=>$id))->affectedRows();
        if($af > 0){
            return '';
        } else {
            return 'hide';
        }
    }
    public function condition($data)
    {
        $where = '';
        if($data == 'new_order')
        {
            $where .= " AND serviceStatus='no'";
        }
        else if($data == 'in_progress')
        {
            $where .= " AND serviceStatus ='ip' ";
        }
        else if($data == 'ask_refund')
        {
            $where .= " AND serviceStatus ='ar' ";
        }
        else if($data == 'closed')
        {
            $where .= " AND serviceStatus ='cl' ";
        }
        else if($data == 'payment_pending')
        {
            $where .= " AND serviceStatus ='p' ";
        }
        else if($data == 'completed')
        {
            $where .= " AND serviceStatus ='c' ";
        }
        else if($data == 'under_dispute')
        {
            $where .= " AND serviceStatus ='ud' ";
        }
        else if($data == 'dispute_solved')
        {
            $where .= " AND serviceStatus ='dsc' ";
        }
        return $where;
    }

    public function service_status($status)
    {
        if($status == 'no')
        {
            $data = NEW_ORDER;
        }
        else if($status == 'ip')
        {
            $data = IN_PROGRESS;
        }
        else if($status == 'ar')
        {
            $data = ASK_FOR_REFUND;
        }
        else if($status == 'c')
        {
            $data = C_SO_COMPLETED_LBL;
        }
        else if($status == 'p')
        {
            $data = PAYMENT_PENDING;
        }
        else if($status == 'ud')
        {
            $data = C_SO_UNDER_DISPUTE_LBL;
        }
        else if($status == 'ds')
        {
            $data = DISPUTE_SOLVED;
        }
        else if($status == 'dsc')
        {
            $data = DISPUTE_SOLVED_AND_CLOSED;
        }
        return $data;
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
                    "%DAY_REQUIRED%" => $value['addonDayRequired']
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

        $this->db->update("tbl_services_order",array("work_start_date"=>date('Y-m-d',strtotime($start_date)),"work_end_date"=>date('Y-m-d',strtotime($end_date)),"deadline_accept_status"=>'p'),array("id"=>$sId));
        $msgType = $_SESSION["msgType"] = disMessage(array('type' => 'suc', 'var' => DEADLINE_HAS_BEEN_SAVED_SUCCESSFULLY));
        redirectPage(SITE_URL."f/services-order");
    }
    public function deadlineDetail($id,$btn_record='')
    {
        $data = $this->db->pdoQuery("select * from tbl_services_order where id=?",array($id))->result();
        $sub_content = new MainTemplater(DIR_TMPL .$this->module . "/deadline_detail-sd.skd");
        $sub_content = $sub_content->compile();
        if($btn_record!='')
        {
            $submit_class = ($data['deadline_reject_description']=='') ? 'hide' : '';
            return $submit_class;
        }
        else
        {
            $array = array(
                "%START_DATE%" => date('dS, M Y',strtotime($data['work_start_date'])),
                "%END_DATE%" => date('dS, M Y',strtotime($data['work_end_date'])),
                "%STATUS%" => ($data['deadline_accept_status']=='p') ? 'Pending' : (($data['deadline_accept_status']=='a') ? 'Accept': 'Reject'),
                "%MESSAGE%" => filtering($data['deadline_reject_description']),
                "%MESSAGE_CLASS%" => ($data['deadline_reject_description']=='') ? 'hide' : '',
                "%REPLY_BTN%" => ($data['deadline_accept_status']=='r') ? '' : 'hide'

            );
            return str_replace(array_keys($array), array_values($array), $sub_content);

        }
    }
    public function saveMsg($data)
    {
        extract($data);
        $data = $this->db->pdoQuery("select * from tbl_services_order where id=?",array($orderId))->result();
        $this->db->insert("tbl_messages",array("entityId"=>$orderId,"senderId"=>$this->sessUserId,"receiverId"=>$data['customerId'],"entityType"=>'S',"message"=>$reply,"readStatus"=>'UR',"createdDate"=>date('Y-m-d H:i:s'),"messageType"=>'text',"ipAddress"=>get_ip_address()));

        $msgType = $_SESSION["msgType"] = disMessage(array('type' => 'suc', 'var' => MESSAGE_HAS_BEEN_SUBMITTED_SUCCESSFULLY));
        redirectPage(SITE_URL."C/services-order");

    }

}
?>


