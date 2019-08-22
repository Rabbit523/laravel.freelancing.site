<?php

class ReceivedJob extends Home {
	function __construct($module = "", $slug = 0,$token = '' ) {
		foreach ($GLOBALS as $key => $values) {
			$this->$key = $values;
		}
		$this->module = $module;
		// $this->id = $id;
    $this->slug = $slug;
	}

  	public function getPageContent()
  	{
        $content = "";
        $sub_content = new MainTemplater(DIR_TMPL . $this->module ."/".$this->module.".skd");
        $sub_content = $sub_content->compile();

        $jobs = $this->db->pdoQuery("
          select b.*,j.* from tbl_job_bids AS b
          LEFT JOIN tbl_jobs AS j ON j.id = b.jobid
          where j.jobSlug = '".$this->slug."' ")->affectedRows();

        $job_title = getTableValue("tbl_jobs","jobTitle",array("jobSlug"=>$this->slug));
        $no_freelancers = ($jobs > 5) ? '' : 'hide';
        $remeoveAllHide = ($jobs <= 0) ? 'hide' : '';
        $content = array(
            "%JOB_TITLE%" => $job_title,
            "%FREELANCERS%" => $this->getFreelancers($this->slug),
            "%LOAD_CLASS%" => $no_freelancers,
            "%RM_HIDE%" =>$remeoveAllHide,
            "%JOB_SLUG%" => $this->slug
        );
        return str_replace(array_keys($content),array_values($content), $sub_content);
    }

    public function getFreelancers($slug)
    {
      $bids = $this->db->pdoQuery("
            select b.*,b.id as bid_id,b.createdDate as biddedDate,b.budget as bid_amount,j.id as jobId,u.id as uid,j.*,u.*,
            c.".l_values('category_name')." as category_name,s.".l_values('subcategory_name')." as subcategory_name FROM tbl_job_bids AS b
            LEFT JOIN tbl_jobs AS j ON j.id = b.jobid
            LEFT JOIN tbl_users AS u ON u.id = b.userId
            LEFT JOIN tbl_category AS c ON c.id = j.jobCategory
            LEFT JOIN tbl_subcategory AS s ON s.id = j.jobSubCategory
            where j.jobSlug ='".$slug."' ORDER BY b.createdDate DESC")->results();

      $bid_accepted = array_search('a',array_column($bids,'isHired'));
      $bid_hired = array_search('y',array_column($bids, 'isHired'));

      if(!empty($bid_accepted) || $bid_accepted === '0'){
        $bid_hire = "hide";
      } else if(!empty($bid_hired) || $bid_accepted === '0'){
        $bid_hire = "hide";
      } else {
        $bid_hire = "";
      }
      $content = '';
      foreach ($bids as $key => $value) {
        $service_content = new MainTemplater(DIR_TMPL.$this->module."/freelancer_desc-sd.skd");
        $service_content = $service_content->compile();
        $que_ans = $this->getQueAns($value['addedQuestion'],$value['answers']);
        $hire_freelancer = $this->job_workroom($value['jobId'],$value['bid_id']);
        if($value['isHired'] == 'n'){
          $acc_status_txt = "Pending";
        }
        elseif($value['isHired'] == 'a'){
          $acc_status_txt = "Accepted";
        }
        elseif ($value['isHired'] == 'r') {
          $acc_status_txt = "Rejected";
        }
        else{
          $acc_status_txt = "Hired";
        }
        $data_array = array(
          // "%ID%" => $userData[$key],
          "%JOB_TITLE%" => $value['jobTitle'],
          "%JOB_SLUG%" => $value['jobSlug'],
          "%USER_NAME%" => $value['firstName'].' '.$value['lastName'],
          "%USER_RATINGS%" => getAvgUserReview($value['uid'],"C")*20,
          "%USER_LOCATION%" => $value['location'],
          "%USER_IMG%" => getUserImage($value['uid']),
          "%BID_AMOUNT%" => $value['bid_amount']."<span>".CURRENCY_SYMBOL."</span>",
          "%EST_DURATION%" => $value['estDuration'],
          "%ESCROW_REQ%" => ($value['escrowRequired'] == "n") ? 'No':'Yes',
          "%BID_DESC%" => $value['bidDesc'],
          "%BID_DATE%" => date('d-M-Y h:i A',strtotime($value['biddedDate'])),
          "%ACC_STATUS%" => $acc_status_txt,
          "%ACC_HIDE%" => ($value['isHired'] == 'n' ) ? '':'hide',
          "%INVITE_HIDE%" => ($value['jobType'] == 'pu') ? 'hide':'',
          "%QUESTION%" => $que_ans,
          "%BID_ID%" => $value['bid_id'],
          '%SHOW_MORE%' => !empty($que_ans) ? '' : 'hide',
          "%BID_ACC%" => $bid_hire,
          "%HIDE_WORKROOM%" => ($value['isHired'] == 'a' || $value['isHired'] == 'h') ? '' : 'hide',
          "%HIRE_FREELANCER%" => $hire_freelancer,
          "%USER_SLUG%" => $value['userSlug']
        );
        $content .= str_replace(array_keys($data_array), array_values($data_array), $service_content);
      }
      return $content;
    }

    public function getQueAns2($bid_id){
      $content = '';

      if($que_id!='' && $ans!= '' ) {
        $data = $this->db->pdoQuery("select question from tbl_question where id in(".$que_id.")")->results();
        $ans_data = explode("###",$ans);
        $content = "";

        foreach ($data as $key => $value) {
              $question = new MainTemplater(DIR_TMPL.$this->module."/que_ans-sd.skd");
              $question = $question->compile();

              $array = array(
                '#QUESTION#' => $value['question'],
                '#ANSWER#' => $ans_data[$key]
              );
              $content .= str_replace(array_keys($array),array_values($array),$question);
        }
      }
      return $content;
    }

    public function getQueAns($que_id,$ans){
      $content = '';

      if($que_id!='' && $ans!= '' ) {
        $data = $this->db->pdoQuery("select question from tbl_question where id in(".$que_id.")")->results();
        $ans_data = explode("###",$ans);
        $content = "";

        foreach ($data as $key => $value) {
              $question = new MainTemplater(DIR_TMPL.$this->module."/que_ans-sd.skd");
              $question = $question->compile();

              $array = array(
                '#QUESTION#' => $value['question'],
                '#ANSWER#' => $ans_data[$key]
              );
              $content .= str_replace(array_keys($array),array_values($array),$question);
        }
      }
      return $content;
    }

    public function submitContent($data){
        extract($data);
        $update = $this->db->update("tbl_jobs",array("budget"=>$budget),array('id'=>$jobId_edit));

        $msgType = $_SESSION["msgType"] = disMessage(array('type' => 'suc', 'var' => JOB_UPDATED_SUCCESSFULLY));
        redirectPage(SITE_URL.'c/my-jobs');
    }

    public function acceptBid($id){
        $aWhere = array("id" => $_REQUEST['id']);
        $up = array("isHired" => "a",'accept_reject_date' => date('Y-m-d H:i:s'));

        $data = $this->db->pdoQuery("
          SELECT b.*,b.budget as bidBudget,b.userId AS bidderId,j.*,c.* FROM tbl_job_bids as b
          LEFT JOIN tbl_jobs AS j ON j.id = b.jobId
          LEFT JOIN tbl_users AS c ON j.posterId = c.id
          where b.id = ".$aWhere['id'])->result();

        $update = $this->db->update("tbl_job_bids",$up,$aWhere);
        $user = getUser($data['bidderId']);

        $custName = $data['firstName'].' '.$data['lastName'];
        $job_url = SITE_URL.'job/'.$data['jobSlug'];
        $cust_url = SITE_URL.'c/profile/'.$data['userSlug'];
        $email = $user['email'];

        $arrayCont = array(
          'CUSTOMER_NAME' => $custName,
          'JOB_URL' => $job_url,
          'JOB_NAME' => $data['jobTitle'],
          'AMOUNT' => $data['bidBudget'],
          'CUST_URL' => $cust_url
        );

        if($user['NotifyCustomerAcceptRejectBid'] == 'y'){
          $array = generateEmailTemplate('bid_accept_cust',$arrayCont);
          sendEmailAddress($email,$array['subject'],$array['message']);
        }
        $msg="Your Proposal for job ".$data['jobTitle']." has been accepted. More details on email";
        $link=SITE_URL."my-bids";
        notify('f',$data['bidderId'],$msg,$link);
        return "true";
    }


    public function job_workroom($jobId,$bidId){
        /* Milestones accepted by the Freelancer or Bid accepted by the Customer */

        $data = $this->db->pdoQuery("
            SELECT j.*,b.*,ml.*,ml.id AS mlsId,ml.status AS mlStatus  FROM tbl_jobs AS j
            LEFT JOIN tbl_job_bids AS b ON j.id = b.jobId
            LEFT JOIN tbl_milestones AS ml ON j.id = ml.jobId
            where b.isHired = 'a' and ml.status = 'a' and j.id = ".$jobId." and b.id =".$bidId)->results();

        $data_count = count($data);
        if($data_count == "0"){
            return 'hide';
        } else if($data_count > 0){
            return '';
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
        /* If Escrow is required for this job then amount should be go to admin wallet and on holding */
        /* Payment paid user to milestone wise */
        if($budget > $walletAmount){
          /* If user has insufficient balance */

          //$msgType = $_SESSION["msgType"] = disMessage(array('type' => 'suc', 'var' => 'Insufficient balance in wallet'));
          $returnVal = "insuff";
          return $returnVal;
          exit;
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
          /* Freelancer has been hired successfully */
      }

      $string = $custName.'has hired '.$freelancerName.' for Job - '.$jobTitle;
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


