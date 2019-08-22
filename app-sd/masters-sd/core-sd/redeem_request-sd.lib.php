<?php

class RedeemRequest extends Home {
    
    public $typeName;
    public $isActive;
    public $data = array();

    public function __construct($module, $id = 0, $objPost = NULL, $searchArray = array(), $type = '') {
        global $db, $fields, $sessCataId;
        $this->db = $db;
        $this->data['id'] = $this->id = $id;
        $this->fields = $fields;
        $this->module = $module;
        $this->table = 'tbl_redeem_request';

        $this->type = ($this->id > 0 ? 'edit' : 'add');
        $this->searchArray = $searchArray;
        parent::__construct();
        if ($this->id > 0) {
            $qrySel = $this->db->select($this->table, "*", array("id" => $id))->result();
            
            $fetchRes = $qrySel;
            $this->data['userId'] = $this->userId = $fetchRes['userId'];
            $this->data['amount'] = $this->amount = $fetchRes['amount'];
            $this->data['projectId'] = $this->projectId = $fetchRes['projectId'];
            $this->data['transactionId'] = $this->transactionId = $fetchRes['transactionId'];
            $this->data['paymentStatus'] = $this->paymentStatus = $fetchRes['paymentStatus'];
            $this->data['earnFrom'] = $this->earnFrom = $fetchRes['earnFrom'];
            $this->data['status'] = $this->status = $fetchRes['status'];
            $this->data['createdDate'] = $this->createdDate = $fetchRes['createdDate'];

        } else {
            $this->data['userId'] = $this->userId = '';
            $this->data['amount'] = $this->amount = '';
            $this->data['projectId'] = $this->projectId = '';
            $this->data['transactionId'] = $this->transactionId = '';
            $this->data['paymentStatus'] = $this->paymentStatus = '';
            $this->data['earnFrom'] = $this->earnFrom = '';
            $this->data['status'] = $this->status = '';
            $this->data['createdDate'] = $this->createdDate = '';

        }
        switch ($type) {
            case 'add' : {
                    $this->data['content'] = $this->getForm();
                    break;
                }
            case 'edit' : {
                    $this->data['content'] = $this->getForm();
                    break;
                }
            case 'view' : {
                    $this->data['content'] = $this->viewForm();
                    break;
                }
            case 'delete' : {
                    $this->data['content'] = json_encode($this->dataGrid());
                    break;
                }
            case 'datagrid' : {
                    $this->data['content'] = json_encode($this->dataGrid());
                    break;
                }
        }
    }

    public function viewForm() {
        $content = 
                $this->displayBox(array("label" => "Sub AdminType&nbsp;:", "value" => filtering($this->typeName)));
                
        return $content;
    }

    public function getForm() {
        $content = '';
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/form-sd.skd");
        $main_content = $main_content->compile();
        $static_a = ($this->isActive == 'y' ? 'checked' : '');
        $static_d = ($this->isActive != 'y' ? 'checked' : '');

        $fields = array(
            "%CATEGORY_NAME%",
            "%STATIC_A%",
            "%STATIC_D%",
            "%TYPE%",
            "%ID%"
        );

        $fields_replace = array(
            filtering($this->data['categoryName']),
            filtering($static_a),
            filtering($static_d),
            filtering($this->type),
            filtering($this->id, 'input', 'int')
        );

        $content = str_replace($fields, $fields_replace, $main_content);
        return sanitize_output($content);
    }

