<?php

class FreelancerProfile {
	function __construct($module = "", $id = 0, $token = "") {
		foreach ($GLOBALS as $key => $values) {
			$this->$key = $values;
		}
		$this->module = $module;
		$this->id = $id;
	}

  	public function getPageContent()
  	{
      global $sessUserType;
  		$sub_content = new MainTemplater(DIR_TMPL . $this->module . "/freelancer_profile_new-sd.skd");
      $sub_content = $sub_content->compile();
      
          $query = $this->db->pdoQuery("select * from tbl_users where id=? ",array($this->sessUserId))->result();
          $rating_detail = $this->db->pdoQuery("select AVG(startratings) As rating from tbl_reviews where freelancerId='".$this->sessUserId."' ")->result();
          $rating = ($rating_detail['rating']*20);
          $userName = filtering(ucfirst($query['firstName']))." ".filtering(ucfirst($query['lastName']));

          $profileUrl = SITE_URL."f/profile/".$query['userSlug'];
         	$array = array(
            "%B_LVL%" => ($query['freelancerLvl']=='f') ? 'selected' : '',
            "%P_LVL%" => ($query['freelancerLvl']=='e') ? 'selected' : '',
            "%STAR_RATING%" => $rating,
            "%LOCATION%" => $query['location'],
         		"%PERSONAL_DETAIL%" => $this->personalDetail(),
            "%SUBCAT_LIST%" => ($query['subCategoryList']!='') ? $this->subCategoryList($query['subCategoryList']) :'',
            "%SUBCAT_LIST_LABEL%" => ($query['subCategoryList']!='') ? 'hide' : '',
            "%FR_SWITCH_URL%" => $sessUserType=="Customer"?SITE_URL."switchprofile/":"javascript:void(0)",
            "%CS_SWITCH_URL%" => $sessUserType=="Freelancer"?SITE_URL."switchprofile/":"javascript:void(0)",
            "%USER_IMG%" => getUserImage($this->sessUserId),
            "%USER_IMAGE_VISIBILITY%" => empty($query['profileImg']) ? ' style="display:none" ' :  '',
            "%SKILL_SELECTION_LIST%" => $this->skill_list($query['skillList'],'all'),
            "%LANG_SELECTION_LIST%" => $this->lang_list(user_language_list($this->sessUserId),'all'),
            "%FIRST_NAME%" => filtering($query['firstName']),
            "%LAST_NAME%" => filtering($query['lastName']),
            "%LOCATION%" => $query['location'],
            "%VIDEO_URL%" => $query['videoUrl'],
            "%VIDEO_IFRAME%" => !empty($query['videoUrl']) ? '<iframe width="100%" id="playVideo" height="150" src="'.$query['videoUrl'].'"></iframe>' : '',
            "%PROJECT_SKILL_LIST%" => $this->skill_list('','all'),
            "%PORTFOLIO_SECTION%" => $this->portFolio(),
            "%PORTFOLIO_MODAL%" => $this->portfolioSection(),
            "%EDUCATION_MODAL%" => $this->educationSection(),
            "%EDUCATION_DETAIL%" => $this->education(),
            "%CERTIFICATION_DETAIL%" => $this->certificationDetail(),
            "%CERTIFICATION_MODAL%" => $this->certificationSection(),
            "%EXP_DETAIL%" => $this->experienceDetail(),
            "%EXP_MODAL%" => $this->experienceSection(),
            "%CATEGORY_MODAL%" => $this->getCategory($query['subCategoryList']),
            "%SHARE_LINK_MODAL%" => $this->share_url($profileUrl,$userName),
            "%OVERVIEW_MODAL%" => $this->overViewSection($query['professionalOverview']),
            "%SKILL_LIST%" => ($query['skillList']!='') ? $this->skill_list($query['skillList']) : '',
            "%LANG_LIST%" => (user_language_list($this->sessUserId)!='') ? $this->lang_list(user_language_list($this->sessUserId)) : '',
            //'%OWNER_LOGIN%' => empty($this->sessUserId) ? 'hide' : '',
            "%PROFESSIONAL_TITLE_MODAL%" => $this->proTitleSection($query['professionalTitle']),
            
         	);
  		return str_replace(array_keys($array), array_values($array), $sub_content);
    }

