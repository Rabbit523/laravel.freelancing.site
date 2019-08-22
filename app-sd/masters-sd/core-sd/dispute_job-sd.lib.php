<?php

class JobDispute extends Home {

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
        $this->table = 'tbl_listing_reported';

        $this->type = ($this->id > 0 ? 'edit' : 'add');
        $this->searchArray = $searchArray;
        parent::__construct();
        switch ($type) {
            case 'workroom' : {
                $this->data['content'] = $this->workroom();
                break;
            }
            case 'edit' : {
                    $this->data['content'] = $this->getForm();
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
    public function workroom() {
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/workroom-sd.skd");
        $main_content = $main_content->compile();   

        /* Load Messages Content*/
        $messageContent = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/message-sd.skd");
        $messageContent = $messageContent->compile();

        // $data = $this->db->SELECT("tbl_dispute",array('entityId'),array('disputeId'=>$this->id))->result();
        $data = $this->db->pdoQuery("
                SELECT d.*,ml.jobId from tbl_dispute as d
                left join tbl_milestones as ml on (ml.id = d.entityId)
                where d.disputeid = ?",array($this->id)
        )->result();

        $entityId = $data['jobId'];

        $messageData = $this->db->pdoQuery("SELECT tbl_messages.*,f.id as fid,f.userType as fuserType,f.userName as fuserName,
                c.id as cid from tbl_messages
                left join tbl_users as f on (f.id = tbl_messages.senderId)
                left join tbl_users as c on (c.id = tbl_messages.receiverId)
                where tbl_messages.entityId = $entityId and tbl_messages.entityType = 'J'   
                ")->results();

        $message_content = '';
        foreach ($messageData as $key => $value) {
            extract($value);
            if($messageType == 'text'){
                $class = ($value['fuserType'] == 'C') ? 'customer' : 'user';
                $time = date(DATE_FORMAT_ADMIN.' h:i:s A',strtotime($createdDate));
                $fieldArr = array("%CLASS%",'%MESSAGES%','%USER_NAME%','%TIME%');
                $replaceArr = array($class,$message,$fuserName,$time);
                $message_content .= str_replace($fieldArr, $replaceArr,$messageContent); 
            }
        }
        /* End Code */

        /* Getting files of message conversation */
        $fileContent = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/files-sd.skd");
        $fileContent = $fileContent->compile();
        $fileData = '';
        foreach ($messageData as $key => $value) {
            $img_class = isset($img_class) ? $img_class :'';

            extract($value);
            if($messageType == 'file'){
                
                $time = date('h:i:s A',strtotime($createdDate));
                $type = explode('.',$fileName);
                $type = (string)$type[1];
                $file_class = ''; 
                
                if($type !='pdf'){
                    $file_type ="<img src='".SITE_WORKROOM.$fileName."' alt=''>";
                } else {
                    $file_type ="";
                }

                $fileNM = SITE_WORKROOM.$fileName;
                $fieldArr = array('%FILE_NAME%','%FILE_PATH%','%FILE_NM%','%TYPE%','%TIME%','%IMG_CLASS%','%FILE_CLASS%');
                $replaceArr = array($fileName,$file_type,$fileNM,$type,$time,$img_class,$file_class);
                $fileData .= str_replace($fieldArr, $replaceArr,$fileContent); 
            }
        }
        /* End Code */
                  
        /* Getting Milestone Data */
        $mileStone = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/milestone-sd.skd");
        $mileStone = $mileStone->compile();

        $mileStoneContent = $this->db->SELECT('tbl_milestones',array('*'),array('jobId'=>$entityId))->results();
        $mileStoneData = '';
        foreach ($mileStoneContent as $key => $value) {
            extract($value);
            $date = date('j-M-Y',strtotime($createdDate));
            $status = ($paymentStatus == 'C') ? 'Paid': (($paymentStatus == 'p') ? 'Pending' : 'Asked For Payment');

            $fieldArr = array("%TITLE%",'%DESC%','%AMOUNT%','%DATE%','%STATUS%');
            $replaceArr = array($milestoneTitle,$milestoneDesc,$amount,$date,$status);
            $mileStoneData .= str_replace($fieldArr, $replaceArr,$mileStone); 
        }
        /* End code */


        $fieldArr = array("%MESSAGE%","%MILESTONE%","%FILEDATA%");
        $replaceArr = array($message_content,$mileStoneData,$fileData);
        
        $html = str_replace($fieldArr,$replaceArr, $main_content);     
        return $html;
    }


    public function viewForm() {
        $content='';

        $fetchResPCats = $this->db->pdoQuery("
            SELECT ml.*,tbl_dispute.*,u.id as uid,
            u.firstName AS ufirstName,u.lastName AS ulastName,u.profileImg as uprofileImg,u.userType AS uuserType,u.email as uemail,u.location AS ulocation,d.id AS did,
            d.firstName AS dfirstName,d.lastName AS dlastName,d.userType AS duserType,d.email AS demail,d.profileImg as dprofileImg,d.location AS dlocation,
            j.id as jid,j.jobTitle as jtitle,j.budget as jBudget  
            from tbl_dispute 
                left join tbl_users as u on (u.id=tbl_dispute.disputerId)
                left join tbl_users as d on (d.id=tbl_dispute.disputedId)
                left join tbl_milestones as ml on (ml.id = tbl_dispute.entityId)
                join tbl_jobs as j on (j.id = ml.jobId)
                where tbl_dispute.disputeId =".$this->id)->result();
        extract($fetchResPCats);
        // printr($fetchResPCats,1);

        $img1 = ($uprofileImg!='')? $uprofileImg : "no_user_image.png";
        $uimg = "<img src='".SITE_UPD."profile/".$img1."' alt='".$ufirstName.' '.$ulastName." profile' height='100px' width='100px'></img>";

        $img2 = ($fetchResPCats['uprofileImg'] !='')? $fetchResPCats['uprofileImg'] : "no_user_image.png";
        $dimg = "<img src='".SITE_UPD."profile/".$img2."' alt='".$fetchResPCats['ufirstName']." profile' height='100px' width='100px'></img>";
        $uuserType = ($uuserType =='C') ? 'Customer' :'Freelancer';
        $duserType = ($duserType =='C') ? 'Customer' :'Freelancer';
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

        $content.=$this->displayBox(array("label" => "Job Title&nbsp;:", "value" => filtering($jtitle)));
        $content.=$this->displayBox(array("label" => "Job Budget&nbsp;:", "value" => filtering(CURRENCY_SYMBOL.$amount)));

        $content.=$this->displayBox(array("label" => "Dispute Reason&nbsp;:", "value" => filtering($disputeReason)));
        $content.=$this->displayBox(array("label" => "Dispute Description&nbsp;:", "value" => filtering($disputeDesc)));
        if($status=='S'){
            $content.=$this->displayBox(array("label" => "Dispute Status&nbsp;:", "value" => filtering($disputeStatus)));
            $content.=$this->displayBox(array("label" => "Paid to User(Dispute Raised By) &nbsp;:", "value" => filtering($payToDisputer)));
            $content.=$this->displayBox(array("label" => "Paid to User(Dispute Against) &nbsp;:", "value" => filtering($payToEntityOwner)));            
        }        
        $content.=$this->displayBox(array("label" => "Disputed Date&nbsp;:", "value" => filtering(date('d-M-Y h:i A',strtotime($insertedDate)))))."</div>";
        
        return $content;
    }

    public function dataGrid() {
        $content = $operation = $whereCond = $totalRow = NULL;
        $whereCond="where 1=1 AND type ='ML' ";
        $result = $tmp_rows = $row_data = array();
        extract($this->searchArray);
        $chr = str_replace(array('_', '%',"'",'"'), array('\_', '\%',"\'",'\"'), $chr);
        $chr = trim($chr);
        if (isset($chr) && $chr != '') {
            $whereCond.= " and ( u.userType LIKE '%" . $chr . "%' or  u.firstName LIKE '%" . $chr . "%' or u.lastName LIKE '%" . $chr . "%'  or DATE_FORMAT(tbl_dispute.insertedDate, '" . MYSQL_DATE_FORMAT . "') LIKE '%" . $chr . "%')";
        }

        //echo $filtering_type;
        if(!empty($filtering_type) || $filtering_type!=0) {
            $whereCond.=" and tbl_dispute.status='".$filtering_type."'";
        }

        if (isset($sort))
            $sorting = $sort . ' ' . $order;
        else
            $sorting = 'disputeId DESC';

        $qrySel = $this->db->pdoQuery("
            SELECT tbl_dispute.*,ml.*,tbl_dispute.status as dstatus,j.*,u.firstName AS ufirstName,u.lastName AS ulastName,u.userType AS uuserType from tbl_dispute 
            join tbl_users as u on (u.id=tbl_dispute.disputerId)
            LEFT JOIN tbl_milestones AS ml ON (ml.id = tbl_dispute.entityId)
            LEFT JOIN tbl_jobs AS j ON (j.id=ml.jobId)
        $whereCond ORDER BY $sorting limit $offset , $rows")->results();

        $totalRow = $this->db->pdoQuery("SELECT tbl_dispute.*,u.firstName AS ufirstName,u.lastName AS ulastName,u.userType AS uuserType 
                from tbl_dispute 
                join tbl_users as u on (u.id=tbl_dispute.disputerId)
         $whereCond ")->affectedRows();

        foreach ($qrySel as $fetchRes) {    
            // printr($fetchRes,1);          
            $operation = '';
            // $operation .=($fetchRes['file_accept_status']=='rejected')?((in_array('edit', $this->Permission)) ? $this->operation(array("href" => "ajax." . $this->module . ".php?action=edit&id=" . $fetchRes['disputeId'] . "", "class" => "btn default black btnEdit","extraAtt" => "title = 'Edit'", "value" => '<i class="fa fa-edit"></i>')): ''):'';
            
            if($fetchRes['dstatus'] == 'P'){
                $operation .= (in_array('edit', $this->Permission)) ? $this->operation(array("href" => "ajax." . $this->module . ".php?action=edit&id=" . $fetchRes['disputeId'] . "", "class" => "btn default  black btnEdit","extraAtt" => "title = 'Solve Dispute'", "value" => 'Solve Dispute')) : '';
            }

            $operation .= (in_array('edit', $this->Permission)) ? $this->operation(array("href" => "ajax." . $this->module . ".php?action=workroom&id=" . $fetchRes['disputeId'] . "", "class" => "btn default  black btnEdit","extraAtt" => "title = 'View Workroom'", "value" => 'View Workroom')) : '';

            $operation .=(in_array('view', $this->Permission)) ? '' . $this->operation(array("href" => "ajax." . $this->module . ".php?action=view&id=" . $fetchRes['disputeId'] . "", "class" => "btn default blue btn-viewbtn","extraAtt" => "title = 'View'", "value" => '<i class="fa fa-laptop"></i>')) : '';

            $operation .=(in_array('delete', $this->Permission)) ? '' . $this->operation(array("href" => "ajax." . $this->module . ".php?action=delete&id=" . $fetchRes['disputeId'] . "", "class" => "btn default  red btn-delete","extraAtt" => "title = 'Delete'", "value" => '<i class="fa fa-trash-o"></i>')) : '';

            $status = $fetchRes['dstatus']=='P' ? 'Pending' : 'Solved';
            // $user_status = ($fetchRes['userDeletStatus']=='y')?"<span class='label label-warning'>&nbsp;User deleted</span>":'';

            $final_array = array(
                filtering($fetchRes["disputeId"]),
                filtering($fetchRes['ufirstName'].' '.$fetchRes['ulastName']),
                filtering($fetchRes['uuserType'] == 'C' ? 'Customer' :'Provider'),
                $status,
                filtering(date(DATE_FORMAT_ADMIN,strtotime($fetchRes["insertedDate"])))
            );
           
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
    public function getForm() {

        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/form-sd.skd");
        $main_content = $main_content->compile();

        $query = $this->db->pdoQuery('
            SELECT tbl_dispute.*,ml.*,ml.id as mlId,u.id as uid,
            u.firstName AS ufirstName,u.lastName AS ulastName,u.profileImg as uprofileImg,u.userType AS uuserType,u.email as uemail,u.location AS ulocation,d.id AS did,
            d.firstName AS dfirstName,d.lastName AS dlastName,d.userType AS duserType,d.email AS demail,d.profileImg as dprofileImg,d.location AS dlocation,
            j.id as jid,j.jobTitle as jtitle,j.budget as jBudget  
            from tbl_dispute 
                left join tbl_users as u on (u.id=tbl_dispute.disputerId)
                left join tbl_users as d on (d.id=tbl_dispute.disputedId)
                left join tbl_milestones as ml on (ml.id =tbl_dispute.entityId)
                left join tbl_jobs as j on (j.id=ml.jobId)
             where tbl_dispute.disputeId='.$this->id )->result();

        extract($query);
        $username = $ufirstName.' '.$ulastName;
        $userType = ($uuserType == 'C') ? 'Customer' : 'Freelancer';
        $duserName = $dfirstName.' '.$dlastName;
        $duserType = ($duserType == 'C') ? 'Customer' : 'Freelancer';


        // Count admin commission, we need to also update on dispute service module.        
        $bid_res = $this->db->pdoQuery("SELECT escrowRequired FROM tbl_job_bids WHERE jobid= ? AND isHired= ?",array($query["jid"],"y"))->result();
        $comm_type = (!empty($bid_res["escrowRequired"]) && $bid_res["escrowRequired"]=="y")?"E":"";

        $commission = getCommision($amount,$comm_type);
        if(empty($commission)){ 
            $commission_per = getTableValue("tbl_site_settings","value",array("constant"=>"DEFAULT_SERVICE_COMM"));
            $commission = !empty($stotalPayment) ? (($stotalPayment*$commission_per)/100) : 0; 
        }

        $milestone_res = $this->db->pdoQuery("SELECT SUM(amount) as amount FROM tbl_milestones WHERE jobid= ? AND paymentStatus!= ?",array($query["jid"],'c'))->result();
        $amount = !empty($milestone_res["amount"]) ? $milestone_res["amount"] : 0; 
        $grandTotal = ($amount-$commission);
        
        $fieldArr = array(  
            "%DUSER_NAME%"=>$username,   
            '%DUSER_TYPE%'=>$userType,    
            '%USER_NAME%'=>$duserName,  
            "%USER_TYPE%"=>$duserType,    
            "%BUDGET%"=>$grandTotal, 
            "%COMMISSION%"=>$commission, 
            "%PAY_TO_DISPUTER%"=>$payToDisputer, 
            "%PAY_TO_USER%"=>$payToEntityOwner, 
            "%TYPE%"=>"edit",    
            "%ID%"=>$disputeId, 
            "%UID%"=>$did, 
            "%DID%"=>$uid,
            "%JOB_ID%"=>$jid,
            "%MILESTONE%"=>$mlId
        );
        
        $html = str_replace(array_keys($fieldArr), array_values($fieldArr), $main_content);     
        return $html;
    }
    public function getPageContent() {
        $final_result = NULL;
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/" . $this->module . ".skd");
        $main_content->breadcrumb = $this->getBreadcrumb();
        $final_result = $main_content->compile();
        return $final_result;
    }

    public function contentSubmit($data,$Permission){
        $response = array();
        $response['status'] = false;
        
        if(!empty($data))
        {
            extract($data);
            $jobTitle = "<a href='".SITE_URL.'job/'.$job['jobSlug']."'>".$job['jobTitle']."</a>";
            $totalAmount = $feesType;

            $job = $this->db->pdoQuery(" SELECT ml.*,j.* from tbl_milestones as ml
                left join tbl_jobs as j on (j.id =ml.jobId)
                where ml.id = ?",array($milestoneId))->result();
            $ml_index = $this->getMilestone($milestoneId,$job['jobId']);
            
            $jobTitle = "<a href='".SITE_URL.'job/'.$job['jobSlug']."'>".$job['jobTitle']."</a>";
            $disputerDetail = getUser($disputerId);
            $disputedDetail = getUser($disputedId);

            notify('c',$disputerId,SITE_NM.' has solved your dispute.',SITE_URL.'job/workroom/'.$job['jobSlug']);
            notify('f',$disputedId,SITE_NM.' has solved your dispute.',SITE_URL.'job/workroom/'.$job['jobSlug']);
            
            if($disputerDetail['id'] == $job['posterId']){
                echo $job['posterId'].' '.$disputerDetail['id'];    
            }
            if($payToEntityOwner != $totalAmount  && $payToEntityOwner > 0 &&  $payToDisputer!= $totalAmount &&  $payToDisputer > 0){
                // If value is split into dispute creater and dispute raised
                if($ml_index['isLast']==0){
                    /* If milestone is not last milestone */
                    $arrayCont = array(
                        "JOB_TITLE" => $job_title,
                        "TOTAL_AMOUNT" => $totalAmount,
                        "AMOUNT1" => CURRENCY_SYMBOL.$payToEntityOwner,
                        "AMOUNT2" => CURRENCY_SYMBOL.$payToDisputer,
                        "TITLE" => $job['jobTitle'],
                        "JOB_TITLE" => "Milestone - ".$job['milestoneTitle'].'Job - '.$jobTitle,
                        "USER_NAME" => $disputerDetail['firstName'].' '.$disputerDetail['lastName'],
                        "CUST_NAME" => $disputerDetail['firstName'].' '.$disputerDetail['lastName']
                    );
                    $array = generateEmailTemplate('pay_to_dispute_creator',$arrayCont);                    
                    sendEmailAddress($disputerDetail['email'],$array['subject'],$array['message']);
                    $arrayCont1 = array(
                        "TITLE" => $job['jobTitle'],
                        "TOTAL_AMOUNT" => $totalAmount,
                        "JOB_TITLE" => "Milestone - ".$job['milestoneTitle'].'Job - '.$jobTitle,
                        "AMOUNT" => CURRENCY_SYMBOL.$payToDisputer,
                        "USER_NAME" => $disputedDetail['firstName'].' '.$disputedDetail['lastName']
                    );
                    $array1 = generateEmailTemplate('pay_to_disputed_user',$arrayCont1);                    
                    sendEmailAddress($disputedDetail['email'],$array1['subject'],$array1['message']);
                    /* End code */
                } else if($ml_index['isLast'] ==1){
                    /* If Disputed milestone is Last milestone */
                    
                    $arrayCont = array(
                        "JOB_TITLE" => $job_title,
                        "TOTAL_AMOUNT" => $totalAmount,
                        "AMOUNT1" => CURRENCY_SYMBOL.$payToDisputer,
                        "AMOUNT2" => CURRENCY_SYMBOL.$payToEntityOwner,
                        "TITLE" => $job['jobTitle'],
                        "JOB_TITLE" => "Milestone - ".$job['milestoneTitle'].'Job - '.$jobTitle,
                        "USER_NAME" => $disputerDetail['firstName'].' '.$disputerDetail['lastName']
                    );
                    $array = generateEmailTemplate('pay_to_dispute_creator_last_milestone',$arrayCont);
                    sendEmailAddress($disputedDetail['email'],$array['subject'],$array['message']); 

                    $arrayCont1 = array(
                        "TITLE" => $job['jobTitle'],
                        "TOTAL_AMOUNT" => $totalAmount,
                        "JOB_TITLE" => "Milestone - ".$job['milestoneTitle'].'Job - '.$jobTitle,
                        "AMOUNT1" => CURRENCY_SYMBOL.$payToDisputer,
                        "AMOUNT2" => CURRENCY_SYMBOL.$payToEntityOwner,
                        "USER_NAME" => $disputedDetail['firstName'].' '.$disputedDetail['lastName']
                        );
                    $array = generateEmailTemplate('pay_to_disputed_user',$arrayCont1);
                    
                    sendEmailAddress($disputerDetail['email'],$array['subject'],$array['message']);
                    /* End code */
                }
            } else if($payToDisputer == $totalAmount ){
                /* When full amount will be transfer to disputer */
                /* Mail to disputer */
                // pay_to_full_amount_disputer_creator_last_milestone
                if($ml_index['isLast']==0){
                    /* If Milestone is not last milestone */
                    $arrayCont = array(
                        "JOB_TITLE" => $job_title,
                        "TOTAL_AMOUNT" => $totalAmount,
                        "AMOUNT" => CURRENCY_SYMBOL.$payToDisputer,
                        "TITLE" => $job['jobTitle'],
                        "JOB_TITLE" => "Milestone - ".$job['milestoneTitle'].'Job - '.$jobTitle,
                        "USER_NAME" => $disputerDetail['firstName'].' '.$disputerDetail['lastName']
                    );

                    $array = generateEmailTemplate('pay_to_disputed_user',$arrayCont);
                    sendEmailAddress($disputerDetail['email'],$array['subject'],$array['message']); 
                    

                } else if($ml_index['isLast']==1){
                    /* If Milestone is last milestone */
                    $arrayCont = array(
                        "JOB_TITLE" => $job_title,
                        "TOTAL_AMOUNT" => $totalAmount,
                        "AMOUNT" => CURRENCY_SYMBOL.$payToDisputer,
                        "TITLE" => $job['jobTitle'],
                        "JOB_TITLE" => "Milestone - ".$job['milestoneTitle'].'Job - '.$job['jobTitle'],
                        "USER_NAME" => $disputerDetail['firstName'].' '.$disputerDetail['lastName']
                    );

                    $array = generateEmailTemplate('pay_to_full_amount_disputer_creator_last_milestone',$arrayCont);
                    sendEmailAddress($disputerDetail['email'],$array['subject'],$array['message']); 
                    
                }
                /* Mail to disputed */
                $arrayCont = array(
                    "JOB_TITLE" => $job_title,
                    "TOTAL_AMOUNT" => $totalAmount,
                    "AMOUNT" => CURRENCY_SYMBOL.$payToDisputer,
                    "TITLE" => $job['jobTitle'],
                    "JOB_TITLE" => "Milestone - ".$job['milestoneTitle'].'Job - '.$jobTitle,
                    "USER_NAME" => $disputerDetail['firstName'].' '.$disputerDetail['lastName']
                );

                $array = generateEmailTemplate('pay_to_full_amount_disputed_user',$arrayCont);
                sendEmailAddress($disputedDetail['email'],$array['subject'],$array['message']); 
                /* End code */
            } else if($payToEntityOwner == $totalAmount) {
                /* When full amount will be transfer to disputed user */
                
                /* End code */
            } 

            $id = isset($id) ? $id : 0;
            if ($type == 'edit' && $id > 0) {
                if (in_array('edit', $Permission)) {
                    $array=array(
                        "payToDisputer" => $payToDisputer,
                        "payToEntityOwner" => $payToEntityOwner,
                        "status" => 'S', 
                        "paidDate" => date('Y-m-d H:i:s')
                    );
                    $whereArray = array("disputeid " => $id); 
                    $this->db->update("tbl_dispute", $array, $whereArray);

                    $disputeDetail = $this->db->pdoQuery("SELECT d.*,ml.*,jb.*,jb.id as jobId FROM tbl_dispute AS d
                        JOIN tbl_milestones AS ml ON (ml.id = d.entityId)
                        JOIN tbl_jobs AS jb ON (jb.id = ml.jobId)
                        where d.disputeId = ?",array($id))->result();
                    $date = date('Y-m-d H:i:s');
                    $jobId = $disputeDetail['jobId'];
                    $aWhere = array("id"=> $jobId);
                    $update = array("jobStatus"=>'dsCo');
                    $this->db->update('tbl_jobs',$update,$aWhere);

                    release_job_payment($disputeDetail['entityId']);

                    //Update Dispute amount on both users
                    $this->db->delete('tbl_wallet',array('entity_id'=>$jobId,'entity_type'=>'j'));

                    $DisputedId = $disputeDetail['disputedId'];
                    $DisputerId = $disputeDetail['disputerId'];
                    $disputedUser = getUser($DisputedId);
                    $disputerUser = getUser($DisputerId);
                    $email = $disputedUser['email'];
                    //Update disputer amount on wallet
                    $disputer_array = array(
                        "paymentStatus" => 'a',
                        "userType" => $disputerUser["userType"],
                        "entity_id" => $jobId,
                        "entity_type" => 'j',
                        "userId" => $DisputerId,
                        "amount" => $disputeDetail["payToDisputer"],
                        "transactionType" => 'disputeSolved',
                        "status" => 'disputeCompleted',
                        "createdDate" => date('Y-m-d H:i:s'),
                        "ipAddress" => get_ip_address(),
                    );
                    // pre_print($disputer_array,false);
                    $this->db->insert('tbl_wallet',$disputer_array);

                    //Update disputed amount on wallet
                    $disputed_array = array(
                        "paymentStatus" => 'a',
                        "userType" => $disputedUser["userType"],
                        "entity_id" => $jobId,
                        "entity_type" => 'j',
                        "userId" => $DisputedId,
                        "amount" => $disputeDetail["payToEntityOwner"],
                        "transactionType" => 'disputeSolved',
                        "status" => 'disputeCompleted',
                        "createdDate" => date('Y-m-d H:i:s'),
                        "ipAddress" => get_ip_address(),
                    );
                    // pre_print($disputed_array);
                    $this->db->insert('tbl_wallet',$disputed_array);

                    //update admin commission 
                    if(!empty($adminCommission)){
                        $this->db->insert("tbl_admin_commision",array("entityid"=>intval($job['jobId']),"entitytype"=>'j',"amount"=>(string)$adminCommission,"createdDate"=>date('Y-m-d H:i:s')));
                    }

                    if($payToDisputer > 0){
                        $this->db->pdoQuery("UPDATE tbl_users SET walletAmount = walletAmount + ? WHERE id = ?", array($payToDisputer,$disputerId));
  
                    } else if($payToEntityOwner > 0){
                        $this->db->pdoQuery("UPDATE tbl_users SET walletAmount = walletAmount + ? WHERE id = ?", array($payToEntityOwner,$disputedId));
                    }

                    /* Send mail disputed user */
                    $arrayCont = array(
                        "USER_NAME" => $disputerUser['firstName'].' '.$disputerUser['lastName'],
                        "TITLE" => $disputeDetail['jobTitle']
                    );
                    $array = generateEmailTemplate('disputer_ask_for_work_cancellation',$arrayCont);
                    sendEmailAddress($email,$array['subject'],$array['message']);
                    /* End code */
                                               
                    $response['status'] = true;
                    $response['success'] = "Status updated successfully";
                    echo json_encode($response);
                    exit;
                } else {
                    $response['error'] = "You don't have permission to edit Status";
                    echo json_encode($response);
                    exit;
                }
            }   
        }
    }

    public function getMilestone($ml_id,$job_id){
        $mls = $this->db->select('tbl_milestones',array('*'),array('jobId'=>$job_id))->results(); 
        $count_numbers = count($mls);

        foreach ($mls as $key => $value) {
            if($value['id'] == $ml_id){
                $data['index'] = $key + 1;    
            } 
            if($value['id'] == $ml_id && $count_numbers == $key+1){
                $data['isLast'] = 1;        
            } else {
                $data['isLast'] = 0;
            }   
        }   
        return $data;        
    }
}
