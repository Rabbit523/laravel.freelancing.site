<?php

class FreelancerJobInvitation {
	function __construct($module = "", $id = 0, $token = "") {
		foreach ($GLOBALS as $key => $values) {
			$this->$key = $values;
		}
		$this->module = $module;
		$this->id = $id;
	}

  	public function getPageContent()
  	{

  		$sub_content = new MainTemplater(DIR_TMPL . $this->module . "/freelancer_jobInvitation-sd.skd");
        $sub_content = $sub_content->compile();

        $where = "ji.freelancerId ='".$this->sessUserId."' AND ji.status != 'i' ";
        $totalRecords = $this->db->pdoQuery("select j.*,c.".l_values('category_name')." as category_name ,s.".l_values('subcategory_name')." as subcategory_name,ji.status As invStatus,ji.createdDate As recDate,u.firstName,u.lastName from tbl_jobs As j
	        LEFT JOIN tbl_category As c ON c.id = j.jobCategory
	        LEFT JOIN tbl_subcategory As s ON s.id = j.jobSubCategory
	        LEFT JOIN tbl_job_invitation As ji ON ji.jobId = j.id
	        LEFT JOIN tbl_users As u ON u.id = j.posterId
	        where ".$where)->affectedRows();

        $load_class = ($totalRecords>5) ? '' : 'hide';
        return str_replace(array("%LOOP_DATA%","%LOAD_CLASS%","%SUB_HEADER_CONTENT%"), array($this->job_data(1),$load_class,subHeaderContent("invitation")), $sub_content);
    }


    public function job_data($pageNo)
	{
	    global $db;
	    $num_rec_per_page=5;
	    $start_from = ($pageNo-1) * $num_rec_per_page;

	    $where = "ji.freelancerId ='".$this->sessUserId."'";
	    $query = $this->db->pdoQuery("select j.*,ji.freelancerId As FreelancerId,c.".l_values('category_name')." as category_name,s.".l_values('subcategory_name')." as subcategory_name,ji.status As invStatus,ji.createdDate As recDate,u.firstName,u.lastName from tbl_jobs As j
	        LEFT JOIN tbl_category As c ON c.id = j.jobCategory
	        LEFT JOIN tbl_subcategory As s ON s.id = j.jobSubCategory
	        LEFT JOIN tbl_job_invitation As ji ON ji.jobId = j.id
	        LEFT JOIN tbl_users As u ON u.id = j.posterId
	        where ".$where." ORDER BY ji.id DESC LIMIT ".$start_from.",".$num_rec_per_page)->results();
	    $data = '';
	    if(count($query)>0)
	    {
		    foreach ($query as $value) {
		        $sub_content = new MainTemplater(DIR_TMPL .$this->module . "/job_data-sd.skd");
		        $sub_content = $sub_content->compile();
		        $skills = explode(",", get_skill($value['skills']));$skill_list='';
		        foreach ($skills as $skill) {
		        	$skill_list .= "<li>".$skill."</li>";
		        }
		        $no_of_applicant = job_applicant($value['id'],$value['FreelancerId']);
		        $array = array(
		            "%FEATURED_LBL_CLASS%" => checkClass($value['featured'],$value['isDelete']),
		            "%FEATURED_LBL%" => (checkClass($value['featured'],$value['isDelete'])!='') ? ((checkClass($value['featured'],$value['isDelete'])=='deleted-class') ? 'Deleted' : 'Featured') : '' ,
		            "%JOB_TITLE%" => filtering(ucfirst($value['jobTitle'])),
		            "%CATEGORY%" => filtering(ucfirst($value['category_name'])),
		            "%SUBCATEGORY%" => filtering(ucfirst($value['subcategory_name'])),
		            "%USER_LVL%" => getJobExpLevel($value['expLevel']),
		            "%BUDGET%" => $value['budget']."<span>".CURRENCY_SYMBOL."</span>",
		            "%POSTED_TIME%" => getTime($value['jobPostDate']),
		            "%INV_STATUS%" => ($value['invStatus']=='p') ? 'pending' : (($value['invStatus']=='a') ? 'Accepted' : 'Rejected'),
		            "%INV_STATUS_CLASS%" => ($value['invStatus']=='p') ? 'badge-warning' : (($value['invStatus']=='a') ? 'badge-success' : 'badge-danger'),
		            "%JOB_DESC%" => filtering($value['description']),
		            "%APPLICANTS%" => $no_of_applicant,
		            "%REMAIN_DAYS%" => get_time_diff(date('Y-m-d H:i:s',strtotime($value['biddingDeadline']))),
		            "%INV_REC_DATE%" => date('jS, M Y',strtotime($value['recDate'])),
		            "%SKILL_LIST%" => $skill_list,
		            "%CUST_IMG%" => getUserImage($value['posterId']),
		            "%CUST_NAME%" => filtering(ucfirst($value['firstName']))." ".filtering(ucfirst($value['lastName'])),
		            "%JOB_ID%" => $value['id'],
		            "%JOB_ACTION_CLASS%" => ($value['invStatus'] == 'a' || $value['invStatus'] == 'r') ? 'hide' : '',
		            "%DELETED_JOB_MAIN_CLASS%" => ($value['isDelete'] == 'y') ? 'deleted_post' : '',
	                "%DELETED_DIV%" => ($value['isDelete'] == 'y') ? '<div class="deleted-post"><span>Deleted</span></div>' : '',
	                "%DETAIL_LINK%" => ($value['isDelete'] == 'y') ? 'javascript:void(0)' : SITE_URL."job/".$value['jobSlug']
	            );
		        $data .= str_replace(array_keys($array), array_replace($array), $sub_content);
		    }
	    }
	    else
	    {
	    	$data .= "<span class='no-records'><i class='fa fa-exclamation-triangle'></i>".NO_INVITATION_FOUND."</span>";
	    }
	    return $data;
	}
	public function job_applicant($jobId)
	{
		$result = $this->db->pdoQuery("select * from tbl_job_bids where jobid='".$jobId."' ")->affectedRows();
		return $result;
	}

   public function time($timestamp)
   {
   		$daysleft = '0';
   	 	$now = date_create(date('Y-m-d H:i:s'));

	    $future_date = date_create($timestamp);


	    $interval = $future_date->diff($now);
	    $days = $interval->format('%d');
	    $years = $interval->format('%y');
	    $months = $interval->format('%m');
	    $hours = $interval->format('%h');
	    $minut = $interval->format('%i');
	    $seconds = $interval->format('%s');

	    if(strtotime(date('Y-m-d H:i:s')) < strtotime($timestamp))
	    {
	        if($years != '0')
	            $time = "ends in ".$years.' '.YEAR_S;
	        else if($months != '0')
	        {
	            //return $months.' months left';
	            $time = "ends in ".$days.' '.DAY_S;
	        }
	        else if($days!='0'){
	            $time = "ends in ".$days.' '.DAY_S;
	        }else if($hours!=0){
	            $time = "ends in ".$hours.' '.HOUR_S;
	        }else if($minut!=0){
	            $time = "ends in ".$minut.' '.MINUTE_S;
	        }else if($seconds!=0){
	            $time = "ends in ".$seconds.' '.SECOND_S;
	        }else{
	            $time =  '';
	        }
	    }
	    else
	    {
	    	$time = EXPIRED;
	    }

	    return $time;
   }

}
 ?>


