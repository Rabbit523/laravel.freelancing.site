<?php

class FreelancerDetailPage {
  function __construct($module = "", $id = 0, $token = "",$slug='') {
    foreach ($GLOBALS as $key => $values) {
      $this->$key = $values;
    }
    $this->module = $module;
    $this->id = $id;
    $this->slug = $slug;
  }

    public function getPageContent()
    {

      $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/freelancer_detailPage-sd.skd");
      $sub_content = $sub_content->compile();

          $query = $this->db->pdoQuery("select * from tbl_users where id=? and isActive='y' and isDeleted='n' ",array($this->id))->result();
          if(empty($query)){
            $msgType = $_SESSION["msgType"] = disMessage(array('type' => 'err', 'var' => USER_NOT_AVAILABLE));
            redirectPage(SITE_URL);
          }
          $rating_detail = $this->db->pdoQuery("select AVG(startratings) As rating from tbl_reviews where freelancerId='".$this->id."' ")->result();
          $rating = ($rating_detail['rating']*20);
          $userName = filtering(ucfirst($query['firstName']))." ".filtering(ucfirst($query['lastName']));

          $url = SITE_URL."f/profile/".$query['userSlug'];
          $array = array(
            "%B_LVL%" => ($query['freelancerLvl']=='f') ? 'selected' : '',
            "%P_LVL%" => ($query['freelancerLvl']=='e') ? 'selected' : '',
            "%STAR_RATING%" => $rating,
            "%LOCATION%" => $query['location'],
            "%PERSONAL_DETAIL%" => $this->personalDetail(),
            "%USER_IMG%" => getUserImage($this->id),
            "%SKILL_SELECTION_LIST%" => $this->skill_list($query['skillList'],'all'),
            "%LANG_SELECTION_LIST%" => $this->lang_list($query['langList'],'all'),
            "%FIRST_NAME%" => filtering($query['firstName']),
            "%LAST_NAME%" => filtering($query['lastName']),
            "%LOCATION%" => $query['location'],
            "%VIDEO_URL%" => $query['videoUrl'],
            "%VIDEO_CLASS%" => ($query['videoUrl'] == '') ? 'hide' : '',
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
            "%LINK_SHARE_CLASS%" => '',
            "%SHARE_LINK_MODAL%" => $this->share_url($url,$userName),
            "%OVERVIEW_MODAL%" => $this->overViewSection($query['professionalOverview']),
            "%PROFESSIONAL_TITLE_MODAL%" => $this->proTitleSection($query['professionalTitle']),
            "%FREELANCER_IMG%" => getUserImage($query['id']),
            "%FREELANCER_NAME%" => filtering(ucfirst($query['firstName']))." ".filtering(ucfirst($query['lastName'])),
            "%PROFESSIONAL_DETAIL%" => filtering($query['professionalTitle']),
            "%FREELANCER_ID%" => $this->id,
            "%ACTIVE_JOBS%" => $this->activeJobsList(),
            "%VIDEO_LINK%" => $query['videoUrl'],
            "%VIDEO_CLASS%" => ($query['videoUrl'] == '') ? 'hide' : '',
            "%SERVICES_LIST%" => $this->services(),
            '%OWNER_LOGIN%' => empty($this->sessUserId) ? 'hide' : '',
            "%SERVICE_CLASS%" => ($this->services() != '') ? '' : 'hide',            
            "%SUBCAT_LIST%" => ($query['subCategoryList']!='') ? $this->subCategoryList($query['subCategoryList']) :'',
            "%LANG_LIST%" => (user_language_list($this->id)!='') ? $this->lang_list(user_language_list($this->id)) : '',
            "%SKILL_LIST%" => ($query['skillList']!='') ? $this->skill_list($query['skillList']) : '',
          );
      return str_replace(array_keys($array), array_values($array), $sub_content);
    }