    public function dataGrid() {
        $content = $operation = $whereCond = $totalRow = NULL;
        $result = $tmp_rows = $row_data = array();
        extract($this->searchArray);
        $chr = str_replace(array('_', '%'), array('\_', '\%'), $chr);
        // $whereCond = "";
            // $date = date('Y-m-d');

        if (isset($chr) && $chr != '') {
           $whereCond .= " AND (u.userName LIKE '%" . $chr . "%' OR r.amount LIKE '%".$chr."%' OR DATE_FORMAT(r.createdDate, '" . MYSQL_DATE_FORMAT . "') LIKE '%" . $chr . "%') OR u.userType='%".$chr."%'";
        }
        if(isset($user_type) && $user_type!=''){
            $whereCond.=' and u.userType="'.$user_type.'"';
        }
        if(isset($filtering_type) && $filtering_type!=''){
            $whereCond.=' and r.paymentStatus="'.$filtering_type.'"';
        }

        if(isset($date) && $date!='') 
        {
             $dates = explode(',',$date);
             // print_r($dates);exit();
            // $dates = date('Y-m-d');
            if(!empty($dates)){
                $start_date = $dates[0]." 00:00:00";
                $end_date = $dates[1]." 23:59:00";
                $whereCond .= " AND (r.createdDate BETWEEN '".$start_date."' AND '".$end_date."')";
                // $whereCond .= "AND (r.createdDate BETWEEN '".$start_date."' AND '".$end_date."')";
            }

        }

        if (isset($sort))
            $sorting = $sort . ' ' . $order;
        else
            $sorting = 'r.id DESC';

        $totalRow = $this->db->pdoQuery("Select r.id As redeemId,r.amount,r.createdDate,u.firstName,u.lastName,u.userType,r.paymentStatus from tbl_redeem_request As r 
            LEFT JOIN tbl_users As u ON u.id = r.userId where 1 $whereCond 
            ")->affectedRows();

         $qrySel = $this->db->pdoQuery("Select r.id As redeemId,r.amount,r.createdDate,u.firstName,u.lastName,u.userType,r.paymentStatus from tbl_redeem_request As r 
            LEFT JOIN tbl_users As u ON u.id = r.userId where 1 
             $whereCond ORDER BY $sorting LIMIT $offset , $rows")->results();

       
  
        foreach ($qrySel as $fetchRes) {
            // $status = ($fetchRes['isActive'] == "y") ? "checked" : "";
            $operation = '';           

            if($fetchRes['paymentStatus']=="pending")
            {
                $operation .= $this->operation(array("href" => "index.php?action=pay_user&wid=" . $fetchRes['redeemId'] . "", "class" => "btn default btn-warning btn-xs","extraAtt" => "title = 'Pay'", "value" => CURRENCY_SYMBOL.$fetchRes['amount'].'&nbsp;Pay to User','title'=>'pay to User',"target"=>'_blank'));
                $payment_status = 'Pending';
                $operation .= $this->operation(array("href" => "ajax.redeem_request-sd.php?action=decline_payment&wid=" . $fetchRes['redeemId'] . "", "class" => "btn default btn-danger btn-xs decline_payment","extraAtt" => "title = 'Decline Request' style='font-size: 16px;' ", "value" =>'<i class="fa fa-times" aria-hidden="true"></i>','title'=>'Decline Request',"target"=>'_blank'));
            }
            else
            {
                $operation .= "<button class='btn default btn-info btn-xs'>Payment Completed</button>";
                $payment_status = 'Already Paid';
            }
            


            $final_array = array(
                filtering($fetchRes["redeemId"]),
                filtering(ucfirst($fetchRes["firstName"]))." ".filtering(ucfirst($fetchRes["lastName"])),
                ($fetchRes["userType"]=='C') ? 'Customer':'Freelancer',
                CURRENCY_SYMBOL.filtering($fetchRes["amount"]),
                $payment_status,
                filtering(date(DATE_FORMAT_ADMIN,strtotime($fetchRes["createdDate"]))),
                $operation
            );
            
            
            if (in_array('view', $this->Permission)) {
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

    

    public function operation($text) 
    {
        $text['title'] = isset($text['title']) ? $text['title'] : '';
        $text['href'] = isset($text['href']) ? $text['href'] : 'Enter Link Here: ';
        $text['value'] = isset($text['value']) ? $text['value'] : '';
        $text['name'] = isset($text['name']) ? $text['name'] : '';
        $text['class'] = isset($text['class']) ? '' . trim($text['class']) : '';
        $text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';
        $main_content = new MainTemplater(DIR_ADMIN_TMPL .'/operation-sd.skd');
        $main_content = $main_content->compile();
        $fields = array("%HREF%", "%CLASS%", "%VALUE%", "%EXTRA%","%TITLE%");
        $fields_replace = array($text['href'], $text['class'], $text['value'], $text['extraAtt'],$text['title']);
        return str_replace($fields, $fields_replace, $main_content);
    }

    public function displaybox($text) {

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

    public function getPageContent() {
        $final_result = NULL;
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/" . $this->module . ".skd");
        $main_content->breadcrumb = $this->getBreadcrumb();
        $final_result = $main_content->compile();
        return $final_result;
    }

}
