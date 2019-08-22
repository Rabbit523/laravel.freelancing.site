<?php

class Report extends Home {

    public $page_name;
    public $page_title;
    public $meta_keyword;
    public $meta_desc;
    public $page_desc;
    public $isActive;
    public $data = array();

    public function __construct($module, $id = 0, $objPost = NULL, $searchArray = array(), $type = '') {
        global $db, $fields, $sessCataId;
        $this->db = $db;
        $this->data['id'] = $this->id = $id;
        $this->fields = $fields;
        $this->module = $module;
        $this->table = 'tbl_report';

        $this->type = ($this->id > 0 ? 'edit' : 'add');
        $this->searchArray = $searchArray;
        parent::__construct();
        switch ($type) {
            case 'add' : {
                    $this->data['content'] = (in_array('add', $this->Permission)) ? $this->getForm() : '';
                    break;
                }
            case 'edit' : {
                    $this->data['content'] = (in_array('edit', $this->Permission)) ? $this->getForm() : '';
                    break;
                }    
            case 'delete' : {
                    $this->data['content'] = json_encode($this->dataGrid());
                    break;
                }
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
        $content = '';

        $data = $this->db->pdoQuery("
                SELECT tbl_report.*,r.userType as rusertype,r.firstName as rfirstName,r.lastName as rlastName,r.email as remail,r.profileImg as rprofileImg,r.location as rlocation,r.createdDate as rcreatedDate,rp.firstName as rpfirstName,rp.lastName as rplastName,rp.userType as rpusertype,rp.profileImg as rpprofileImg,rp.location as rplocation,rp.email as rpemail,rp.createdDate as rpcreatedDate 
            from tbl_report
            join tbl_users as r on (r.id=tbl_report.userId)
            join tbl_users as rp on (rp.id = tbl_report.reporterId) where tbl_report.id = $this->id
        ")->result();

        extract($data);
        $title = '';
        $value = '';
        $count_reported = '';

        if($data['reportType'] == 'User'){
            $title = "User Name";
            $name = getUserName($reportedId);            
            $value = "<a href='".SITE_ADM_MOD."users-sd'>$name</a>";
            $reported = $this->db->pdoQuery("SELECT count(id) as cnt_id from tbl_report where reportType='User' group by reporterId   
            ")->result();
            $count_reported = $reported['cnt_id'];

        } else if($data['reportType'] == 'Job'){
            $title = 'Job Title';
            $name = getJobTitle($reportedId); 
            $value = "<a href='".SITE_ADM_MOD."jobs-sd'>$name</a>";
            $reported = $this->db->pdoQuery("SELECT count(id) as cnt_id from tbl_report where reportType='Job' group by reporterId   
            ")->result();
            $count_reported = $reported['cnt_id'];
        } else {
            $title = 'Service Title';
            $name = getServiceTitle($reportedId);
            $value = "<a href='".SITE_ADM_MOD."services-sd'>$name</a>";
            $reported = $this->db->pdoQuery("SELECT count(id) as cnt_id from tbl_report where reportType='Service' group by reporterId   
            ")->result();
            $count_reported = $reported['cnt_id'];
        }
        
        $rusertype = ($rusertype =='C') ?'Customer' : 'Freelancer';  
        $rpusertype = ($rpusertype == 'C') ? 'Customer' : 'Freelancer';

        $img1=($rpprofileImg!='')? $rpprofileImg : "no_user_image.png";
        $rpimg="<img src='".SITE_UPD."profile/".$img1."' alt='".$rpfirstName.' '.$rplastName." profile' height='100px' width='100px'></img>";

        $img=($rprofileImg!='')? $rprofileImg : "no_user_image.png";
        $rimg="<img src='".SITE_UPD."profile/".$img."' alt='".$rfirstName.' '.$rlastName." profile' height='100px' width='100px'></img>";

        
        $content.="<div class='well'><center><h2><b> Reported User Details </b></h2></center>";
        
        $content.=$this->displayBox(array("label" => "User Name&nbsp;:", "value" => $rpfirstName.' '.$rplastName));
        $content.=$this->displayBox(array("label" => "Profile Image&nbsp;:", "value" => $rpimg));
        $content.=$this->displayBox(array("label" => "User Type&nbsp;:", "value" => $rpusertype));
        $content.=$this->displayBox(array("label" => "User Location&nbsp;:", "value" => $rplocation));
        $content.=$this->displayBox(array("label" => "User Email&nbsp;:", "value" => $rpemail));

        $content.=$this->displayBox(array("label" => "Registered Date&nbsp;:", "value" => filtering(date(DATE_FORMAT_ADMIN,strtotime($rpcreatedDate)))))."</div>";

        $content.="<div class='well'><center><h2><b> Reporter User Details </b></h2></center>";
        $content.=$this->displayBox(array("label" => "User Name&nbsp;:", "value" => $rfirstName.' '.$rlastName));
        $content.=$this->displayBox(array("label" => "Profile Image&nbsp;:", "value" => $rimg));
        $content.=$this->displayBox(array("label" => "User Type&nbsp;:", "value" => $rusertype));
        $content.=$this->displayBox(array("label" => "User Location&nbsp;:", "value" => $rlocation));
        $content.=$this->displayBox(array("label" => "User Email&nbsp;:", "value" => $remail));

        $content.=$this->displayBox(array("label" => "Registered Date&nbsp;:", "value" => filtering(date(DATE_FORMAT_ADMIN,strtotime($rcreatedDate)))))."</div>";

        $content.="<div class='well'><center><h2><b> Entity Details </b></h2></center>";
        $content.=$this->displayBox(array("label" => "Report Type&nbsp;:", "value" => '<b>'.$reportType.'</b>'));

        $content.=$this->displayBox(array("label" => $title, "value" => $value));
        $content.=$this->displayBox(array("label" => 'No. of times reported', "value" =>$count_reported));
        $content.=$this->displayBox(array("label" => 'Report Description', "value" => $reportMessage))."</div>";

        
        return $content;
    }
    public function dataGrid() {
        $content = $operation = $whereCond = $totalRow = NULL;
        $whereCond="where 1=1";
        $result = $tmp_rows = $row_data = array();
        extract($this->searchArray);
        $chr = str_replace(array('_', '%',"'",'"'), array('\_', '\%',"\'",'\"'), $chr);
        // if (isset($chr) && $chr != '') {
        //    $whereCond .= " AND (u.userName LIKE '%" . $chr . "%') OR u.userType='%".$chr."%'";
        // }
        if (isset($chr) && $chr != '') {

            $whereCond.= " AND (r.userName LIKE '%" . $chr . "%'  or rp.firstName LIKE '%".$chr."%')";
        }
        if(!empty($filtering_type)) {
            $whereCond.=" and tbl_report.reportType='".$filtering_type."'";
        }

        if(!empty($filtering_status)){
            $whereCond.=" and tbl_report.status='".$filtering_status."'";
        }

        if (isset($sort))
            $sorting = $sort . ' ' . $order;
        else
            $sorting = 'id DESC';

        $qrySel = $this->db->pdoQuery("SELECT tbl_report.*,r.userName as ruserName,r.email as remail,rp.userName as rpuserName,rp.email as rpemail 
            from tbl_report
            join tbl_users as r on (r.id=tbl_report.userId)
            join tbl_users as rp on (rp.id = tbl_report.reporterId)
            $whereCond ORDER BY $sorting limit $offset , $rows")->results();
        //printr($qrySel,1);


        $totalRow = $this->db->pdoQuery("SELECT tbl_report.*,r.userName as ruserName,r.email as remail,rp.userName as rpuserName,rp.email as rpemail 
            from tbl_report
            join tbl_users as r on (r.id=tbl_report.userId)
            join tbl_users as rp on (rp.id = tbl_report.reporterId)
            $whereCond")->affectedRows();

           
        foreach ($qrySel as $fetchRes) {
            // printr($fetchRes,1);
            // $status = ($fetchRes['isActive'] == "y") ? "checked" : "";

         $reported_time = $this->db->pdoQuery("SELECT count(reportedId) as count FROM tbl_report WHERE reportedId = ".$fetchRes['reportedId'] ." GROUP BY reportedId ")->result();

            // $switch = (in_array('status', $this->Permission)) ? $this->toggel_switch(array("action" => "ajax." . $this->module . ".php?id=" . $fetchRes['id'] . "", "check" => $status)) : '';
            $operation = '';
            $operation .=(in_array('view', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=view&id=" . $fetchRes['id'] . "", "class" => "btn default blue btn-viewbtn","extraAtt" => "title = 'View'", "value" => '<i class="fa fa-laptop"></i>')) : '';

            $operation .=(in_array('delete', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=delete&id=" . $fetchRes['id'] . "", "class" => "btn btn red btn-delete","extraAtt" => "title = 'Delete'", "value" => '<i class="fa fa-trash-o"></i>')) : '';
            
            // $operation .= 'Accept';

            if($fetchRes['status'] =='Acc'){
                $operation .='<label class="label label-success" style="font-size: 12px;">Accepted<label>';
            } else if($fetchRes['status'] =='Rej'){
                $operation .='<label class="label label-danger" style="font-size: 12px;">Rejected</label>';
            } else {
                // $operation .= $fetchRes['id'];
                $operation .= (in_array('view', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=approve&id=" . $fetchRes['id'] . "", "class" => "btn default btn-sm blue btn-approve", "value" => '<i class="fa fa-check"></i>', "title"=>"Approve")) : '';

                $operation .= (in_array('view', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=edit&id=" . $fetchRes['id'] . "", "class" => "btn default red btn-sm btnEdit", "value" => '<i class="fa fa-times"></i>', 'title'=>"Reject")) : '';
            }

            $user_status = isset($user_status) ? $user_status : '';
            $seller_status = isset($seller_status) ? $seller_status : '';
           
            $final_array = array(
                filtering($fetchRes["id"]),
                filtering($fetchRes["reportType"]),
                filtering(ucfirst($fetchRes["ruserName"]))."<br>".$user_status,
                filtering(ucfirst($fetchRes["rpuserName"]))."<br>".$seller_status
                //filtering($reported_time['count'])
            );

            // if (in_array('status', $this->Permission)) {
            //     $final_array = array_merge($final_array, array($switch));
            // }
            if (in_array('edit', $this->Permission) || in_array('delete', $this->Permission) || in_array('view', $this->Permission)) {
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

    public function getForm(){
        $content = '';

        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/form-sd.skd");
        $main_content = $main_content->compile();

        $fields = array("%MEND_SIGN%", "%FIRSTNAME%", "%MESSAGE%", "%REPLAYMESSAGE%", "%LASTNAME%", "%EMAIL%", "%ID%",  "%TYPE%" );

        $fields_replace = array(MEND_SIGN, $this->firstName, $this->message,$this->replayMessage,$this->lastName, $this->email, $this->id,$this->type);

        $content = str_replace($fields, $fields_replace, $main_content);
        return sanitize_output($content);
    }

    public function getPageContent() {
        $final_result = NULL;
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/" . $this->module . ".skd");
        $main_content->breadcrumb = $this->getBreadcrumb();
        $final_result = $main_content->compile();
        return $final_result;
    }

    public function contentSubmit($data){
        extract($data);
        /* Sending Mail to user who have been reported Job, Service, Freelancer or Customer */
        $qrySel=$this->db->pdoQuery("SELECT tbl_report.*,r.userName as ruserName,r.email as remail,rp.userName as rpuserName,rp.email as rpemail 
            from tbl_report
            join tbl_users as r on (r.id=tbl_report.userId)
            join tbl_users as rp on (rp.id = tbl_report.reporterId) where tbl_report.id = $id")->result();

        $reporterEmail = $qrySel['rpemail'];
        
        $userName = $qrySel['rpuserName'];
        $item =  $qrySel['reportType'];
        $item_name = $qrySel['ruserName'];
        $reject_reason = $rejectMessage;

        $objPost = new stdClass();
        $objPost->rejectMessage = isset($rejectMessage) ? filtering($rejectMessage, 'input') : '';
        $template_name ='report_reject';
        
        $arrayCont = array('greetings'=>$userName,
                           'ITEM'=>$item,
                           'ITEM_NAME'=>$item_name,
                           'REJECT_REASON'=>$reject_reason
                           );
        $array = generateEmailTemplate('report_reject',$arrayCont);
        sendEmailAddress($reporterEmail,$array['subject'],$array['message']);
        /* End Code */
        $type == 'edit';

        /* Updating Status and Rejection Message */
        $setVal = array(
                'status' => 'Rej',
                'rejectMessage' => $reject_reason
                );
        $this->db->update($this->table, $setVal, array("id" => $id));
        /* End Code */

        $activity_array = array("id" => $id, "module" => $this->module, "activity" => $type);
        add_admin_activity($activity_array);  

        $_SESSION["toastr_message"] = disMessage(array('type' => 'suc', 'var' => 'Reject message sent successfully'));
        redirectPage(SITE_ADM_MOD . $this->module);
        
    }
}