    /*Professional title section*/
    public function proTitleSection($title)
    {
       $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/professional_title-sd.skd");
       $sub_content = $sub_content->compile();
       return str_replace(array("%PRO_TITLE%"), array($title), $sub_content);
    }
    /*overview section*/

    public function overViewSection($overview)
    {
       $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/overview-sd.skd");
       $sub_content = $sub_content->compile();
       return str_replace(array("%OVERVIEW%"), array($overview), $sub_content);
    }

    /*share url*/
    public function share_url($url,$userName)
    {
       $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/share_link-sd.skd");
       $sub_content = $sub_content->compile();
       return str_replace(array("%URL%","%USERNM%"), array($url,getUserName($userName)), $sub_content);
    }

    /*Category section*/
    public function getCategory($cat_list)
    {

      $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/category_section-sd.skd");
      $sub_content = $sub_content->compile();
      return str_replace(array("%CATEGORY_DATA%"), array($this->category_data($cat_list)), $sub_content);
    }
    /*Experience detail*/
    public function experienceDetail()
    {
        $loop_data = '';
        $query = $this->db->pdoQuery("select * from tbl_freelancer_experience where userId=? ",array($this->sessUserId))->results();
        foreach ($query as $value) {
            $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/experience-sd.skd");
            $sub_content = $sub_content->compile();
            $start_time = $value['start_month']." ".$value['start_year'];
            $end_time = $value['end_month']." ".$value['end_year'];
            $array = array(
                "%TITLE%" => filtering($value['Title']),
                "%CMP_NAME%" => filtering($value['company_name']),
                "%LOCATION%" => filtering($value['location']),
                "%DURATION%" => ($value['current_status']=='y') ? 'Currently work here' : $start_time." - ".$end_time,
                "%DESC%" => $value['description'],

                "%ID%" => $value['id']
            );
            $loop_data .= str_replace(array_keys($array), array_values($array), $sub_content);
        }
        return $loop_data;
    }
    public function experienceSection($id='')
    {
        $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/addexp-sd.skd");
        $sub_content = $sub_content->compile();

        if($id!='')
        {
          $query = $this->db->pdoQuery("select * from  tbl_freelancer_experience where userId=? and id=?",array($this->sessUserId,trim($id)))->result();

          $array = array(
                          "%CERTI_NAME%" => filtering($query['certificateName']),
                          "%CMP_NAME%" => $query['company_name'],
                          "%LOCATION%" => $query['location'],
                          "%TITLE%" => $query['Title'],
                          "%START_MON%" => $this->month_load($query['start_month']),
                          "%START_YR%" => $this->year_load($query['start_year']),
                          "%END_MON%" => $this->month_load($query['end_month']),
                          "%END_YR%" => $this->year_load($query['end_year']),
                          "%DESC%" => $query['description'],
                          "%CURRENT_CHECKED%" => ($query['current_status']=='Y') ? 'checked':'',
                          "%ID%" => $query['id']
                  );
        }
        else
        {
          $array = array(

                          "%CERTI_NAME%" => '',
                          "%CMP_NAME%" => '',
                          "%LOCATION%" => '',
                          "%TITLE%" => '',
                          "%START_MON%" => $this->month_load(''),
                          "%START_YR%" => $this->year_load(''),
                          "%END_MON%" => $this->month_load(''),
                          "%END_YR%" => $this->year_load(''),
                          "%DESC%" => '',
                          "%CURRENT_CHECKED%" => '',
                          "%ID%" => ''

          );
        }
        return str_replace(array_keys($array), array_values($array), $sub_content);
    }
    /*Certification detail */
    public function certificationDetail()
    {
        $loop_data = '';
        $query = $this->db->pdoQuery("select * from tbl_freelancer_certification where userId=? ",array($this->sessUserId))->results();
        foreach ($query as $value) {
            $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/certification-sd.skd");
            $sub_content = $sub_content->compile();

            $array = array(
                "%TITLE%" => filtering($value['certificateName']),
                "%ID%" => $value['id']
            );
            $loop_data .= str_replace(array_keys($array), array_values($array), $sub_content);
        }
        return $loop_data;
    }
    public function certificationSection($id='')
    {
          $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/addcertificate-sd.skd");
          $sub_content = $sub_content->compile();

          if($id!='')
          {
            $query = $this->db->pdoQuery("select * from  tbl_freelancer_certification where userId=? and id=?",array($this->sessUserId,trim($id)))->result();

            $array = array(
                    "%CERTI_NAME%" => filtering($query['certificateName']),
                    "%ID%" => $query['id']
            );
          }
          else
          {
            $array = array(
                    "%CERTI_NAME%" => '',
                    "%ID%" => ''
            );
          }
          return str_replace(array_keys($array), array_values($array), $sub_content);
    }
    /*add edit education start*/
    public function educationSection($id='')
    {
          $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/addEditeducation-sd.skd");
          $sub_content = $sub_content->compile();

          if($id!='')
          {
            $query = $this->db->pdoQuery("select * from  tbl_freelancer_education where userId=? and id=?",array($this->sessUserId,trim($id)))->result();

            $array = array(
                    "%INSTITUTE%" => filtering($query['institute_name']),
                    "%START_YR%" => $this->year_load($query['start_year']),
                    "%END_YR%" => $this->year_load($query['end_year']),
                    "%DEGREE%" => filtering($query['degree_name']),
                    "%AREA_STUDY%" => filtering($query['area_study']),
                    "%EDU_DESC%" => filtering($query['eduDesc']),
                    "%ID%" => $query['id']
            );
          }
          else
          {
            $array = array(
                    "%INSTITUTE%" => '',
                    "%START_YR%" => $this->year_load(),
                    "%END_YR%" => $this->year_load(),
                    "%DEGREE%" => '',
                    "%AREA_STUDY%" => '',
                    "%EDU_DESC%" => '',
                    "%ID%" => ''
            );
          }

          return str_replace(array_keys($array), array_values($array), $sub_content);
    }

