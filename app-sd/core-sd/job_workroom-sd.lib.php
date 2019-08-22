<?php

class JobWorkroom extends Home {
	function __construct($module = "", $slug = 0, $id="",$token = "") {
		foreach ($GLOBALS as $key => $values) {
			$this->$key = $values;
		}
		$this->module = $module;
		$this->id = $id;
        $this->slug = $slug;
    }

    public function getPageContent()
    {
        $content = $show_dispute = "";
        $sub_content = new MainTemplater(DIR_TMPL . $this->module ."/".$this->module.".skd");
        $sub_content = $sub_content->compile();
        $hide_dispute_btn = $hire_freelancer_btn = 'hide';

        if($this->sessUserType == 'Customer'){

            $bidderDetail = $this->db->pdoQuery("
                select b.*,b.id as bidId,b.budget AS bidPrice,j.*,j.id as jobId,c.".l_values('category_name')." AS cName,s.".l_values('subcategory_name')." AS sName from tbl_job_bids AS b
                LEFT JOIN tbl_jobs AS j ON b.jobId  = j.id
                LEFT JOIN tbl_category AS c ON c.id = j.jobCategory
                LEFT JOIN tbl_subcategory AS s ON j.jobSubCategory = s.id
                where j.jobSlug ='".$this->slug."' and (b.isHired ='a' || b.isHired ='y')")->result();
            /*New Query JJ*/

            $milestones_approve = $this->db->pdoQuery('SELECT * FROM `tbl_milestones` WHERE jobId = ? and status =? ',array($bidderDetail['jobId'],'a'))->affectedRows();

            if($bidderDetail['isHired'] == 'a' && $milestones_approve > 0){
                $hire_freelancer_btn = '';
            }

            $jobStatus = $bidderDetail['jobStatus'];
            $jobId = $bidderDetail['jobId'];

            /* Review Data */
            $review_query = $this->db->pdoQuery('select * from tbl_reviews where entityId = ? and entityType = ? ',array($jobId,'J'));
            $review_record = $review_query->affectedRows();
            $review_detail = $review_query->result();
            /* Review Data End */

            /* Raise dispute */
            $milestone_data = $this->db->select('tbl_milestones',array('*'),array('jobId'=>$jobId))->results();
            // $dispute_res = $this->db->select('tbl_dispute',array('*'),array('entityId'=>$jobId))->result();            
            $is_hired_freelancer = getTableValue('tbl_job_bids','id',array('jobid'=>$jobId,'isHired'=>'y'));
            $couter = 1;
            if(!empty($milestone_data)){
                foreach ($milestone_data as $key => $value) {
                    if($value['status'] == 'a' && $couter ==1){
                        $couter = $couter+1;
                         $dispute_res = $this->db->pdoQuery("SELECT  d.disputeId FROM `tbl_dispute` as d JOIN tbl_milestones as m ON (m.id=d.entityId) WHERE jobId= ? ",array($jobId))->result();
                        if(empty($dispute_res) && !empty($is_hired_freelancer)){
                            $hide_dispute_btn = '';
                        }
                    } 
                }
            }
            /* End code  */

            $winnerId = $bidderDetail['userId'];
            $customerId = $this->sessUserId;
            $customerDetail = getUser($customerId);
            $freelancerData = getUser($winnerId);
            $posterId = $this->sessUserId;
            $milestone_content = $this->getMilestones($jobId,$posterId);
            $milestones = ($milestone_content !='0') ? $milestone_content :'';

            $jobCommission = 0;
            $budget = $bidderDetail['bidPrice'];

            if($bidderDetail['escrowRequired']=='y'){
                $comm = getCommision($budget,'E');
                $adminJobCommision= $comm;
            } else {
                $comm = JOB_ADMIN_COMM;
                $adminJobCommision = ($budget * $comm) /100;
                $show_dispute="hide";
            }
            $content = array(
                "%F_NAME%" => $_SESSION['pickgeeks_userType'] == 'Freelancer' ? $customerDetail['firstName'].' '.$customerDetail['lastName'] : $freelancerData['firstName'].' '.$freelancerData['lastName'],
                "%F_LOCATION%" => $_SESSION['pickgeeks_userType'] == 'Freelancer' ? $customerDetail['location'] : $freelancerData['location'],
                "%F_IMAGE%" => $_SESSION['pickgeeks_userType'] == 'Freelancer' ? getUserImage($customerId) : getUserImage($winnerId),
                "%C_NAME%" => $customerDetail['firstName'].' '.$customerDetail['lastName'],
                "%C_LOCATION%" => $customerDetail['location'],
                '%USER_SLUG%' => SITE_URL.'f/profile/'.$freelancerData['userSlug'],
                "%C_IMAGE%" => getUserImage($customerId),
                "%JOB_NAME%" => $bidderDetail['jobTitle'],
                "%JOB_SLUG%" => $bidderDetail['jobSlug'],
                "%CAT_NAME%" => $bidderDetail['cName'],
                "%SUB_CAT%" => $bidderDetail['sName'],
                "%JOB_TYPE%" => ($bidderDetail['jobType'] == 'pu') ? 'Public': 'Private',
                "%JOB_LEVEL%" => getJobExpLevel($bidderDetail['expLevel']),
                "%JOB_STATUS%" => getJobStatus($bidderDetail['jobStatus']),
                "%JOB_PENDING%" => ($bidderDetail['jobStatus'] == 'p') ? 'hide':'',
                "%JOB_BUDGET%" =>  $budget,
                "%SHOW_DISPUTE%" => $show_dispute,
                "%BID_ID%" => $bidderDetail['bidId'],
                "%JOB_ID%" => $bidderDetail['jobId'],
                "%MILESTONE_CONTENT%" => $milestones,
                "%EDIT_MILESTONE%" => $this->edit_milestone($jobId),
                "%SHOW_EDIT_MILESTONE%" => $this->edit_milestone($jobId) == '' ? 'hide' : '',
                "%CUST_MILESTONE_HIDE%" => ($milestone_content == '0') ? 'hide':'',
                '%HIRE_FREELANCER_BTN%' => $hire_freelancer_btn,
                "%ADMIN_COMM%" => $adminJobCommision,
                "%MILESTONE_CREATED%" => ($milestone_content != '0') ? 'hide' :'',
                "%CONFIRM_MILESTONES%" => 'hide',
                "%START_JOB_WORK_HIDE%" => 'hide',
                "%MESSAGES%" => $this->messages_list($jobId),
                "%RECEIVER_NAME%" => filtering(ucfirst($freelancerData['firstName']))." ".filtering(ucfirst($freelancerData['lastName'])),
                "%RECEIVER_IMG%" => getUserImage($winnerId),
                "%RATING_LOOP%" =>  $this->review_loop($jobId),
                "%WINNER_ID%" => $winnerId,
                "%FILES%" =>$this->file_list($jobId),
                "%LEAVE_FEEDBACK%" => ($jobStatus=='co') ? '' : 'hide',
                "%GIVEN_FEEDBACK%" => ($jobStatus=='co' && $review_detail['startratings'] > 0) ? '' : 'hide',
                "%REVIEW_RATE%" => ($jobStatus=='co') ? '' : 'hide',
                "%MESSAGE_READ_ONLY%" => ($jobStatus=='co') ? 'hide' : '',
                "%REVIEW_ID%" => $review_detail['id'],
                "%GIVE_FEEDBACK%" => ($review_detail['startratings'] =='') ? '':'hide',
                "%DISPUTE_LIST%" => $this->dispute_list($jobId),
                "%HIDE_DISPUTE_BTN%" => $hide_dispute_btn,
                "%RP_NAME%" => $freelancerData['firstName'].' '.$freelancerData['lastName'],
                "%RP_IMAGE%" =>  getUserImage($winnerId),
                "%RP_ID%" => $winnerId
            );

        } else {
            $bidderDetail = $this->db->pdoQuery("
                select b.*,b.id as bidId,b.id as bidId,j.*,j.id as jobId,c.".l_values('category_name')." AS cName,s.".l_values('subcategory_name')." AS sName from tbl_job_bids AS b
                LEFT JOIN tbl_jobs AS j ON b.jobId  = j.id
                LEFT JOIN tbl_category AS c ON c.id = j.jobCategory
                LEFT JOIN tbl_subcategory AS s ON j.jobSubCategory = s.id
                where j.jobSlug ='".$this->slug."' and b.userId = ".$this->sessUserId." and (b.isHired = 'a' || b.isHired = 'y')")->result();

            $jobStatus = $bidderDetail['jobStatus'];

            if($bidderDetail['isHired'] == 'a' || $bidderDetail['isHired'] == 'y'){

            } else {
                redirectPage(SITE_URL.'my-bids');
            }

            $jobId = $bidderDetail['jobId'];

            if($bidderDetail['jobStatus'] =='ud'){
                $hide_dispute_btn = '';
            }
            $btn_dispute = 'hide';

            /* Get all milestones and check whether milestone first is completed or not */
            $ml_content = $this->getAllMilestones($jobId);
            $ml_cnt=1;
            if(!empty($ml_content)){
                foreach ($ml_content as $key => $value) {
                    if($ml_cnt == 1 && (($value["workStatus"]=="p" && $value["submitWork"]=="y") || $value['workStatus'] == 'c' || $value['paymentStatus'] == 'c')){
                        $dispute_res = $this->db->pdoQuery("SELECT  d.disputeId FROM `tbl_dispute` as d JOIN tbl_milestones as m ON (m.id=d.entityId) WHERE jobId= ? ",array($jobId))->result();
                        if(empty($dispute_res)){
                            $btn_dispute = '';
                        }
                    }
                }
            }
            /* end code */

            /* Review Data */
            $review_query = $this->db->pdoQuery('select * from tbl_reviews where entityId = ? and entityType = ? ',array($jobId,'J'));
            $review_record = $review_query->affectedRows();
            $review_detail = $review_query->result();
            /* Review Data End */

            /* End code */
            $posterId = $bidderDetail['posterId'];
            $userId = $this->sessUserId;
            $userDetail = getUser($userId);
            $customerDetail = getUser($posterId);
            $milestone_content = $this->getMilestones($jobId,$posterId);
            $milestones = ($milestone_content !='0') ? $milestone_content :'';
            $milestones_status = $this->milestonesAccepted($jobId);
            $start_work = ($jobStatus=="h"?"":"hide");

            if($bidderDetail['escrowRequired']=='n'){
                $show_dispute="hide";
            }

            $content = array(
                "%JOB_NAME%" => $bidderDetail['jobTitle'],
                "%JOB_SLUG%" => $bidderDetail['jobSlug'],
                "%F_NAME%" => $_SESSION['pickgeeks_userType'] == 'Freelancer' ? $customerDetail['firstName'].' '.$customerDetail['lastName'] : $userDetail['firstName'].' '.$userDetail['lastName'],
                "%F_LOCATION%" => $_SESSION['pickgeeks_userType'] == 'Freelancer' ? $customerDetail['location'] : $userDetail['location'],
                "%F_IMAGE%" => $_SESSION['pickgeeks_userType'] == 'Freelancer' ? getUserImage($posterId) : getUserImage($userId) ,
                "%C_LOCATION%" => $_SESSION['pickgeeks_userType'] == 'Freelancer' ? $customerDetail['location'] : $userDetail['location'],
                '%USER_SLUG%' => 'javascript:void(0)',
                "%C_IMAGE%" => getUserImage($posterId),
                "%C_NAME%" => $customerDetail['firstName'].' '.$customerDetail['lastName'],
                "%CAT_NAME%" => $bidderDetail['cName'],
                "%SUB_CAT%" => $bidderDetail['sName'],
                "%JOB_TYPE%" => ($bidderDetail['jobType'] == 'pu') ? 'Public': 'Private',
                "%JOB_LEVEL%" => getJobExpLevel($bidderDetail['expLevel']),
                "%JOB_STATUS%" => getJobStatus($bidderDetail['jobStatus']),
                "%SHOW_DISPUTE%" => $show_dispute,
                "%MILESTONE_CREATED%" => 'hide',
                "%MILESTONE_CONTENT%" => $milestones,
                "%CONFIRM_MILESTONES%" => '',
                "%START_JOB_WORK_HIDE%" => $start_work,
                "%BID_ID%" => $bidderDetail['bidId'],
                "%JOB_ID%" => $bidderDetail['jobId'],
                "%BTN_MILESTONE_HIDE%" => $milestones_status,
                "%RECEIVER_IMG%" => getUserImage($posterId),
                "%SHOW_EDIT_MILESTONE%" =>  'hide' ,
                '%HIRE_FREELANCER_BTN%' => $hire_freelancer_btn,
                "%WINNER_ID%" => $userId,
                "%RECEIVER_NAME%" => filtering(ucfirst($customerDetail['firstName']))." ".filtering(ucfirst($customerDetail['lastName'])),
                "%MESSAGES%" => $this->messages_list($jobId),
                "%RATING_LOOP%" => $this->review_loop($jobId),
                "%REVIEW_ID%" => $review_detail['id'],
                "%GIVE_FEEDBACK%" => ($review_detail['customerStarRating'] =='' || $review_detail['customerStarRating'] == 0) ? '':'hide',
                "%FILES%" =>$this->file_list($jobId),
                "%DISPUTE_LIST%" => $this->dispute_list($jobId),
                "%DISPUTE_BTN%" => $hide_dispute_btn,
                "%LEAVE_FEEDBACK%" => ($jobStatus == 'co') ? '' : 'hide',
                "%GIVEN_FEEDBACK%" => ($jobStatus=='co' && $review_detail['customerStarRating'] > 0) ? '' : 'hide',
                "%REVIEW_RATE%" => ($jobStatus=='co' ) ? '' : 'hide',
                "%MESSAGE_READ_ONLY%" => ($jobStatus=='co') ? 'hide' : '',
                "%HIDE_DISPUTE_BTN%" => $btn_dispute,
                "%RP_NAME%" => $customerDetail['firstName'].' '.$customerDetail['lastName'],
                "%RP_IMAGE%" =>  getUserImage($posterId),
                "%RP_ID%" => $posterId
            );
            
        }

        return str_replace(array_keys($content),array_values($content), $sub_content);
    }


    public function currentMilestone($jobId){

        $data = $this->db->select('tbl_milestones',array('*'),array('jobid'=>$jobId))->results();
        $cnt = 1;
        $currentMilestone = '';
        foreach ($data as $key => $value) {
            if($cnt=='1' && $value['paymentStatus'] == 'p' || $value['paymentStatus'] == 'ap'){
                $cnt++;
                $currentMilestone = $value['id'];
            }
        }
        return $currentMilestone;
    }

    public function saveDisputeData($data)
    {
        extract($data);

        $userDetail = $this->jobDetail($entityId);

        $disputedId = ($userDetail['posterId'] == $this->sessUserId) ? $userDetail['bidderId'] : $userDetail['posterId'];


        $currentMilestone = $this->currentMilestone($entityId);
        $mlid = $currentMilestone;
        $milestoneIndex = $this->getMilestone($mlid,$entityId);

        $mlData = $this->db->select('tbl_milestones',array('*'),array('id'=>$mlid))->result();

        if($currentMilestone != ''){

            $this->db->insert("tbl_dispute",
                array(
                    "disputerId"=>$this->sessUserId,
                    "disputedId"=>$disputedId,
                    "type"=>'ML',
                    "entityId"=>$mlid,
                    "disputeReason"=>$reason,
                    "disputeDesc"=>$description,
                    'disputerAccept' => 'n',
                    'disputedAccept' => 'n',
                    "insertedDate"=>date('Y-m-d H:i:s'),
                    "status"=> 'P',
                    "ipAddress"=> get_ip_address()
                )
            );
            $this->db->update("tbl_jobs",array("jobStatus"=>'ud'),array("id"=>$entityId));


            $disputerDetail = getUser($this->sessUserId);
            $disputedDetail = getUser($disputedId);


            $nmsg = $disputerDetail['userName'].' has raised a dispute against you for '.$mlData['milestoneTitle'].' milestone';
            $nlink = SITE_URL.'job/workroom/'.$userDetail['jobSlug'];
            notify('f',$disputedId,$nmsg,$nlink);

            $nmsg = $disputerDetail['userName'].' has raised a dispute for Milestone';
            $nlink = SITE_ADMIN_URL.'units-sd/dispute_job-sd';
            notify('a','0',$nmsg,$nlink);

            $disputername = filtering(ucfirst($disputerDetail['firstName']))." ".filtering(ucfirst($disputerDetail['lastName']));
            $disputedname = filtering(ucfirst($disputedDetail['firstName']))." ".filtering(ucfirst($disputedDetail['lastName']));



            $admin_link = SITE_ADMIN_URL."dispute_job-sd";
            $admin_link = "<a href='".$admin_link."'>Admin Link</a>";
            $disputer_link = "<a href='".$link1."'>".$disputername."</a>";
            $disputed_link = "<a href='".$link2."'>".$disputedname."</a>";

            $jobTitle = $userDetail['jobTitle'];
            $title = $mlData['milestoneTitle'];
            $str = $disputername.'has raised dispute against '.$disputedname.' for job - '.$jobTitle;

            $this->db->insert("tbl_notification",array('userId'=>0,'message'=>$str,'isRead'=>'n','createdDate'=>date('Y-m-d H:i:s'),'notificationType'=>'a'));

            $type = $this->sessUserType== 'c' ? 'f' :'c';

            $this->db->insert("tbl_notification",array('userId'=>0,'message'=>$str,'isRead'=>'n','createdDate'=>date('Y-m-d H:i:s'),'notificationType'=>$type));

            /* send mail to disputed Person */
            $arrayCont = array(
                "USER_NAME"=> $disputer_link,
                "NO" => $milestoneIndex['index'],
                "TITLE" => $title,
                "JOB_TITLE" => $jobTitle,
                "C_EMAIL" => $disputerDetail['email'],
                "F_EMAIL" => $disputedDetail['email'],
                "FREELANCER_NAME" => $disputedname,
                "REASON"=>$reason,
                "DESCRIPTION"=>$description,
                "ADM_LINK" => $adm_link
            );


            $array = generateEmailTemplate('dispute_raised_by_customer',$arrayCont);
            sendEmailAddress($disputedDetail['email'],$array['subject'],$array['message']);
            
            /* End code */

            /* Send mail to Admin */
            $arrayCont = array(
                "USER_NAME" => $disputer_link,
                "CUST" => $disputerdname,
                "NO" => $milestoneIndex['index'],
                "FREELANCER_NAME" => $disputed_link,
                "C_EMAIL" => $disputerDetail['email'],
                "F_EMAIL" => $disputedDetail['email'],
                "REASON"=>$reason,
                "DESCRIPTION"=>$description,
                "ADM_LINK" => $adm_link,
                "TITLE" => $title,
                "JOB_TITLE" => $jobTitle,
            );


            $array = generateEmailTemplate('dispute_raised_by_customer_to_admin',$arrayCont);
            
            sendEmailAddress(ADMIN_EMAIL,$array['subject'],$array['message']);
            /* End code */

            $msgType = $_SESSION["msgType"] = disMessage(array('type'=>'suc','var'=>DISPUTE_HAS_BEEN_GENERATED_SUCCESSFULLY));
            redirectPage(SITE_URL."job/workroom/".$userDetail['jobSlug']);
        }
        
    }

    public function dispute_list($id)
    {
        $ml_id = $this->currentMilestone($id);
        $query = $this->db->pdoQuery("
            select d.*,u.firstName,u.lastName,u.userType from tbl_dispute As d
            LEFT JOIN tbl_users As u ON d.disputerId = u.id
            where d.type=? and d.entityId=? and (d.disputerId = ? OR d.disputedId=?)",array('ML',$ml_id,$this->sessUserId,$this->sessUserId))->results();
        // pre_print($query);
        $data = '';
        $disputerStatus = 'hide';
        $query_cnt = count($query);
        if($query_cnt > 0)
        {
            $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/job_dispute-sd.skd");
            $sub_content = $sub_content->compile();
            foreach ($query as $key => $value) {
                $AccContentstatus = '';
                    //if($query_cnt == $key + 1) {
                $userType = '';
                $userType = $_SESSION['pickgeeks_userType'];
                $AccContentstatus = 'hide';
                $disputerStatus = 'hide';

                if($value['status'] != 'S' || $value['disputerAccept'] == 'r' || $value['disputedAccept'] == 'r' || ($value['disputerAccept'] == 'y' && $value['disputedAccept'] == 'y')){
                    $AccContentstatus = 'hide';
                    $disputerStatus = 'hide';
                }else{       
                    if($value["disputerId"] == $this->sessUserId && $value["disputerAccept"]=="n"){
                        $AccContentstatus = '';
                        $disputerStatus = 'hide';
                    }else if($value["disputedId"] == $this->sessUserId && $value["disputedAccept"]=="n" && $value["disputerAccept"]=="y"){
                        $disputerStatus = '';
                        $AccContentstatus = 'hide';
                    }
                }

                $freelancer_res = $this->db->pdoQuery("SELECT CONCAT(firstName,' ',lastName) as name FROM tbl_users WHERE id=".$value["disputedId"])->result();
                if($_SESSION["pickgeeks_userType"]=="Customer"){
                    $full_name = $freelancer_res["name"];
                }else{
                    $full_name = $value['firstName']." ".$value['lastName'];                    
                }
                $array = array(
                    "%DISPUTER_NAME%" => filtering(ucfirst($full_name)),
                    "%DISPUTER_TYPE%" => ($userType != 'Freelancer') ? 'Freelancer' : 'Customer',
                    "%REASON%" => filtering($value['disputeReason']),
                    "%DESC%" => filtering($value['disputeDesc']),
                    "%DISPUTE_STATUS%" => ($value['status'] == 'P') ? 'Pending':'Solved',
                    "%DISPUTE_DATE%"=> date('dS F,Y',strtotime($value['insertedDate'])),
                    "%USER_TYPE%" => $userType,
                    "%ML_ID%" => $value['entityId'],
                    "%ID%" => $value['disputeId'],
                    "%ACC_HIDE%" => $AccContentstatus,
                    "%DISPUTER_STATUS%" => $disputerStatus,
                    "%USER_NAME%" => filtering(ucfirst($value['firstName']))." ".filtering(ucfirst($value['lastName']))
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

    public function milestonesAccepted($id){

        $ml_data = $this->db->pdoQuery("SELECT * from tbl_milestones where status = 'a' and jobId =".$id)->affectedRows();
        $ml_data2 = $this->db->pdoQuery("SELECT * from tbl_milestones where jobId =".$id)->affectedRows();
        if(($ml_data > 0) || $ml_data2 == 0){
            return 'hide';
        } else {
            return '';
        }
    }

    public function review_loop($jobId)
    {
        $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/give_review_loop-sd.skd");
        $sub_content = $sub_content->compile();

        if($this->sessUserType == "Customer")
        {
            $array = array(
                "%RATE1%" => 'Punctuality',
                "%RATE2%" => 'Work Clarification',
                "%RATE3%" => 'Expertise',
                "%RATE4%" => 'Communication',
                "%RATE5%" => 'Work Quality',
                "%RATE5_CLASS%" => '',
                "%USER_TYPE%" => 'C',
                "%JOB_ID%" => $jobId
            );
        }
        else
        {
            $array = array(
                "%RATE1%" => 'Requirement Clarification',
                "%RATE2%" => 'On Time Payment',
                "%RATE3%" => 'On Time Response',
                "%RATE4%" => 'Communication',
                "%RATE5%" => 'Average Star rating',
                "%RATE5_CLASS%" => 'hide',
                "%USER_TYPE%" => 'F',
                "%JOB_ID%" => $jobId
            );
        }
        return str_replace(array_keys($array), array_replace($array), $sub_content);
    }

    public function messages_list($id)
    {
        $data = $this->db->pdoQuery("select * from tbl_messages where entityId=? and entityType=? and (deleteUser IS NULL OR (NOT FIND_IN_SET(".$this->sessUserId.",deleteUser))) ",array($id,'J'))->results();

        $loop_data = '';
        if(count($data)>0)
        {
            $img_src = '';
            foreach ($data as $value)
            {
                $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/job_messages-sd.skd");
                $sub_content = $sub_content->compile();
                if($value['messageType']=='file')
                {
                    $ext = explode(".", $value['fileName']);
                    $type = (string)$ext[1];

                    if ($type=='pdf')
                    {
                        $img_src = SITE_UPD."pdf.png";
                    }
                    else if($type=='doc' || $type=='docx')
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
                    "%MSG_ID%" => $value['id']
                );

                $loop_data .= str_replace(array_keys($array), array_replace($array), $sub_content);
            }
        }
        else
        {
            $loop_data = '
            <div class="no-msg">
            <div class="no-msg-img">
            <img src="'.SITE_IMG.'icon/no-msg.png" alt=""/>
            <h2>'.NO_MESSAGES_FOUND.'</h2>
            <p>'.LOOKS_LIKE_YOU_HAVE_NOT_INITIATED_A_CONVERSATION.'</p>
            </div>
            </div>';
        }
        // echo $loop_data;
        return $loop_data;
    }

    public function startwork($data)
    {
        extract($data);
        /* If Dispute creator would like to continue work with Disputed Person */
        $aWhere = array("disputeId"=> $dp_id);
        $update = array("disputerAccept" => 'y');
        $this->db->update('tbl_dispute',$update,$aWhere);

        $disputeDetail = $this->db->pdoQuery("SELECT d.*,ml.*,jb.* FROM tbl_dispute AS d
            LEFT JOIN tbl_milestones AS ml ON (ml.id = d.entityId)
            LEFT JOIN tbl_jobs AS jb ON (jb.id = ml.jobId)
            where d.disputeId = ?
            ",array($dp_id))->result();

        $DisputedId = $disputeDetail['disputedId'];
        $DisputerId = $disputeDetail['disputerId'];
        $disputedUser = getUser($DisputedId);
        $disputerUser = getUser($DisputerId);
        $email = $disputedUser['email'];

        /* Send mail disputed user */
        $arrayCont = array(
            "USER_NAME" => $disputerUser['firstName'].' '.$disputerUser['lastName'],
            "CUST_NAME" => $disputedUser['firstName'].' '.$disputedUser['lastName'],
            "TITLE" => $disputeDetail['jobTitle'],
            "JOB_TITLE" => $disputeDetail['jobTitle']
        );

        $array = generateEmailTemplate('disputer_ask_for_work_continue',$arrayCont);
        
        sendEmailAddress($email,$array['subject'],$array['message']);
        /* End code */

    }

    public function endWork($data)
    {
        extract($data);
        /* If Dispute creator would like to continue work with Disputed Person */
        $aWhere = array("disputeId"=> $dp_id);
        $update = array("disputerAccept" => 'r');
        $this->db->update('tbl_dispute',$update,$aWhere);

        $disputeDetail = $this->db->pdoQuery("SELECT d.*,ml.*,jb.*,jb.id as jobId FROM tbl_dispute AS d
            LEFT JOIN tbl_milestones AS ml ON (ml.id = d.entityId)
            LEFT JOIN tbl_jobs AS jb ON (jb.id = ml.jobId)
            where d.disputeId = ?
            ",array($dp_id))->result();
        $date = date('Y-m-d H:i:s');
        $jobId = $disputeDetail['jobId'];
        $aWhere = array("id"=> $jobId);
        $update = array("jobStatus"=>'dsc');
        $this->db->update('tbl_jobs',$update,$aWhere);

        release_job_payment($disputeDetail['entityId']);

        //Update Dispute amount on both users
        $this->db->update('tbl_wallet',array("paymentStatus"=>'ds','status'=>'disputeCompleted','createdDate'=>$date),array('entity_id'=>$jobId,'entity_type'=>'j'));

        $DisputedId = $disputeDetail['disputedId'];
        $DisputerId = $disputeDetail['disputerId'];
        $disputedUser = getUser($DisputedId);
        $disputerUser = getUser($DisputerId);
        $email = $disputedUser['email'];

        //Update disputer amount on wallet
        $disputer_array = array(
            "paymentStatus" => 'c',
            "userType" => $disputerUser["userType"],
            "entity_id" => $jobId,
            "entity_type" => 'j',
            "userId" => $DisputerId,
            "amount" => $disputeDetail["payToDisputer"],
            "transactionType" => 'disputeSolved',
            "status" => 'completed',
            "createdDate" => date('Y-m-d H:i:s'),
            "ipAddress" => get_ip_address(),
        );
        // pre_print($disputer_array,false);
        $this->db->insert('tbl_wallet',$disputer_array);

        //Update disputed amount on wallet
        $disputed_array = array(
            "paymentStatus" => 'c',
            "userType" => $disputedUser["userType"],
            "entity_id" => $jobId,
            "entity_type" => 'j',
            "userId" => $DisputedId,
            "amount" => $disputeDetail["payToEntityOwner"],
            "transactionType" => 'disputeSolved',
            "status" => 'completed',
            "createdDate" => date('Y-m-d H:i:s'),
            "ipAddress" => get_ip_address(),
        );
        // pre_print($disputed_array);
        $this->db->insert('tbl_wallet',$disputed_array);

        // amount update on user wallet
        if($disputeDetail["payToDisputer"] > 0){
            $this->db->pdoQuery("UPDATE tbl_users SET walletAmount = walletAmount + ? WHERE id = ?", array($disputeDetail["payToDisputer"],$DisputerId));
        } 
        if($disputeDetail["payToEntityOwner"] > 0){
            $this->db->pdoQuery("UPDATE tbl_users SET walletAmount = walletAmount + ? WHERE id = ?", array($disputeDetail["payToEntityOwner"],$disputedId));
        }

        /* Send mail disputed user */
        $arrayCont = array(
            "USER_NAME" => $disputerUser['firstName'].' '.$disputerUser['lastName'],
            "TITLE" => $disputeDetail['jobTitle']
        );


        $array = generateEmailTemplate('disputer_ask_for_work_cancellation',$arrayCont);
        sendEmailAddress($email,$array['subject'],$array['message']);
        /* End code */

    }

    public function disputeAccJob($data){
        /* If disputed user want to accept the job and again work with customer or freelancer */
        extract($data);
        $aWhere = array("disputeId"=> $dp_id);
        $update = array("disputedAccept" => 'y');
        $this->db->update('tbl_dispute',$update,$aWhere);

        $disputeDetail = $this->db->pdoQuery("SELECT d.*,ml.*,jb.*,jb.id as jobId FROM tbl_dispute AS d
            LEFT JOIN tbl_milestones AS ml ON (ml.id = d.entityId)
            LEFT JOIN tbl_jobs AS jb ON (jb.id = ml.jobId)
            where d.disputeId = ?
            ",array($dp_id))->result();

        $jobId = $disputeDetail['jobId'];
        $aWhere = array("id"=> $jobId);
        $update = array("jobStatus"=>'dsp');
        $this->db->update('tbl_jobs',$update,$aWhere);


        $DisputedId = $disputeDetail['disputedId'];
        $DisputerId = $disputeDetail['disputerId'];
        $disputedUser = getUser($DisputedId);
        $disputerUser = getUser($DisputerId);
        $email1 = $disputedUser['email'];
        $email2 = $disputerUser['email'];
        $link = SITE_URL.'job/'.$disputeDetail['jobSlug'];

        // amount update on user wallet
        // if($disputeDetail["payToDisputer"] > 0){
        //     $this->db->pdoQuery("UPDATE tbl_users SET walletAmount = walletAmount + ? WHERE id = ?", array($disputeDetail["payToDisputer"],$DisputerId));
        // } 
        // if($disputeDetail["payToEntityOwner"] > 0){
        //     $this->db->pdoQuery("UPDATE tbl_users SET walletAmount = walletAmount + ? WHERE id = ?", array($disputeDetail["payToEntityOwner"],$disputedId));
        // }

        /* Send mail to Both disputed user */
        $arrayCont = array(
            "TITLE" => $disputeDetail['jobTitle'],
            "JOB_TITLE" => "<a href='".$link."'>".$disputeDetail['jobTitle']."</a>"
        );

        $array = generateEmailTemplate('both_agreed_for_job',$arrayCont);
        
        sendEmailAddress($email1,$array['subject'],$array['message']);
        sendEmailAddress($email2,$array['subject'],$array['message']);
        /* end code */
    }


    public function disputeDenyJob($data){
        /* If disputed user want to accept the job and again work with customer or freelancer */
        extract($data);
            //printr($data,1);
        $aWhere = array("disputeId"=> $dp_id);
        $update = array("disputedAccept" => 'r');
        $this->db->update('tbl_dispute',$update,$aWhere);

        $disputeDetail = $this->db->pdoQuery("SELECT d.*,ml.*,jb.*,jb.id as jobId FROM tbl_dispute AS d
            LEFT JOIN tbl_milestones AS ml ON (ml.id = d.entityId)
            LEFT JOIN tbl_jobs AS jb ON (jb.id = ml.jobId)
            where d.disputeId = ?
            ",array($dp_id))->result();

        release_job_payment($disputeDetail['entityId']);
        $jobId = $disputeDetail['jobId'];
        $aWhere = array("id"=> $jobId);
        $update = array("jobStatus"=>'dsc');
        $this->db->update('tbl_jobs',$update,$aWhere);
        
        //Update Dispute amount on both users

        $this->db->update('tbl_wallet',array("paymentStatus"=>'ds','status'=>'disputeCompleted','createdDate'=>$date),array('entity_id'=>$jobId,'entity_type'=>'j'));

        //Update disputer amount on wallet
        $DisputedId = $disputeDetail['disputedId'];
        $DisputerId = $disputeDetail['disputerId'];
        $disputedUser = getUser($DisputedId);
        $disputerUser = getUser($DisputerId);
        $email = $disputedUser['email'];

        //Update disputer amount on wallet
        $disputer_array = array(
            "paymentStatus" => 'c',
            "userType" => $disputerUser["userType"],
            "entity_id" => $jobId,
            "entity_type" => 'j',
            "userId" => $DisputerId,
            "amount" => $disputeDetail["payToDisputer"],
            "transactionType" => 'disputeSolved',
            "status" => 'completed',
            "createdDate" => date('Y-m-d H:i:s'),
            "ipAddress" => get_ip_address(),
        );
        // pre_print($disputer_array,false);
        $this->db->insert('tbl_wallet',$disputer_array);

        //Update disputed amount on wallet
        $disputed_array = array(
            "paymentStatus" => 'c',
            "userType" => $disputedUser["userType"],
            "entity_id" => $jobId,
            "entity_type" => 'j',
            "userId" => $DisputedId,
            "amount" => $disputeDetail["payToEntityOwner"],
            "transactionType" => 'disputeSolved',
            "status" => 'completed',
            "createdDate" => date('Y-m-d H:i:s'),
            "ipAddress" => get_ip_address(),
        );
        // pre_print($disputed_array);
        $this->db->insert('tbl_wallet',$disputed_array);

        /* Send mail disputed user */
        $arrayCont = array(
            "USER_NAME" => $disputerUser['firstName'].' '.$disputerUser['lastName'],
            "TITLE" => $disputeDetail['jobTitle']
        );

        $array = generateEmailTemplate('disputer_ask_for_work_cancellation',$arrayCont);
        sendEmailAddress($disputedUser['email'],$array['subject'],$array['message']);
        sendEmailAddress($disputerUser['email'],$array['subject'],$array['message']);
        /* end code */
    }


    public function file_list($jobId)
    {
        $query = $this->db->pdoQuery("select * from tbl_messages where entityId=? and entityType=? and messageType=?",array($jobId,'J','file'))->results();
        $data = '';
        foreach ($query as $value)
        {
            $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/files_loop-sd.skd");
            $sub_content = $sub_content->compile();
            $ext = explode(".",$value['fileName']);
            $type = (string)$ext[1];

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
                "%LINK%"=> SITE_WORKROOM.$value['fileName'],
                "%IMG%"=> "<img src='".$img_src."'>",
                "%IMAGE_NAME%"=> $value['fileName'],
                "%TIME%"=> date('dS F,Y',strtotime($value['createdDate']))
            );
            $data .= str_replace(array_keys($array), array_values($array), $sub_content);
        }
        $mls = $this->db->select('tbl_milestones',array('*'),array('jobId'=>$jobId))->results();
        foreach ($mls as $key => $value) {
            if($value['submitWork'] == 'y'){
                $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/files_loop-sd.skd");
                $sub_content = $sub_content->compile();
                $img_src = SITE_UPD."Downloads 4.png";
                $array1 = array(
                    "%EXT%"=> 'work',
                    "%LINK%"=> SITE_MLS_FILES.$value['submitWorkFile'],
                    "%IMG%"=> "<img src='".$img_src."'>",
                    "%IMAGE_NAME%"=> $value['submitWorkFile']."<p>".filtering($value['submitWorkMessage'])."</p>",
                    "%TIME%"=> date('dS F,Y',strtotime($value['submitWorkDate']))
                );
                $data .= str_replace(array_keys($array1), array_values($array1), $sub_content);
            }
        }

        return $data;
    }

    public function jobDetail($jobId)
    {

        $data = $this->db->pdoQuery("
            SELECT j.*,b.*,b.userId as bidderId from tbl_jobs AS j
            LEFT JOIN  tbl_job_bids AS b ON j.id = b.jobId
            where j.id = ? and (b.isHired = 'y' or b.isHired = 'a')",array($jobId))->result();

        return $data;
    }

    public function saveReviewData($data)
    {
        extract($data);
        $jobDetails = $this->jobDetail($jobId);
        $customerId = $jobDetails['posterId'];
        $freelanserId =  $jobDetails['bidderId'];

        $jobId = !empty($jobId) ? $jobId : 0;

        $review_query = $this->db->pdoQuery('select * from tbl_reviews where entityId = ? and entityType = ? ',[$jobId,'J']);
        $review_record = $review_query->affectedRows();
        $review_row = $review_query->result();

        if($userType == 'C')
        {
            $avg_rate = ROUND(($rate1Val+$rate2Val+$rate3Val+$rate4Val+$rate5Val)/5);
            $array = array(
                "entityId"=>$jobId,
                "entityType"=>'J',
                "customerId"=>$customerId,
                "freelancerId"=>$freelanserId,
                "review" =>filtering($review),
                "punctality"=>$rate1Val,
                "workClarification"=>$rate2Val,
                "expertise"=>$rate3Val,
                "communication"=>$rate4Val,
                "workQuality"=>$rate5Val,
                "createdDate"=>date('Y-m-d H:i:s'),
                "startratings"=>(int)$avg_rate
            );

            $freelanserDetail = getUser($freelanserId);
            $user = filtering(ucfirst($freelanserDetail['firstName']));
            $userId = $freelanserId;
            $notificationType = 'f';

        } else {
            $avg_rate = ROUND(($rate1Val+$rate2Val+$rate3Val+$rate4Val)/4);
            $array = array(
                "entityId"=>$jobId,
                "entityType"=>'J',
                "customerId"=>$customerId,
                "freelancerId"=>$freelanserId,
                "custReview"=>filtering($review),
                "reqClarification"=>$rate1Val,
                "onTimePayment"=>$rate2Val,
                "onTimeResponse"=>$rate3Val,
                "custComm "=>$rate4Val,
                "customerCreatedDate"=>date('Y-m-d H:i:s'),
                "customerStarRating"=>(int)$avg_rate
            );
            $customerDetail = getUser($customerId);
            $user = filtering(ucfirst($customerDetail['firstName']));
            $userId = $customerId;
            $notificationType = 'c';
        }

        if($review_record > 0)
        {
            $this->db->update("tbl_reviews",$array,array("id"=>$review_row['id']));
        }
        else
        {
            $this->db->insert("tbl_reviews",$array);
        }

        $msg = $user." gives review for service - ".filtering(ucfirst($jobDetail['jobTitle']));
        $link = SITE_URL."job/workroom/".$slug;
        $this->db->insert("tbl_notification",array("userId"=>$userId,"message"=>$msg,"detail_link"=>$link,"isRead"=>'n',"notificationType"=>$notificationType,"createdDate"=>date('Y-m-d H:i:s')));
        $msgType = $_SESSION["msgType"] = disMessage(array('type'=>'suc','var'=>YOUR_REVIEW_HAS_BEEN_SUBMITTED_SUCCESSFULLY));
        redirectPage(SITE_URL."job/workroom/".$slug);
    }

    public function reviewRating($id,$type)
    {
        $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/given_review_loop-sd.skd");
        $sub_content = $sub_content->compile();

        $review_query = $this->db->pdoQuery("select * from tbl_reviews where id=?",array($id));
        $value = $review_query->result();
        $review_rows = $review_query->affectedRows();
        $data = '';
        $user = 0;
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
                    "%RATE1%" => 'Punctuality',
                    "%RATE2%" => 'Work Clarification',
                    "%RATE3%" => 'Expertise',
                    "%RATE4%" => 'Communication',
                    "%RATE5%" => 'Work Quality',
                    "%RATE5_CLASS%" => '',
                    "%USER_TYPE%" => 'F',
                    "%RATE1_VAL%" => $value['punctality']*20,
                    "%RATE2_VAL%" => $value['workClarification']*20,
                    "%RATE3_VAL%" => $value['expertise']*20,
                    "%RATE4_VAL%" => $value['communication']*20,
                    "%RATE5_VAL%" => $value['workQuality']*20,
                    "%REVIEW%" => $value['review'],
                    "%AVG%" => $value['startratings']*20,
                    "%REVIEW_TIME%" => date('dS F,Y',strtotime($value['createdDate']))
                );
            }
            else
            {
                $array = array(
                    "%RATE1%" => 'Requirement Clarification',
                    "%RATE2%" => 'On Time Payment',
                    "%RATE3%" => 'On Time Response',
                    "%RATE4%" => 'Communication',
                    "%RATE5%" => 'Average Star rating',
                    "%RATE5_CLASS%" => 'hide',
                    "%USER_TYPE%" => 'C',
                    "%RATE1_VAL%" => $value['reqClarification']*20,
                    "%RATE2_VAL%" => $value['onTimePayment']*20,
                    "%RATE3_VAL%" => $value['onTimeResponse']*20,
                    "%RATE4_VAL%" => $value['custComm']*20,
                    "%AVG%" => $value['customerStarRating']*20,
                    "%REVIEW%" => $value['custReview'],
                    "%REVIEW_TIME%" => date('dS F,Y',strtotime($value['customerCreatedDate']))
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

    public function submitContent($data){
        extract($data);
        $mls = count($milestoneTitle);
        $objPost = new stdClass();

            //printr($_POST,1);

        foreach($milestoneTitle as $key => $value) {
            $objPost->milestoneTitle = $milestoneTitle[$key];
            $objPost->milestoneDesc = $milestoneDesc[$key];
            $objPost->amount = $amount[$key];
            $objPost->createdDate = date('Y-m-d H:i:s');
            $objPost->jobId = $job_id;
            $objPost->ownerId = $this->sessUserId;
            $objPost->status = 's';
            $objPost->completionDate = date('Y-m-d H:i:s',strtotime($milestone_date[$key]));
            if(empty($edit_milestone_field) || empty($mid[$key])){
                $this->db->insert('tbl_milestones',(array)$objPost);
            }else{
                $this->db->update('tbl_milestones',(array)$objPost,array("id"=>$mid[$key]));
            }
        }

        $msgType = $_SESSION["msgType"] = disMessage(array('type' => 'suc', 'var' => MILESTONES_CREATED_SUCCESSFULLY));
        $arr = explode("/",$_SERVER['REQUEST_URI']);
        $slug = end($arr);
        $user_data = $this->db->select("tbl_job_bids",array("userId"),array("jobid"=>$job_id))->result();
        $job_name = $this->db->select("tbl_jobs",array("jobTitle"),array("id"=>$job_id))->result();
        $msg = "Milestones for job ".$job_name['jobTitle']." has been created.";
        $link=SITE_URL."job/workroom/".$slug;
        notify('f',$user_data['userId'],$msg,$link);
        redirectPage(SITE_URL."job/workroom/".$slug);
    }


    public function edit_milestone($job_id){
        $review_query = $this->db->pdoQuery("SELECT * FROM `tbl_milestones` WHERE jobId = ? AND status != 'a'",array($job_id));
        $data = $review_query->results();

        $content = new MainTemplater(DIR_TMPL.$this->module."/edit_milestone-sd.skd");
        $content = $content->compile();

        $final_content = '';

        foreach ($data as $key => $value) {
            $arr = [
                '%KEY%' => ($key+1),
                '%ID%' => $value['id'],
                '%JOBID%' => $value['jobId'],
                '%OWNERID%' => $value['ownerId'],
                '%MILESTONETITLE%' => $value['milestoneTitle'],
                '%MILESTONEDESC%' => $value['milestoneDesc'],
                '%AMOUNT%' => $value['amount'],
                '%STATUS%' => $value['status'],
                '%PAYMENTSTATUS%' => $value['paymentStatus'],
                '%WORKPERCENTAGE%' => $value['workPercentage'],
                '%WORKSTATUS%' => $value['workStatus'],
                '%SUBMITWORK%' => $value['submitWork'],
                '%SUBMITWORKMESSAGE%' => $value['submitWorkMessage'],
                '%SUBMITWORKFILE%' => $value['submitWorkFile'],
                '%SUBMITWORKDATE%' => $value['submitWorkDate'],
                '%CREATEDDATE%' => $value['createdDate'],
                '%COMPLETIONDATE%' => date('d-m-Y',strtotime($value['completionDate'])),
                '%PAIDDATE%' => $value['paidDate']
            ];
            $final_content  .= str_replace(array_keys($arr), array_values($arr),$content);
        }
        return $final_content;
    }


    public function getMilestones($id,$posterId){
        // $userId = $this->sessUserId;
        $data = $this->db->select('tbl_milestones',array('*'),array('jobId'=>$id,'ownerId'=>$posterId))->results();
        $jobId = $this->db->select('tbl_jobs',array('jobStatus'),array('id'=>$id))->result();
        $bidId = $this->db->select('tbl_job_bids',array('isHired'),array('jobid'=>$id))->result();


        $jobStatus = $jobId['jobStatus'];
        $ml_ids = array_column($data, 'id');
        $ml_ids = !empty($ml_ids) ? implode(',', $ml_ids) : 0;

        $dispute_data = $this->db->pdoQuery("SELECT disputeId FROM `tbl_dispute` WHERE type = 'ML' and status = 'p' AND entityId in (".$ml_ids.")")->affectedRows();

        $dispute_accept = $this->db->pdoQuery("SELECT disputeId FROM `tbl_dispute` WHERE type = 'ML' and status = 's' AND entityId in (".$ml_ids.") and (disputerAccept = 'n' || disputedAccept = 'n') ")->affectedRows();

        $milestone_content = '';
        $currentDate = date('y-m-d');
        $cnt = 1;

        $userType = $this->sessUserType;
        
        if(count($data) > 0){
            foreach ($data as $key => $value) {
                $value['isHired'] = !empty($bidId['isHired']) ? $bidId['isHired'] : 'n';
                $hideProgess = $payToUser = $askForPayment = $submitWork = 'hide';
                $content = new MainTemplater(DIR_TMPL.$this->module."/milestones-sd.skd");
                $content = $content->compile();
                if($value['status'] == 'a' && $value['workStatus'] == 'p' && $cnt == '1'){
                    $Datediff = getDateDiff($value['completionDate']);
                    $submitWork ='';
                    if($Datediff >= 1) {
                        $status = "";
                        $cnt++;
                    }
                } else if($cnt == '2'){
                    $status = "";
                }
                $status = ($value['workStatus'] == "p") ? 'Pending':'Completed';
                $is_msg = "";
                if($jobStatus == 'dsc'){
                    $disput_res = $this->db->pdoQuery("SELECT * FROM tbl_dispute WHERE entityId=".$value["id"]." AND type='ML'")->result();
                    if(!empty($disput_res)){
                       $is_msg = "Dispute resolved. Amount transfered to Disputer : ".$disput_res["payToDisputer"].CURRENCY_SYMBOL.", Disputed : ".$disput_res["payToEntityOwner"].CURRENCY_SYMBOL;
                    } 
                }
                if($userType != "Customer"){
                    $waiting_milestone = ($jobStatus == 'h') ? '' : 'hide';                    
                    $submitWork = ($jobStatus == 'h' || $jobStatus == 'p' || $value['status'] == 's' || $value['status'] == 'h') ? 'hide' : '';   
                    if($jobStatus=='ip' && $key==0){
                        $submitWork = $submitWork;
                        $waiting_milestone="hide"; 
                    }else if($jobStatus=='ip' && $key>0){
                        $last_milestone = $this->db->pdoQuery("SELECT id FROM tbl_milestones WHERE jobId=".$id." AND id<".$value['id']." AND ownerId=".$posterId." AND paymentStatus='c'")->affectedRows();   
                        if($last_milestone>0){
                            $submitWork="";
                            $waiting_milestone="hide";    
                        }else{
                            $submitWork="hide";
                            $waiting_milestone="";    
                        }
                    }
                    $hideProgess = '';
                    $status = 'Pending';
                    if($value['submitWork'] == 'y' && $value['paymentStatus'] != 'c'){
                        $askForPayment = '';
                    }
                    if($value['submitWork'] == 'y' && $value['paymentStatus'] != 'c'){
                        $askForPayment = '';
                    }
                    if($value['submitWork'] == 'y'){
                        $hideProgess = 'hide';
                        $submitWork = 'hide';
                    }
                    if($value['isHired'] != 'n' && $value['status'] == 'a'){
                        //$hideProgess = 'hide';
                        $status = 'Accepted';
                    }
                    if($value['isHired'] == 'y' && $value['status'] == 'a'){
                        $hideProgess = 'hide';
                        //$status = 'Accepted';
                    }
                    if($value['submitWork'] != 'y' && $value['paymentStatus'] == 'p'){
                        //$hideProgess = 'hide';
                    }
                    if($value['submitWork'] == 'y' && $value['paymentStatus'] == 'p'){
                        $hideProgess = 'hide';
                        $status = 'Pending';
                    }
                    if($value['submitWork'] == 'y' && $value['paymentStatus'] == 'ap'){
                        $askForPayment = 'hide';
                        $hideProgess = '';
                        $status = 'Payment requested';
                    }
                    if($dispute_data > 0 && $value['paymentStatus'] != 'c'){
                        $hideProgess = '';
                        $status = 'Dispute raised';
                        $askForPayment = $submitWork =  'hide';
                    }
                    if($dispute_accept > 0 && $value['paymentStatus'] != 'c'){
                        $hideProgess = '';
                        $status = 'Dispute Solved';
                        $askForPayment = $submitWork =  'hide';
                    }
                    if($jobStatus == 'dsc' ){
                        $status = 'Job Discontinued';
                        $hideProgess = '';
                        $askForPayment = $submitWork =  'hide';
                    }
                    if($value['paymentStatus'] == 'c'){
                        $status = 'Completed';
                        $hideProgess = '';
                    }
                }else{
                    $submitWork = ($userType == "Customer") ? 'hide' : '';
                    $waiting_milestone = ($userType == "Customer") ? 'hide' : '';                    
                    $hideProgess = '';
                    $status = 'Pending';
                    if($value['submitWork'] == 'y' && $value['paymentStatus'] == 'ap'){
                        $payToUser = '';
                    } else {
                        $hideProgess = '';
                    }
                    if($value['isHired'] != 'n' && $value['status'] == 'a'){
                        $status = 'Accepted';
                    }
                        // echo $value['isHired'];
                        // printr($value,1);
                        // die;
                    if($value['submitWork'] == 'n' && $value['isHired'] == 'y'){
                        $status = 'Pending';
                    }
                    if($value['submitWork'] == 'y' && $value['paymentStatus'] == 'p'){
                        $hideProgess = '';
                        $status = 'Work submitted';
                    }
                    if($value['submitWork'] != 'y' && $value['paymentStatus'] == 'p'){
                        //$hideProgess = '';
                        //$status = 'Pending';
                    }
                    if($dispute_data > 0 && $value['paymentStatus'] != 'c'){
                        $hideProgess = '';
                        $status = 'Disputed';
                        $askForPayment = $submitWork = $payToUser =  'hide';
                    }
                    if($dispute_accept > 0 && $value['paymentStatus'] != 'c'){
                        $hideProgess = '';
                        $status = 'Dispute Solved';
                        $askForPayment = $submitWork = $payToUser =  'hide';
                    }
                    if($jobStatus == 'dsc' ){

                        $status = 'Job Discontinued';
                        $hideProgess = '';
                        $askForPayment = $payToUser =  'hide';
                    }
                    if($value['paymentStatus'] == 'ap'){
                        $hideProgess = 'hide';
                    }
                    if($value['paymentStatus'] == 'c'){
                        $status = 'Completed';
                        $hideProgess = '';
                    }
                }
                $array = array(
                    "%TITLE%" => $value['milestoneTitle'],
                    "%DESC%" => $value['milestoneDesc'],
                    "%AMOUNT%" => $value['amount']."<span>".CURRENCY_SYMBOL."</span>",
                    "%DATE%" => date('d F Y',strtotime($value['completionDate'])),
                    "%STATUS%" => $status,
                    "%IS_MSG_DIV%" => !empty($is_msg)?"":"hide",
                    "%IS_MSG%" => $is_msg,
                    "%SUBMIT_WORK%" => $submitWork,
                    "%MILESTONE_ID%" => $value['id'],
                    "%WAITING%" => $waiting_milestone,
                        //"%WORK_STATUS%" => ($submitWork == "") ? 'hide':'',
                    "%WORK_STATUS%" => '',
                    "%ASK_FOR_PAYMENT%" => $askForPayment,
                    "%PAY_TO_USER%" => $payToUser,
                    "%BTN_HIDE%" => $hideProgess
                );
                $milestone_content .= str_replace(array_keys($array), array_values($array),$content);
            }

        } else {
            $milestone_content = '0';
        }
        return $milestone_content;
    }

    public function saveSubmitWork($data,$files)
    {
        extract($data);
        $id = $milestoneId;
        $file_name = uploadFile($_FILES['workFile'], DIR_MLS_FILES,SITE_MLS_FILES);

        $this->db->update("tbl_milestones",array("submitWork"=>'y',"submitWorkMessage"=>filtering($submitWorkMsg),"submitWorkFile"=>$file_name['file_name'],"submitWorkDate"=>date('Y-m-d H:i:s')),array("id"=>$id));

        $mls = $this->db->select("tbl_milestones",array('milestoneTitle','jobId','ownerId'),array('id'=>$id))->result();

        $job = $this->db->select("tbl_jobs",array('jobTitle','jobSlug','posterId'),array('id'=>$mls['jobId']))->result();

        $job_title = "<a href='".SITE_URL.'job/'.$job['jobSlug']."'>".$job['jobTitle']."</a>";
        $customerDetail = getUser($mls['ownerId']);
        $userDetail = getUser($this->sessUserId);
        $userName = filtering(ucfirst($userDetail['firstName']))." ".filtering(ucfirst($userDetail['lastName']));

        $user_link = SITE_URL."f/profile/".$userDetail['userSlug'];
        $freelancerName = "<a href='".$user_link."'>".$userName."</a>";
        $login_link = "<a href='".SITE_URL.'SignIn'."'>Login</a>";
        $workroom_url = "";

        $arrayCont = array(
            "USER_NAME" => $userName,
            "USER_SLUG" => $user_link,
            "TITLE" => $mls['milestoneTitle'],
            "WORKROOM_URL" => SITE_URL."job/workroom/".$job['jobSlug'],
            "LOGIN" => $login_link,
            "JOB_TITLE" => $job_title
        );

        $nmsg = $userDetail['userName'].' has submitted a milestone for '.$job['jobTitle'].' job ';
        $nlink = SITE_URL."job/workroom/".$job['jobSlug'];
        notify('c',$job['posterId'],$nmsg,$nlink);

        $array = generateEmailTemplate('milestone_submit',$arrayCont);
        sendEmailAddress($customerDetail['email'],$array['subject'],$array['message']);

        $msgType = $_SESSION["msgType"] = disMessage(array('type'=>'suc','var'=>YOUR_WORK_HAS_BEEN_SUBMITTED_SUCCESSFULLY));
        redirectPage(SITE_URL."job/workroom/".$job['jobSlug']);
    }

    public function askForPayment($ml_id){
        $userType = $this->sessUserType;

        if($userType == "Freelancer"){
            $mls = $this->db->select("tbl_milestones",array("*"),array('id'=>$ml_id))->result();

            if($mls['paymentStatus'] == 'p'){
                $this->db->update("tbl_milestones",array("paymentStatus"=>'ap'),array("id"=>$ml_id));
                $customerDetail = getUser($mls['ownerId']);
                $job = $this->db->select("tbl_jobs",array('jobTitle','jobSlug','posterId'),array('id'=>$mls['jobId']))->result();

                $job_title = "<a href='".SITE_URL.'job/'.$job['jobSlug']."'>".$job['jobTitle']."</a>";

                $customerDetail = getUser($mls['ownerId']);
                $userDetail = getUser($this->sessUserId);
                $userName = filtering(ucfirst($userDetail['firstName']))." ".filtering(ucfirst($userDetail['lastName']));
                $user_link = SITE_URL."f/profile/".$userDetail['userSlug'];
                $login_link = "<a href='".SITE_URL.'SignIn'."'>Login</a>";

                $nmsg = $userDetail['userName'].' has ask for payment for '.$mls['milestoneTitle'].' milestone';
                $nlink = SITE_URL.'job/workroom/'.$job['jobSlug'];
                notify('c',$job['posterId'],$nmsg,$nlink);

                $arrayCont = array(
                    "USER_NAME" => $userName,
                    "USER_SLUG" => $user_link,
                    "MLS_TITLE" => $mls['milestoneTitle'],
                    "WORKROOM_URL" => SITE_URL."job/workroom/".$job['jobSlug'],
                    "LOGIN" => $login_link,
                    "JOB_TITLE" => $job_title,
                    "AMOUNT" => CURRENCY_SYMBOL.$mls['amount']
                );
                $array = generateEmailTemplate('milestone_payment_request',$arrayCont);
                sendEmailAddress($customerDetail['email'],$array['subject'],$array['message']);

                $msgType = $_SESSION["msgType"] = disMessage(array('type'=>'suc','var'=>YOUR_REQUEST_HAS_BEEN_SUBMITTED_SUCCESSFULLY));
                $data['link'] = SITE_URL."job/workroom/".$job['jobSlug'];

            } else if($mls['paymentStatus'] == 'ap'){
                $msgType = $_SESSION["msgType"] = disMessage(array('type'=>'err','var'=>YOU_HAVE_ALREADY_SUBMITTED_REQUEST));
            }
            $data['link'] = SITE_URL."job/workroom/".$job['jobSlug'];
            return $data;
        }
    }

    public function acceptMilestones($bidId,$jobId){

        /* Accepting Milestones and sending mail to Customer  */
        $job_id = $jobId;
        $login_link = "<a href='".SITE_URL.'SignIn'."'>Login</a>";
        if($this->sessUserType == 'Freelancer'){

            $aWhere = array('jobId'=>$job_id);
            $up = array('status'=>"a");
            $result = $this->db->update('tbl_milestones',$up,$aWhere)->affectedRows();

            $data = $this->db->select('tbl_jobs',array('jobTitle','jobSlug','posterId'),array('id'=>$job_id))->result();
            $userData = getUser($this->sessUserId);
            $jobUrl = SITE_URL.'job/'.$data['jobSlug'];
            $jobTitle = $data['jobTitle'];
            $name = $userData['firstName'].' '.$userData['lastName'];
            $userUrl = SITE_URL.'f/profile/'.$userData['userName'];
            $custData = getUser($data['posterId']);
            $email = $custData['email'];

            $nmsg = $name.' has accepted your milestone for '.$data['jobTitle'].' job';
            $nlink = SITE_URL.'job/workroom/'.$data['jobSlug'];
            notify('c',$data['posterId'],$nmsg,$nlink);

            $arrayCont = array(
                'FREELANCER_URL' => $userUrl,
                'FREELANCER_NAME' => $name,
                'JOB_URL' => $jobUrl,
                'JOB_TITLE' => $data['jobTitle']
            );

            $array = generateEmailTemplate('milestone_accepted',$arrayCont);
            sendEmailAddress($email, $array['subject'], $array['message']);
            $msgType = $_SESSION["msgType"] = disMessage(array('type'=>'suc','var'=>MILESTONE_ACCEPTED_SUCCESSFULLY));
            return "true";
        }
    }

    public function milestonePayment($ml_id){
        if($this->sessUserType == 'Customer'){
            $userId = $this->sessUserId;
            $date = date('Y-m-d H:i:s');
            $mls = $this->db->pdoQuery("
                SELECT ml.*,ml.id as mlid,jb.*,bd.budget as jobBudget,bd.userId as fID,us.*,us.id,us.id as fid,us.email,bd.id,bd.escrowRequired as bidId from tbl_milestones AS ml
                LEFT JOIN tbl_jobs AS jb ON ml.jobId = jb.id
                LEFT JOIN tbl_job_bids AS bd ON bd.jobId = jb.id
                LEFT JOIN tbl_users AS us ON bd.userId = us.id
                where ml.id =".$ml_id." and bd.isHired ='y' ")->result();


            $fdetial = getUser($mls['fID']);
            $cdetial = getUser($this->sessUserId);

            $nlink = SITE_URL.'job/workroom/'.$mls['jobSlug'];
            $nmsgf = $cdetial['userName'].' has released your payment for '.$mls['milestoneTitle'].' milestone';
            notify('f',$fdetial['id'],$nmsgf,$nlink);

            $jobId = $mls['jobId'];
            $data = $this->getMilestone($ml_id,$jobId);
            $milestoneNo = $data['index'];
            $isLastMilestone = $data['isLast'];
            $ml_title = $mls['milestoneTitle'];
            $jobTitle = $mls['jobTitle'];
            $userdetial = getUser($userId);
            //printr($mls,1);

            $freelancerUrl = "<a href='".SITE_URL."f/profile/".$mls['userSlug']."'>".$mls['firstName'].' '.$mls['lastName']."</a>";
            $customerUrl = "<a href='".SITE_URL.'c/profile/'.$userdetial['userSlug']."'>".$userdetial['firstName'].' '.$userdetial['lastName']."</a>";

            $budget = $mls['amount'];

            $wallet = $userdetial['walletAmount'];

            if($budget < $wallet) {
                $userId = $this->sessUserId;
                /* Step -1 Update User Wallet */
                $this->db->pdoQuery("update tbl_users set walletamount = walletamount - ".$budget." where id = ?",array($userId));

                if($milestoneNo == 1){
                    $totalAmount = $mls['jobBudget'];
                    $comm = getCommision($mls['jobBudget'],'E');
                    $adminJobCommision= $comm;
                    $this->db->insert("tbl_admin_commision",array("entityid"=>intval($jobId),"entitytype"=>'j',"amount"=>(string)$comm,"createdDate"=>date('Y-m-d H:i:s')));

                    /* Insert into admin commision */
                    $budget = $budget-$comm;
                }
                /* Step 2 Insert on wallet */
                $objPost = new stdClass();
                $objPost->userType = 'c';
                $objPost->userId = $userId;
                $objPost->entity_id = $mls['mlid'];
                $objPost->entity_type = 'ml';   
                $objPost->amount = (string)$budget;
                $objPost->paymentStatus = 'c';
                $objPost->status = 'completed';
                $objPost->transactionType = 'payToFreelancer';
                $objPost->createdDate =  date('Y-m-d H:i:s');
                $objPost->ipAddress = get_ip_address();
                $date = date('Y-m-d H:i:s');
                $this->db->insert('tbl_wallet',(array)$objPost);

                $Cemail = $userdetial['email'];
                $Femail = $mls['email'];

                /* Step 3 Check whether milestone is Last or not */
                if($isLastMilestone == '0'){
                    $mailToFreelancer = 'milestone_payment_for_freelancer';
                    $mailToCustomer = 'milestone_payment_paid_by_customer';
                    $arrayCont = array(
                        'USER_NAME' => $customerUrl,
                        'NO' => $milestoneNo,
                        'TITLE' => $ml_title,
                        'JOB_TITLE' => $jobTitle
                    );

                    $arrayContC = array(
                        'USER_NAME' => $freelancerUrl,
                        'NO' => $milestoneNo,
                        'TITLE' => $ml_title,
                        'JOB_TITLE' => $jobTitle
                    );

                    /* Step 4 Update milestone payment status */
                    $this->db->update('tbl_milestones',array('paymentStatus'=>'c','workStatus'=>'complete'),array('id'=>$mls['mlid']));

                } else {
                    $mailToFreelancer = 'milestone_last_payment_for_freelancer';
                    $mailToCustomer = 'milestone_last_payment_paid_by_customer';

                    $arrayCont = array(
                        'USER_NAME' => $customerUrl,
                        'NO' => $milestoneNo,
                        'TITLE' => $ml_title,
                        'JOB_TITLE' => $data['jobTitle'],
                        'CUSTOMER' => $customerUrl,
                        'LOGIN' => $login_link
                    );

                    $arrayContC = array(
                        'USER_NAME' => $freelancerUrl,
                        'NO' => $milestoneNo,
                        'TITLE' => $ml_title,
                        'JOB_TITLE' => $data['jobTitle'],
                        'PROVIDER_NAME' => $freelancerUrl,
                        'CUSTOMER' => $freelancerUrl,
                        'LOGIN' => $login_link
                    );
                    
                    $this->db->update('tbl_milestones',array('paymentStatus'=>'c','workStatus'=>'complete'),array('id'=>$mls['mlid']));                    
                    $this->db->update('tbl_wallet',array('status'=>'completed','createdDate'=>$date),array('entity_id'=>$jobId,'entity_type'=>'j'));
                    $this->db->update('tbl_jobs',array('jobStatus'=>'co'),array('id'=>$jobId));
                }
                updateWallet($mls['fid'],$budget,'p');
                $array = generateEmailTemplate($mailToFreelancer,$arrayCont);
                sendEmailAddress($Femail, $array['subject'], $array['message']);
                $Carray = generateEmailTemplate($mailToCustomer,$arrayContC);
                sendEmailAddress($Cemail, $Carray['subject'], $Carray['message']);
                $msgType = $_SESSION["msgType"] = disMessage(array('type'=>'suc','var'=>MILESTONE_PAYMENT_COMPLETED_SUCCESSFULLY));
                return "true";
            } else {
                $msgType = $_SESSION["msgType"] = disMessage(array('err'=>'suc','var'=>INSUFFICIENT_BALANCE_IN_YOUR_WALLET));
                return "false";
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

    public function getAllMilestones($job_id){
        $mls = $this->db->select('tbl_milestones',array('*'),array('jobId'=>$job_id))->results();
        return $mls;
    }

    public function reportUser($data){
        extract($data);
        $reportedId = $rp_id;
        $report_check = $this->db->pdoQuery("select * from tbl_report where reportedId=? and reporterId=? and reportType=? and userId=?",array($reportedId,$this->sessUserId,'User',$rp_id))->affectedRows();
        if($report_check > 0)
        {
            $msgType = $_SESSION["msgType"] = disMessage(array('type' => 'err', 'var' => YOUR_HAVE_ALREADY_REPORTED_TO_THIS_USER));
            redirectPage(SITE_URL."job/workroom/".$slug);
        }
        else
        {
            $this->db->insert("tbl_report",array("reportedId"=>$reportedId,"reportType"=>'User',"userId"=>$rp_id,"reporterId"=>$this->sessUserId,"reportMessage"=>trim($report_reason),"status"=>'Pen',"createdDate"=>date('Y-m-d H:i:s'),"ipAddress"=>get_ip_address()));

            $msgType = $_SESSION["msgType"] = disMessage(array('type' => 'suc', 'var' => YOUR_REPORT_HAS_BEEN_SENT_SUCCESSFULLY));
            redirectPage(SITE_URL."job/workroom/".$slug);
        }
    }


    public function hireFreelancer($bidId){

      $data = $this->db->pdoQuery("
        SELECT j.*,j.id AS jobId,b.*,b.id as bidId,b.userId AS bidderId,b.budget as bidBudget FROM tbl_jobs AS j
        LEFT JOIN tbl_job_bids AS b ON j.id = b.jobId
        where b.id =".$bidId)->result();
      $jobTitle = $data['jobTitle'];
      $budget = $data['bidBudget'];
      $bidderDetail = getUser($data['bidderId']);
      $email = $bidderDetail['email'];
      $jobId = $data['jobId'];
      $customerDetail = getUser($data['posterId']);
      $custName = $customerDetail['firstName'].' '.$customerDetail['lastName'];
      $freelancerName = $bidderDetail['firstName'].' '.$bidderDetail['lastName'];
      $custSlug =  SITE_URL.'c/user/'.$customerDetail['userName'];
      $walletAmount = $customerDetail['walletAmount'];
      $job_url = SITE_URL.'job/'.$data['jobSlug'];
      $bidId = $data['bidId'];
      $workroom_url = SITE_URL.'job/workroom/'.$data['jobSlug'];
      $returnVal = "true";

      if($data['escrowRequired']=='y'){

        if($budget > $walletAmount){
          $msgType = $_SESSION["msgType"] = disMessage(array('type' => 'suc', 'var' => INSUFFICIENT_BALANCE_IN_YOUR_WALLET));  
          $returnVal = "insuff";
          return $returnVal;
        } else {
          $objPost = new stdClass();
          $objPost->paymentStatus = 'a';
          $objPost->userType ='c';
          $objPost->entity_id =$jobId;
          $objPost->entity_type = 'j';
          $objPost->userId = $data['posterId'];
          $objPost->amount = $budget;
          $objPost->transactionType = 'escrow';
          $objPost->status = 'onhold';
          $objPost->createdDate = date('Y-m-d H:i:s');
          $objPost->ipAddress = get_ip_address();
          $this->db->insert('tbl_wallet',(array)$objPost);
      }

  } else {

  }

  notify('f',$data['bidderId'],$custName.'has hired you for job - '.$jobTitle,SITE_URL.'job/workroom/'.$data['jobSlug']);


  $string = $custName.'has hired '.$freelancerName.' for job - '.$jobTitle;
  $not_array = array(
      'userId' => 0,
      'message' => $string,
      'isRead' => 'n',
      'notificationType' => 'a',
      'createdDate' => date('Y-m-d H:i:s')
  );

  $this->db->insert('tbl_notification',$not_array);
  $not_user_array = array(
      'userId' => $bidderDetail,
      'message' => $string,
      'isRead' => 'n',
      'notificationType' => 'f',
      'createdDate' => date('Y-m-d H:i:s')
  );
  $this->db->insert('tbl_notification',$not_array);

  $update = $this->db->update('tbl_jobs',array('jobStatus'=>'h'),array('id'=>$jobId));
  $bid_update = $this->db->update('tbl_job_bids',array('isHired'=>'y'),array('id'=>$bidId));

  $arrayCont = array(
    'USER_NAME' => $custName,
    'USER_SLUG' => $custSlug,
    'JOB_URL' => $job_url,
    'JOB_TITLE' => $data['jobTitle'],
    'WORKROOM_URL' => $workroom_url
);

  $array = generateEmailTemplate('freelancer_hired',$arrayCont);
  sendEmailAddress($email, $array['subject'], $array['message']);
  $msgType = $_SESSION["msgType"] = disMessage(array('type' => 'suc', 'var' => FREELANCER_HIRED_SUCCESSFULLY));
  return $returnVal;
}


}
?>


