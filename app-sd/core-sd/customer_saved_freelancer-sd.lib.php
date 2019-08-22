<?php

class CustomerSavedFreelancer {
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
        $content = "";

        $sub_content = new MainTemplater(DIR_TMPL . $this->module ."/".$this->module.".skd");
        $sub_content = $sub_content->compile();

        $freelancers = $this->db->pdoQuery("select * from tbl_saved_freelancer where customerId =".$this->sessUserId)->affectedRows();

        $no_freelancers = ($freelancers > 5) ? '' : 'hide';
        $remeoveAllHide = ($freelancers > 0) ? '' : 'hide';
        $content = array(
          "%SUB_HEADER_CONTENT%" => customerSubHeaderContent("saved_freelancer"),
          "%FREELANCERS%" => $this->getFreelancers($this->search_array,1),
          "%LOAD_CLASS%" => $no_freelancers,
          "%RM_HIDE%" =>$remeoveAllHide
        );

        return str_replace(array_keys($content),array_values($content), $sub_content);
    }

    public function getFreelancers($search_array ='',$page_no = 1)
    {
        $services = $this->db->pdoQuery("select group_concat(freelancerId) as FRID,group_concat(id) as FID from tbl_saved_freelancer where customerId = ".$this->sessUserId)->result();

        $content = '';
        if($services['FRID'] > 0 || $services['FRID']!='') {
           $savedFreelancers = explode(",",$services['FRID']);
           $freelancerSavedId = explode(",",$services['FID']);

           foreach ($savedFreelancers as $key => $value)
           {
                $service_content = new MainTemplater(DIR_TMPL.$this->module."/freelancer_desc-sd.skd");
                $service_content = $service_content->compile();
                $userData = $this->db->pdoQuery("
                    select u.*,sub.".l_values('subcategory_name')." as subcategory_name,count(pr.id) as PRF from tbl_users AS u
                    LEFT JOIN tbl_subcategory AS sub ON sub.id IN (u.subCategoryList)
                    LEFT JOIN tbl_freelancer_portfolio AS pr ON pr.userId = u.id
                    where u.id =".$value)->result();

                $skill_list ='';
                if(!empty($userData['skillList'])){
                    $skills = explode(",",get_skill($userData['skillList']));
                    foreach ($skills as $skill) {
                        $skill_list .= "<li>".$skill."</li>";
                    }
                }

                $subCat = "";

                if(!empty($userData['subCategoryList']))
                {
                    $sub = explode(",",$this->getSubCatName($userData['subCategoryList']));
                    foreach($sub as $value1) {
                        $subCat .= "<span>".$value1."</span>";
                    }
                }

                $userContent = getUser($value);
                $service_sold = $this->sold_service($value);
                $data_array = array(
                    "%ID%" => $savedFreelancers[$key],
                    "%DELETED_CLASS%" => ($userData['isDeleted'] == 'y') ? '' : 'hide',
                    "%SUB_CAT_NAME%" => $userData['subcategory_name'],
                    "%USER_NAME%" => $userData['firstName'].' '.$userData['lastName'],
                    "%PROF_TITLE%" => $userData['professionalTitle'],
                    "%PROF_TITLE_CLASS%" => ($userData['professionalTitle']=='') ? 'hide' : '',
                    "%USER_LVL%" => getUserExpLevel($userData['freelancerLvl']),
                    "%SOLD_SERVICES%" => $service_sold,
                    "%USER_RATINGS%" => getAvgUserReview($userData['id'],"freelancer")*20,
                    "%USER_LOCATION%" => $userData['location'],
                    "%USER_IMG%" => getUserImage($userData['id']),
                    "%JOB_COMPLETED%" => $this->completed_jobs($userData['id']),
                    "%PORTFOLIO%" => $userData['PRF'],
                    "%TOTAL_EARN%" => earnedAmountFreelancer($userData['id']),
                    "%RESP_TIME%" => $this->responseTime($value),
                    "%SKILLS%" => $skill_list,
                    "%SUB_CAT%" => $subCat,
                    "%FID%" => $freelancerSavedId[$key],
                    "%USER_LINK%" => ($userData['isDeleted'] == 'y') ? 'javascript:void(0)' : SITE_URL."f/profile/".$userData['userSlug']
                );

                $content .= str_replace(array_keys($data_array), array_values($data_array), $service_content);
           }

        } else {
          $content .= "<div class='col-md-12'><span class='no-records'><i class='fa fa-exclamation-triangle'></i>".NO_RECORDS_FOUND."</span></div>";

        }
        return $content;
    }

    public function completed_jobs($id)
    {
        $jobs_list = $this->db->pdoQuery("select count(jobId) As totalJobs from tbl_job_bids As jb
            LEFT JOIN tbl_jobs As j ON j.id = jb.jobId
            where userId=? and (j.jobStatus=? OR j.jobStatus=?)",array($id,'dsCo','co'))->result();
        return $jobs_list['totalJobs'];
    }

    public function getSubCatName($id){

        $query = $this->db->pdoQuery("select * from tbl_subcategory where id IN(".$id.") ")->results();
        $cat_list = "";
        foreach ($query as $value) {
            $cat_list .= $value['subcategory_name'].",";
        }
        return trim($cat_list,",");
    }

    public function submitContent($data){
        extract($data);
        $update = $this->db->update("tbl_jobs",array("budget"=>$budget),array('id'=>$jobId_edit));

        $msgType = $_SESSION["msgType"] = disMessage(array('type' => 'suc', 'var' => 'Job has been updated successfully'));
        redirectPage(SITE_URL.'C/my-jobs');
    }

    public function sold_service($id)
    {
      $data = $this->db->pdoQuery("select * from tbl_services_order where freelanserId='".$id."' and paymentStatus='c' and serviceStatus='c'")->results();
      $earned_amount = 0;
      foreach ($data as $value) {
        $earned_amount ++;
      }
      return $earned_amount;
    }

   public function responseTime($id)
   {
      $job_detail = $this->db->pdoQuery("select * from tbl_job_invitation where freelancerId='".$id."' and status!='p' ")->results();
      $d = 0;
      $i=0;
      if(count($job_detail) ==0){
          return '0';
      }
      foreach ($job_detail as $value)
      {
        $now = date_create($value['createdDate']);
        $date = date_create($value['acceptRejectDate']);
        $interval = $date->diff($now);
        $days = $interval->format('%i');
        $d += $days;
        $i++;
      }
      $final = ceil($d/$i);

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