    public function year_load($yr='')
    {

      $yrData='<option value="">--'.SELECT_YEAR.'--</option>';
      for($i=date('Y');$i>=1900;$i--)
      {
        $select = ($yr==$i) ? "selected" : '';
        $yrData .= "<option value='".$i."' ".$select.">".$i."</option>";
      }
      return $yrData;
    }

    public function month_load($yr='')
    {

      $month = array(
                    "1" => "January", "2" => "February", "3" => "March", "4" => "April",
                    "5" => "May", "6" => "June", "7" => "July", "8" => "August",
                    "9" => "September", "10" => "October", "11" => "November", "12" => "December",
                );
      $yrData='<option value="">--'.SELECT_MONTH.'--</option>';
      for($i=1;$i<=12;$i++)
      {
        $select = ($yr==$month[$i]) ? "selected" : '';
        $yrData .= "<option value='".$month[$i]."' ".$select.">".$month[$i]."</option>";
      }
      return $yrData;
    }

    public function education()
    {
        $loop_data = '';
        $query = $this->db->pdoQuery("select * from  tbl_freelancer_education where userId=? ",array($this->sessUserId))->results();
        foreach ($query as $value) {
            $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/educationDetail-sd.skd");
            $sub_content = $sub_content->compile();

            $array = array(
                "%AREA_STUDY%" => filtering($value['area_study']),
                "%DEGREE%" => filtering($value['degree_name']),
                "%INSTITUTE%" => filtering($value['institute_name']),
                "%START_YR%" => $value['start_year'],
                "%END_YR%" => $value['end_year'],
                "%DESC%" => filtering($value['eduDesc']),
                "%ID%" => $value['id']
            );
            $loop_data .= str_replace(array_keys($array), array_values($array), $sub_content);
        }
        return $loop_data;
    }
    /*add edit portfolio start*/
    public function portfolioSection($id='')
    {

        $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/addEditportfolio-sd.skd");
        $sub_content = $sub_content->compile();

        if($id!='')
        {
          $query = $this->db->pdoQuery("select * from  tbl_freelancer_portfolio where userId=? and id=?",array($this->sessUserId,trim($id)))->result();
          $img = ($query['image']=='') ?  SITE_UPD."default-image_450.png" : SITE_PORTFOLIO_IMG.$this->sessUserId."/".$query['image'];
          $array = array(
                  "%PORTFOLIO_IMG%" => "<img src='".$img."' class='img_pre' height='100' width='100'>",
                  "%PROJECT_TITLE%" => filtering($query['Title']),
                  "%PROJECT_DURATION1%" => (filtering(trim($query['duration'])) == "1 day or less") ? 'selected' : '',
                  "%PROJECT_DURATION2%" => (filtering(trim($query['duration'])) == "Less than 1 week") ? 'selected' : '',
                  "%PROJECT_DURATION3%" => (filtering(trim($query['duration'])) == "1 to 2 weeks") ? 'selected' : '',
                  "%PROJECT_DURATION4%" => (filtering(trim($query['duration'])) == "3 to 4 weeks") ? 'selected' : '',
                  "%PROJECT_DURATION5%" => (filtering(trim($query['duration'])) == "1 to 6 month") ? 'selected' : '',
                  "%PROJECT_DURATION6%" => (filtering(trim($query['duration'])) == "More than 6 month") ? 'selected' : '',
                  "%PROJECT_DESC%" => filtering($query['overview']),
                  "%PROJECT_SKILL_LIST%" => $this->skill_list($query['skill'],'all'),
                  "%ID%" => $query['id'],
                  "%OLD_IMG%" => $query['image']
          );
        }
        else
        {
          $array = array(
                  "%PORTFOLIO_IMG%" => '',
                  "%PROJECT_TITLE%" => '',
                  "%PROJECT_DURATION1%" => '',
                  "%PROJECT_DURATION2%" => '',
                  "%PROJECT_DURATION3%" => '',
                  "%PROJECT_DURATION4%" => '',
                  "%PROJECT_DURATION5%" => '',
                  "%PROJECT_DURATION6%" => '',
                  "%PROJECT_DESC%" => '',
                  "%PROJECT_SKILL_LIST%" => $this->skill_list('','all'),
                  "%ID%" => '',
                  "%OLD_IMG%" => ''
          );
        }

        return str_replace(array_keys($array), array_values($array), $sub_content);
    }

