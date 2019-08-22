<?php

class CustomerMyJobs {
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
        $path = $_SERVER['REQUEST_URI'];
        $paramsArr = explode("/",$path);
        $content = array();
        if(in_array('job-invitation',$paramsArr)){
            $job_slug = end($paramsArr);
            $data = $this->showInvitations($job_slug);
            return $data;

        } else {
            $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/".$this->module.".skd");
            $sub_content = $sub_content->compile();

            $jobs = $this->db->pdoQuery("select j.*,c.".l_values('category_name')." as category_name,s.".l_values('subcategory_name')." as subcategory_name,jb.isHired,jb.userId
                from tbl_jobs as j
                LEFT JOIN tbl_category AS c ON j.jobCategory = c.id
                LEFT JOIN tbl_subcategory AS s ON j.jobSubCategory = s.id
                LEFT JOIN tbl_job_bids AS jb ON jb.jobid = j.id
                where j.posterId = ".$this->sessUserId." group by j.id order by j.id desc")->affectedRows();

            $no_jobs = ($jobs > 10) ? '':'hide';
            $content = array(
                "%SUB_HEADER_CONTENT%" => customerSubHeaderContent("myjobs"),
                "%JOB_DETAILS%" => $this->getJobs($this->search_array,1),
                "%LOAD_CLASS%" => $no_jobs
            );
        }
        return str_replace(array_keys($content),array_values($content), $sub_content);
    }

    public function showInvitations($slug){

        $invitation = new MainTemplater(DIR_TMPL.$this->module."/invited_freelancer-sd.skd");
        $invitation = $invitation->compile();

        $job_data = $this->db->select('tbl_jobs',array('id'),array('jobSlug'=>$slug))->result();
        $users = $this->db->pdoQuery("select group_concat(freelancerId) as user_list from tbl_job_invitation where jobId =".$job_data['id'])->result();        
        $users_list = explode(",",$users['user_list']);
        $usercontent = '';
        foreach ($users_list as $key => $value) {
            $user_data = new MainTemplater(DIR_TMPL.$this->module."/freelancer-sd.skd");
            $user_data = $user_data->compile();
            $userData = getUser($value);
            $start = date_create(date('Y-m-d H:i:s'));
            $end = date_create($userData['createdDate']);
            $diff = date_diff($start,$end);
            $since = MEMBER_SINCE." ";
            $service_sold = $this->sold_service($value);
            $skill_list ='';

            if(!empty($userData['skillList'])){
                $skills = explode(",",get_skill($userData['skillList']));
                foreach ($skills as $skill) {
                    $skill_list .= "<li>".$skill."</li>";
                }
            }

            if($diff->y > 0){
                $since = $diff->y.' '.YEAR_S;
            } else {
                if($diff->m > 0){
                    $since =  $diff->m.' '.MONTH_S;
                } else if($diff->d > 0){
                    $since = $diff->d.' '.DAY_S;
                } else {
                    $since = $diff->h.' '.HOUR_S.' '.$diff->m." ".MINUTE_S;
                }
            }

            $rating_detail = $this->db->pdoQuery("select AVG(startratings) As rating from tbl_reviews where freelancerId='".$value."' ")->result();
            $rating = ($rating_detail['rating']*20);

            $completed_jobs = $this->db->pdoQuery("SELECT * FROM `tbl_job_bids` WHERE userId = ? AND isHired = 'y' AND jobid in (SELECT id FROM `tbl_jobs` WHERE `jobStatus` ='co' ) ",[$value])->affectedRows();

            $earned = $this->db->pdoQuery("SELECT SUM(amount) as earned FROM `tbl_wallet` WHERE userId = ? AND (entity_type = 'ml' or entity_type ='r') and userType = 'f' ",[$value])->affectedRows();

            $invRes = $this->db->pdoQuery("SELECT * FROM tbl_job_invitation WHERE jobId=? AND freelancerId=?",array($job_data['id'],$value))->result();
            $status = "";
            if($invRes["status"]=="p"){
                $status = "Pending";
            } else if($invRes["status"]=="a"){
                $status = "Accepted";
            } else if($invRes["status"]=="r"){
                $status = "Rejected";
            }else if($invRes["status"]=="i"){
                $status = "Private job under approval";
            }

            $user_arr = array(
                "%NAME%" => $userData['firstName']." ".$userData['lastName'],
                "%IMAGE%" => getUserImage($value),
                "%USER_PROFILE_URL%" => SITE_URL.'f/profile/'.$userData['userSlug'],
                "%PROF_TITLE%" => $userData['professionalTitle'],
                "%LOCATION%" => $userData['location'],
                "%USER_SINCE%" => $since,
                "%RATINGS%" => $rating,
                "%SOLD_SERVICE%" =>$service_sold,
                "%COMPLETED_JOB%" => $completed_jobs,
                "%EARNED%" => !empty($earned['earned']) ? $earned['earned'] : 0,
                "%SKILLS%" => $skill_list,
                "%RESPONSE_TIME%" => $this->responseTime($value),
                "%INV_STATUS%" => ($invRes["status"]=='p') ? 'pending' : (($invRes["status"]=='a') ? 'Accepted' : 'Rejected'),
                "%SENT_DATE%" =>date(DATE_FORMAT, strtotime($invRes["createdDate"])),
                "%INVITATION_STATUS%" => $status
            );
            $usercontent .= str_replace(array_keys($user_arr),array_values($user_arr),$user_data);
        }
        //die;
        $content = str_replace("%USERS%",$usercontent,$invitation);
        return  $content;
    }

