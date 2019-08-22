<?php

class PaymentHistory extends Home {

    public $question;
    public $answer;
    public $priority;
    public $isActive;
    public $data = array();

    public function __construct($module, $id = 0,$wallet_id = 0, $objPost = NULL, $searchArray = array(), $type = '') {
        global $db, $fields, $sessCataid;
        $this->db = $db;
        $this->data['id'] = $this->id = $id;
        $this->wallet_id = $wallet_id;
        $this->fields = $fields;
        $this->module = $module;
        $this->table = 'tbl_commision';

        $this->type = ($this->id > 0 ? 'edit' : 'add');
        $this->searchArray = $searchArray;
        parent::__construct();
        if ($this->id > 0) {
            $qrySel = $this->db->select($this->table, "*", array("id" => $id))->result();
            $fetchRes = $qrySel;
            $fetchRes['defaultAmount'] ='';
            $this->data['id'] = $this->id = $fetchRes['id'];
            $this->data['minPrice'] = $this->minPrice = $fetchRes['minPrice'];
            $this->data['maxPrice'] = $this->maxPrice = $fetchRes['maxPrice'];
            $this->data['specificAmount'] = $this->specificAmount = $fetchRes['specificAmount'];
            $this->data['defaultAmount'] = $this->defaultAmount = $fetchRes['defaultAmount'];
            $this->data['isActive'] = $this->isActive = $fetchRes['isActive'];
            $this->data['createdDate'] = $this->createdDate = $fetchRes['createdDate'];
        } else {
            $this->data['id'] = $this->id = '';
            $this->data['minPrice'] = $this->minPrice = '';;
            $this->data['maxPrice'] = $this->maxPrice = '';
            $this->data['specificAmount'] = $this->specificAmount = '';            
            $this->data['defaultAmount'] = $this->defaultAmount = '';            
            $this->data['isActive'] = $this->isActive = 'y';
            $this->data['createdDate'] = $this->createdDate = '';
        }
        switch ($type) {            
            case 'view' : {
                    $this->data['content'] = $this->viewForm();
                    break;
                } 
            case 'datagrid' : {
                    $this->data['content'] = json_encode($this->dataGrid());
                    break;
                }
        }
    }
    public function viewForm() {

        $query = $this->db->pdoQuery("SELECT w.*,u.id as userId,u.firstName,u.lastName,u.userType,j.jobTitle,s.serviceTitle,u.createdDate as userDate,p.title as plan_title
            FROM tbl_wallet As w
            JOIN tbl_users AS u ON u.id = w.userId
            LEFT JOIN tbl_jobs as j ON(j.id = w.entity_id AND (w.entity_type='j' OR w.entity_type='ml') )
            LEFT JOIN tbl_services as s ON(s.id = w.entity_id AND w.entity_type='s')
            LEFT JOIN tbl_credit_package as p ON(p.id = w.entity_id AND w.entity_type='cr')
            where w.id=?",array($this->wallet_id))->result();
        
        $userType = ($this->userType == 'C') ?'Customer' : 'Freelancer';
        $content =$this->displayBox(array("label" => "Transaction ID&nbsp;:", "value" => !empty($query["transactionId"])?$query["transactionId"]:"-"));
        $content .=$this->displayBox(array("label" => "Payment Amount&nbsp;:", "value" => $query["amount"].CURRENCY_SYMBOL));
        $content.=$this->displayBox(array("label" => "Payment Date&nbsp;:", "value" => date(DATE_FORMAT_ADMIN,strtotime($query["createdDate"]))));
        $content.=$this->displayBox(array("label" => "Payment Status&nbsp;:", "value" => $query["status"]));
        $content.=$this->displayBox(array("label" => "User name&nbsp;:", "value" => $query["firstName"]." ".$query["lastName"]));
        $content.=$this->displayBox(array("label" => "UserType&nbsp;:", "value" => $this->location));                         
        $content.=$this->displayBox(array("label" => "Registration Date of user&nbsp;:", "value" => date(DATE_FORMAT_ADMIN,strtotime($query["userDate"]))));       
        if($query["entity_type"]=="j" ||$query["entity_type"]=="ml"){
            $content.=$this->displayBox(array("label" => "Job Title&nbsp;:", "value" => $query["jobTitle"]));
        }else if($query["entity_type"]=="s"){
            $content.=$this->displayBox(array("label" => "Service Title&nbsp;:", "value" => $query["serviceTitle"]));
        }else if($query["entity_type"]=="cr"){
            $content.=$this->displayBox(array("label" => "Membership Plan name&nbsp;:", "value" => $query["plan_title"]));
        }
        return $content;
    }

    public function getForm() {
        $content = '';
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/form-sd.skd");
        $main_content = $main_content->compile();
        $listing='';
        //$default=($this->defaultAmount=='y' ?'disabled' : '');
        $default ='';
        $static_a = ($this->isActive == 'y' ? 'checked' : '');
        $static_d = ($this->isActive != 'y' ? 'checked' : '');
        $fields = array(
            "%MAXPRICE%" => filtering($this->data['maxPrice']),
            "%MINPRICE%" => filtering($this->data['minPrice']),
            "%SPECIFICAMOUNT%" =>filtering($this->data['specificAmount']),
            "%DEFAULT%" => $default,
            "%STATIC_A%" => filtering($static_a),
            "%STATIC_D%" => filtering($static_d),
            "%TYPE%" => filtering($this->type),
            "%ID%" => filtering($this->id, 'input', 'int')
        );

        $content = str_replace(array_keys($fields), array_values($fields), $main_content);
        return sanitize_output($content);
    }

    public function dataGrid() 
    {
        $content = $operation = $whereCond = $totalRow =NULL;
        $result = $tmp_rows = $row_data = array();
        extract($this->searchArray);
        /*$chr = str_replace(array('_', '%',"'",'"'), array('\_', '\%',"\'",'\"'), $chr);
        // $whereCond = "";
        $chr = trim($chr); 
        if (isset($chr) && $chr != '') {
            $whereCond .=  " and  (maxPrice LIKE '%".$chr."%' OR minPrice LIKE '%".$chr."%' OR specificAmount LIKE '%".$chr."%' OR DATE_FORMAT(createdDate, '" . MYSQL_DATE_FORMAT . "') LIKE '%" . $chr . "%')"; 
        }*/
        

        if (isset($sort))
            $sorting = $sort . ' ' . $order;
        else
            $sorting = 'id DESC';

       // $qrySel = $this->db->pdoQuery("SELECT * FROM tbl_wallet" . $whereCond . " ORDER BY " . $sorting. " LIMIT " . $offset . " ," . $rows ." ")->results();
        $query = $this->db->pdoQuery("SELECT w.*,u.id as userId,u.firstName,u.lastName,u.userType
            FROM tbl_wallet As w
            JOIN tbl_users AS u ON u.id = w.userId
            where w.entity_type IN ('j','s','ml') AND w.status!='disputeCompleted' ORDER BY w.createdDate DESC")->results();
        
        foreach ($query as $fetchRes) 
        {
            $status = ($fetchRes['isActive'] == "y") ? "checked" : "";
            $switch = (in_array('status', $this->Permission)) ? $this->toggel_switch(array("action" => "ajax." . $this->module . ".php?id=" . $fetchRes['id'] . "", "check" => $status)) : '';
            $operation = '';
            $class='btn-viewbtn';
            $operation .=(in_array('view', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=view&wallet_id=" . $fetchRes['id'],"class" => "btn default blue $class","extraAtt" => "title = 'View'","value" => '<i class="fa fa-laptop"></i>')) : '';
            $status = "-";
            if($fetchRes["status"]=='c'){
                $status = "Paid";
            }else if($fetchRes["status"]=='p'){
                $status = "Pending";
            }else{
                if($fetchRes["transactionType"]=='disputeSolved'){
                    $status = "Dispute Solved";
                }
            }
            $final_array = array(
                filtering($fetchRes["transactionId"]),
                filtering($fetchRes["amount"].CURRENCY_SYMBOL),
                filtering(date(DATE_FORMAT_ADMIN,strtotime($fetchRes['createdDate']))),
                filtering($status),
                filtering($fetchRes["userType"]=="F"?"Freelancer":"Customer"),
                filtering($fetchRes["firstName"]." ".$fetchRes["lastName"]) 
            );            
            if (in_array('edit', $this->Permission) || in_array('delete', $this->Permission)) {
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

    public function getPageContent() {
        $final_result = NULL;
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/" . $this->module . ".skd");
        $main_content->breadcrumb = $this->getBreadcrumb();
        $final_result = $main_content->compile();
        return $final_result;
    }

    public function contentSubmit($data,$Permission){
        $table = "tbl_commision";

        $response = array();
        $response['status'] = false;
        $objPost = new stdClass();
        extract($data);
        $id = (isset($id) && $id!="") ? $id : 0;
        $objPost->minPrice = isset($minPrice) ? $minPrice : 0;
        $objPost->maxPrice = isset($maxPrice) ? $maxPrice : 0; 
        $objPost->specificAmount = isset($specificAmount) ? $specificAmount : ''; 
        $objPost->isActive = isset($isActive) ? $isActive : 'n';
        //print_r($objPost);exit;
        if($objPost->minPrice != ""  && $objPost->maxPrice!="")
        {
            if($objPost->maxPrice <= $objPost->minPrice){
                $response['error'] = "Maximum Price should be greater than the Minimum Price";
                echo json_encode($response);
                exit;
            }

            $query = $this->db->pdoQuery("SELECT minPrice,maxPrice from tbl_commision where commisionType='E' and  id != $id and (($objPost->minPrice between minPrice and maxPrice) or ($objPost->maxPrice between minPrice and maxPrice))")->results();
            
            
            if(count($query) > 0){
                $response['error'] = "This Price Range is already exists, Please select another Price Range";
                echo json_encode($response);
                exit;
            }

        } 

        if ($type == 'edit' && $id > 0) {
            if (in_array('edit', $Permission)) {
                    $objPostArray = (array) $objPost;
                    $this->db->update($table, $objPostArray, array("id" => $id));
                    $activity_array = array("id" => $id, "module" => $this->module, "activity" => 'edit');
                    add_admin_activity($activity_array);
                    $response['status'] = true;
                    $response['success'] = "Commission updated successfully";
                    $_SESSION["toastr_message"] = disMessage(array('type' => 'suc', 'var' => 'Commission updated successfully'));                
                    echo json_encode($response);
                    exit;
                } else {
                    $response['error'] = "You don't have permission to edit Commission";
                    echo json_encode($response);
                    exit;
                    }
        } else {

                if (in_array('add', $Permission)) {             

                   $objPost->createdDate = date("Y-m-d H:i:s");
                   $objPost->commisionType = 'E';
                   $objPostArray = (array) $objPost;
                   $id = $this->db->insert($table, $objPostArray)->getLastInsertId();

                   $activity_array = array("id" => $id, "module" => $this->module, "activity" => 'add');
                   add_admin_activity($activity_array);
                   $response['status'] = true;
                   $response['success'] = "Escrow Commission added successfully";
                   echo json_encode($response);
                   exit;
                } else {
                    $response['error'] = "You don't have permission to add Escrow Commission";
                    echo json_encode($response);
                    exit;
                }
            }
        }

}
