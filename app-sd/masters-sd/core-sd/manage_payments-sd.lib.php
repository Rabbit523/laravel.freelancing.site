<?php

class Payments extends Home {
    
    public $typeName;
    public $isActive;
    public $data = array();

    public function __construct($module, $id = 0, $objPost = NULL, $searchArray = array(), $type = '') {
        global $db, $fields, $sessCataId;
        $this->db = $db;
        $this->data['id'] = $this->id = $id;
        $this->fields = $fields;
        $this->module = $module;
        $this->table = 'tbl_wallet';

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
                $this->displayBox(array("label" => "User Name&nbsp;:", "value" => filtering(getUserDetails('userName',$this->userId))));
        $content.=($this->projectId!=0)? $this->displayBox(array("label" => "Project URL&nbsp;:", "value" => filtering(getlistingUrl_admin($this->projectId)))):'';
        $content.=$this->displayBox(array("label" => "Amount&nbsp;:", "value" => filtering(CURRENCY_SYMBOL.$this->amount))).
                $this->displayBox(array("label" => "Transaction Id&nbsp;:", "value" => (filtering($this->transactionId) == '')?'--':filtering($this->transactionId))).
                /*$this->displayBox(array("label" => "Earn From&nbsp;:", "value" => filtering($this->earnFrom))).*/
                $this->displayBox(array("label" => "Status&nbsp;:", "value" => filtering($this->status))).
                $this->displayBox(array("label" => "Payment Status&nbsp;:", "value" => (($this->paymentStatus=='c')?'Completed':'Pending'))).
                $this->displayBox(array("label" => "Payment Date&nbsp;:", "value" => filtering(date(DATE_FORMAT_ADMIN.' h:i:s A',strtotime(($this->createdDate))))));
        return $content;
    }

    
    public function dataGrid() {
        $content = $operation = $whereCond = $totalRow = NULL;
        $result = $tmp_rows = $row_data = array();
        extract($this->searchArray);
        $chr = str_replace(array('_', '%'), array('\_', '\%'), $chr);
         $whereCond = "where 1=1";
        if (isset($chr) && $chr != '') {
           $whereCond .= " and (userName LIKE '%" . $chr . "%' OR w.amount LIKE '%".$chr."%' OR w.transactionId LIKE '%".$chr."%' OR DATE_FORMAT(w.createdDate, '" . MYSQL_DATE_FORMAT . "') LIKE '%" . $chr . "%')";
        }
        if(isset($filtering_type) && $filtering_type != ''){
            $whereCond.=" and w.earnFrom='".$filtering_type."'";
        }

        if (isset($sort))
            $sorting = $sort . ' ' . $order;
        else
            $sorting = 'w.id DESC';

        $totalRow = $this->db->pdoQuery("SELECT w.*,w.id As wid,w.createdDate As walletDate,u.*,u.id As uId,(CASE when w.userId=0 then 'admin' else  u.userName end) as userName  FROM tbl_wallet As w LEFT JOIN tbl_users As u ON w.userId = u.id $whereCond")->affectedRows();
        
        $qrySel = $this->db->pdoQuery("SELECT w.*,w.id As wid,w.createdDate As walletDate,u.*,u.id As uId,(CASE when w.userId=0 then 'admin' else  u.userName end) as userName FROM tbl_wallet As w LEFT JOIN tbl_users As u ON w.userId = u.id $whereCond ORDER BY $sorting LIMIT $offset , $rows")->results();
        foreach ($qrySel as $fetchRes) {
            $status = ($fetchRes['isActive'] == "y") ? "checked" : "";
            $operation = '';           

            $operation .=(in_array('view', $this->Permission)) ? '&nbsp;&nbsp;' . $this->operation(array("href" => "ajax." . $this->module . ".php?action=view&id=" . $fetchRes['wid'] . "", "class" => "btn default blue  btn-viewbtn","extraAtt" => "title = 'View'", "value" => '<i class="fa fa-laptop"></i>')) : '';
            

            $final_array = array(
                filtering($fetchRes["wid"]),
                filtering(ucfirst($fetchRes["userName"])),
                CURRENCY_SYMBOL.filtering($fetchRes["amount"]),
                filtering($fetchRes["transactionId"]),
                filtering(date(DATE_FORMAT_ADMIN.' h:i:s A',strtotime($fetchRes["walletDate"])))
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

    

    public function operation($text) {

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

        $main_content = new MainTemplater(DIR_ADMIN_TMPL . '/displaybox.skd');
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
