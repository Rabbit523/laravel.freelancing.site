<?php

class freelancerSearch {
    function __construct($module = "", $type='jobs',$id = 0, $token = "",$search_array= array()) {
        foreach ($GLOBALS as $key => $values) {
            $this->$key = $values;
        }
        $this->module = $module;
        $this->id = $id;
        $this->type = $type;
        $this->search_array = $search_array;
    }

    public function getPageContent()
    {
        $keyword = (array_key_exists('keyword',$this->search_array)) ? urldecode($this->search_array['keyword']) : '';
        $category = (array_key_exists('category',$this->search_array)) ? urldecode($this->search_array['category']) : ((array_key_exists('Rcategory',$this->search_array)) ? urldecode($this->search_array['Rcategory']) : '');

        $subcategory = (array_key_exists('subcategory',$this->search_array)) ? urldecode($this->search_array['subcategory']) : ((array_key_exists('Rsubcategory',$this->search_array)) ? urldecode($this->search_array['Rsubcategory']) : '');

        $skills = (array_key_exists('skills',$this->search_array)) ? base64_decode(urldecode($this->search_array['skills'])) : ((array_key_exists('Rskills',$this->search_array)) ? base64_decode(urldecode($this->search_array['Rskills'])) : '');
        $sort = (array_key_exists('sorting',$this->search_array)) ? urldecode($this->search_array['sorting']) : '';

        $exp_lvl = (array_key_exists('exp_lvl',$this->search_array) ? ((urldecode($this->search_array['exp_lvl']) == 'beginner') ? 'f' : 'e') : (array_key_exists('Rexp_lvl',$this->search_array) ? ((urldecode($this->search_array['Rexp_lvl']) == 'beginner') ? 'f' : 'e') : ''));

        $location = $this->search_array['location'] = (array_key_exists('location',$this->search_array)) ? urldecode($this->search_array['location']) : '';
        $avg_rate = (array_key_exists('avg_rate',$this->search_array)) ? urldecode($this->search_array['avg_rate']) : ((array_key_exists('Ravg_rate',$this->search_array)) ? urldecode($this->search_array['Ravg_rate']) : '0');
        $last_activity = (array_key_exists('last_activity',$this->search_array)) ? urldecode($this->search_array['last_activity']) : ((array_key_exists('Rlast_activity',$this->search_array)) ? urldecode($this->search_array['Rlast_activity']) : '');
        $eng_lvl = (array_key_exists('eng_lvl',$this->search_array)) ? urldecode($this->search_array['eng_lvl']) : ((array_key_exists('Reng_lvl',$this->search_array)) ? urldecode($this->search_array['Reng_lvl']) : '');
        $main_cat_id = $this->db->pdoQuery("select id from tbl_category where ".l_values('category_name')." LIKE '%".$category."%' ")->result();
        $where = $sorting = '';

        if($this->type=='feed')
        {
            $where .= $this->jobFeedCondition();
        }
        if(isset($sorting))
        {
            $sorting .= $this->dataSort($sorting);
        }
        if($this->search_array!='')
        {
            $where .= $this->conditionWhere($this->search_array);
        }
        /*if(!empty($where)){
            $where .= "and f.email !='".$_SESSION['pickgeeks_email']."'";
        }*/


        $query = $this->db->pdoQuery("select f.*,AVG(r.startratings) As starRate from tbl_users As f
            LEFT JOIN tbl_subcategory As s ON s.id = f.subCategoryList
            LEFT JOIN tbl_reviews As r on r.freelancerId = f.id
            LEFT JOIN tbl_user_language As l ON l.userId = f.id
            $where group by f.id ".$sorting)->affectedRows();
        $load_class = ($query>10) ? '' : 'hide';

        $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/freelancer_search-sd.skd");
        $sub_content = $sub_content->compile();

        $array = array(
            "%KEYWORD%" => $keyword,
            "%LOAD_CLASS%" => $load_class,
            "%CATEGORY%" => $this->getCategory($category),
            "%SUBCATEGORY%" => $this->getSubcategory($main_cat_id['id'],$subcategory),
            "%SKILLS%" => $this->skill_list($skills),
            "%FREELANCER_DATA%" => $this->freelancerList($this->search_array),
            "%BEGINNER_CLASS%" => ($exp_lvl!='' && $exp_lvl=='f') ? 'checked' : '',
            "%PRO_CLASS%" => ($exp_lvl!='' && $exp_lvl=='e') ? 'checked' : '',
            "%IM_CLASS%" => ($exp_lvl!='' && $exp_lvl=='i') ? 'checked' : '',
            "%AVG_RATE%" => $avg_rate,
            "%LAST_ACTIVITY_I%" => ($last_activity == "2M") ? 'checked' : '',
            "%LAST_ACTIVITY_II%" => ($last_activity == "1M") ? 'checked' : '',
            "%LAST_ACTIVITY_III%" => ($last_activity == "2W") ? 'checked' : '',
            "%LAST_ACTIVITY_IV%" => ($last_activity == "all") ? 'checked' : '',
            "%LOCATION%" => $location,
            "%ENG_LVL_BASIC%" => ($eng_lvl == "Basic") ? 'checked' : '',
            "%ENG_LVL_CONV%" => ($eng_lvl == "Conversational") ? 'checked' : '',
            "%ENG_LVL_FLU%" => ($eng_lvl == "Fluent") ? 'checked' : '',
            "%ENG_LVL_NATIVE%" => ($eng_lvl == "Native") ? 'checked' : '',
            "%NEWEST%" => ($sort=="newest") ? 'selected' : '',
            "%LTIHRATE%" => ($sort=="lTOhRating") ? 'selected' : '',
            "%HTOLRATE%" => ($sort=="hTOlRating") ? 'selected' : '',
            "%LTOHINV%" => ($sort=="lTohInvest") ? 'selected' : '',
            "%HTOLINV%" => ($sort=="hTolInvest") ? 'selected' : '',
            "%SEARCH_SECTION%" => $this->search_label($keyword,$location)
            );

        $data = str_replace(array_keys($array), array_replace($array), $sub_content);
        return $data;
    }

    public function search_label($keyword='',$location='')
    {
        $sub_content = new MainTemplater(DIR_TMPL . "/search_section-sd.skd");
        $sub_content = $sub_content->compile();
        if(isset($this->sessUserId) && $this->sessUserId>0)
        {
            if($this->sessUserType=='Customer')
            {
                $array = array(
                        "%JOB_CLASS%" => 'hide',
                        "%FREELANCER_CLASS%" => '',
                        "%SERVICE_CLASS%" => '',
                        "%FIRST_LABEL%" => 'Freelancer',
                        "%TEXT_PLACEHOLDER%" => 'Find Freelancer',
                        "%FIRST_DATA_URL%" => 'Freelancer',
                        "%KEYWORD%" => $keyword
                    );
            }
            else
            {
                $array = array(
                        "%JOB_CLASS%" => '',
                        "%FREELANCER_CLASS%" => 'hide',
                        "%SERVICE_CLASS%" => 'hide',
                        "%FIRST_LABEL%" => 'Freelancer',
                        "%TEXT_PLACEHOLDER%" => 'Find Freelancer',
                        "%FIRST_DATA_URL%" => 'Freelancer',
                        "%KEYWORD%" => $keyword
                    );
            }
        }
        else
        {
                $array = array(
                        "%JOB_CLASS%" => '',
                        "%FREELANCER_CLASS%" => '',
                        "%SERVICE_CLASS%" => '',
                        "%FIRST_LABEL%" => 'Freelancer',
                        "%TEXT_PLACEHOLDER%" => 'Find Freelancer',
                        "%FIRST_DATA_URL%" => 'Freelancer',
                        "%KEYWORD%" => $keyword
                    );
        }
        $option = "";
        $option .= $array["%FREELANCER_CLASS%"]==""?'<option value="'.SLIDER_LIST_I.'" %I_SELECTED%>'.SLIDER_LIST_I.'</option>':"";
        $option .= $array["%JOB_CLASS%"]==""?'<option value="'.SLIDER_LIST_II.'" %II_SELECTED%>'.SLIDER_LIST_II.'</option>':"";
        $option .= $array["%SERVICE_CLASS%"]==""?'<option value="'.SLIDER_LIST_III.'" %III_SELECTED%>'.SLIDER_LIST_III.'</option>':"";
        $array["%TYPE%"]=$option;
        $array["%LOCATION%"]=$location;
        $array["%I_SELECTED%"]="selected";
        $array["%II_SELECTED%"]="";
        $array["%III_SELECTED%"]="";
        $data = str_replace(array_keys($array), array_values($array), $sub_content);
        return $data;
    }


    public function freelancerList($search_array='',$pageNo=1)
    {
        $num_rec_per_page=10;
        $start_from = ($pageNo-1) * $num_rec_per_page;


        $where = $sorting = '';

        $sorting = '';
        if(isset($search_array['sorting']))
        {
            $sorting .= $this->dataSort($search_array['sorting']);
        }
        if($search_array!='')
        {
            $where .= $this->conditionWhere($search_array);
        }

        /*if(!empty($where)){
            $where .= "and f.email !='".$_SESSION['pickgeeks_email']."'";
        }*/

        $query = $this->db->pdoQuery("select f.*,AVG(r.startratings) As starRate from tbl_users As f
            LEFT JOIN tbl_subcategory As s ON s.id = f.subCategoryList
            LEFT JOIN tbl_reviews As r on r.freelancerId = f.id
            LEFT JOIN tbl_user_language As l ON l.userId = f.id
            $where group by f.id ".$sorting." LIMIT ".$start_from.",".$num_rec_per_page)->results();
        $data = '';
        if(count($query)>0)
        {
            foreach ($query as $value)
            {
                $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/freelancer_loop-sd.skd");
                $sub_content = $sub_content->compile();

                if(isset($this->sessUserId) && $this->sessUserId>0)
                    $saved_freelancer = $this->db->pdoQuery("select * from tbl_saved_freelancer where freelancerId=? and customerId=?",array($value['id'],$this->sessUserId))->affectedRows();
                else
                    $saved_freelancer = 0;
                $array = array(
                                "%ID%" => $value['id'],
                                "%RATING%" => ($value['starRate']*20),
                                "%IMG%" => getUserImage($value['id']),
                                "%SLUG%" => $value['userSlug'],
                                "%FREELANCER_NAME%" => filtering(ucfirst($value['firstName']))." ". filtering(ucfirst($value['lastName'])),
                                "%PROFESSIONAL_TITLE%" => filtering(ucfirst($value['professionalTitle'])),
                                "%COMPLETED_JOBS%" => $this->completed_jobs($value['id']),
                                "%SOLD_SERVICES%" => $this->sold_service($value['id']),
                                "%EARNED%" => earnedAmountFreelancer($value['id']),
                                "%RESPONSE_TIME%" => $this->responseTime($value['id']),
                                "%RESPONSE_TIME_CLASS%" => ($this->responseTime($value['id'])==0) ? 'hide' : '',
                                "%PORTFOLIO%" => $this->portfolio($value['id']),
                                "%DESC%" => filtering($value['aboutme']),
                                "%DESC_CLASS%" => ($value['aboutme']!='')?'':'hide',
                                "%USER_LVL%" => getUserExpLevel($value['freelancerLvl']),
                                "%LOCATION%" => filtering($value['location']),
                                "%SKILLS%" => ($value['skillList']!='') ? $this->skills($value['skillList']) : '',
                                "%SAVE_CLASS%" => ($saved_freelancer>0) ? 'fa fa-heart' : 'fa fa-heart-o',
                                "%SAVE_RIGHTS%" => (isset($this->sessUserId) && $this->sessUserId>0)  ? (($this->sessUserType=='Customer') ? ($this->sessUserId == $value['id'] ? 'hide' : '') : 'hide') : '',
                            );
                $data .= str_replace(array_keys($array), array_replace($array), $sub_content);
            }
        }
        else
        {
            //$data .= "No records Found";
            $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/no_record-sd.skd");
            $sub_content = $sub_content->compile();
            $keyword = !empty($this->search_array['keyword']) ? $this->search_array['keyword'] : '';

            $array = [
                '%REMOVE_DESC%' => $keyword == '' ? 'style="display: none;"' : '',
                '%KEYWORD_DESC%' => $keyword == '' ? 'Search Freelancers'   : "Search Freelancers for : '".$keyword."' ",
                '%REDIRECT_URL%' => SITE_URL.'search/freelancer/'.(empty($keyword) ? ''  : '?keyword='.$keyword)
            ];
            $data .= str_replace(array_keys($array), array_replace($array), $sub_content);
        }
        return $data;
    }
    public function responseTime($id)
    {
      $job_detail = $this->db->pdoQuery("select * from tbl_job_invitation where freelancerId='".$id."' and status!='p' ")->results();
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
          $final_result = 0;
      }
      return $final_result;
    }
    public function completed_jobs($id)
    {
        $jobs_list = $this->db->pdoQuery("select count(jobId) As totalJobs from tbl_job_bids As jb
            LEFT JOIN tbl_jobs As j ON j.id = jb.jobId
            where userId=? and (j.jobStatus=? OR j.jobStatus=?)",array($id,'dsCo','co'))->result();
        return $jobs_list['totalJobs'];
    }
    public function portfolio($id)
    {
        $user_detail = $this->db->pdoQuery("select count(id) As portfolio from tbl_freelancer_portfolio where userId=?",array($id))->result();
        return $user_detail['portfolio'];
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
    public function skills($id)
    {
        $skills = $this->db->pdoQuery("select * from tbl_skills where id IN(".$id.") ")->results();
        $data = '';
        foreach ($skills as $value)
        {
            $data .= "<li>".$value['skill_name']."</li>";
        }
        return $data;
    }
    public function conditionWhere($search_array)
    {
        //printr($search_array,1);
        $where = 'where f.userType="F" and f.isDeleted="n" and f.isActive="y"  ';
        if(!empty($_SESSION['pickgeeks_email'])){
            $where .= "and f.email !='".$_SESSION['pickgeeks_email']."'";
        }

        if((isset($search_array['category']) && $search_array['category']!=''))
        {
            $subCategoryList = $this->db->pdoQuery("SELECT GROUP_CONCAT(s.id) As subcatList FROM `tbl_category` As c
                                LEFT JOIN tbl_subcategory As s ON c.id = s.maincat_id
                                WHERE c.".l_values('category_name')." LIKE '%".urldecode($search_array['category'])."%' ")->result();
            if($subCategoryList['subcatList']!='')
            {
                $where .= " and (";
                $sub_cat = explode(",", $subCategoryList['subcatList']);
                $i=1;
                foreach ($sub_cat as $key => $value) {

                    $where .= ($i==count($sub_cat)) ? " FIND_IN_SET('".$value."',subCategoryList) " : " FIND_IN_SET('".$value."',subCategoryList)  OR ";
                    $i++;
                }
                $where .= " )";
            }
        }
        if(isset($search_array['subcategory']) && $search_array['subcategory']!='')
        {
            $sub_id = $this->db->pdoQuery("select id from tbl_subcategory where ".l_values('subcategory_name')." LIKE '%".urldecode($search_array['subcategory'])."%' ")->result();
            $where .= " and FIND_IN_SET('".$sub_id['id']."',subCategoryList) ";
        }
        if(isset($search_array['skills']) && $search_array['skills']!='')
        {
            $where .= " and FIND_IN_SET('".base64_decode($search_array['skills'])."',f.skillList) ";
        }
        if(isset($search_array['exp_lvl']) && $search_array['exp_lvl']!='')
        {
            $exp_lvl = '';
            if($search_array['exp_lvl']=='pro'){
                $exp_lvl='e';
            }
            else if($search_array['exp_lvl']=='beginner'){
                $exp_lvl='f';
            }
            else{
                $exp_lvl='i';
            }

            $where .= " and f.freelancerLvl = '".$exp_lvl."' ";
        }
        if(isset($search_array['keyword']) && $search_array['keyword']!='')
        {
            $where .= " and (f.firstName LIKE '%".$search_array['keyword']."%' OR f.lastName LIKE '%".$search_array['keyword']."%') ";
        }
        if(isset($search_array['avg_rate']) && $search_array['avg_rate']!='')
        {
            $where .= ($search_array['avg_rate']=='0') ? ' ' : " and r.startratings = '".$search_array['avg_rate']."' ";
        }
        if(isset($search_array['eng_lvl']) && $search_array['eng_lvl']!='')
        {
            $where .= " and l.langType = '".$search_array['eng_lvl']."' ";
        }
        if(isset($search_array['location']) && $search_array['location']!='')
        {
            $where .= " and f.location LIKE '%".$search_array['location']."%' ";
        }
        if(isset($search_array['last_activity']) && $search_array['last_activity']!='')
        {
            if($search_array['last_activity']=='2M')
            {
                $activity = " AND ROUND(DATEDIFF(now(), f.lastLogin)/30, 0)<2 ";
            }
            else if($search_array['last_activity']=='1M')
            {
                $activity = " AND ROUND(DATEDIFF(now(), f.lastLogin)/30, 0)<1 ";
            }
            else if($search_array['last_activity']=='2W')
            {
                $activity = " AND ROUND(DATEDIFF(now(), f.lastLogin)/7, 0)<2";
            }
            else
            {
                $activity = "";
            }
            $where .= $activity;
        }

        return $where;
    }
    public function dataSort($order='Newest')
    {
        $sorting = '';
        if($order == "Newest")
        {
            $sorting .= " ORDER BY f.id DESC";
        }
        if($order == "lTohRatesave_freelancer")
        {
            $sorting .= " ORDER BY AVG(r.startratings) ASC";
        }
        if($order == "hTolRatesave_freelancer")
        {
            $sorting .= " ORDER BY AVG(r.startratings) DESC";
        }
        if($order == "lToHResponsesave_freelancer")
        {
             $sorting .= " ORDER BY (SELECT AVG(DATEDIFF(createdDate, acceptRejectDate)) FROM `tbl_job_invitation` As j WHERE j.freelancerId = f.id and j.status!='p') ASC";
        }
        if($order == "hTolResponsesave_freelancer")
        {
            $sorting .= " ORDER BY (SELECT AVG(DATEDIFF(createdDate, acceptRejectDate)) FROM `tbl_job_invitation` As j WHERE j.freelancerId=f.id and j.status!='p') DESC";
        }
        return $sorting;
    }

    public function getCategory($cat='')
    {

        $category = $this->db->pdoQuery("select c.id As catId,c.".l_values('category_name')." as category_name from tbl_category As c
            LEFT JOIN tbl_subcategory As s ON s.maincat_id = c.id
            where c.isActive=? and c.isDelete=? and s.maincat_id IS NOT NULL group by s.maincat_id",array('y','n'))->results();
        $category_content = '<option value="">--'.SELECT_CATEGORY.'--</option>';

        foreach ($category as $key => $value) {
            $select = ($cat == $value['category_name']) ? 'selected' : '';
            $category_content .=  "<option value='".$value['category_name']."' ".$select.">".$value['category_name']."</option>";
        }
        return $category_content;
    }

    public function getSubcategory($main_cat,$sub_cat='')
    {

        $category = $this->db->select('tbl_subcategory',array('id',l_values('subcategory_name').' as subcategory_name '),array('isActive'=>'y','maincat_id'=>$main_cat,'isDelete'=>'n'))->results();
        $category_content = '<option value="">--'.SELECT_SUBCATEGORY.'--</option>';

        foreach ($category as $key => $value) {
            $select = ($sub_cat == $value['subcategory_name']) ? 'selected' : '';
            $category_content .=  "<option value='".$value['subcategory_name']."' ".$select." >".$value['subcategory_name']."</option>";
        }
        return $category_content;
    }

    public function skill_list($skills='')
    {

        $query = $this->db->pdoQuery("select * from tbl_skills where isActive='y' and isApproved='y' and isDelete='n' ")->results();
        $data = '<option value="">--'.SELECT_SKILLS.'--</option>';
        foreach ($query as $value) {
            $select = ($skills!='') ? (($skills == $value['id']) ? 'selected' : '') : '';
            $data .= "<option value='".base64_encode($value['id'])."' ".$select.">".$value['skill_name']."</option>";
        }
        return $data;
    }

}
 ?>


