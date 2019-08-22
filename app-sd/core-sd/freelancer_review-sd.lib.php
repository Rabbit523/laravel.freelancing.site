<?php

class FreelancerReview {
	function __construct($module = "", $id = 0, $token = "") {
		foreach ($GLOBALS as $key => $values) {
			$this->$key = $values;
		}
		$this->module = $module;
		$this->id = $id;
	}
	
  	public function getPageContent() 
  	{

  		$sub_content = new MainTemplater(DIR_TMPL . $this->module . "/freelancer_review-sd.skd");
        $sub_content = $sub_content->compile();

        $load_data = $this->db->pdoQuery("select u.firstName,u.lastName,r.*,s.serviceTitle,j.jobTitle,o.totalPayment,j.budget  from tbl_reviews As r
            LEFT JOIN tbl_users As u ON u.id = r.customerId         
            LEFT JOIN tbl_jobs As j ON j.id = r.entityId
            LEFT JOIN tbl_services As s ON s.id = r.entityId
            LEFT JOIN tbl_services_order As o ON o.servicesId = s.id
            where r.freelancerId=? ",array($this->id))->affectedRows();
        $load_class = ($load_data>10) ? '' : 'hide';

        return str_replace(array("%REVIEW_LOOP%","%LOAD_CLASS%","%FREELANCER_ID%","%SUB_HEADER_CONTENT%"), array($this->review_loop(1),$load_class,$this->id,subHeaderContent("review")), $sub_content);

    }
    public function review_loop($pageNo)
    {
        $num_rec_per_page=10;
        $start_from = ($pageNo-1) * $num_rec_per_page; 

        $query = $this->db->pdoQuery("select u.firstName,u.profileImg,u.lastName,r.id as r_id, r.*,s.serviceTitle,j.jobTitle,o.totalPayment,j.budget  from tbl_reviews As r
        	LEFT JOIN tbl_users As u ON u.id = r.customerId        	
        	LEFT JOIN tbl_jobs As j ON j.id = r.entityId
        	LEFT JOIN tbl_services As s ON s.id = r.entityId
            LEFT JOIN tbl_services_order As o ON o.servicesId = s.id
        	where r.freelancerId=? LIMIT ".$start_from.",".$num_rec_per_page,array($this->id))->results();
        $loop_data = '';
        if(count($query)>0)
        {
            foreach($query As $value)
            {   
                if(!empty($value['review']) && $value['communication'] > 0 && $value['workClarification'] > 0){
                	$sub_content = new MainTemplater(DIR_TMPL . $this->module . "/review_loop-sd.skd");
                	$sub_content = $sub_content->compile();
                	$array = array(
                        "%RID%" =>$value["r_id"],
        	        	"%PROJECT_NAME%" => ($value['entityType'] == 'J') ? filtering($value['jobTitle']) : filtering($value['serviceTitle']),
        	        	"%POSTED_DATE%" =>  date('d F Y h:i A', strtotime($value['createdDate'])),
        	        	"%REVIEW%" => filtering($value['review']),
        	        	"%AVG_RATE%" => ((($value['workClarification']+$value['punctality']+$value['expertise']+$value['communication']+$value['workQuality'])/5)*20),
        				"%CUST_NAME%" => filtering(ucfirst($value['firstName']))." ".filtering(ucfirst($value['lastName'])),
        				"%CUST_IMG%" => ($value['profileImg']=='') ? SITE_UPD."no_user_image.png" : SITE_USER_PROFILE.$value['profileImg'],
        				"%COST%" => ($value['entityType'] == 'J') ? (($value['budget']=='') ? CURRENCY_SYMBOL.'0' : CURRENCY_SYMBOL.$value['budget']) : (($value['totalPayment']=='') ? CURRENCY_SYMBOL.'0' : CURRENCY_SYMBOL.$value['totalPayment'])
                	);
            	   $loop_data .= str_replace(array_keys($array), array_values($array), $sub_content);
                }
            }
        }
        else
        {
            $loop_data .= "<span class='no-records'><i class='fa fa-exclamation-triangle'></i>".NO_REVIEWS."</span>";
        }
  		return $loop_data;

    }
    public function getAllReview($rid){

        $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/review_popup-sd.skd");
        $sub_content = $sub_content->compile();

        $query = $this->db->pdoQuery("select u.firstName,u.profileImg,u.lastName,r.*,s.serviceTitle,j.jobTitle,o.totalPayment,j.budget  from tbl_reviews As r
            LEFT JOIN tbl_users As u ON u.id = r.customerId         
            LEFT JOIN tbl_jobs As j ON j.id = r.entityId
            LEFT JOIN tbl_services As s ON s.id = r.entityId
            LEFT JOIN tbl_services_order As o ON o.servicesId = s.id
            where r.id=? ",array($rid))->result();
        
        if(!empty($query['review']) && $query['communication'] > 0 && $query['workClarification'] > 0){            
            $array = array(
                "%WORK_RATE%" => ($query['workClarification'] * 20),
                "%PUNCTUALITY_RATE%" => ($query['punctality'] * 20),
                "%EXP_RATE%" => ($query['expertise'] * 20),
                "%COMMUNICATION_RATE%" => ($query['communication'] * 20),
                "%QUALITY_RATE%" => ($query['workQuality'] * 20),
            );
           $data = str_replace(array_keys($array), array_values($array), $sub_content);
        }
        return $data;
    }
     
}
 ?>


