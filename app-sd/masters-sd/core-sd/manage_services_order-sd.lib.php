<?php
class servicesOrder extends Home 
{

    public $page_name;
    public $page_title;
    public $meta_keyword;
    public $meta_desc;
    public $page_desc;
    public $isActive;
    public $data = array();

    public function __construct($module, $id = 0, $objPost = NULL, $searchArray = array(), $type = '') 
    {
        global $db, $fields, $sessCataId;
        $this->db = $db;
        $this->data['id'] = $this->id = $id;
        $this->fields = $fields;
        $this->module = $module;
        $this->table = 'tbl_services_order';

        $this->type = ($this->id > 0 ? 'edit' : 'add');
        $this->searchArray = $searchArray;
        parent::__construct();
        if ($this->id > 0) 
        {
            $qrySel = $this->db->pdoQuery("select so.*,s.serviceTitle,s.servicesPrice,s.noDayDelivery,f.userName As freelancer,f.location As fLocation,f.profileImg As fImg,c.profileImg As custImg,c.location As cLocation,c.userName As customerName,f.email As fEmail,c.email As cEmail,cat.category_name from tbl_services_order As so 
                LEFT JOIN tbl_services As s ON s.id = so.servicesId
                LEFT JOIN tbl_category As cat ON cat.id = s.servicesCategory
                LEFT JOIN tbl_users As f ON f.id = so.freelanserId
                LEFT JOIN tbl_users As c ON c.id = so.customerId
                where so.id='".$this->id."' ")->result();
            $fetchRes = $qrySel;
            $this->data['servicesId'] = $this->servicesId = $fetchRes['servicesId'];
            $this->data['serviceTitle'] = $this->serviceTitle = $fetchRes['serviceTitle'];
            $this->data['servicesPrice'] = $this->servicesPrice = $fetchRes['servicesPrice'];
            $this->data['noDayDelivery'] = $this->noDayDelivery = $fetchRes['noDayDelivery'];
            $this->data['category_name'] = $this->category_name = $fetchRes['category_name'];
            $this->data['freelancer'] = $this->freelancer = $fetchRes['freelancer'];
            $this->data['freelancerImg'] = $this->freelancerImg = $fetchRes['fImg'];
            $this->data['freelancerLocation'] = $this->freelancerLocation = $fetchRes['fLocation'];   
            $this->data['freelancerEmail'] = $this->freelancerEmail = $fetchRes['fEmail'];      
            $this->data['customerName'] = $this->customerName = $fetchRes['customerName']; 
            $this->data['customerImg'] = $this->customerImg = $fetchRes['custImg'];
            $this->data['customerLocation'] = $this->customerLocation = $fetchRes['cLocation'];   
            $this->data['customerEmail'] = $this->customerEmail = $fetchRes['cEmail'];           
            $this->data['orderDate'] = $this->orderDate = $fetchRes['orderDate'];      
            $this->data['quantity'] = $this->quantity = $fetchRes['quantity'];
            $this->data['accept_status'] = $this->accept_status = $fetchRes['accept_status'];
            $this->data['work_start_date'] = $this->work_start_date = $fetchRes['work_start_date'];
            $this->data['work_end_date'] = $this->work_end_date = $fetchRes['work_end_date'];
            $this->data['paymentStatus'] = $this->paymentStatus = $fetchRes['paymentStatus'];
            $this->data['serviceStatus'] = $this->serviceStatus = $fetchRes['serviceStatus'];
            $this->data['totalPayment'] = $this->totalPayment = $fetchRes['totalPayment'];
        } 
        else 
        {
            $this->data['servicesId'] = $this->servicesId = '';
            $this->data['serviceTitle'] = $this->serviceTitle = '';
            $this->data['servicesPrice'] = $this->servicesPrice = '';
            $this->data['noDayDelivery'] = $this->noDayDelivery = '';
            $this->data['category_name'] = $this->category_name = '';
            $this->data['freelancer'] = $this->freelancer = '';
            $this->data['freelancerImg'] = $this->freelancerImg = '';
            $this->data['freelancerLocation'] = $this->freelancerLocation = '';   
            $this->data['freelancerEmail'] = $this->freelancerEmail = '';      
            $this->data['customerName'] = $this->customerName = '';
            $this->data['customerImg'] = $this->customerImg = '';
            $this->data['customerLocation'] = $this->customerLocation = '';   
            $this->data['customerEmail'] = $this->customerEmail = '';    
            $this->data['orderDate'] = $this->orderDate = '';
            $this->data['quantity'] = $this->quantity = '';
            $this->data['accept_status'] = $this->accept_status = '';
            $this->data['work_start_date'] = $this->work_start_date = '';
            $this->data['work_end_date'] = $this->work_end_date = '';
            $this->data['paymentStatus'] = $this->paymentStatus = '';
            $this->data['serviceStatus'] = $this->serviceStatus = 'New Order';
            $this->data['totalPayment'] = $this->totalPayment = 0;
        }
        switch ($type) 
        {
            case 'add' : 
            {
                $this->data['content'] = $this->getForm();
                break;
            }
            case 'edit' : 
            {
                $this->data['content'] = $this->getForm();
                break;
            }
            case 'view' : 
            {
                $this->data['content'] = $this->viewForm();
                break;
            }      
            case 'disputeView' : 
            {
                $this->data['content'] = $this->viewDisputeForm();
                break;
            }      
            case 'undo' : {
                $this->data['content'] = json_encode($this->dataGrid());
                break;
            }
            case 'delete' : 
            {                    
                $this->data['content'] = json_encode($this->dataGrid());
                break;
            }
            case 'datagrid' : 
            {
                $this->data['content'] = json_encode($this->dataGrid());
                break;
            }
        }
    }
   
    public function viewDisputeForm()
    {
        $content='';      

        $fetchResPCats = $this->db->pdoQuery("
            SELECT tbl_dispute.*,u.id as uid,
            u.firstName AS ufirstName,u.lastName AS ulastName,u.profileImg as uprofileImg,u.userType AS uuserType,u.email as uemail,u.location AS ulocation,d.id AS did,
            d.firstName AS dfirstName,d.lastName AS dlastName,d.email AS demail,d.profileImg as dprofileImg,d.location AS dlocation,
            s.id as sid,s.serviceTitle as stitle,s.servicesPrice as sprice  
            from tbl_dispute 
                left join tbl_users as u on (u.id=tbl_dispute.disputerId)
                left join tbl_users as d on (d.id=tbl_dispute.disputedId)
                join tbl_services as s on (s.id=tbl_dispute.entityId)
                where tbl_dispute.entityId =".$this->servicesId )->result();
        extract($fetchResPCats);

        $img1 = ($uprofileImg!='')? $uprofileImg : "no_user_image.png";
        $uimg = "<img src='".SITE_UPD."profile/".$img1."' alt='".$ufirstName.' '.$ulastName." profile' height='100px' width='100px'></img>";

        $img2 = ($fetchResPCats->profileImg!='')? $fetchResPCats->profileImg : "no_user_image.png";
        $dimg = "<img src='".SITE_UPD."profile/".$img2."' alt='".$this->userName." profile' height='100px' width='100px'></img>";
        $uuserType = ($uuserType =='C') ?'Customer' :'Freelancer';
        $duserType = ($duserType =='C') ?'Customer' :'Freelancer';
        $disputeStatus = ($status =='S') ? 'Solved' :'Pending';

        $content.="<div class='well'><center><h2><b> User Details (Dispute Raised By) </b></h2></center>";
        $content.=$this->displayBox(array("label" => "User Name &nbsp;:", "value" => filtering($ufirstName.' '.$ulastName)));
        $content.=$this->displayBox(array("label" => "User Profile &nbsp;:", "value" => $uimg));
        $content.=$this->displayBox(array("label" => "User Type &nbsp;:", "value" => $uuserType));
        $content.=$this->displayBox(array("label" => "User Location &nbsp;:", "value" => $ulocation));
        $content.=$this->displayBox(array("label" => "User Email &nbsp;:", "value" => $uemail))."</div>";

        $content.="<div class='well'><center><h2><b> User Details (Dispute Raised Against) </b></h2></center>";
        $content.=$this->displayBox(array("label" => "User Name &nbsp;:", "value" => filtering($dfirstName.' '.$dlastName)));
        $content.=$this->displayBox(array("label" => "User Profile &nbsp;:","value" => $dimg));
        $content.=$this->displayBox(array("label" => "User Type &nbsp;:", "value" => $duserType));
        $content.=$this->displayBox(array("label" => "User Location &nbsp;:", "value" => filtering($dlocation)));
        $content.=$this->displayBox(array("label" => "User Email &nbsp;:", "value" => filtering($demail)))."</div>";
        $content.="<div class='well'><center><h2><b> Dispute Details </b></h2></center>";

        $content.=$this->displayBox(array("label" => "Service Title&nbsp;:", "value" => filtering($stitle)));
        $content.=$this->displayBox(array("label" => "Service Budget&nbsp;:", "value" => filtering(CURRENCY_SYMBOL.$sprice)));

        $content.=$this->displayBox(array("label" => "Dispute Reason&nbsp;:", "value" => filtering($disputeReason)));
        $content.=$this->displayBox(array("label" => "Dispute Description&nbsp;:", "value" => filtering($disputeDesc)));
        if($status=='S'){
            $content.=$this->displayBox(array("label" => "Dispute Status&nbsp;:", "value" => filtering($disputeStatus)));
            $content.=$this->displayBox(array("label" => "Paid to User(Dispute Raised By) &nbsp;:", "value" => filtering(CURRENCY_SYMBOL.$payToDisputer)));
            $content.=$this->displayBox(array("label" => "Paid to User(Dispute Against) &nbsp;:", "value" => filtering(CURRENCY_SYMBOL.$payToEntityOwner)));            
        }
        
        $content.=$this->displayBox(array("label" => "Disputed Date&nbsp;:", "value" => filtering(date(DATE_FORMAT_ADMIN.' h:i:s A',strtotime($insertedDate)))))."</div>";
        
        return $content;
    }
    public function viewForm() 
    {
        $services_img = $adonDetail = $freelancer_data = $customer_data = $service_status_data = "";

        /*addon detail start*/
        $addon_detail = $this->db->pdoQuery("select * from tbl_services_addon where services_id='".$this->id."' ")->results();
        if(count($addon_detail)>0)
        {
            $adonDetail .= "<center><strong><h5>Add On detail</h5></strong></center>";
            foreach ($addon_detail as $key => $value) 
            {
                $adonDetail .= $this->displayBox(array("label" => "Addon Title &nbsp;:", "value" => $value['addonTitle']))
                            .$this->displayBox(array("label" => "No of Days &nbsp;:", "value" => $value['addonDayRequired']))
                            .$this->displayBox(array("label" => "Addon Price &nbsp;:", "value" => $value['addonPrice']));
            }
        }
        /*addon detail end*/
        /*freelancer detail start*/
        
        
        $f_img = ($this->freelancerImg =='' ) ? SITE_UPD."no_user_image.png" : SITE_USER_PROFILE.$this->freelancerImg;
        $f_image = "<img src='".$f_img."' width='50' height='50'>";
        $freelancer_data .= "<div class='well'><center><h2><strong>Freelancer Detail</strong></h2></center><hr>"
                         . $this->displayBox(array("label" => "Freelancer Profile &nbsp;:", "value" => $f_image ))
                         . $this->displayBox(array("label" => "Freelancer Name &nbsp;:", "value" => filtering($this->freelancer)))
                         . $this->displayBox(array("label" => "Freelancer Location &nbsp;:", "value" => filtering($this->freelancerLocation)))
                         . $this->displayBox(array("label" => "Freelancer E-mail &nbsp;:", "value" => filtering($this->freelancerEmail)))."</div>"
                            ;
        
        /*freelancer detail end*/   
        /*customer detail start*/
        
            
        $c_img = ($this->customerImg =='') ? SITE_UPD."no_user_image.png" : SITE_USER_PROFILE.$this->customerImg;
        $c_image = "<img src='".$c_img."' width='50' height='50'>";
        $customer_data .= "<div class='well'><center><h2><strong>Customer Detail</strong></h2></center><hr>"
                         . $this->displayBox(array("label" => "Customer Profile &nbsp;:", "value" => $c_image))
                         . $this->displayBox(array("label" => "Customer Name &nbsp;:", "value" => filtering($this->customerName)))
                         . $this->displayBox(array("label" => "Customer Location &nbsp;:", "value" => filtering($this->customerLocation)))
                         . $this->displayBox(array("label" => "Customer E-mail &nbsp;:", "value" => filtering($this->customerEmail)))
                         ."</div>"
                         ;
        
        /*customer detail end*/
        if($this->serviceStatus=='no')
        {
            $service_status_data .=  $this->displayBox(array("label" => 'Services Status', "value" => 'New Order'));
        }
        else if($this->serviceStatus == 'ip' && $this->accept_status =='a')
        {
            $service_status_data .=  
                    $this->displayBox(array("label" => 'Services Status', "value" => 'In Processing')).
                    $this->displayBox(array("label" => 'Start Date', "value" => date('d-m-Y H:i:s',strtotime($this->work_start_date)))).
                    $this->displayBox(array("label" => 'End Date', "value" => date('d-m-Y H:i:s',strtotime($this->work_end_date))));
                                
        }
        else if($this->serviceStatus == 'ar')
        {
            $refund_detail = $this->db->pdoQuery("select * from tbl_refund_request where serviceOrderId='".$this->id."' ")->result();
            if($refund_detail['acceptStatus']=='a')
            {
                $status = 'Accepted';$reason = '';
            }
            else if($refund_detail['acceptStatus']=='r')
            {
                $status = 'Rejected';
                $reason = $this->displayBox(array("label" => 'Refund Request Status', "value" => $refund_detail['reason']));
            }
            else
            {
                $status = 'Pending';$reason = '';
            }
           
            $service_status_data .= 
                $this->displayBox(array("label" => 'Services Status', "value" => 'Customer has asked for refund')).
                $this->displayBox(array("label" => 'Refund Request Status', "value" => $status)).
                $reason;
        }
        else if($this->serviceStatus == 'c')
        {
            $service_status_data .= 
                $this->displayBox(array("label" => 'Services Status', "value" => 'Closed'));
        }
        else if($this->serviceStatus == 'p')
        {
            $service_status_data .= 
                $this->displayBox(array("label" => 'Services Status', "value" => 'Payment Pending'));
        }
        else if($this->serviceStatus == 'ud')
        {
            $dispute_detail = $this->db->pdoQuery("select d.*,u.* from tbl_dispute AS d 
                LEFT JOIN tbl_users As u ON u.id = d.disputerId
                where d.type='s' and d.entityId='".$this->id."' 
                ")->result();

            $disputer_img = ($dispute_detail['profileImg'] =='' || !file_exists(SITE_USER_PROFILE.$dispute_detail['profileImg'])) ? SITE_UPD."no_user_image.png" : SITE_USER_PROFILE.$dispute_detail['profileImg'];
            $dispute_image = "<img src='".$disputer_img."' height='50' width='50'>";
            $disputer_type = ($dispute_detail['userType'] == 'F') ? 'Freelancer' : 'Customer';
            $service_status_data .= 
                $this->displayBox(array("label" => 'Disputer ', "value" => ucfirst($dispute_detail['firstName']." ".$dispute_detail['lastName']) )).
                $this->displayBox(array("label" => 'Disputer Profile', "value" => $dispute_image )).
                $this->displayBox(array("label" => 'User Type ', "value" => $disputer_type )).
                $this->displayBox(array("label" => 'Dispute Date ', "value" => date('d-m-Y H:i:s',strtotime($dispute_detail['insertedDate'])) )).
                $this->displayBox(array("label" => 'Dispute Reason ', "value" => $dispute_detail['disputeReason'] )).
                $this->displayBox(array("label" => 'Dispute Description ', "value" => $dispute_detail['disputeDesc'] ));              
        }

        $commission = getCommision($this->totalPayment,'S');
        $commission = ($this->totalPayment*$commission)/100;
        // $commission = ($this->totalPayment*$commission);

        $service_status_data .= "</div>";
        $content = 
            $this->displayBox(array("label" => "Services Title &nbsp;:", "value" => filtering($this->serviceTitle))).
            $this->displayBox(array("label" => "Services Category &nbsp;:", "value" => filtering($this->category_name))).
            $this->displayBox(array("label" => "Service Price &nbsp;:", "value" => filtering(CURRENCY_SYMBOL.$this->servicesPrice))).
            $this->displayBox(array("label" => "Admin Commission &nbsp;:", "value" => CURRENCY_SYMBOL.$commission)).
            $this->displayBox(array("label" => "Delivery(No. of Days) &nbsp;:", "value" => filtering($this->noDayDelivery))).
            $adonDetail.
            $this->displayBox(array("label" => "Purchased Quantity &nbsp;:", "value" => $this->quantity)).
            $this->displayBox(array("label" => "Total Duration &nbsp;:", "value" => '')).
            $this->displayBox(array("label" => "Paid amount &nbsp;:", "value" => '')).
            $freelancer_data.
            $customer_data.
            $service_status_data;

        return $content;
    }

    public function getForm() 
    {

        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/form-sd.skd");
        $main_content = $main_content->compile();
        $hideFrmSearch_y = ($this->hideFrmSearch == 'y' ? 'checked' : '');
        $hideFrmSearch_n = ($this->hideFrmSearch != 'y' ? 'checked' : '');

        $featured_y = ($this->featured == 'y' ? 'checked' : '');
        $featured_n = ($this->featured == 'n' ? 'checked' : '');

        $category = $subcategory = $skills = '';

        /*category list*/
        $category_detail = $this->db->pdoQuery("select * from tbl_category where (category_type='j' OR category_type='b') and isActive='y' and isDelete='n'")->results();
        foreach ($category_detail as $value) {
            $cat_sel = ($this->servicesCategory == $value['id']) ? 'selected' : '';
            $category .="<option value='".$value['id']."' ".$cat_sel.">".$value['category_name']."</option>";
        }
        /*subcategory list*/
        $subcategory_detail = $this->db->pdoQuery("select * from tbl_subcategory where maincat_id='".$this->servicesCategory."' and isActive='y' and isDelete='n' ")->results();
        foreach ($subcategory_detail as $value1) 
        {
            $sub_cat_sel = ($this->servicesSubCategory == $value1['id']) ? 'selected' : '';
            $subcategory .="<option value='".$value1['id']."' ".$sub_cat_sel.">".$value1['subcategory_name']."</option>";
        }

        $add_on_section = ($this->type == 'edit') ? $this->addOnSection() : '';

        $image = SITE_SERVICES_FILE.$this->services_image;
        $services_img = ($this->services_image!='') ? "<img src='".$image."' width='100' height='100'>" : '';

        /*skills list*/
        $question_list = '';
        $fields = array(
            "%BYDEFAULT%" => ($this->type == 'add') ? 'checked': '',
            "%SERVICES_IMG%" => $services_img,
            "%OLD_IMG%" => ($this->services_image != '') ? $this->services_image : '',
            "%SERVICES_TITLE%" => filtering($this->serviceTitle),
            "%CATEGORY%" => $category,
            "%SUB_CATEGORY%" => $subcategory,
            "%DESC%" => $this->description,
            "%NO_DELIVERY%" => $this->noDayDelivery,
            "%SERVICES_PRICE%" => $this->servicesPrice,
            "%SERVICE_ADON_TITLE%" => $this->serviceAdonTitle,
            "%SERVICE_ADON_REQUIRED%" => $this->serviceAdondayRequired,
            "%SERVICE_ADON_PRICE%" => $this->serviceAdonPrice,
            "%SERVICE_ADON_DESC%" => $this->serviceAdonDesc,
            "%REQUIRED_DETAILS%" => $this->requiredDetails,
            "%ADDON_CONTENT%" => $add_on_section,
            "%FEATURED_Y%" => $featured_y,
            "%FEATURED_N%" => $featured_n,
            "%TYPE%" => filtering($this->type),
            "%ID%" => filtering($this->id, 'input', 'int')
        );

        $content = str_replace(array_keys($fields), array_values($fields), $main_content);
        return sanitize_output($content);
    }

    public function addOnSection()
    {
        $data = $this->db->pdoQuery("Select * from tbl_services_order_addon where services_id='".$this->id."' ")->results();
        $loop_data = '';
        foreach ($data as $value) 
        {
            $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/addOn_div-sd.skd");
            $main_content = $main_content->compile();

            $array = array(
                "%ADDONTITLE%" => $value['addonTitle'],
                "%ADDONDAYREQUIRED%" => $value['addonDayRequired'],
                "%ADDONPRICE%" => $value['addonPrice'],
                "%ADDONDESC%" => $value['addonDesc']
                );
            $loop_data .= str_replace(array_keys($array), array_replace($array), $main_content);
        }
        return $loop_data;
    }
    

    public function dataGrid() 
    {
        $content = $operation = $whereCond = $totalRow = NULL;
        $result = $tmp_rows = $row_data = array();
        extract($this->searchArray);
        $chr = str_replace(array('_', '%'), array('\_', '\%'), $chr);
        $whereCond = "where 1";
        $wArray = array();
        if (isset($chr) && $chr != '') 
        {
                $whereCond .= " AND (s.serviceTitle LIKE ? OR f.userName LIKE ? OR c.userName LIKE ?) ";
                $wArray[] = "%$chr%";$wArray[] = "%$chr%";$wArray[] = "%$chr%";
        }
        if(isset($orderStatus) && $orderStatus!='')
        {
            $whereCond .= " AND (so.serviceStatus = ?) ";
            $wArray[] = $orderStatus;
        }

        if (isset($sort))
            $sorting = $sort . ' ' . $order;
        else
            $sorting = 'so.id DESC';

        
        $qrySel = $this->db->pdoQuery("Select so.*,s.serviceTitle,s.servicesPrice,f.userName As freelancer,c.userName As customerName from tbl_services_order As so 
                LEFT JOIN tbl_services As s ON s.id = so.servicesId
                LEFT JOIN tbl_users As f ON f.id = so.freelanserId
                LEFT JOIN tbl_users As c ON c.id = so.customerId
                ".$whereCond."
                ORDER BY $sorting LIMIT $offset,$rows ",$wArray)->results();

         $totalRow = $this->db->pdoQuery("Select so.*,s.serviceTitle,f.userName As freelancer,c.userName As customerName from tbl_services_order As so 
                LEFT JOIN tbl_services As s ON s.id = so.servicesId
                LEFT JOIN tbl_users As f ON f.id = so.freelanserId
                LEFT JOIN tbl_users As c ON c.id = so.customerId
                ".$whereCond."
                ORDER BY $sorting ",$wArray)->affectedRows();
        foreach ($qrySel as $fetchRes) 
        {
            $operation = "";
          
            $operation .=(in_array('view', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=view&id=" . $fetchRes['id'] . "", "extraAtt" => "title = 'View'", "class" => "btn default blue btn-viewbtn", "value" => '<i class="fa fa-laptop"></i>')) : '';

            if($fetchRes['serviceStatus'] == 'ar')
            {
                
                $operation .= $this->operation(array("href" => "javascript:void(0)" , "extraAtt" => "data-id = '".$fetchRes['id']."' ", "class" => "btn btn-danger btn-refundView", "value" => '<i class="fa fa-undo"></i>' ));
               
            }
            else if($fetchRes['serviceStatus'] == 'cls')
            {
                $operation .= "<span class='label label-info'>Refund Request Accepted</span>";
            }
            else if($fetchRes['serviceStatus'] == 'ip')
            {
                $refund_detail = $this->db->pdoQuery("select * from tbl_refund_request where serviceOrderId='".$fetchRes['id']."' ")->result();
                if($refund_detail['acceptStatus'] == 'r')
                {
                     $operation .= "<span class='label label-danger'>Refund Request Rejected</span>";
                }
            }
            else if($fetchRes['serviceStatus'] == 'ud')
            {
                $operation .=(in_array('view', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=disputeView&id=" . $fetchRes['id'] . "", "extraAtt" => "title = 'View'", "class" => "btn default btn-info btn-viewbtn", "value" => '<i class="fa fa-eye"></i>')) : '';
            }
            $status_arr = array(
                "no" => " new order",
                "ip" => "in progress",
                "ar" => "ask for refund",
                "c" => "completed",
                "p" => "payment pending",
                "ud" => "under dispute",
                "ds" => "dispute solved",
                "cls" => "closed",
                "dsc" => "dispute solved and completed",
            );
            $final_array = array(
                $fetchRes['serviceTitle'],
                filtering($fetchRes["freelancer"]),
                filtering($fetchRes["customerName"]),
                filtering(CURRENCY_SYMBOL.$fetchRes['servicesPrice']),
                date('d-m-Y H:i:s',strtotime($fetchRes["orderDate"])),
                filtering($status_arr[$fetchRes["serviceStatus"]])
            );
           
            if (in_array('edit', $this->Permission) || in_array('delete', $this->Permission) || in_array('view', $this->Permission)) 
            {
                $final_array = array_merge($final_array, array($operation));
            }
            $row_data[] = $final_array;
        }
        $result["sEcho"] = $sEcho;
        $result["iTotalRecords"] = (int) $totalRow;
        $result["iTotalDisplayRecords"] = (int) $totalRow;
        $result["aaData"] = $row_data;
        return $result;
    }

    public function toggel_switch($text) 
    {
        $disabledSwitch=NULL;
        $text['action'] = isset($text['action']) ? $text['action'] : 'Enter Action Here: ';
        $text['check'] = isset($text['check']) ? $text['check'] : '';
        $text['name'] = isset($text['name']) ? $text['name'] : '';
        $text['class'] = isset($text['class']) ? '' . trim($text['class']) : '';
        $text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';
        /*if(!empty($text['deletecat'])){
             if(empty($text['check']) && $text['deletecat']=='y'){
                                $disabledSwitch='disabled';
                            }
        }
        if(!empty($text['homecat'])){
             if(empty($text['check']) && $text['homecat']=='y'){
                                $disabledSwitch='disabled';
                            }
        }
*/
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/switch-sd.skd');
        $main_content = $main_content->compile();
        $fields = array("%NAME%", "%CLASS%", "%ACTION%", "%EXTRA%", "%CHECK%");
        $fields_replace = array($text['name'], $text['class'], $text['action'], $text['extraAtt'], $text['check']);
        return str_replace($fields, $fields_replace, $main_content);
    }

    public function operation($text) 
    {

        $text['href'] = isset($text['href']) ? $text['href'] : 'Enter Link Here: ';
        $text['value'] = isset($text['value']) ? $text['value'] : '';
        $text['name'] = isset($text['name']) ? $text['name'] : '';
        $text['class'] = isset($text['class']) ? '' . trim($text['class']) : '';
        $text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/operation-sd.skd');
        $main_content = $main_content->compile();
        $fields = array("%HREF%", "%CLASS%", "%VALUE%", "%EXTRA%");
        $fields_replace = array($text['href'], $text['class'], $text['value'], $text['extraAtt']);
        return str_replace($fields, $fields_replace, $main_content);
    }

    public function displaybox($text) 
    {

        $text['label'] = isset($text['label']) ? $text['label'] : 'Enter Text Here: ';
        $text['value'] = isset($text['value']) ? $text['value'] : '';
        $text['name'] = isset($text['name']) ? $text['name'] : '';
        $text['class'] = isset($text['class']) ? 'form-control-static ' . trim($text['class']) : 'form-control-static';
        $text['onlyField'] = isset($text['onlyField']) ? $text['onlyField'] : false;
        $text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';

        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/displaybox.skd');
        $main_content = $main_content->compile();
        $fields = array("%LABEL%", "%CLASS%", "%VALUE%");
        $fields_replace = array($text['label'], $text['class'], $text['value']);
        return str_replace($fields, $fields_replace, $main_content);
    }

    public function getPageContent() 
    {
        $final_result = NULL;
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/" . $this->module . ".skd");
        $main_content->breadcrumb = $this->getBreadcrumb();
        $final_result = $main_content->compile();
        return $final_result;
    }
    public function refundRequestSubmit($data){
        
        extract($data);
        if($refundStatus=='r')
        {
            $status = 'r';
            $reason = $rejectReason;
        }
        else
        {
            $status = 'a';
            $reason = '';
        }
        $this->db->update("tbl_refund_request",array("acceptStatus"=>$status,'reason'=>$reason),array('serviceOrderId'=>$serviceId));
        if($refundStatus!='r')
        {
            $this->db->update("tbl_services_order",array("serviceStatus"=>'cl'),array('id'=>$serviceId));
        }
        else
        {
            $this->db->update("tbl_services_order",array("serviceStatus"=>'ip'),array('id'=>$serviceId));
        }
        $service_detail = $this->db->pdoQuery("select totalPayment,customerId from tbl_services_order where id='".$serviceId."' ")->result();
        if($status == 'a')
        {
            $admin_fees = PAYPAL_SERVICE_FEES;
            $final_commision = ($service_detail['totalPayment']*$admin_fees) / 100;


            $refundable_amount = $service_detail['totalPayment'] - round($final_commision);

            $this->db->pdoQuery("UPDATE tbl_users SET walletAmount = walletAmount + '".$refundable_amount."' WHERE id = ?", array($service_detail['customerId']));
        }
        else
        {
            $user_detail = $this->db->pdoQuery("select * from tbl_users where id='".$service_detail['customerId']."' ")->result();
            $arrayCont = array(
                "greetings" => $user_detail['userName'],
                "REJECT_REASON" => $reason
                );
            $array = generateEmailTemplate('refund_request_reject',$arrayCont);
            sendEmailAddress($user_detail['email'],$array['subject'],$array['message']);
        }
        $_SESSION["toastr_message"] = disMessage(array('type' => 'suc', 'var' => 'Refund Request has been updated successfully'));
        redirectPage(SITE_ADM_MOD . 'manage_services_order-sd');
    }
}




        
            
            

               
          
            
