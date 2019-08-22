 <?php

class FreelancerMyJobs {
	function __construct($module = "", $id = 0, $token = "") {
		foreach ($GLOBALS as $key => $values) {
			$this->$key = $values;
		}
		$this->module = $module;
		$this->id = $id;
	}

  	public function getPageContent()
  	{
  		$sub_content = new MainTemplater(DIR_TMPL . $this->module . "/freelancer_myjobs-sd.skd");
        $sub_content = $sub_content->compile();

        $where = "jb.userId ='".$this->sessUserId."' and (jb.isHired='y' OR jb.isHired='a')  and j.id IS NOT NULL";
        $query = $this->db->pdoQuery("select j.*,c.".l_values('category_name')." as category_name,s.".l_values('subcategory_name')." as subcategory_name,u.firstName,u.lastName,u.location from tbl_job_bids As jb
	    	LEFT JOIN tbl_jobs As j ON j.id = jb.jobId
	    	LEFT JOIN tbl_category As c ON c.id = j.jobCategory
	        LEFT JOIN tbl_subcategory As s ON s.id = j.jobSubCategory
	        LEFT JOIN tbl_users As u ON u.id = j.posterId
	        where ".$where)->affectedROws();
        $load_class = ($query>10) ? '' : 'hide';
        $data = str_replace(array("%SUB_HEADER_CONTENT%","%LOOP_DATA%","%LOAD_CLASS%"), array(subHeaderContent("my-jobs"),$this->job_data(1),$load_class), $sub_content);
        return $data;
    }


    public function job_data($pageNo)
	{
	    global $db;
	    $num_rec_per_page=10;
	    $start_from = ($pageNo-1) * $num_rec_per_page;

	    $where = "jb.userId ='".$this->sessUserId."' and (jb.isHired='y' OR jb.isHired='a') and j.id IS NOT NULL";

	    $query = $this->db->pdoQuery("select j.*,c.".l_values('category_name')." as category_name,s.".l_values('subcategory_name')." as subcategory_name,u.firstName,u.lastName,u.location,j.jobStatus,u.userSlug,u.id as user_id from tbl_job_bids As jb
	    	LEFT JOIN tbl_jobs As j ON j.id = jb.jobId
	    	LEFT JOIN tbl_category As c ON c.id = j.jobCategory
	        LEFT JOIN tbl_subcategory As s ON s.id = j.jobSubCategory
	        LEFT JOIN tbl_users As u ON u.id = j.posterId
	        where ".$where." ORDER BY jb.isHired ASC LIMIT ".$start_from.",".$num_rec_per_page)->results();

	    $data = '';
	    if(!empty($query)){
		    foreach ($query as $value) {
		        $sub_content = new MainTemplater(DIR_TMPL .$this->module . "/job_data-sd.skd");
		        $sub_content = $sub_content->compile();

		        $skills = ($value['skills']!='') ? explode(",", get_skill($value['skills'])) : '';
		        $skill_list = '';
		        if($skills!='')
		        {
			        foreach ($skills as $skill) {
			        	$skill_list .= "<li>".$skill."</li>";
			        }
		        }
		        else
		        {
		        	$skill_list='';
		        }

		        $no_of_applicant = job_applicant($value['id']);
		        //$review = (getAvgUserReview($job['user_id'])*20);
		        $review = (getAvgUserReview($value['user_id'],"C")*20);
		        $status_res = job_status($value['jobStatus']);
		        $array = array(
//                            "%SUB_HEADER_CONTENT%" => subHeaderContent("my-jobs"),
		        	"%SLUG%" =>$value['jobSlug'],
		        	"%USRE_SLUG%" =>$value['userSlug'],
		            "%FEATURED_LBL_CLASS%" => checkClass($value['featured'],$value['isDelete']),
		            "%FEATURED_LBL%" => (checkClass($value['featured'],$value['isDelete'])!='') ? ((checkClass($value['featured'],$value['isDelete'])=='deleted-class') ? 'Deleted' : 'Featured') : '' ,
		            "%JOB_TITLE%" => filtering(ucfirst($value['jobTitle'])),
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
		            "%CUST_SPEN%" => customerSpentAmount($value['posterId']),
		           	"%JOB_STATUS%" => $status_res["job_status"],
		            "%JOB_CLASS%" => $status_res["job_class"],
		            "%DELETED_JOB_MAIN_CLASS%" => ($value['isDelete'] == 'y') ? 'deleted_post' : '',
	                "%DELETED_DIV%" => ($value['isDelete'] == 'y') ? '<div class="deleted-post"><span>Deleted</span></div>' : '',
	                "%DETAIL_LINK%" => ($value['isDelete'] == 'y') ? 'javascript:void(0)' : SITE_URL."job/".$value['jobSlug']

		            );
		        $data .= str_replace(array_keys($array), array_replace($array), $sub_content);
		    }
		}
	    return $data;
	}

	






}
 ?>