    public function getJobs($search_array = '',$pageNo = '1'){

        $job_file = new MainTemplater(DIR_TMPL.$this->module."/jobs_desc-sd.skd");
        $job_file = $job_file->compile();

        $num_rec_per_page = 10;
        // $num_rec_per_page = 10;
        $start_from = load_more_pageNo($pageNo,10);
        $where = " j.posterId = ".$this->sessUserId." and j.isDelete='n'";


        if(is_array($search_array)){
            if(isset($search_array['status']) && $search_array['status'] != '')
            {
                $where .= " AND j.jobStatus='".$search_array['status']."' ";
            }
            if(isset($search_array['appStatus']) && $search_array['appStatus']!='')
            {
                $where .= " AND j.isApproved='".$search_array['appStatus']."' ";
            }
            if(isset($search_array["levelStatus"]) && $search_array["levelStatus"]!=""){
                $where .= " AND j.expLevel='".$search_array['levelStatus']."' ";
            }
            if(isset($search_array["typeStatus"])  && $search_array["typeStatus"] != ""){
                $where .= " AND j.jobType='".$search_array['typeStatus']."' ";
            }
            if(isset($search_array["keyword"]) && $search_array["keyword"] != ""){
                $where .= " AND j.jobTitle like '%".$search_array['keyword']."%' ";
            }
        }

        $jobs = $this->db->pdoQuery("select j.*,j.id as JID,c.".l_values('category_name')." as category_name,s.".l_values('subcategory_name')." as subcategory_name,jb.isHired,jb.userId
            from tbl_jobs as j
            LEFT JOIN tbl_category AS c ON j.jobCategory = c.id
            LEFT JOIN tbl_subcategory AS s ON j.jobSubCategory = s.id
            LEFT JOIN tbl_job_bids AS jb ON jb.jobid = j.id
            where".$where." group by j.id order by j.id desc LIMIT ".$start_from.",".$num_rec_per_page)->results();


        $job_content = '';
        if(count($jobs) > 0){

            foreach ($jobs as $key => $value) {
                $jobStatus = getJobStatus($value['jobStatus']);

                $skill_list ='';
                if(!empty($value['skills'])){
                    $skills = explode(",",get_skill($value['skills']));
                    foreach ($skills as $skill) {
                        $skill_list .= "<li>".$skill."</li>";
                    }
                }
                $value['isApproved'];
                $no_of_applicant = job_applicant($value['JID']);
                $walletData = 0;

                if($value['featured'] == 'y'){
                    $walletData = $this->db->select('tbl_wallet',array('*'),array(
                        'entity_id'=> $value['JID'],
                        'entity_type' => 'j',
                        'transactionType' => 'featuredFees',
                        'userType' => 'c'
                        )
                    )->result();
                    if(!empty($walletData)){
                        $walletData = count($walletData);
                    }
                }

                if($value['isApproved'] == 'a'){
                    // if($value['featured'] == 'y' ){
                    //     $walletData = $this->db->select('tbl_wallet',array('*'),array(
                    //         'entity_id'=> $value['JID'],
                    //         'entity_type' => 'j',
                    //         'transactionType' => 'featuredFees',
                    //         'userType' => 'c'
                    //         )
                    //     )->results();
                    //     $walletData = count($walletData);
                    // }
                    $isApproved = 'Approved';
                } else if($value['isApproved'] == 'r'){
                    $isApproved = 'Rejected';
                } else {
                    $isApproved = 'Pending';
                }

                $accepted_bids = $this->db->pdoQuery("SELECT * FROM `tbl_job_bids`  WHERE jobid = ? and (isHired = 'a' or isHired = 'y')",[$value['id']])->affectedRows();

                $workroom_hide = $this->job_workroom($value['id']);
                
                if($no_of_applicant ==0 || $accepted_bids == 0){
                    $workroom_hide = 'hide';
                }


                $biddingDeadline = get_time_diff(date('Y-m-d H:i:s',strtotime($value['biddingDeadline'])));
                $payFeature = 'hide';

                if($value['featured'] == 'y' && $value['isApproved']=='a' && $walletData == 0 && $biddingDeadline != 'Expired' && $value['featuredPayment'] == 'n'){
                    $payFeature = '';
                }

                $data_dur = $value['featuredDuration'];

                $job_array = array(
                    "%FEATURED_LBL_CLASS%" => checkClass($value['featuredPayment'],$value['isDelete']),
                    "%FEATURED_LBL%" => (checkClass($value['featured'],$value['isDelete'])!='') ? ((checkClass($value['featured'],$value['isDelete'])=='    deleted-class') ? 'Deleted' : 'Featured') : '' ,
                    "%JOB_ID%" => $value['id'],
                    "%JOB_TITLE%" => filtering(ucfirst($value['jobTitle'])),
                    "%JOB_CATEGORY%" => filtering(ucfirst($value['category_name'])),
                    "%JOB_SUB_CATEGORY%" => filtering(ucfirst($value['subcategory_name'])),
                    "%JOB_DESC%" => nl2br($value['description']),
                    "%JOB_LEVEL%" => getJobExpLevel($value['expLevel']),
                    "%JOB_BUDGET%" => $value['budget']."<span>".CURRENCY_SYMBOL."</span>",
                    "%JOB_TYPE%" => ($value['jobType'] == 'pu') ? 'Public':'Private',
                    "%JOB_SKILLS%" => $skill_list,
                    "%JOB_POSTED_TIME%" => getTime($value['jobPostDate']),
                    "%JOB_APPLICANT%" => $no_of_applicant,
                    "%HIDE_CLASS%" => ($no_of_applicant >= 1) ? 'hide':'',
                    "%BIDDING_DEADLINE%" => $biddingDeadline,
                    "%INVITATION%" => ($value['jobType'] == 'pu') ? 'hide' : '',
                    "%JOB_SLUG%" => $value['jobSlug'],
                    "%APPR_STATUS%" => $isApproved,
                    "%BID_HIDE%" => ($no_of_applicant == '0') ? 'hide':'',
                    "%WORKROOM_HIDE%" => $workroom_hide,
                    "%IS_REJECTED%" => ($value['isApproved'] == 'a') ? '' : 'hide',
                    "%FEATURE_HIDE%" => ($no_of_applicant == 0 && $value['featured'] == 'n'  && $value['featuredPayment'] == 'n') ? '' : 'hide',
                    "%PAY_FEATURE_HIDE%" => ($value['isApproved'] == 'a' && $no_of_applicant == 0 && $value['featured'] == 'y' && $value['featuredPayment'] == 'n') ? '' : 'hide',
                    "%LBL_FEATURE_HIDE%" => ($value['featured'] == 'y' && $walletData > 0 ) ? '' : 'hide',
                    "%EDIT_BTN_HIDE%" => ($biddingDeadline == "Expired" ) ? 'hide':'',
                    // "%PAY_FEATURE_HIDE%" => $payFeature,
                    "%JOB_STATUS%" => $jobStatus,
                    "%DETAIL_LINK%" => ($value['isDelete'] == 'y') ? 'javascript:void(0)' : SITE_URL."job/".$value['jobSlug'],
                    "%DATA_DURATION%"=> $data_dur
                );

                $job_content .= str_replace(array_keys($job_array),array_values($job_array),$job_file);
            }
        } else {
            $job_content .= "<span class='no-records'><i class='fa fa-exclamation-triangle'></i>".NO_RECORDS_FOUND."</span>";
        }


        return $job_content;
    }

    public function job_workroom($jobId){
        /* Milestones accepted by the Freelancer or Bid accepted by the Customer */
        $data = $this->db->pdoQuery("
            SELECT j.*,b.*,ml.*,ml.id AS mlsId,ml.status AS mlStatus  FROM tbl_jobs AS j
            LEFT JOIN tbl_job_bids AS b ON j.id = b.jobId
            LEFT JOIN tbl_milestones AS ml ON j.id = ml.jobId
            where  b.isHired = 'a' or ml.status = 'a' and j.id = ".$jobId)->results();

        $data_count = count($data);
        
        if($data_count > 0){
            return '';
        } else {
            return 'hide';
        }
    }

    public function review_loop($pageNo)
    {
        $num_rec_per_page = 5;
        $start_from = ($pageNo-1) * $num_rec_per_page;

        $query = $this->db->pdoQuery("select u.firstName,u.lastName,r.*,s.serviceTitle,j.jobTitle,o.totalPayment,j.budget  from tbl_reviews As r
        	LEFT JOIN tbl_users As u ON u.id = r.freelancerId
        	LEFT JOIN tbl_jobs As j ON j.id = r.entityId
        	LEFT JOIN tbl_services As s ON s.id = r.entityId
            LEFT JOIN tbl_services_order As o ON o.servicesId = s.id
        	where r.customerId=? LIMIT ".$start_from.",".$num_rec_per_page,array($this->sessUserId))->results();
        $loop_data = '';

        foreach($query As $value)
        {
        	$sub_content = new MainTemplater(DIR_TMPL . $this->module . "/review_loop-sd.skd");
        	$sub_content = $sub_content->compile();

        	$array = array(
	        	"%PROJECT_NAME%" => ($value['entityType'] == 'J') ? filtering($value['jobTitle']) : filtering($value['serviceTitle']),
	        	"%POSTED_DATE%" =>  date('d F Y h:i A', strtotime($value['createdDate'])),
	        	"%REVIEW%" => filtering($value['review']),
	        	"%AVG_RATE%" => ((($value['reqClarification']+$value['onTimePayment']+$value['onTimePayment']+$value['onTimeResponse']+$value['custComm'])/5)*20),
				"%REQ_CLARIFICATION%" => ($value['reqClarification']* 20),
				"%ON_TIME_PAYMENT%" => ($value['onTimePayment'] * 20),
				"%ON_TIME_RESPONSE%" => ($value['onTimeResponse']* 20),
				"%COMMUNICATION_RATE%" => ($value['custComm']),
				"%QUALITY_RATE%" => ($value['workQuality'] * 20),
				"%CUST_NAME%" => filtering(ucfirst($value['firstName']))." ".filtering(ucfirst($value['lastName'])),
				"%CUST_IMG%" => ($value['profileImg']=='') ? SITE_UPD."no_user_image.png" : SITE_USER_PROFILE.$value['profileImg'],
				"%COST%" => ($value['entityType'] == 'J') ? CURRENCY_SYMBOL.$value['budget'] : CURRENCY_SYMBOL.$value['totalPayment']
        		);
        	$loop_data .= str_replace(array_keys($array), array_values($array), $sub_content);
        }
  		return $loop_data;
    }

    public function submitContent($data){
        extract($data);
        $update = $this->db->update("tbl_jobs",array("budget"=>$budget),array('id'=>$jobId_edit));

        $msgType = $_SESSION["msgType"] = disMessage(array('type' => 'suc', 'var' => 'Job updated successfully'));
        redirectPage(SITE_URL.'C/my-jobs');
    }

    public function sold_service($id)
    {
      $data = $this->db->pdoQuery("select * from tbl_services_order where freelanserId='".$id."' and paymentStatus='c' and serviceStatus='c'")->affectedRows();
      /*$earned_amount = 0;
      foreach ($data as $value) {
        $earned_amount ++;
      }*/
      return $data;
    }

    public function responseTime($id)
    {
      $job_detail = $this->db->pdoQuery("select * from tbl_job_invitation where freelancerId='".$id."' and status!='p' ")->results();
      $d = 0;
      $i = 0;
      foreach ($job_detail as $value)
      {
        $now = date_create($value['createdDate']);
        $date = date_create($value['acceptRejectDate']);
        $interval = $date->diff($now);
        $days = $interval->format('%i');
        $d += $days;
        $i++;
      }

      $final = ($d!=0) ? ceil($d/$i) : '';
      $final = !empty($final) ? $final : 0;

      if($final<1440)
      {
        if($final>60)
          $final_result = $final/60 ." ".MINUTE_S;
        else
          $final_result = $final." ".MINUTE_S;
      }
      else if($final>1440 && $final<43200)
      {
          $final_result = floor($final/1440)." ".DAY_S;
      }
      else if($final>43200)
      {
          $final_result = floor($final/1440)." ".MONTH_S;
      }
      return $final_result;
    }

}
 ?>


