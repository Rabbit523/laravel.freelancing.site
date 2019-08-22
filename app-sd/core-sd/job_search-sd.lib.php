<?php

class jobSearch {
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
       
        $start_amount = (array_key_exists('start_amount',$this->search_array)) ? urldecode($this->search_array['start_amount']) : '';
        $end_amount = (array_key_exists('end_amount',$this->search_array)) ? urldecode($this->search_array['end_amount']) : '';
        $keyword = (array_key_exists('keyword',$this->search_array)) ? urldecode($this->search_array['keyword']) : '';
        $category = (array_key_exists('category',$this->search_array)) ? urldecode($this->search_array['category']) : ((array_key_exists('Rcategory',$this->search_array)) ? urldecode($this->search_array['Rcategory']) : '');
        $subcategory = (array_key_exists('subcategory',$this->search_array)) ? urldecode($this->search_array['subcategory']) : ((array_key_exists('Rsubcategory',$this->search_array)) ? urldecode($this->search_array['Rsubcategory']) : '');

        $skills = (array_key_exists('skills',$this->search_array)) ? urldecode($this->search_array['skills']) : ((array_key_exists('Rskills',$this->search_array)) ? urldecode($this->search_array['Rskills']) : '');
        $sort = (array_key_exists('sorting',$this->search_array)) ? urldecode($this->search_array['sorting']) : '';
        $exp_lvl = (array_key_exists('exp_lvl',$this->search_array)) ? urldecode($this->search_array['exp_lvl']) : ((array_key_exists('Rexp_lvl',$this->search_array)) ? urldecode($this->search_array['Rexp_lvl']) : '');
        $no_applicants = (array_key_exists('no_applicants',$this->search_array)) ? urldecode($this->search_array['no_applicants']) : ((array_key_exists('Rno_applicants',$this->search_array)) ? urldecode($this->search_array['Rno_applicants']) : '');
        $startdate = (array_key_exists('startdate',$this->search_array)) ? urldecode($this->search_array['startdate']) : ((array_key_exists('startdate',$this->search_array)) ? urldecode($this->search_array['startdate']) : '');
        $enddate = (array_key_exists('enddate',$this->search_array)) ? urldecode($this->search_array['enddate']) : ((array_key_exists('Renddate',$this->search_array)) ? urldecode($this->search_array['Renddate']) : '');
        $jobType = (array_key_exists('jobType',$this->search_array)) ? urldecode($this->search_array['jobType']) : ((array_key_exists('RjobType',$this->search_array)) ? urldecode($this->search_array['RjobType']) : '');
        $location = $this->search_array['location'] = (array_key_exists('location',$this->search_array)) ? urldecode($this->search_array['location']) : '';
        if($category!=""){
            $main_cat_id = $this->db->pdoQuery("select id from tbl_category where ".l_values('category_name')." LIKE '%".$category."%' ")->result();
        }
        else{
            $main_cat_id['id']=0;
        }
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
        if(isset($this->sessUserId) && $this->sessUserId>0)
        {
            $where .= $this->loginUserCondition();
        }

