<?php
class Job extends Home
{

    public $page_name;
    public $page_title;
    public $meta_keyword;
    public $meta_desc;
    public $page_desc;
    public $isActive;
    public $data = array();

    public function __construct($module, $id = 0, $objPost = NULL, $searchArray = array(), $type = '')
    {
        global $db, $fields, $sessCataId;
        $this->db = $db;
        $this->data['id'] = $this->id = $id;
        $this->fields = $fields;
        $this->module = $module;
        $this->table = 'tbl_jobs';

        $this->type = ($this->id > 0 ? 'edit' : 'add');
        $this->searchArray = $searchArray;
        parent::__construct();
        if ($this->id > 0)
        {
            $qrySel = $this->db->pdoQuery("Select j.*,jc.category_name,jsc.subcategory_name,u.id as uId,CONCAT(u.firstName,' ',u.lastName) As username from tbl_jobs As j LEFT JOIN tbl_category As jc ON j.jobCategory = jc.id
                LEFT JOIN tbl_subcategory As jsc ON j.jobSubCategory = jsc.id
                LEFT JOIN tbl_users As u ON j.posterId = u.id
                where j.id='".$id."'
                ")->result();
            $fetchRes = $qrySel;

            $img1 = ($userRes["profileUrl"]!='')? $userRes["profileUrl"] : "no_user_image.png";
            $uimg = "<img src='".SITE_UPD."profile/".$img1."' alt='".$ufirstName.' '.$ulastName." profile' height='100px' width='100px'></img>";
            $review = (getAvgUserReview($fetchRes["uId"],"C")*20);

            $this->data['jobTitle'] = $this->jobTitle = $fetchRes['jobTitle'];
            $this->data['jobCategory'] = $this->jobCategory = $fetchRes['jobCategory'];
            $this->data['jobSubCategory'] = $this->jobSubCategory = $fetchRes['jobSubCategory'];
            $this->data['skills'] = $this->skills = $fetchRes['skills'];
            $this->data['description'] = $this->description = $fetchRes['description'];
            $this->data['jobPostDate'] = $this->jobPostDate = $fetchRes['jobPostDate'];
            $this->data['file'] = $this->file = $fetchRes['file'];
            $this->data['budget'] = $this->budget = $fetchRes['budget'];
            $this->data['expLevel'] = $this->expLevel = $fetchRes['expLevel'];
            $this->data['estimatedDuration'] = $this->estimatedDuration = $fetchRes['estimatedDuration'];
            $this->data['biddingDeadline'] = $this->biddingDeadline = $fetchRes['biddingDeadline'];
            $this->data['jobType'] = $this->jobType = $fetchRes['jobType'];
            $this->data['JobStatus'] = $this->JobStatus = $fetchRes['JobStatus'];
            $this->data['bidsFromLocation'] = $this->bidsFromLocation = $fetchRes['bidsFromLocation'];
            $this->data['addedQuestion'] = $this->addedQuestion = $fetchRes['addedQuestion'];
            $this->data['featured'] = $this->featured = $fetchRes['featured'];
            $this->data['hideFrmSearch'] = $this->hideFrmSearch = $fetchRes['hideFrmSearch'];
            $this->data['posterId'] = $this->posterId = $fetchRes['posterId'];
            $this->data['isApproved'] = $this->isApproved = $fetchRes['isApproved'];
            $this->data['reportStatus'] = $this->reportStatus = $fetchRes['reportStatus'];
            $this->data['category_name'] = $this->category_name = $fetchRes['category_name'];
            $this->data['subcategory_name'] = $this->subcategory_name = $fetchRes['subcategory_name'];
            $this->data['userImage'] = $this->userImage = $uimg;
            $this->data['username'] = $this->username = $fetchRes['username'];
            $this->data['review'] = $this->review = $review;
            $this->data['isActive'] = $this->isActive = $fetchRes['isActive'];            
        }
        else
        {
            $this->data['jobTitle'] = $this->jobTitle = '';
            $this->data['jobCategory'] = $this->jobCategory = '';
            $this->data['jobSubCategory'] = $this->jobSubCategory = '';
            $this->data['skills'] = $this->skills = '';
            $this->data['description'] = $this->description = '';
            $this->data['jobPostDate'] = $this->jobPostDate = '';
            $this->data['file'] = $this->file = '';
            $this->data['budget'] = $this->budget = '';
            $this->data['expLevel'] = $this->expLevel = '';
            $this->data['estimatedDuration'] = $this->estimatedDuration = '';
            $this->data['biddingDeadline'] = $this->biddingDeadline = '';
            $this->data['jobType'] = $this->jobType = '';
            $this->data['JobStatus'] = $this->JobStatus = '';
            $this->data['bidsFromLocation'] = $this->bidsFromLocation = '';
            $this->data['addedQuestion'] = $this->addedQuestion = '';
            $this->data['featured'] = $this->featured = '';
            $this->data['hideFrmSearch'] = $this->hideFrmSearch = '';
            $this->data['posterId'] = $this->posterId = '';
            $this->data['isApproved'] = $this->isApproved = '';
            $this->data['reportStatus'] = $this->reportStatus = '';
            $this->data['category_name'] = $this->category_name = '';
            $this->data['subcategory_name'] = $this->subcategory_name = '';
            $this->data['userId'] = $this->userId = '';
            $this->data['username'] = $this->username = '';
            $this->data['userImage'] = $this->userImage = '';
            $this->data['review'] = $this->review = '';
            $this->data['isActive'] = $this->isActive = 'y';
        }
        switch ($type)
        {
            case 'add' :
            {
                $this->data['content'] = $this->getForm();
                break;
            }
            case 'edit' :
            {
                $this->data['content'] = $this->getForm();
                break;
            }
            case 'view' :
            {
                $this->data['content'] = $this->viewForm();
                break;
            }
            case 'undo' : {
                $this->data['content'] = json_encode($this->dataGrid());
                break;
            }
            case 'delete' :
            {

                $this->data['content'] = json_encode($this->dataGrid());
                break;
            }
            case 'datagrid' :
            {
                $this->data['content'] = json_encode($this->dataGrid());
                break;
            }
        }
    }

    public function viewForm()
    {
        
        $exp = ($this->expLevel=='f') ? 'Fresher' : 'Experinced';
        $jobType = ($this->jobType=='pu') ? 'Public' : 'Private';
        $skills_list = "";
        $skill_detail = $this->db->pdoQuery("select * from tbl_skills where id IN(".$this->skills.")")->results();
        foreach ($skill_detail as $value) {
            $skills_list .= $value['skill_name'].",";
        }

        $question_list = "";$i=1;
        if($this->addedQuestion == ''){
            $question_list = 'N/A';
        }else{
            $question_detail = $this->db->pdoQuery("select * from tbl_question where id IN(".$this->addedQuestion.")")->results();
            foreach ($question_detail as $value) {
                $question_list .= $i.". ".$value['question']."<br>";
                $i++;
            }
        }
        $userRes = $this->db->pdoQuery("select * from tbl_users where id=8",array($this->$userId))->result();
        $unm = ($this->username =='') ? 'Admin':$this->username;
        $country = get_country_list_for_view_jobs($this->bidsFromLocation);
        
        $content =
            $this->displayBox(array("label" => "Job Title &nbsp;:", "value" => filtering($this->jobTitle))).
            $this->displayBox(array("label" => "Job Category &nbsp;:", "value" => filtering($this->category_name))).
            $this->displayBox(array("label" => "Job Subcategory &nbsp;:", "value" => filtering($this->subcategory_name))).
            $this->displayBox(array("label" => "Job Skills &nbsp;:", "value" => filtering(trim($skills_list,",")))).
            $this->displayBox(array("label" => "Job Description &nbsp;:", "value" => filtering($this->description))).
            $this->displayBox(array("label" => "Job Posted date &nbsp;:", "value" => filtering(date('d-M-Y H:i A',strtotime($this->jobPostDate))))).
            $this->displayBox(array("label" => "Job Budget &nbsp;:", "value" => CURRENCY_SYMBOL.filtering($this->budget))).
            $this->displayBox(array("label" => "Job Experience Level &nbsp;:", "value" => $exp)).
            $this->displayBox(array("label" => "Estimated Duration &nbsp;:", "value" => filtering($this->estimatedDuration))).
            $this->displayBox(array("label" => "Bidding Deadline &nbsp;:", "value" => filtering(date('d-M-Y',strtotime($this->biddingDeadline))))).
            $this->displayBox(array("label" => "Job Type &nbsp;:", "value" => $jobType)).
            $this->displayBox(array("label" => "Bid From Location &nbsp;:", "value" => filtering($country))).
            $this->displayBox(array("label" => "Added Question &nbsp;:", "value" => trim($question_list,","))).
            $this->displayBox(array("label" => "Featured &nbsp;:", "value" => ($this->featured == 'y') ? 'Yes':'No')).
            $this->displayBox(array("label" => "Hide from search &nbsp;:", "value" => ($this->hideFrmSearch=='y')? 'Yes':'No')).
            $this->displayBox(array("label" => "Who Has posted a job &nbsp;:", "value" => filtering($unm))).
            $this->displayBox(array("label" => "posted user image &nbsp;:", "value" => filtering($uimg))).
            $this->displayBox(array("label" => "posted user ratings &nbsp;:", "value" => filtering($this->review))).
            $this->displayBox(array("label" => "posted user spend amount &nbsp;:", "value" => filtering($unm))).
            $this->displayBox(array("label" => "posted user location &nbsp;:", "value" => filtering($unm))).
            $this->displayBox(array("label" => "Approval status &nbsp;:", "value" => filtering($unm)));
        return $content;
    }

    public function getForm()
    {

        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/form-sd.skd");
        $main_content = $main_content->compile();
       /* $hideFrmSearch_y = ($this->hideFrmSearch == 'y' ? 'checked' : '');
        $hideFrmSearch_n = ($this->hideFrmSearch != 'y' ? 'checked' : '');

        $public = ($this->jobType == 'pu' ? 'checked' : '');
        $private = ($this->jobType == 'pr' ? 'checked' : '');

        $featured_y = ($this->featured == 'y' ? 'checked' : '');
        $featured_n = ($this->featured == 'n' ? 'checked' : '');

        $hideFrmSearch_y = ($this->hideFrmSearch == 'y' ? 'checked' : '');
        $hideFrmSearch_n = ($this->hideFrmSearch == 'n' ? 'checked' : '');

        $fresher_lvl = ($this->expLevel == 'b' ? 'checked' : '');
        $exp_lvl = ($this->expLevel == 'p' ? 'checked' : '');
*/
        $category = $subcategory = $skills = '';

        /*category list*/
        $category_detail = $this->db->pdoQuery("select * from tbl_category where (category_type='j' OR category_type='b') and isActive='y' and isDelete='n'")->results();
        foreach ($category_detail as $value) {
            $cat_sel = ($this->jobCategory == $value['id']) ? 'selected' : '';
            $category .="<option value='".$value['id']."' ".$cat_sel.">".$value['category_name']."</option>";
        }
        /*subcategory list*/
        $subcategory_detail = $this->db->pdoQuery("select * from tbl_subcategory where maincat_id='".$this->jobCategory."' and isActive='y' and isDelete='n' ")->results();
        foreach ($subcategory_detail as $value1)
        {
            $sub_cat_sel = ($this->jobSubCategory == $value1['id']) ? 'selected' : '';
            $subcategory .="<option value='".$value1['id']."' ".$sub_cat_sel.">".$value1['subcategory_name']."</option>";
        }

        /*skills list*/

        $skill_detail = $this->db->pdoQuery("select * from tbl_skills where isActive='y' and isDelete='n' ")->results();
        foreach ($skill_detail as $value)
        {
            $string = $this->skills;$selected='';
            $what_to_find = $value['id'];
            if (preg_match('/\b' . $what_to_find . '\b/', $string)) {
               $selected = "selected";
            }
            $skills .="<option value='".$value['id']."' ".$selected.">".$value['skill_name']."</option>";
        }
       
        $question_list = '';
        $fields = array(
            "%BYDEFAULT%" => ($this->type == 'add') ? 'checked': '',
            "%JOB_TITLE%" => filtering($this->jobTitle),
            "%CATEGORY%" => $category,
            "%SUB_CATEGORY%" => $subcategory,
            "%SKILLS%" => $skills,
            "%DESC%" => $this->description,
            "%EST1%" => ($this->estimatedDuration == C_ONE_DAY_OR_LESS_LBL) ? 'selected' : '',
            "%EST2%" => ($this->estimatedDuration == C_LESS_THEN_ONE_WEEK) ? 'selected' : '',
            "%EST3%" => ($this->estimatedDuration == C_ONE_TO_TWO_WEEK_LBL) ? 'selected' : '',
            "%EST4%" => ($this->estimatedDuration == C_THREE_TO_FOUR_WEEK_LBL) ? 'selected' : '',
            "%EST5%" => ($this->estimatedDuration == C_ONE_TO_SIX_MONTH_LBL) ? 'selected' : '',
            "%EST6%" => ($this->estimatedDuration == C_MORE_THEN_SIX_MONTH_LBL) ? 'selected' : '',
            "%EST7%" => ($this->estimatedDuration == C_ONGOING_LBL) ? 'selected' : '',
            "%EST8%" => ($this->estimatedDuration == C_NOT_SURE_LBL) ? 'selected' : '',
            "%QUESTIONS%" => $this->questions_list(),
            "%TYPE%" => filtering($this->type),
            "%ID%" => filtering($this->id, 'input', 'int'),
            "%JOB_FILES%" => $this->get_job_files($this->id),
            "%COUNTRY%" => $this->getCountries()
        );

        $content = str_replace(array_keys($fields), array_values($fields), $main_content);
        return sanitize_output($content);
    }

    public function getCountries()
    {
        $country = $this->db->select('tbl_country',array('id','country_name'))->results();
        $countries = '';

        foreach ($country as $key => $value) {
            $countries .= "<option value='".$value['id']."' >".$value['country_name']."</option>";
        }
        return $countries;
    }

    public function get_job_files()
    {

    }
    public function questions_list()
    {
        $questions= '';
        $question = $this->db->pdoQuery("select * from tbl_question where isActive='y'")->results();
        foreach ($question as $value)
        {
            $checked = '';
            $string = $this->addedQuestion;
            $what_to_find = $value['id'];
            if (preg_match('/\b' . $what_to_find . '\b/', $string)) {
               $checked = "checked";
            }
            $questions .= "<div class='row'><input type='checkbox' name='question[]' class='chkQue' value='".$value['id']."' ".$checked." > ".$value['question']."</div>";
        }
        return $questions;
    }

    public function dataGrid()
    {
        $content = $operation = $whereCond = $totalRow = NULL;
        $result = $tmp_rows = $row_data = array();
        extract($this->searchArray);
        $chr = str_replace(array('_', '%'), array('\_', '\%'), $chr);
        $whereCond = "1 = ? ";
        $wArray = array('1');

        if (!empty($chr))
        {
                $whereCond .= " AND (j.jobTitle LIKE ?) ";
                $wArray[] = "%$chr%";
        }

        if (isset($sort))
            $sorting = $sort . ' ' . $order;
        else
            $sorting = 'j.isApproved DESC, j.jobPostDate DESC';
        if (isset($filterCategory) && $filterCategory != ''){
             if ($whereCond) {
                $whereCond .= " AND ";
            } else {
                $whereCond .= " WHERE ";
            }
            $whereCond .= " j.jobCategory = '$filterCategory'";
        } if (isset($filterSubCategory) && $filterSubCategory != ''){
                if ($whereCond) {
                $whereCond .= " AND ";
            } else {
                $whereCond .= " WHERE ";
            }
            
            $whereCond .= " j.jobSubCategory = '$filterSubCategory'";


             
        } elseif (isset($filterSkill) && $filterSkill != ''){
             if ($whereCond) {
                $whereCond .= " AND ";
            } else {
                $whereCond .= " WHERE ";
            }
            $whereCond .= " FIND_IN_SET('$filterSkill',j.skills)";
        } elseif (isset($filterJobStatus) && $filterJobStatus != ''){
             if ($whereCond) {
                $whereCond .= " AND ";
            } else {
                $whereCond .= " WHERE ";
            }
            $whereCond .= "  j.jobStatus = '$filterJobStatus'";
        } elseif (isset($filterFeatured) && $filterFeatured != ''){
             if ($whereCond) {
                $whereCond .= " AND ";
            } else {
                $whereCond .= " WHERE ";
            }
            $whereCond .= "  j.featured = '$filterFeatured'";
        }
//         else if (isset($filterSubCategory) && $filterSubCategory != '' && $filterCategory != ''){
//             if ($whereCond) {
//                 $whereCond .= " AND ";
//             } else {
//                 $whereCond .= " WHERE ";
//             }
//             $whereCond .= " s.servicesSubCategory = '$filterSubCategory' AND s.servicesCategory = '$filterCategory'";
//         }


        $qrySel = $this->db->pdoQuery("SELECT j.isApproved,j.isActive,j.isDelete,j.jobTitle,jc.category_name,j.budget,j.jobType,j.jobStatus,CONCAT(u.firstName,' ',u.lastName) As username,j.id As jobID FROM tbl_jobs As j
            LEFT JOIN tbl_category As jc ON jc.id = j.jobCategory
            LEFT JOIN tbl_users As u ON u.id = j.posterId
            WHERE $whereCond ORDER BY $sorting LIMIT $offset , $rows",$wArray)->results();

        $totalRow = $this->db->pdoQuery("SELECT j.isActive,j.isDelete,j.jobTitle,jc.category_name,j.budget,j.jobType,CONCAT(u.firstName,' ',u.lastName) As username,j.id As jobID FROM tbl_jobs As j
            LEFT JOIN tbl_category As jc ON jc.id = j.jobCategory
            LEFT JOIN tbl_users As u ON u.id = j.posterId
            WHERE $whereCond ORDER BY $sorting ",$wArray)->affectedRows();

        foreach ($qrySel as $fetchRes)
        {
            $operation = '';
           
            /*$switch = (in_array('status', $this->Permission)) ? $this->toggel_switch(array("action" => "ajax." . $this->module . ".php?id=" . $fetchRes['jobID'] . "", "check" => $status)) : '';*/
            $jdelete = checkJobDelete($fetchRes['jobID']);
            $delete_status = ($fetchRes['isDelete']=='y') ? "<span class='label  label-warning  label-large'>&nbsp;Job deleted</span>" : '';
            if($fetchRes['isApproved'] == 'p'){
                $operation = (in_array('status', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=approveStatus&id=" . $fetchRes['jobID'] . "",  "extraAtt" => "title = 'Approve'","class" => "btn default black btn-approve", "value" => '<i class="fa fa-check"></i>', "title"=>"Approve")) : '' ;

                $operation .="<button type='button' class='btn default red reject' data-id='".$fetchRes['jobID']."'><i class='fa fa-times' aria-hidden='true'></i></button>";
                $switch= "<label class='label label-info'>Pending</label>";
                 $operation .= (in_array('edit', $this->Permission)) ? $this->operation(array("href" => "ajax." . $this->module . ".php?action=edit&id=" . $fetchRes['jobID'] . "", "extraAtt" => "title = 'Edit'", "class" => "btn default black btnEdit", "value" => '<i class="fa fa-edit"></i>')) : '';
//                                             $operation .=(in_array('view', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=view&id=" . $fetchRes['jobID'] . "", "extraAtt" => "title = 'View'", "class" => "btn default blue btn-viewbtn", "value" => '<i class="fa fa-laptop"></i>')) : '';

            }else if($fetchRes['isApproved'] == 'a'){
                $status = ($fetchRes['isActive'] == "y") ? "checked" : "";
                $switch = (in_array('status', $this->Permission)) ? $this->toggel_switch(array("action" => "ajax." . $this->module . ".php?id=" . $fetchRes['jobID'] . "", "check" => $status)) : '';
                if($fetchRes['isDelete'] != 'y'){

                $operation .=(in_array('delete', $this->Permission)) ?$this->operation(array("href" =>  "ajax." . $this->module . ".php?action=delete&id=" . $fetchRes['jobID'] . "", "extraAtt" => "title = 'Delete' data-id='".$fetchRes['jobID']."'", "class" => "btn default red btn-delete", "value" => '<i class="fa fa-trash-o"></i>')) : '';
                $operation .= (in_array('edit', $this->Permission)) ? $this->operation(array("href" => "ajax." . $this->module . ".php?action=edit&id=" . $fetchRes['jobID'] . "", "extraAtt" => "title = 'Edit'", "class" => "btn default black btnEdit", "value" => '<i class="fa fa-edit"></i>')) : '';
                }

            }else if($fetchRes['isApproved'] == 'r') {
                if($fetchRes['isDelete'] != 'y'){
                  
                 $operation .=(in_array('delete', $this->Permission)) ?$this->operation(array("href" =>  "ajax." . $this->module . ".php?action=delete&id=" . $fetchRes['jobID'] . "", "extraAtt" => "title = 'Delete' data-id='".$fetchRes['jobID']."'", "class" => "btn default red btn-delete", "value" => '<i class="fa fa-trash-o"></i>')) : '';
                }
                $switch= "<label class='label label-danger'>Rejected</label>";
            }
            if($fetchRes['isDelete'] == 'y'){
//                 $operation .=(in_array('undo', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=undo&id=" . $fetchRes['jobID'] . "","extraAtt" => "title = 'Undo'", "class" => "btn default btn-info btn-undo", "value" => '<i class="fa fa-reply"></i>')) : '';
                 $operation .=(in_array('delete', $this->Permission)) ?$this->operation(array("href" =>  "ajax." . $this->module . ".php?action=perDelete&id=" . $fetchRes['jobID'] . "", "extraAtt" => "title = 'Permanent Delete' data-id='".$fetchRes['jobID']."'", "class" => "btn default red btn-perdelete", "value" => '<i class="fa fa-trash-o"></i>')) : '';
                // echo "<br>".$cnt;
            }
           /* if($jdelete == '1' && $fetchRes['isDelete']=='n'){
                $operation .=(in_array('delete', $this->Permission)) ?$this->operation(array("href" => "javascript:void(0);", "extraAtt" => "title = 'Delete' data-id='".$fetchRes['jobID']."'", "class" => "btn default red jb_delete", "value" => '<i class="fa fa-trash-o"></i>')) : '';
            }*/                            $operation .=(in_array('view', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=view&id=" . $fetchRes['jobID'] . "", "extraAtt" => "title = 'View'", "class" => "btn default blue btn-viewbtn", "value" => '<i class="fa fa-laptop"></i>')) : '';



            $jobID=$fetchRes['jobID'];
            $poster_name = ($fetchRes['username'] == '') ? 'admin' : $fetchRes['username'];

            $jobStatusArray = array(
                "p"=>"Pending",
                "c"=>"Closed",
                "h"=>"Hired",
                "ip"=>"In Progress",
                "ud"=>"Under Dispute",
                "dsp"=>"Dispute Solved In Progress",
                "dsc"=>"Dispute Solved and Closed",
                "dsCo"=>"DisputeSsolved and Completed",
                "co"=>"Completed",
            );
            $final_array = array(
                $jobID,
                filtering($fetchRes['jobTitle']).'<br>'.$delete_status,
                filtering($fetchRes["category_name"]),
                ucfirst($poster_name),
                ($fetchRes["budget"]),
                ($fetchRes["jobType"]=='pu') ? 'Public':'Private',
                $jobStatusArray[$fetchRes["jobStatus"]]

            );
            if (in_array('status', $this->Permission))
            {
                $final_array = array_merge($final_array, array($switch));
            }
            if (in_array('edit', $this->Permission) || in_array('delete', $this->Permission) || in_array('view', $this->Permission))
            {
                $final_array = array_merge($final_array, array($operation));
            }
            $row_data[] = $final_array;
        }

        $result["sEcho"] = $sEcho;
        $result["iTotalRecords"] = (int) $totalRow;
        $result["iTotalDisplayRecords"] = (int) $totalRow;
        $result["aaData"] = $row_data;
        return $result;
    }

    public function toggel_switch($text)
    {
        $disabledSwitch=NULL;
        $text['action'] = isset($text['action']) ? $text['action'] : 'Enter Action Here: ';
        $text['check'] = isset($text['check']) ? $text['check'] : '';
        $text['name'] = isset($text['name']) ? $text['name'] : '';
        $text['class'] = isset($text['class']) ? '' . trim($text['class']) : '';
        $text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';
        if(!empty($text['deletecat'])){
             if(empty($text['check']) && $text['deletecat']=='y'){
                                $disabledSwitch='disabled';
                            }
        }
        if(!empty($text['homecat'])){
             if(empty($text['check']) && $text['homecat']=='y'){
                                $disabledSwitch='disabled';
                            }
        }

        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/switch-sd.skd');
        $main_content = $main_content->compile();
        $fields = array("%NAME%", "%CLASS%", "%ACTION%", "%EXTRA%", "%CHECK%","%DISABLECAT%");
        $fields_replace = array($text['name'], $text['class'], $text['action'], $text['extraAtt'], $text['check'],$disabledSwitch);
        return str_replace($fields, $fields_replace, $main_content);
    }

    public function operation($text)
    {

        $text['href'] = isset($text['href']) ? $text['href'] : 'Enter Link Here: ';
        $text['value'] = isset($text['value']) ? $text['value'] : '';
        $text['name'] = isset($text['name']) ? $text['name'] : '';
        $text['class'] = isset($text['class']) ? '' . trim($text['class']) : '';
        $text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/operation-sd.skd');
        $main_content = $main_content->compile();
        $fields = array("%HREF%", "%CLASS%", "%VALUE%", "%EXTRA%");
        $fields_replace = array($text['href'], $text['class'], $text['value'], $text['extraAtt']);
        return str_replace($fields, $fields_replace, $main_content);
    }

    public function displaybox($text)
    {

        $text['label'] = isset($text['label']) ? $text['label'] : 'Enter Text Here: ';
        $text['value'] = isset($text['value']) ? $text['value'] : '';
        $text['name'] = isset($text['name']) ? $text['name'] : '';
        $text['class'] = isset($text['class']) ? 'form-control-static ' . trim($text['class']) : 'form-control-static';
        $text['onlyField'] = isset($text['onlyField']) ? $text['onlyField'] : false;
        $text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';

        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/displaybox.skd');
        $main_content = $main_content->compile();
        $fields = array("%LABEL%", "%CLASS%", "%VALUE%");
        $fields_replace = array($text['label'], $text['class'], $text['value']);
        return str_replace($fields, $fields_replace, $main_content);
    }

     public function getPageContent() {
       
        $final_result = NULL;
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/" . $this->module . ".skd");
        $main_content->breadcrumb = $this->getBreadcrumb();
        $final_result = $main_content->compile();
        $fields = array(
                "%CATEGORY_OPTIONS%" => $this->getCategory(),
                "%SUB_CATEGORY%" => $this->getSubCategory(),
                "%JOB_SKILL%" => $this->getSkills()
        );
        $final_result = str_replace(array_keys($fields), array_values($fields), $final_result);
        return $final_result;
    }
    public function getCategory(){
        $content='';
        $company_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/category_option-sd.skd");
        $company_content = $company_content->compile();
        $qryCompany = $this->db->pdoQuery("SELECT * from tbl_category WHERE isActive='y' and isDelete='n'")->results();
              foreach ($qryCompany as $key => $value) {
                    $fields = array(
                        "%CATEGORY%" => filtering(ucfirst($value['category_name'])),
                        "%CAT_ID%" => $value['id'],
                     );
            $content .= str_replace(array_keys($fields), array_values($fields), $company_content);
          }
        return $content;
    }
    public function getSubCategory(){
        $content='';
        $company_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/subcat_option-sd.skd");
        $company_content = $company_content->compile();
        $qryCompany = $this->db->pdoQuery("SELECT * from tbl_subcategory WHERE isActive='y' and isDelete='n'")->results();
              foreach ($qryCompany as $key => $value) {
                    $fields = array(
                        "%SUB_CAT%" => filtering(ucfirst($value['subcategory_name'])),
                        "%SUB_CAT_ID%" => $value['id'],
                     );
            $content .= str_replace(array_keys($fields), array_values($fields), $company_content);
          }
        return $content;
    }public function getSkills(){
        $content='';
        $company_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/skill_option-sd.skd");
        $company_content = $company_content->compile();
        $qryCompany = $this->db->pdoQuery("SELECT * from tbl_skills WHERE isActive='y'")->results();
              foreach ($qryCompany as $key => $value) {
                    $fields = array(
                        "%SKILLS%" => filtering(ucfirst($value['skill_name'])),
                        "%SKILL_ID%" => $value['id'],
                     );
            $content .= str_replace(array_keys($fields), array_values($fields), $company_content);
          }
        return $content;
    }
    public function contentSubmit($data,$Permission){
        $response = array();
        $response['status'] = false;
        $isExist="";
        extract($data);
        $objPost = new stdClass();
        $objPost->id = isset($id) ? $id : '';
        $objPost->jobTitle = isset($jobTitle) ? $jobTitle : '';  
        $objPost->jobCategory = isset($jobCategory) ? $jobCategory : '';
        $objPost->jobSubCategory = isset($jobSubCategory) ? $jobSubCategory : '';
        $array = array_filter($skills);
        $objPost->skills =implode(',',(($array)));
        $objPost->description = isset($description) ? $description : '';
        $objPost->estimatedDuration = isset($estimatedDuration) ? $estimatedDuration : '';
        $arrayQue = array_filter($addedQuestion);
        $objPost->addedQuestion =implode(',',(($arrayQue)));
        if ($type == 'edit' && $id > 0) 
        {
            if (in_array('edit', $Permission))
            {
               
                    $objPostArray = (array) $objPost;
                    $this->db->update($this->table, $objPostArray, array("id" => $id));

                    $response['status'] = true;
                    $response['success'] = "Job has been updated successfully";
                    $activity_array = array("id"=>$id, "module"=>$this->module, "activity"=>'edit');
                    add_admin_activity($activity_array);
                    echo json_encode($response);
                    exit;
            }

        } 
        else 
        {
            $response['error'] = "You don't have permission";
            echo json_encode($response);
            exit;
        }
    }

}