    public function activeJobsList()
    {
        $job_invited = $this->db->pdoQuery("select GROUP_CONCAT(distinct(jobId)) As jobList from tbl_job_invitation where customerId=? and freelancerId=? ",array($this->sessUserId,$this->id))->result();
        if($job_invited['jobList']!='')
        {
          $where = "posterId='".$this->sessUserId."' and jobStatus='p' and isApproved='a' and jobType='pr' and isActive='y' and id NOT IN(".$job_invited['jobList'].") ";

        }
        else
        {
          $where = "posterId='".$this->sessUserId."' and jobStatus='p' and isApproved='a' and jobType='pr' and isActive='y' ";
        }
        $data = $this->db->pdoQuery("select id,jobTitle from tbl_jobs where $where")->results();

        $dataList = '<option value="">--Select Job--</option>';
        foreach ($data as $value) {
            $dataList .= "<option value='".$value['id']."'>".$value['jobTitle']."</option>";
        }
        return $dataList;

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
       return str_replace(array("%URL%","%USERNM%","%SHARE_DIV%"), array($url,getUserName($userName),$this->share_div($url)), $sub_content);
    }
    public function share_div($url)
    {
      if($url=='')
      {
        $data = NO_URL_TO_SHARE;
      }
      else
      {
          $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/share_div-sd.skd");
          $sub_content = $sub_content->compile();
          $data = str_replace(array("%URL%"),array($url),$sub_content);
      }
      return $data;
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
        $query = $this->db->pdoQuery("select * from tbl_freelancer_experience where userId=? ",array($this->id))->results();
        if(count($query)>0)
        {
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
        }
        else
        {
           $loop_data .= "<span class='no-records'><i class='fa fa-exclamation-triangle'></i>".NO_RECORDS_FOUND."</span>";
        }
        return $loop_data;
    }
    public function experienceSection($id='')
    {
        $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/addexp-sd.skd");
        $sub_content = $sub_content->compile();

        if($id!='')
        {
          $query = $this->db->pdoQuery("select * from  tbl_freelancer_experience where userId=? and id=?",array($this->id,trim($id)))->result();

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
        $query = $this->db->pdoQuery("select * from tbl_freelancer_certification where userId=? ",array($this->id))->results();
        if(count($query)>0)
        {
          foreach ($query as $value) {
              $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/certification-sd.skd");
              $sub_content = $sub_content->compile();

              $array = array(
                  "%TITLE%" => filtering($value['certificateName']),
                  "%ID%" => $value['id']
              );
              $loop_data .= str_replace(array_keys($array), array_values($array), $sub_content);
          }
        }
        else
        {
           $loop_data .= "<span class='no-records'><i class='fa fa-exclamation-triangle'></i>".NO_RECORDS_FOUND."</span>";
        }
        return $loop_data;
    }
    public function certificationSection($id='')
    {
          $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/addcertificate-sd.skd");
          $sub_content = $sub_content->compile();

          if($id!='')
          {
            $query = $this->db->pdoQuery("select * from  tbl_freelancer_certification where userId=? and id=?",array($this->id,trim($id)))->result();

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
            $query = $this->db->pdoQuery("select * from  tbl_freelancer_education where userId=? and id=?",array($this->id,trim($id)))->result();

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

      $yrData='<option value="">--Select Year--</option>';
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
      $yrData='<option value="">--Select Month--</option>';
      for($i=1;$i<=12;$i++)
      {
        $select = ($yr==$i) ? "selected" : '';
        $yrData .= "<option value='".$month[$i]."' ".$select.">".$month[$i]."</option>";
      }
      return $yrData;
    }

    public function education()
    {
        $loop_data = '';
        $query = $this->db->pdoQuery("select * from  tbl_freelancer_education where userId=? ",array($this->id))->results();
        if(count($query)>0)
        {
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
        }
        else
        {
           $loop_data .= "<span class='no-records'><i class='fa fa-exclamation-triangle'></i>".NO_RECORDS_FOUND."</span>";
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
          $query = $this->db->pdoQuery("select * from  tbl_freelancer_portfolio where userId=? and id=?",array($this->id,trim($id)))->result();
          $img = ($query['image']=='') ?  SITE_UPD."default-image_450.png" : SITE_PORTFOLIO_IMG.$this->id."/".$query['image'];
          $array = array(
                  "%PORTFOLIO_IMG%" => "<img src='".$img."' height='50' width='50'>",
                  "%PROJECT_TITLE%" => filtering($query['Title']),
                  "%PROJECT_DURATION%" => filtering($query['duration']),
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
                  "%PROJECT_DURATION%" => '',
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
        $query = $this->db->pdoQuery("select * from  tbl_freelancer_portfolio where userId=? ",array($this->id))->results();
        if(count($query)>0)
        {
            foreach ($query as $value) {
                $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/portfolio-sd.skd");
                $sub_content = $sub_content->compile();
                $skill = explode(",", $value['skill']);$skills='';
                foreach ($skill as $value1)
                {
                    $skill_detail = $this->db->pdoQuery("select * from tbl_skills where id=?",array($value1))->result();
                    $skills .= "<li class='flp-skil'>".$skill_detail['skill_name']."</li>";
                }
                $array = array(
                    "%PORTFOLIO_IMG%" => ($value['image']=='') ?  SITE_UPD."default-image_450.png" : SITE_PORTFOLIO_IMG.$this->id."/".$value['image'],
                    "%TITLE%" => filtering($value['Title']),
                    "%DURATION%" => filtering($value['duration']),
                    "%DESC%" => filtering($value['overview']),
                    "%SKILLS%" => $skills,
                    "%ID%" => $value['id']
                );
                $loop_data .= str_replace(array_keys($array), array_values($array), $sub_content);
            }
        }
        else
        {
           $loop_data .= "<span class='no-records'><i class='fa fa-exclamation-triangle'></i>".NO_RECORDS_FOUND."</span>";
        }
        return $loop_data;
    }




    public function personalDetail()
    {
        $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/personal_detail-sd.skd");
        $sub_content = $sub_content->compile();

        $query = $this->db->pdoQuery("select * from tbl_users where id=? ",array($this->id))->result();
        $profile_url = SITE_URL."f/profile/".$query['userSlug'];

        $saved_user_detail = $this->db->pdoQuery("select * from tbl_saved_freelancer where customerId=? and freelancerId=?",array($this->sessUserId,$this->id))->affectedRows();

        $report_user_detail = $this->db->pdoQuery("select * from tbl_report where reportedId=? and reportType=? and userId=? and reporterId=? ",array($this->id,'User',$this->id,$this->sessUserId))->affectedRows();
        $array = array(

          "%USERNAME%" => filtering(ucfirst($query['firstName']))." ".filtering(ucfirst($query['lastName'])),
          "%PROTITLE%" => ($query['professionalTitle']=='') ? '-':filtering($query['professionalTitle']),
          "%PROTITLE_CLASS%" => ($query['professionalTitle']=='') ? 'hide':'',
          "%FRELVL%" => getUserExpLevel($query['freelancerLvl']),
          "%SHOW_EMAIL%" => ($sessUserId==$query['id'])?"":"hide",
          "%EMAIL%" => $query['email'],
          "%LOCATION%" => $query['location'],
          "%SUBCAT_LIST%" => ($query['subCategoryList']!='') ? $this->subCategoryList($query['subCategoryList']) :'',
          "%SUBCAT_LIST_LABEL%" => ($query['subCategoryList']!='') ? 'hide' : '',
          "%PROURL%" => $profile_url,
          "%PROURL_CLASS%" => ($query['profileUrl']=='') ? 'hide' : '',
          "%OVERVIEW%" => ($query['professionalOverview']=='') ? '-' : $query['professionalOverview'],
          "%LAST_SEEN%" => getTime($query['lastLogin']),
          "%SINCE_DATE%" => date('d F,Y',strtotime($query['createdDate'])),
          "%EARNED%" => earnedAmountFreelancer($this->id),
          "%COMPLETED_JOB%" => $this->completedJobs(),
          "%SOLD_SERVICE%" => $this->sold_service(),
          "%RESPONSE_TIME%" => $this->responseTime(),
          "%PUN_REVIEW%" => $this->reviewAVG('punctality'),
          "%WORK_REVIEW%" => $this->reviewAVG('workClarification'),
          "%QUAT_REVIEW%" => $this->reviewAVG('workQuality'),
          "%EXP_REVIEW%" => $this->reviewAVG('expertise'),
          "%COMM_REVIEW%" => $this->reviewAVG('communication'),
          "%SKILL_LIST%" => ($query['skillList']!='') ? $this->skill_list($query['skillList']) : '',
          "%LANG_LIST%" => (user_language_list($this->id)!='') ? $this->lang_list(user_language_list($this->id)) : '',
          "%RESPONSE_TIME%" => $this->responseTime(),
          "%DATA_SLUG%" => $query['userSlug'],
          "%SAVED_CLASS%" => ($saved_user_detail>0) ? 'fa fa-heart' : 'fa fa-heart-o',
          "%REPORT_CLASS%" => ($report_user_detail>0) ? 'fa fa-flag' : 'fa fa-flag-o',
          "%FREELANCER_RIGHTS%" => ($this->sessUserType == "Freelancer") ? 'hide' : '',
          "%USER_SLUG%" => $this->slug,
          "%LANG_CLASS%" => (user_language_list($this->id)!='') ? '' : 'hide',
          "%SKILL_CLASS%" => ($query['skillList']!='') ? '' : 'hide',
          "%VIDEO_DIV%" => empty($query['videoUrl'])?"hide":"",
          "%VIDEO_IFRAME%" => !empty($query['videoUrl']) ? '<iframe id="playVideo" style="width:100%; min-height:450px;" src="'.$query['videoUrl'].'"></iframe>' : '',
          "%MY_SERVICES%" => $this->my_services($this->id),

          );
        return str_replace(array_keys($array), array_values($array), $sub_content);
    }
    public function my_services($id)
    {

      $num_rec_per_page = 10;
      $pageNo=1;
      $start_from = load_more_pageNo($pageNo,10);

      $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/my_services-sd.skd");
      $sub_content = $sub_content->compile();

      $where = "s.freelanserId='".$id."' and s.isDelete='n' ";
      
      $query = $this->db->pdoQuery("select s.*,c.".l_values('category_name')." as category_name ,sub.".l_values('subcategory_name')." as subcategory_name from tbl_services As s
        LEFT JOIN tbl_category As c ON c.id = s.servicesCategory
        LEFT JOIN tbl_subcategory As sub ON sub.id = s.servicesSubCategory
        where ".$where." order by s.id DESC LIMIT ".$start_from.",".$num_rec_per_page)->results();
      $data = "";
      if(count($query)>0)
      {
        foreach ($query as $value)
        {
          if($value['featured'] == 'n' && $value['isApproved'] == 'a')
          {
              $make_featured_Class = '';
          }
          else
          {
              $make_featured_Class = 'hide';
          }
          $img = getserviceImages($value['id'],1);
          $delete_rights = (checkDelete($value['id'],$this->sessUserId) == 0) ? '' : 'hide';
          $addon_res =  $this->db->pdoQuery("SELECT COUNT(id) as total FROM tbl_services_addon WHERE services_id=".$value['id'])->result();
          $string = filtering(ucfirst($value['description']));
          $stringCut = substr($string, 0, 150);
          $endPoint = strrpos($stringCut, ' ');
          
          $array = array(
            "%SERVICES_IMG%" => $img[0],
            "%SERVICES_TITLE%" => filtering(ucfirst($value['serviceTitle'])),
            "%DESCRIPTION%" => substr(filtering(ucfirst($value['description'])),0,$endPoint)." ... ",
            "%PRICE%" => filtering($value['servicesPrice'])."<span>".CURRENCY_SYMBOL."</span>",
            "%POSTED_DATE%" => date('dS, F Y',strtotime($value['servicesPostDate'])),
            "%STATUS%" => ($value['isActive']=='y') ? 'Active' : 'DeActive',
            "%DELIVERY_DAYS%" => filtering($value['noDayDelivery'])." Day(s)&lrm;",
            "%REJECTED_CLASS%" => $value['isApproved']=='r' ? '' : 'hide',
            "%FEATURED_CLASS%" => ($value['featured']=='y' && $value['featured_payment_status'] == 'c') ? '' : 'hide',
            "%SLUG%" => $value['servicesSlug'],
            "%SERVICE_ID%" => $value['id'],
            "%FEATURED_DIV_CLASS%" => ($value['featured']=='y' && $value['isApproved'] =='a' && $value['featured_payment_status']=='p') ? '' : 'hide',
            "%FEATURED_FEES%" => SERVICE_FEATURED_FEES*$value['featured_days'],
            "%EDIT_RIGHTS%" => ($value['isApproved']=='a') ? 'already_approve':'',
            "%MAKE_FEATURED_CLASS%" => $make_featured_Class,
            "%DELETE_RIGHTS%" => $delete_rights,
            "%APPROVAL_STATUS%" => ($value['isApproved']=='a') ? 'Approved' : ( ($value['isApproved']=='r') ? "Rejected" : 'Pending'),
            "%TOTAL_ADDONS%" => $addon_res["total"],
          );
          $data .= str_replace(array_keys($array), array_replace($array), $sub_content);
        }
      }
      else
      {
        $data = "<span class='no-records'><i class='fa fa-exclamation-triangle'></i>".NO_RECORDS_FOUND."</span>";
      }
      return $data;
    }

    public function completedJobs()
    {
      $jobs_list = $this->db->pdoQuery("select count(jobId) As totalJobs from tbl_job_bids As jb
        LEFT JOIN tbl_jobs As j ON j.id = jb.jobId
        where userId=? and (j.jobStatus=? OR j.jobStatus=?)",array($this->id,'dsCo','co'))->result();
      return $jobs_list['totalJobs'];
    }

    public function services()
    {
      $services = $this->db->pdoQuery("select * from tbl_services where freelanserId=?",array($this->id))->results();
      $data = '';
      if(count($services)>0)
      {
        foreach ($services as $value)
        {
            if(isset($this->sessUserId) && $this->sessUserId>0)
                      $fav = $this->db->pdoQuery("select * from tbl_favorite_services where customerId=? and serviceId=?",array($this->sessUserId,$value['id']))->affectedRows();
            else
                $fav = 0;

            $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/serviceList-sd.skd");
            $sub_content = $sub_content->compile();
            $img = getserviceImages($value['id'],1);

            $array = array(
              "%FEATURED_CLASS%"=> ($value['featured'] == 'y' && $value['featured_payment_status'] =='c') ? '' : 'hide',
              "%SERVICES_IMG%"=> ($img[0]!='') ? $img[0] : SITE_UPD."default-image_450.png",
              "%SERVICE_TITLE%"=> filtering($value['serviceTitle']),
              "%CATEGORY%"=> $this->serviceCat($value['servicesCategory']),
              "%SUB_CATEGORY%"=> $this->serviceSubCat($value['servicesSubCategory']),
              "%PRICE%"=> CURRENCY_SYMBOL.$value['servicesPrice'],
              "%SOLD%"=> $this->sold_service_total($value['id']),
              "%FAV_CLASS%" => (isset($this->sessUserId) && $this->sessUserId>0)  ? (($this->sessUserType=='Customer') ? '' : 'hide') : '',
              "%FAV_TEST%" => ($fav>0) ? 'fa fa-heart' : 'fa fa-heart-o'

            );
          $data .= str_replace(array_keys($array), array_values($array), $sub_content);
        }
      }
      else
      {
         $data .= '';
      }
      return $data;
    }
    public function sold_service_total($id)
    {
      $query = $this->db->pdoQuery("select COUNT(id) as total from tbl_services_order where servicesId=?",array($id))->result();
      return $query['total'];
    }
    public function serviceCat($id)
    {
      $query = $this->db->pdoQuery("select ".l_values('category_name')." as category_name  from tbl_category where id=?",array($id))->result();
      return filtering(ucfirst($query['category_name']));
    }
    public function serviceSubCat($id)
    {
      $query = $this->db->pdoQuery("select ".l_values('subcategory_name')." as subcategory_name from tbl_subcategory where id=?",array($id))->result();
      return filtering(ucfirst($query['subcategory_name']));
    }
    public function sold_service()
    {
      $data = $this->db->pdoQuery("select * from tbl_services_order where freelanserId='".$this->id."' and paymentStatus='c' and serviceStatus='c'")->results();
      $earned_amount = 0;
      foreach ($data as $value)
      {
        $earned_amount ++;
      }
      return $earned_amount;
    }
    public function responseTime()
    {
      $job_detail = $this->db->pdoQuery("select * from tbl_job_invitation where freelancerId='".$this->id."' and status!='p' ")->results();
      $d = 0;$i=0;
      foreach ($job_detail as $value)
      {
        $now = date_create($value['createdDate']);
        $date = date_create($value['acceptRejectDate']);
        $interval = $date->diff($now);
        $days = $interval->format('%i');
        $d += $days;
        $i++;
      }
      $final = ($i!=0) ? ceil($d/$i) : 0;

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
      else
      {
          $final_result = '0';
      }
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
      $query = $this->db->pdoQuery("select AVG(".$type.") As AvgRating from  tbl_reviews where freelancerId=? ",array($this->id))->result();
      return ($query['AvgRating']=='') ? '0' : ($query['AvgRating']*10);
    }

    public function skill_list($list,$limit='')
    {
      $data = $user_skill =  "";
      if($limit=='all')
      {
          $query = $this->db->pdoQuery("select * from tbl_skills where isActive='y' and isDelete='n' and isApproved='y' ")->results();
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
        $data = $user_lang = "";
        if($limit=='all')
        {
          $query = $this->db->pdoQuery("select * from tbl_language where isActive='y' ")->results();
          foreach ($query as $value)
          {
              $string = $list; $selected ='';
              $what_to_find = $value['id'];
              if (preg_match('/\b' . $what_to_find . '\b/', $string)) {
                 $selected = "selected";
              }
              $user_lang .= "<option value=".$value['id']." ".$selected.">".$value['language']."</option>";
          }
          $data = $user_lang;
        }
        else
        {
            $query = $this->db->pdoQuery("select l.*,ul.langType from tbl_user_language As ul
              LEFT JOIN tbl_language As l ON l.id = ul.languageId
              where userId ='".$this->id."' ")->results();
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
        $checked ='';
        $query = $this->db->pdoQuery("select * from tbl_subcategory where isActive='y' and isDelete='n' and maincat_id='".$mainCatId."' ")->results();
        $data = '';
        foreach ($query as $value)
        {
              $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/category_selection_loop-sd.skd");
              $sub_content = $sub_content->compile();

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

    public function reportService($data)
    {
        extract($data);
        $reportedId = $freelancerId;
        $report_check = $this->db->pdoQuery("select * from tbl_report where reportedId=? and reporterId=? and reportType=? and userId=?",array($reportedId,$this->sessUserId,'User',$freelancerId))->affectedRows();
        if($report_check>0)
        {
            $msgType = $_SESSION["msgType"] = disMessage(array('type' => 'err', 'var' => YOUR_HAVE_ALREADY_REPORTED_THIS_USER));
            redirectPage(SITE_URL."f/profile/".$this->slug);
        }
        else
        {
            $this->db->insert("tbl_report",array("reportedId"=>$reportedId,"reportType"=>'User',"userId"=>$freelancerId,"reporterId"=>$this->sessUserId,"reportMessage"=>trim($report_reason),"status"=>'Pen',"createdDate"=>date('Y-m-d H:i:s'),"ipAddress"=>get_ip_address()));

            $msgType = $_SESSION["msgType"] = disMessage(array('type' => 'suc', 'var' => YOUR_REPORT_HAS_BEEN_SENT_SUCCESSFULLY));
            redirectPage(SITE_URL."f/profile/".$this->slug);
        }

    }
    public function inviteUser($data)
    {
        extract($data);

        $this->db->insert("tbl_job_invitation",array("customerId"=>$this->sessUserId,"freelancerId"=>$freelancerId,"jobId"=>$jobId,"status"=>'p',"createdDate"=>date('Y-m-d H:i:s')));

        $freelancerDetail = getUser($freelancerId);
        $custDetail = getUser($this->sessUserId);
        $customerName = filtering(ucfirst($custDetail['firstName']))." ".filtering(ucfirst($custDetail['lastName']));
        $jobDetail = $this->db->pdoQuery("select * from tbl_jobs where id=?",array($jobId))->result();
        $link = "<a href='".SITE_URL."job/".$jobDetail['jobSlug']."' target='_blank'>Job Link</a>";

        $arrayCont = array('HEADING'=>"Job Invitation",'USER'=>$freelancerDetail['userName'],"CUSTOMER_NAME"=>$customerName,"LINK"=>$link);
        $array = generateEmailTemplate('invite_freelancer_for_job',$arrayCont);
        sendEmailAddress($freelancerDetail['email'],$array['subject'],$array['message']);

        $msgType = $_SESSION["msgType"] = disMessage(array('type' => 'suc', 'var' => INVITAION_SENT_SUCCESSFULLY));
    }
}
 ?>