        //if user is login then jobs are display as pewr their professional type.
        if(!empty($_SESSION["pickgeeks_userId"])){
            $level = getTableValue("tbl_users","freelancerLvl",array("id"=>$_SESSION["pickgeeks_userId"]));
            if(!empty($level)){
                if($level == 'e'){
                    $exp_where='p';
                    $exp_lvl='pro';
                }else if($level == 'f'){
                    $exp_where='b';
                    $exp_lvl='beginner';
                }else if($level == 'i'){
                    $exp_where='i';
                    $exp_lvl='intermediate';
                }
            }
            $where .= " and j.expLevel = '".$exp_where."' ";
        }
        $query = $this->db->pdoQuery("select j.*,c.".l_values('category_name')." as category_name,s.".l_values('subcategory_name')." as subcategory_name,u.firstName,u.lastName,u.location,j.jobStatus from tbl_jobs As j
            LEFT JOIN tbl_category As c ON c.id = j.jobCategory
            LEFT JOIN tbl_subcategory As s ON s.id = j.jobSubCategory
            LEFT JOIN tbl_users As u ON u.id = j.posterId
            LEFT JOIN tbl_reviews As r ON r.customerId = j.posterId
            where j.jobType='pu' and j.jobStatus != 'h' and j.jobStatus != 'co' and j.jobStatus != 'c' and j.isApproved='a' and j.isActive='y' and j.isDelete='n' and u.isDeleted='n' and u.isActive='y' and j.biddingDeadline >= '".date('Y-m-d H:i:s')."' $where group by j.id ".$sorting)->affectedRows();


        $load_class = ($query > 10) ? '' : 'hide';
        $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/job_search-sd.skd");
        $sub_content = $sub_content->compile();
        $array = array(
            "%KEYWORD%" => $keyword,
            "%CATEGORY%" => $this->getCategory($main_cat_id['id']),
            "%SUBCATEGORY%" => $this->getSubcategory($main_cat_id['id'],$subcategory),
            "%SKILLS%" => $this->skill_list($skills),
            "%JOB_DATA%" => $this->jobList($this->search_array),
            "%MAX_BUDGET%" => $this->jobMaxBudget(),
            "%LOAD_CLASS%" => $load_class,
            "%BEGINNER_CLASS%" => ($exp_lvl!='' && $exp_lvl=='beginner') ? 'checked' : '',
            "%PRO_CLASS%" => ($exp_lvl!='' && $exp_lvl=='pro') ? 'checked' : '',
            "%IM_CLASS%" => ($exp_lvl!='' && $exp_lvl=='intermediate') ? 'checked' : '',
            "%NO_APPLICANTS%" => $no_applicants,
            "%NEWEST%" => ($sort=="newest") ? 'selected' : '',
            "%LTIHRATE%" => ($sort=="lTOhRating") ? 'selected' : '',
            "%HTOLRATE%" => ($sort=="hTOlRating") ? 'selected' : '',
            "%LTOHINV%" => ($sort=="lTohInvest") ? 'selected' : '',
            "%HTOLINV%" => ($sort=="hTolInvest") ? 'selected' : '',
            "%FEATURED_CLASS%" => ($jobType=='featured') ? 'selected' : '',
            "%NEW_JOBS_CLASS%" => ($jobType=='new') ? 'selected' : '',
            "%START_DATE%" => ($startdate!='') ? date('d-m-Y',strtotime($startdate)) : '',
            "%END_DATE%" => ($enddate!='') ? date('d-m-Y',strtotime($enddate)) : '',
            "%LOCATION%" => $location,
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
                    "%FIRST_LABEL%" => 'Jobs',
                    "%TEXT_PLACEHOLDER%" => 'Find Jobs',
                    "%FIRST_DATA_URL%" => 'Freelancers',
                    "%KEYWORD%" => $keyword,                        
                );
            }
            else
            {
                $array = array(
                    "%JOB_CLASS%" => '',
                    "%FREELANCER_CLASS%" => 'hide',
                    "%SERVICE_CLASS%" => 'hide',
                    "%FIRST_LABEL%" => 'Jobs',
                    "%TEXT_PLACEHOLDER%" => 'Find Jobs',
                    "%FIRST_DATA_URL%" => 'Jobs',
                    "%KEYWORD%" => $keyword,
                );
            }
        }
        else
        {
            $array = array(
                "%JOB_CLASS%" => '',
                "%FREELANCER_CLASS%" => '',
                "%SERVICE_CLASS%" => '',
                "%FIRST_LABEL%" => 'Jobs',
                "%TEXT_PLACEHOLDER%" => 'Find Jobs',
                "%FIRST_DATA_URL%" => 'Jobs',
                "%KEYWORD%" => $keyword,
            );
        }
        $option = "";
        $option .= $array["%FREELANCER_CLASS%"]==""?'<option value="'.SLIDER_LIST_I.'" %I_SELECTED%>'.SLIDER_LIST_I.'</option>':"";
        $option .= $array["%JOB_CLASS%"]==""?'<option value="'.SLIDER_LIST_II.'" %II_SELECTED%>'.SLIDER_LIST_II.'</option>':"";
        $option .= $array["%SERVICE_CLASS%"]==""?'<option value="'.SLIDER_LIST_III.'" %III_SELECTED%>'.SLIDER_LIST_III.'</option>':"";
        // pre_print($array);
        $array["%TYPE%"]=$option;
        $array["%LOCATION%"]=$location;
        $array["%I_SELECTED%"]="";
        $array["%II_SELECTED%"]="selected";
        $array["%III_SELECTED%"]="";
        $data = str_replace(array_keys($array), array_values($array), $sub_content);
        return $data;
    }
    public function jobMaxBudget()
    {
        $where = " j.jobType='pu' and j.isApproved='a' and j.isActive='y' ";
        if($this->type=='feed')
        {
            $where .= $this->jobFeedCondition();
        }
        $data = $this->db->pdoQuery("select MAX(j.budget) As maxBudget from tbl_jobs As j where $where")->result();
        return $data['maxBudget'];
    }
    public function jobFeedCondition()
    {
        $userCat = getUserDetails('subCategoryList',$this->sessUserId);
        $userSkill = getUserDetails('skillList',$this->sessUserId);

        if($userCat!='')
          $where .= " and j.jobSubCategory IN(".$userCat.")";
        if($userSkill!='')
          $where .= " and j.skills IN(".$userSkill.")";

        return $where;
    }

    public function jobList($search_array='',$pageNo=1)
    {

        $num_rec_per_page=10;
        $start_from = ($pageNo-1) * $num_rec_per_page;


        $where = '';
        if($this->type=='feed')
        {
            $where .= $this->jobFeedCondition();
        }

        $sorting = '';
        if(isset($search_array['sorting']))
        {
            $sorting .= $this->dataSort($search_array['sorting']);
        }
        else
        {
            $sorting .= " ORDER by j.id DESC ";
        }

        if($search_array!='')
        {
            $where .= $this->conditionWhere($search_array);
        }
        if(isset($this->sessUserId) && $this->sessUserId>0)
        {
            $where .= $this->loginUserCondition();

        }

        $query = $this->db->pdoQuery("select j.*,c.".l_values('category_name')." as category_name,s.".l_values('subcategory_name')." as subcategory_name,u.firstName,u.lastName,u.location,j.jobStatus,u.userSlug 
            from tbl_jobs As j
            LEFT JOIN tbl_category As c ON c.id = j.jobCategory
            LEFT JOIN tbl_subcategory As s ON s.id = j.jobSubCategory
            LEFT JOIN tbl_users As u ON u.id = j.posterId
            LEFT JOIN tbl_reviews As r ON r.customerId = j.posterId
            where j.jobType='pu' and j.isActive='y' and j.jobStatus != 'h' and j.jobStatus != 'co' and j.jobStatus != 'c' and j.isApproved='a' and j.isDelete='n' and j.biddingDeadline >= '".date('Y-m-d H:i:s')."' $where and u.isDeleted='n' and u.isActive='y' and j.hideFrmSearch != 'y'  group by j.id ".$sorting."  LIMIT ".$start_from.",".$num_rec_per_page)->results();
        $data = '';
        if(count($query)>0)
        {
            foreach ($query as $value)
            {
                $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/job_loop-sd.skd");
                $sub_content = $sub_content->compile();

                if(isset($this->sessUserId) && $this->sessUserId>0)
                    $fav = $this->db->pdoQuery("select * from tbl_saved_job where userId=? and jobId=?",array($this->sessUserId,$value['id']))->affectedRows();
                else
                    $fav = 0;

                if($value["isActive"]=="n"){
                    $url = "javascript:void(0);";
                    $target = "";
                    $job_class = "job_details";
                    $message = ($value["jobStatus"]=="p")?JOB_HAS_NOT_BEEN_APPROVED_YET:JOB_NOT_AVAILABLE;                   
                }else{
                    $url = SITE_URL."job/".$value['jobSlug'];
                    $target = 'target="_blank"';
                    $job_class = $message = "";
                }

                $skills_data_class = $this->skills($value['skills']);
                $array = array(
                    "%JOB_SLUG%" => $value['jobSlug'],
                    "%FEATURED_LBL_CLASS%" => ($value['featured']=='y' && $value['featuredPayment']=='y') ? '' : 'hide',
                    "%JOB_TITLE%" => filtering(ucfirst($value['jobTitle'])),
                    "%CATEGORY%" => filtering(ucfirst($value['category_name'])),
                    "%SUB_CATEGORY%" => filtering(ucfirst($value['subcategory_name'])),
                    "%JOB_LVL%" => getJobExpLevel($value['expLevel']),
                    "%BUDGET%" => CURRENCY_SYMBOL.$value['budget'],
                    "%POSTED_TIME%" => getTime($value['jobPostDate']),
                    "%DESC%" => filtering($value['description']),
                    "%APPLICANTS%" => job_applicant($value['id']),
                    "%BIDDING_DEADLINE%" => date('dS F,Y',strtotime($value['biddingDeadline'])),
                    "%SKILLS%" => $this->skills($value['skills']),
                    "%SKILLS_CLASS%" => (!empty($skills_data_class))?"show":"hide",
                    "%CUSTOMER_NAME%" => filtering(ucfirst($value['firstName']))." ".filtering(ucfirst($value['lastName'])),
                    "%CUSTOMER_RATING%" => getAvgUserReview($value['posterId'],'Customer')*20,
                    "%LOCATION%" => filtering($value['location']),
                    "%SPENT_AMOUNT%" => customerSpentAmount($value['posterId']),
                    "%JOB_ID%" => $value['id'],
                    "%FAV_ALLOW_CLASS%" => (isset($this->sessUserId) && $this->sessUserId>0 && $this->sessUserType=='Freelancer') ? '' : 'hide',
                    "%FAV_CLASS%" => ($fav==0) ? 'fa fa-heart-o' : 'fa fa-heart',
                    "%CUST_SLUG%" => $value['userSlug'],
                    "%SAVE_CLASS%" => (isset($this->sessUserId) && $this->sessUserId>0)  ? (($this->sessUserType=='Freelancer') ? '' : 'hide') : '',
                    "%JOB_URL%"=>$url,
                    "%JOB_URL_TARGET%"=>$target,
                    "%JOB_CLASS%"=>$job_class,
                    "%JOB_MSG%" => $message
                );
                $data .= str_replace(array_keys($array), array_replace($array), $sub_content);
            }
        }
        else
        {
            //$data .= "No records found";
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
    public function loginUserCondition()
    {
        $userDetail = getUser($this->sessUserId);
        $b = explode(",", $userDetail['location']);
        $a = end($b);
        $country = strtoupper(trim($a));

        $country_name = $this->db->pdoQuery("select id from tbl_country where UPPER(country_name) = '".$country."' ")->result();
        $where = " and (FIND_IN_SET('".$country_name['id']."',j.bidsFromLocation) OR j.bidsFromLocation='') ";
        return $where;
    }
    public function skills($id)
    {
        $data = '';
        if(!empty($id)){
            $skills = $this->db->pdoQuery("select * from tbl_skills where id IN(".$id.") ")->results();
            foreach ($skills as $value)
            {
                $data .= "<li>".$value['skill_name']."</li>";
            }
        }
        return $data;
    }
    public function conditionWhere($search_array)
    {
        $where = '';

        if(!empty($_SESSION['pickgeeks_email'])){
            $where .= "and u.email !='".$_SESSION['pickgeeks_email']."'";
        }

        if(isset($search_array['jobType']) && $search_array['jobType']!='')
        {
            if($search_array['jobType'] == "featured")
            {
                $where .= " and (j.featured = 'y') ";
            }
            else
            {
                $where .= " and (DATEDIFF('".date('Y-m-d H:i:s')."',j.jobPostDate) <15) ";
            }
        }
        if(isset($search_array['location']) && $search_array['location']!='')
        {
            $where .= " and u.location LIKE '%".urldecode($search_array['location'])."%' ";
        }
        if(isset($search_array['category']) && $search_array['category']!='')
        {
            $where .= " and c.".l_values('category_name')." LIKE '%".urldecode($search_array['category'])."%' ";
        }
        if(isset($search_array['subcategory']) && $search_array['subcategory']!='')
        {
            $where .= " and s.".l_values('subcategory_name')." LIKE '%".urldecode($search_array['subcategory'])."%' ";
        }
        if(isset($search_array['skills']) && $search_array['skills']!='')
        {
            $where .= " and FIND_IN_SET('".base64_decode(urldecode($search_array['skills']))."',j.skills) ";
        }

        //if user is login then jobs are display as pewr their professional type.
        if(!empty($_SESSION["pickgeeks_userId"]) && empty($search_array['exp_lvl'])){
            $level = getTableValue("tbl_users","freelancerLvl",array("id"=>$_SESSION["pickgeeks_userId"]));
            if(!empty($level)){
                if($level == 'e'){
                    $search_array['exp_lvl']='pro';
                }else if($level == 'f'){
                    $search_array['exp_lvl']='beginner';
                }else if($level == 'i'){
                    $search_array['exp_lvl']='intermediate';
                }
            }
        }

        if(isset($search_array['exp_lvl']) && $search_array['exp_lvl']!='')
        {
            $exp_lvl='';
            if($search_array['exp_lvl'] == 'pro'){
                $exp_lvl='p';
            }
            else if($search_array['exp_lvl'] == 'beginner'){
                $exp_lvl='b';
            }
            else{
                $exp_lvl='i';
            }
            $where .= " and j.expLevel = '".$exp_lvl."' ";
        }
        if(isset($search_array['no_applicants']) && $search_array['no_applicants']!='')
        {
            $where .= " and (select COUNT(id) from tbl_job_bids As jb where jb.jobid = j.id) = '".$search_array['no_applicants']."' ";
        }
        if(isset($search_array['start_amount']) && $search_array['start_amount']!='')
        {
            $where .= " and (j.budget between '".$search_array['start_amount']."' and '".$search_array['end_amount']."') ";
        }
        if(isset($search_array['keyword']) && $search_array['keyword']!='')
        {
            $where .= " and j.jobTitle LIKE '%".$search_array['keyword']."%' ";
        }
        if(isset($search_array['startdate']) && $search_array['startdate']!='' && $search_array['enddate'] && $search_array['enddate']!='')
        {
            $where .= " and j.biddingDeadline between '".date('Y-m-d',strtotime($search_array['startdate']))."'  and '".date('Y-m-d',strtotime($search_array['enddate']))."' ";
        }
        else
        {
            if(isset($search_array['startdate']) && $search_array['startdate']!='')
            {
                $where .= " and j.biddingDeadline LIKE '%".date('Y-m-d',strtotime($search_array['startdate']))."%' ";
            }
            if(isset($search_array['enddate']) && $search_array['enddate']!='')
            {
                $where .= " and j.biddingDeadline LIKE '%".date('Y-m-d',strtotime($search_array['enddate']))."%' ";
            }
        }
        return $where;
    }
    public function dataSort($order='newest')
    {
        $sorting = '';
        if($order == "newest")
        {
            $sorting .= " ORDER BY j.id DESC";
        }
        if($order == "lTOhRating")
        {
            $sorting .= " ORDER BY r.customerStarRating ASC";
        }
        if($order == "hTOlRating")
        {
            $sorting .= " ORDER BY r.customerStarRating DESC";
        }
        if($order == "lTohInvest")
        {
            $sorting .= " ORDER BY (SELECT count(id) FROM `tbl_jobs` where j.posterId = `tbl_jobs`.posterId and isApproved='a' and isActive='y' group by posterId) ASC";
        }
        if($order == "hTolInvest")
        {
            $sorting .= " ORDER BY (SELECT count(id) FROM `tbl_jobs` where j.posterId = `tbl_jobs`.posterId and isApproved='a' and isActive='y' group by posterId) DESC";
        }
        return $sorting;
    }
    public function getCategory($cat='')
    {
        //echo $cat;exit;
        $where = '';
        $user_cat = getUserDetails('subCategoryList',$this->sessUserId);
        if($this->type == 'feed' && $user_cat!='')
        {
            $where .= " and s.id IN(".$user_cat.") ";
        }

        $category = $this->db->pdoQuery("select c.id As catId,c.".l_values('category_name')." as category_name from tbl_category As c
            LEFT JOIN tbl_subcategory As s ON s.maincat_id = c.id
            where c.isActive=? $where and c.isDelete=? and s.maincat_id IS NOT NULL group by s.maincat_id",array('y','n'))->results();
        $category_content = '<option value="">--'.SELECT_CATEGORY.'--</option>';

        foreach ($category as $key => $value) {
            //echo $cat ." ". $value['catId'].
            $select = ($cat == $value['catId']) ? 'selected' : '';
            $category_content .=  "<option value='".$value['category_name']."' ".$select.">".$value['category_name']."</option>";
        }
        return $category_content;
    }
    public function getSubcategory($main_cat,$sub_cat='')
    {
        $category = $this->db->select('tbl_subcategory',array('id',l_values('subcategory_name').' as subcategory_name'),array('isActive'=>'y','maincat_id'=>$main_cat,'isDelete'=>'n'))->results();
        $category_content = '<option value="">--'.SELECT_SUBCATEGORY.'--</option>';

        foreach ($category as $key => $value) {
            $select = ($sub_cat == $value['subcategory_name']) ? 'selected' : '';
            $category_content .=  "<option value='".$value['subcategory_name']."' ".$select." >".$value['subcategory_name']."</option>";
        }
        return $category_content;
    }
    public function skill_list($skills='')
    {

        $userSkill = getUserDetails('skillList',$this->sessUserId);
        $where = '';
        if($this->type == "feed" && $userSkill!='')
        {
            $where .= " and id IN(".$userSkill.")";
        }
        $query = $this->db->pdoQuery("select * from tbl_skills where isActive='y' and isApproved='y' and isDelete='n' ".$where)->results();
        $data = '<option value="">--'.SELECT_SKILLS.'--</option>';
        foreach ($query as $value) {
            $select = ($skills!='') ? ((base64_decode($skills) == $value['id']) ? 'selected' : '') : '';
            $data .= "<option value='".base64_encode($value['id'])."' ".$select.">".$value['skill_name']."</option>";
        }
        return $data;
    }
}
 ?>