    public function portFolio()
    {
        $loop_data = '';
        $query = $this->db->pdoQuery("select * from  tbl_freelancer_portfolio where userId=? ",array($this->sessUserId))->results();
        foreach ($query as $value) {
            $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/portfolio-sd.skd");
            $sub_content = $sub_content->compile();
            $skill = explode(",", $value['skill']);$skills='';
            foreach ($skill as $value1)
            {
                $skill_detail = $this->db->pdoQuery("select * from tbl_skills where id=?",array($value1))->result();
                $skills .= "<span>".$skill_detail['skill_name']."</span>";
            }
            $array = array(
                "%PORTFOLIO_IMG%" => ($value['image']=='') ?  SITE_UPD."default-image_450.png" : SITE_PORTFOLIO_IMG.$this->sessUserId."/".$value['image'],
                "%TITLE%" => filtering($value['Title']),
                "%DURATION%" => filtering($value['duration']),
                "%DESC%" => filtering($value['overview']),
                "%SKILLS%" => $skills,
                "%ID%" => $value['id']
            );
            $loop_data .= str_replace(array_keys($array), array_values($array), $sub_content);
        }
        return $loop_data;
    }


  	public function personalDetail()
  	{
  		  $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/personal_detail-sd.skd");
       	$sub_content = $sub_content->compile();

       	$query = $this->db->pdoQuery("select * from tbl_users where id=? ",array($this->sessUserId))->result();
        $url = SITE_URL."f/profile/".$query['userSlug'];
        $profile_url = '<a href="#" data-toggle="modal" data-target="#share_link">'.$url.'</a>';
        
       	$array = array(

       		"%USERNAME%" => filtering(ucfirst($query['firstName']))." ".filtering(ucfirst($query['lastName'])),
       		"%PROTITLE%" => ($query['professionalTitle']=='') ? '-':filtering($query['professionalTitle']),
          "%PROTITLE_CLASS%" => ($query['professionalTitle']=='') ? 'hide':'',
       		"%FRELVL%" => getUserExpLevel($query['freelancerLvl']),
       		"%LOCATION%" => $query['location'],
       		"%SUBCAT_LIST%" => ($query['subCategoryList']!='') ? $this->subCategoryList($query['subCategoryList']) :'',
          "%SUBCAT_LIST_LABEL%" => ($query['subCategoryList']!='') ? 'hide' : '',
       		"%PROURL%" => $profile_url,
          "%PROURL_CLASS%" => ($query['profileUrl']=='') ? 'hide' : '',
       		"%OVERVIEW%" => ($query['professionalOverview']=='') ? '-' : $query['professionalOverview'],
       		"%LAST_SEEN%" => getTime($query['lastLogin']),
       		"%SINCE_DATE%" => date('d F,Y',strtotime($query['createdDate'])),
       		"%EARNED%" => earnedAmountFreelancer($this->sessUserId),
       		"%COMPLETED_JOB%" => $this->completedJobs(),
       		"%SOLD_SERVICE%" => $this->sold_service(),
       		"%RESPONSE_TIME%" => $this->responseTime(),
       		"%PUN_REVIEW%" => $this->reviewAVG('punctality'),
       		"%WORK_REVIEW%" => $this->reviewAVG('workClarification'),
       		"%QUAT_REVIEW%" => $this->reviewAVG('workQuality'),
       		"%EXP_REVIEW%" => $this->reviewAVG('expertise'),
       		"%COMM_REVIEW%" => $this->reviewAVG('communication'),
       		"%SKILL_LIST%" => ($query['skillList']!='') ? $this->skill_list($query['skillList']) : '',
       		"%LANG_LIST%" => (user_language_list($this->sessUserId)!='') ? $this->lang_list(user_language_list($this->sessUserId)) : '',
          //"%RESPONSE_TIME%" => $this->responseTime($this->sessUserId),
          "%SLUG%" => $query['userSlug']


       		);

        //echo $query['lastLogin'];
        //die;
        return str_replace(array_keys($array), array_values($array), $sub_content);
  	}
     public function completedJobs()
    {
      $jobs_list = $this->db->pdoQuery("select count(jobId) As totalJobs from tbl_job_bids As jb
        LEFT JOIN tbl_jobs As j ON j.id = jb.jobId
        where userId=? and (j.jobStatus=? OR j.jobStatus=?)",array($this->sessUserId,'dsCo','co'))->result();
      return $jobs_list['totalJobs'];
    }
    public function sold_service()
    {
      $data = $this->db->pdoQuery("select * from tbl_services_order where freelanserId='".$this->sessUserId."' and paymentStatus='c' and serviceStatus='c'")->results();
      $earned_amount = 0;
      foreach ($data as $value) {
        $earned_amount ++;
      }
      return $earned_amount;
    }
    public function responseTime()
    {
      /*$job_detail = $this->db->pdoQuery("select * from tbl_job_invitation where freelancerId='".$this->sessUserId."' and status!='p' ")->results();*/
      $job_detail = $this->db->pdoQuery("SELECT id,freelanserId,orderDate,deadline_requested_date FROM `tbl_services_order` WHERE freelanserId = '".$this->sessUserId."' AND deadline_requested_date != '' ")->results();
      //printr($job_detail,1);
      $d = 0;$i=0;
      foreach ($job_detail as $value)
      {
        $now = date_create($value['orderDate']);
        $date = date_create($value['deadline_requested_date']);
        $interval = $date->diff($now);
        //printr($interval,1);
        $days = $interval->format('%i');
        $d += $days;
        $i++;
      }
      $final = ($i!=0) ? ceil($d/$i) : 0;
      $final_result = '';
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
      //printr($final_result,1);
      return $final_result;
    }

  	public function subCategoryList($catList)
  	{
       	$query = $this->db->pdoQuery("select * from  tbl_subcategory where id IN(".$catList.")")->results();
       	$data = "";
       	foreach ($query as $value) {
	  		$sub_content = new MainTemplater(DIR_TMPL . $this->module . "/subcatList-sd.skd");
	       	$sub_content = $sub_content->compile();

	       	$array = array(
	       		"%CATNAME%" => $value[l_values('subcategory_name')],
            "%ID%" => $value['id']
	       		);
	       	$data .= str_replace(array_keys($array), array_replace($array), $sub_content);
       	}
       	return $data;
  	}
  	public function reviewAVG($type)
  	{
  		$query = $this->db->pdoQuery("select AVG(".$type.") As AvgRating from  tbl_reviews where freelancerId=? ",array($this->sessUserId))->result();
  		return ($query['AvgRating']=='') ? '0' : ($query['AvgRating']*20);
  	}

  	public function skill_list($list,$limit='')
  	{
      $data = $user_skill =  "";
      if($limit=='all')
      {
          $query = $this->db->pdoQuery("select * from tbl_skills where isActive='y' and isDelete='n' and isApproved='y' order by createdDate DESC")->results();
          foreach ($query as $value)
          {
              $string = $list; $selected ='';
              $what_to_find = $value['id'];
              if (preg_match('/\b' . $what_to_find . '\b/', $string)) {
                 $selected = "selected";
              }

              $user_skill .= "<option value=".$value['id']." ".$selected.">".$value['skill_name']."</option>";

          }
          $data = $user_skill;
      }
      else
      {

      		$query = $this->db->pdoQuery("select * from tbl_skills where id IN(".$list.") and isApproved='y' ")->results();
      		$data = "";
          foreach ($query as $value)
          {
      			  $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/skill_lang_list-sd.skd");
      	      $sub_content = $sub_content->compile();

      	      $data .=str_replace(array("%ITEM_NM%","%ITEM_ID%","%ITEM_TYPE%"), array($value['skill_name'],$value['id'],"skill"), $sub_content);
          }
      }
        return $data;
  	}

  	public function lang_list($list,$limit='')
  	{
        $data =  "";
        if($limit=='all')
        {
          $user_lang = '<option value="">--'.SELECT_LANGUAGE.'--</option>';
          $where = ($list!='') ? " and id NOT IN(".$list.") " : "";
          $query = $this->db->pdoQuery("select * from tbl_language where isActive='y' $where")->results();
          foreach ($query as $value)
          {
              $user_lang .= "<option value=".$value['id']." >".$value['language']."</option>";
          }
          $data = $user_lang;
        }
        else
        {
        		$query = $this->db->pdoQuery("select l.*,ul.langType from tbl_user_language As ul
              LEFT JOIN tbl_language As l ON l.id = ul.languageId
              where userId ='".$this->sessUserId."' ")->results();
        		$data = "";
            foreach ($query as $value)
            {
        			  $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/skill_lang_list-sd.skd");
        	      $sub_content = $sub_content->compile();

        	      $data .= str_replace(array("%ITEM_NM%","%ITEM_ID%","%ITEM_TYPE%"), array($value['language']." - ".$value['langType'],$value['id'],"lang"), $sub_content);
            }
        }
        return $data;
  	}
    public function category_data($categorylist)
    {
        $query = $this->db->pdoQuery("select * from tbl_category where id IN (select maincat_id from tbl_subcategory)")->results();
        $data = "";
        foreach ($query as $value)
        {
            $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/category_selection-sd.skd");
            $sub_content = $sub_content->compile();

            $array = array(
                "%CAT_TITLE%" => filtering($value[l_values('category_name')]),
                "%CAT_LIST%" => $this->subcategoryData($value['id'],$categorylist)
              );
            $data .=  str_replace(array_keys($array), array_values($array), $sub_content);
        }
        return $data;
    }
    public function subcategoryData($mainCatId,$categorylist)
    {
        $query = $this->db->pdoQuery("select * from tbl_subcategory where isActive='y' and isDelete='n' and maincat_id='".$mainCatId."' ")->results();
        $data = '';
        foreach ($query as $value)
        {
              $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/category_selection_loop-sd.skd");
              $sub_content = $sub_content->compile();

              $checked ='';
              $string = $categorylist;
              $what_to_find = $value['id'];
              if (preg_match('/\b' . $what_to_find . '\b/', $string)) {
                 $checked = "checked";
              }

              $array = array(
                  "%CAT_ID%" => $value['id'],
                  "%CAT_NAME%" => filtering($value[l_values('subcategory_name')]),
                  "%CHECKED%" => $checked
                );
              $data .=  str_replace(array_keys($array), array_values($array), $sub_content);
        }

        return $data;
    }
    public function data_add($data,$type)
    {
      extract($data);
      if($type=='skills')
      {
        $item_list = ($skill_name!='') ? implode(",", $skill_name) : '';
        if($item_list!='')
        {
          $skill_list = '';
          foreach (explode(",",$item_list) as $value) {
            $ex_skill = "";
            if(!is_numeric($value))
            {
                $msg = NEW_SKILL_REQUEST_ARRIVED;
                $detail_link = SITE_ADM_MOD."manage_skills-sd/";
                $lastId = $this->db->insert("tbl_skills",array("skill_name"=>$value,"isActive"=>'n',"isApproved"=>'n'))->getLastInsertId();
                $this->db->insert("tbl_notification",array("userId"=>0,"message"=>$msg,"isRead"=>'n',"detail_link"=>$detail_link,"notificationType"=>'a',"createdDate"=>date('Y-m-d H:i:s')));
                $ex_skill .= $lastId.",";
            }
            else
            {
              $skill_list .= $value.",";
            }
          }
          $skills = trim($ex_skill,",").",".trim($skill_list,",");
        }
        $array = array("skillList"=>trim($skills,","));
        $msg = SKILL_HAS_BEEN_ADDED_SUCCESSFULLY;
      }
      else if($type=='profile')
      {
        $array = array("firstName"=>$firstName,"lastName"=>$lastName);
        $msg = PROFILE_HAS_BEEN_ADDED_SUCCESSFULLY;
      }
      else if($type=='location')
      {
        $array = array("location"=>$userLocation);
        $msg = LOCATION_HAS_BEEN_ADDED_SUCCESSFULLY;
      }
      else if($type=='videoUrl')
      {

        if(strpos($videoUrl, "iframe")){
          $msg= "Invalid Format for video URL. Try Using embed code Iframe's SRC value only.";
          $msgType = $_SESSION["msgType"] = disMessage(array('type' => 'err', 'var' => $msg));
          redirectPage(SITE_URL."f/profile");
          exit();
        }
        if(strpos($videoUrl, "watch") && strpos($videoUrl, "youtube")){
          $videoUrl = explode("=", $videoUrl);
          $videoUrl = "https://www.youtube.com/embed/".$videoUrl[1];
        }
        if(strpos($videoUrl, "/vimeo")){
          $videoUrl = explode("/", $videoUrl);
          $videoUrl = "https://player.vimeo.com/video/".end($videoUrl);
        }

        $array = array("videoUrl"=>$videoUrl);
        $msg = VIDEO_HAS_BEEN_ADDED_SUCCESSFULLY;
      }
      else if($type=='subCategoryList')
      {
         $subcateList = ($cat_name!='') ? implode(",", $cat_name) : '';

         $array = array("subCategoryList"=>$subcateList);
         $msg = CATEGORY_HAS_BEEN_ADDED_SUCCESSFULLY;
      }
      else if($type=='profileUrl')
      {
        $array = array("profileUrl"=>$profileUrl);
        $msg = PROFILE_URL_HAS_BEEN_ADDED_SUCCESSFULLY;
      }
      else if($type=='overView')
      {
        $array = array("professionalOverview"=>$overview);
        $msg = OVERVIEW_HAS_BEEN_ADDED_SUCCESSFULLY;
      }
      else if($type=='proTitle')
      {
        $array = array("professionalTitle"=>$professional_title);
        $msg = PROFESSIONAL_TITLE_HAS_BEEN_ADDED_SUCCESSFULLY;
      }
      else if($type=='expLvl')
      {
        $array = array("freelancerLvl"=>$userLvl);
        $msg = EXPERIENCE_LEVEL_HAS_BEEN_ADDED_SUCCESSFULLY;
      }


      $this->db->update("tbl_users",$array,array("id"=>$this->sessUserId));
      $msgType = $_SESSION["msgType"] = disMessage(array('type' => 'suc', 'var' => $msg));
      redirectPage(SITE_URL."f/profile");
    }
    public function languageAdd($data)
    {
      extract($data);
      $this->db->insert("tbl_user_language",array("userId"=>$this->sessUserId,"languageId"=>$language_name,"langType"=>$language_type,"createdDate"=>date('Y-m-d H:i:s')));
      $msgType = $_SESSION["msgType"] = disMessage(array('type' => 'suc', 'var' => "Language has been add successfully"));
      redirectPage(SITE_URL."f/profile#");
    }
    public function DeleteRecord($data)
    {
      extract($data);
      if($entityType == 'portfolio')
      {
        $table = "tbl_freelancer_portfolio";
      }
      if($entityType == 'education')
      {
        $table = "tbl_freelancer_education";
      }
      if($entityType == 'certificate')
      {
        $table = "tbl_freelancer_certification";
      }
      if($entityType == 'experience')
      {
        $table = "tbl_freelancer_experience";
      }

      $this->db->delete($table,array("id"=>$entityVal));
      redirectPage(SITE_URL."f/profile");
    }

    public function portfolioAdd($data)
    {
      extract($data);
      if(!empty($_FILES['portFolioimage']['name']))
      {
          if (!is_dir(SITE_PORTFOLIO_IMG.$this->sessUserId))
          {
              mkdir(DIR_PORTFOLIO_IMG.$this->sessUserId, 0777, true);
          }
          $file_name = uploadFile($_FILES['portFolioimage'], DIR_PORTFOLIO_IMG.$this->sessUserId."/", SITE_PORTFOLIO_IMG.$this->sessUserId."/");
          $portFolioimage = $file_name['file_name'];
      }
      else
      {
        $portFolioimage = ($old_image=='') ? '' : $old_image;
      }
      $date = "createdDate";
      if($id!='')
      {
        $date = "updatedDate";
      }
      $array = array(
        "userId" => $this->sessUserId,
        "Title" => $projectTitle,
        "image" => $portFolioimage,
        "duration" => $projectDuration,
        "skill" => trim(implode(",",$project_skill_name)),
        "overview" => $projectDesc,
        $date => date('Y-m-d H:i:s')
        );
      if($id!='')
      {
        $this->db->update("tbl_freelancer_portfolio",$array,array("id"=>$id));
      }
      else
      {
        $this->db->insert("tbl_freelancer_portfolio",$array);
      }
      redirectPage(SITE_URL."f/profile");
    }

    public function educationAdd($data)
    {
      extract($data);

      $date = "createdDate";
      if($id!='')
      {
        $date = "updatedDate";
      }
      $array = array(
        "userId" => $this->sessUserId,
        "institute_name" => $instituteName,
        "start_year" => $start_year,
        "end_year" => $end_year,
        "degree_name" => $degree,
        "area_study" => $study,
        "eduDesc" => $desc,
        $date => date('Y-m-d H:i:s')
      );
      if($id!='')
      {
        $this->db->update("tbl_freelancer_education",$array,array("id"=>$id));
      }
      else
      {
        $this->db->insert("tbl_freelancer_education",$array);
      }
      redirectPage(SITE_URL."f/profile#menu1");
    }

    public function certificateAdd($data)
    {
      extract($data);

      $date = "createdDate";
      if($id!='')
      {
        $date = "updatedDate";
      }
      $array = array(
        "userId" => $this->sessUserId,
        "certificateName" => $certiName,
        $date => date('Y-m-d H:i:s')
      );
      if($id!='')
      {
        $this->db->update("tbl_freelancer_certification",$array,array("id"=>$id));
      }
      else
      {
        $this->db->insert("tbl_freelancer_certification",$array);
      }
      redirectPage(SITE_URL."f/profile#menu2");
    }

    public function expAdd($data)
    {
      extract($data);

      $date = "createdDate";
      if($id!='')
      {
        $date = "updatedDate";
      }
      $array = array(
        "userId" => $this->sessUserId,
        "company_name" => $cmp_nm,
        "location" => !empty($location) ? $location : '',
        "Title" => $title,
        "start_month" => $start_month,
        "start_year" => $start_yr,
        "end_month" => $end_month,
        "end_year" => $end_yr,
        "description" => $desc,
        "current_status"=> ($Current=='y') ? 'y' : 'n',
        $date => date('Y-m-d H:i:s')
      );
      if($id!='')
      {
        $this->db->update("tbl_freelancer_experience",$array,array("id"=>$id));
      }
      else
      {
        $this->db->insert("tbl_freelancer_experience",$array);
      }
      redirectPage(SITE_URL."f/profile#menu3");
    }

}
 ?>


