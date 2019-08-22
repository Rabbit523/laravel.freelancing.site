<?php

class FreelancerSavedJobs {
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
  		$status = (array_key_exists('status',$this->search_array)) ? urldecode($this->search_array['status']) : '';

		$open_status = ($status == 'open') ? 'checked' : '';
        $hired_another_status = ($status == 'hired_another') ? 'checked' : '';
        $hired_status = ($status == 'hired') ? 'checked' : '';
        $closed_status = ($status == 'closed') ? 'checked' : '';


  		$sub_content = new MainTemplater(DIR_TMPL . $this->module . "/freelancer_savedJobs-sd.skd");
        $sub_content = $sub_content->compile();

        $where = "sj.userId ='".$this->sessUserId."' ";
        if($status!='')
	    {
	    	$where .= $this->condition($status);
	    }
	    $query = $this->db->pdoQuery("select j.*,c.".l_values('category_name')." as category_name,s.".l_values('subcategory_name')." as subcategory_name,u.firstName,u.lastName,u.location from tbl_saved_job As sj
	    	LEFT JOIN tbl_jobs As j ON j.id = sj.jobId
	    	LEFT JOIN tbl_category As c ON c.id = j.jobCategory
	        LEFT JOIN tbl_subcategory As s ON s.id = j.jobSubCategory
	        LEFT JOIN tbl_users As u ON u.id = j.posterId
	        LEFT JOIN tbl_job_bids As jb ON jb.jobid = j.id
	        where ".$where)->affectedRows();

	    $load_class = ($query > 5) ? '' : 'hide';
        $data = str_replace(array("%SUB_HEADER_CONTENT%","%LOOP_DATA%","%LOAD_CLASS%","%OPEN_STATUS%","%HIRED_ANOTHER_STATUS%","%HIRED_STATUS%","%CLOSED%"), array(subHeaderContent("my-jobs"),$this->job_data($this->search_array,1),$load_class,$open_status,$hired_another_status,$hired_status,$closed_status), $sub_content);
        return $data;
    }



    public function job_data($search_array='',$pageNo='1')
	{
	    //global $db;
	    // print_r($search_array['status']);exit;
	    $num_rec_per_page = 5;
	    $start_from = load_more_pageNo($pageNo,5);
	    $where = "sj.userId ='".$this->sessUserId."' ";
	    if(isset($search_array['status']) && $search_array['status']!='')
	    {
	    	$where .= $this->condition($search_array['status']);
	    }
	    $query = $this->db->pdoQuery("select j.*,c.".l_values('category_name')." as category_name,s.".l_values('subcategory_name')." as subcategory_name,u.id as user_id, u.firstName,u.lastName,u.location, u.userSlug,jb.isHired,jb.userId As bidder from tbl_saved_job As sj
	    	LEFT JOIN tbl_jobs As j ON j.id = sj.jobId
	    	LEFT JOIN tbl_category As c ON c.id = j.jobCategory
	        LEFT JOIN tbl_subcategory As s ON s.id = j.jobSubCategory
	        LEFT JOIN tbl_users As u ON u.id = j.posterId
	        LEFT JOIN tbl_job_bids As jb ON jb.jobid = j.id
	        where ".$where." ORDER BY sj.createdDate DESC LIMIT ".$start_from.",".$num_rec_per_page)->results();
	    $data = '';

	    if(count($query)>0)
	    {
		    foreach ($query as $value) {
		        $sub_content = new MainTemplater(DIR_TMPL .$this->module . "/job_data-sd.skd");
		        $sub_content = $sub_content->compile();

		        $skills = explode(",", get_skill($value['skills']));
		        $skill_list = '';
		        foreach ($skills as $skill) {
		        	$skill_list .= "<li>".$skill."</li>";
		        }

		        $no_of_applicant = job_applicant($value['id']);
		        $status_res = job_status($value['jobStatus']);
		        $review = (getAvgUserReview($value['user_id'],"C")*20);
		        $array = array(
		            "%FEATURED_LBL_CLASS%" => checkClass($value['featured'],$value['isDelete']),
	            	"%FEATURED_LBL%" => (checkClass($value['featured'],$value['isDelete'])!='') ? ((checkClass($value['featured'],$value['isDelete'])=='deleted-class') ? 'Deleted' : 'Featured') : '' ,
		            "%JOB_TITLE%" => filtering(ucfirst($value['jobTitle'])),
		            "%JOB_SLUG%" => $value['jobSlug'],
		            "%CATEGORY%" => filtering(ucfirst($value['category_name'])),
		            "%SUBCATEGORY%" => filtering(ucfirst($value['subcategory_name'])),
		            "%USER_LVL%" => getJobExpLevel($value['expLevel']),
		            "%BUDGET%" => CURRENCY_SYMBOL.$value['budget'],
		            "%POSTED_TIME%" => getTime($value['jobPostDate']),
		            "%JOB_DESC%" => filtering($value['description']),
		            "%APPLICANTS%" => $no_of_applicant,
		            "%REMAIN_DAYS%" => get_time_diff(date('Y-m-d H:i:s',strtotime($value['biddingDeadline']))),
		            "%SKILL_LIST%" => $skill_list,
		            "%CUST_IMG%" => getUserImage($value['posterId']),
		            "%CUST_NAME%" => filtering(ucfirst($value['firstName']))." ".filtering(ucfirst($value['lastName'])),
		            "%JOB_ID%" => $value['id'],
		            "%AVG_REVIEW%" => $review,
		            "%LOCATION%" => $value['location'],
		            "%USRE_SLUG%" => $value['userSlug'],
		            "%CUST_SPEN%" => customerSpentAmount($value['posterId']),
		            "%JOB_STATUS%" => $status_res["job_status"],
	            	"%JOB_CLASS%" => $status_res["job_class"],
		            "%DELETED_JOB_MAIN_CLASS%" => ($value['isDelete'] == 'y') ? 'deleted_post' : '',
	                "%DELETED_DIV%" => ($value['isDelete'] == 'y') ? '<div class="deleted-post"><span>Deleted</span></div>' : '',
	                "%DETAIL_LINK%" => ($value['isDelete'] == 'y') ? 'javascript:void(0)' : ($value['jobStatus'] == 'h' ?  SITE_URL."job/workroom".$value['jobSlug'] : SITE_URL."job/".$value['jobSlug'])
		            );
		        $data .= str_replace(array_keys($array), array_replace($array), $sub_content);
		    }
	    }
	    else
	    {
	    	$data .= "<span class='no-records'><i class='fa fa-exclamation-triangle'></i>".NO_RECORDS_FOUND."</span>";
	    }
	    return $data;
	}
	public function condition($condition)
    {
    	if($condition == 'open')
    	{
    		$where = " AND j.jobStatus='p'";
    	}
    	else if($condition == 'hired_another')
    	{
    		$where = " AND (jb.isHired='y' AND jb.userId!='".$this->sessUserId."')";
    	}
    	else if($condition == 'hired')
    	{
    		$where = " AND (jb.isHired='y' AND jb.userId='".$this->sessUserId."')";
    	}
    	else
    	{
    		$where = " AND (j.jobStatus='c' OR j.jobStatus='dsc') ";
    	}
    	return $where;
    }






}
 ?>


