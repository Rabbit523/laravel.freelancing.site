<?php
class jobRequest extends Home 
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
            //$qrySel = $this->db->select($this->table, "*", array("id" => $id))->result();
            $qrySel = $this->db->pdoQuery("Select j.*,jc.category_name,jsc.subcategory_name,CONCAT(u.firstName,' ',u.lastName) As username from tbl_jobs As j LEFT JOIN tbl_category As jc ON j.jobCategory = jc.id
                LEFT JOIN tbl_subcategory As jsc ON j.jobSubCategory = jsc.id
                LEFT JOIN tbl_users As u ON j.posterId = u.id
                where j.id='".$id."'
                ")->result();

            $fetchRes = $qrySel;
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
            $this->data['bidsFromLocation'] = $this->bidsFromLocation = $fetchRes['bidsFromLocation'];
            $this->data['addedQuestion'] = $this->addedQuestion = $fetchRes['addedQuestion'];
            $this->data['featured'] = $this->featured = $fetchRes['featured'];
            $this->data['hideFrmSearch'] = $this->hideFrmSearch = $fetchRes['hideFrmSearch'];
            $this->data['posterId'] = $this->posterId = $fetchRes['posterId'];
            $this->data['isApproved'] = $this->isApproved = $fetchRes['isApproved'];
            $this->data['reportStatus'] = $this->reportStatus = $fetchRes['reportStatus'];
            $this->data['category_name'] = $this->category_name = $fetchRes['category_name'];
            $this->data['subcategory_name'] = $this->subcategory_name = $fetchRes['subcategory_name'];
            $this->data['username'] = $this->username = $fetchRes['username'];
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
            $this->data['bidsFromLocation'] = $this->bidsFromLocation = '';
            $this->data['addedQuestion'] = $this->addedQuestion = '';
            $this->data['featured'] = $this->featured = '';
            $this->data['hideFrmSearch'] = $this->hideFrmSearch = '';
            $this->data['posterId'] = $this->posterId = '';
            $this->data['isApproved'] = $this->isApproved = '';
            $this->data['reportStatus'] = $this->reportStatus = '';
            $this->data['category_name'] = $this->category_name = '';
            $this->data['subcategory_name'] = $this->subcategory_name = '';
            $this->data['username'] = $this->username = '';
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
        if($this->skills != '')
        {
            $skill_detail = $this->db->pdoQuery("select * from tbl_skills where id IN(".$this->skills.")")->results();
            foreach ($skill_detail as $value) {
                $skills_list .= $value['skill_name'].",";
            }
        }
        else
        {
            $skills_list .= "-";
        }

        $question_list = "";$i=1;
        if($this->addedQuestion!='')
        {
            $question_detail = $this->db->pdoQuery("select * from tbl_question where id IN(".$this->addedQuestion.")")->results();
            foreach ($question_detail as $value) {
                $question_list .= $i.". ".$value['question']."<br>";
                $i++;
            }
        }
        else
        {
            $question_list .="-";
        }

        $content = 
            $this->displayBox(array("label" => "Job Title &nbsp;:", "value" => filtering($this->jobTitle))).
            $this->displayBox(array("label" => "Job Category &nbsp;:", "value" => filtering($this->category_name))).
            $this->displayBox(array("label" => "Job Subcategory &nbsp;:", "value" => filtering($this->subcategory_name))).
            $this->displayBox(array("label" => "Job Skills &nbsp;:", "value" => filtering(trim($skills_list,",")))).
            $this->displayBox(array("label" => "Job Description &nbsp;:", "value" => filtering($this->description))).
            $this->displayBox(array("label" => "Job Posted date &nbsp;:", "value" => filtering(date('d-m-Y H:i:s',strtotime($this->jobPostDate))))).
            $this->displayBox(array("label" => "Job Budget &nbsp;:", "value" => CURRENCY_SYMBOL.filtering($this->budget))).
            $this->displayBox(array("label" => "Job Experience Level &nbsp;:", "value" => $exp)).
            $this->displayBox(array("label" => "Estimated Duration &nbsp;:", "value" => filtering($this->estimatedDuration))).
            $this->displayBox(array("label" => "Bidding Deadline &nbsp;:", "value" => filtering(date('d-m-Y H:i:s',strtotime($this->biddingDeadline))))).
            $this->displayBox(array("label" => "Job Type &nbsp;:", "value" => $jobType)).
            $this->displayBox(array("label" => "Bid From Location &nbsp;:", "value" => filtering($this->bidsFromLocation))).
            $this->displayBox(array("label" => "Added Question &nbsp;:", "value" => trim($question_list,","))).
            $this->displayBox(array("label" => "Featured &nbsp;:", "value" => ($this->featured == 'y') ? 'Yes':'No')).
            $this->displayBox(array("label" => "Hide from search &nbsp;:", "value" => ($this->hideFrmSearch=='y')? 'Yes':'No')).
            $this->displayBox(array("label" => "Job Poster Name &nbsp;:", "value" => ($this->username=='') ? 'Admin' : filtering($this->username)))
        ;           
        return $content;
    }

    public function getForm() 
    {

        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/form-sd.skd");
        $main_content = $main_content->compile();
        $hideFrmSearch_y = ($this->hideFrmSearch == 'y' ? 'checked' : '');
        $hideFrmSearch_n = ($this->hideFrmSearch != 'y' ? 'checked' : '');

        $public = ($this->jobType == 'pu' ? 'checked' : '');
        $private = ($this->jobType == 'pr' ? 'checked' : '');

        $featured_y = ($this->featured == 'y' ? 'checked' : '');
        $featured_n = ($this->featured == 'n' ? 'checked' : '');

        $hideFrmSearch_y = ($this->hideFrmSearch == 'y' ? 'checked' : '');
        $hideFrmSearch_n = ($this->hideFrmSearch == 'n' ? 'checked' : '');

        $fresher_lvl = ($this->expLevel == 'f' ? 'checked' : '');
        $exp_lvl = ($this->expLevel == 'e' ? 'checked' : '');

        $category = $subcategory = $skills = '';

        /*category list*/
        $category_detail = $this->db->pdoQuery("select * from tbl_category where category_type='j' OR category_type='b' and isActive='y' and isDelete='n'")->results();
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
            "%OLD_FILE%" => ($this->file != '') ? $this->file : '',
            "%JOB_TITLE%" => filtering($this->jobTitle),
            "%CATEGORY%" => $category,
            "%SUB_CATEGORY%" => $subcategory,
            "%SKILLS%" => $skills,
            "%DESC%" => $this->description,
            "%FILE%" => $this->file,
            "%BUDGET%" => $this->budget,            
            "%ESTIMATED_DURATION%" => $this->estimatedDuration,
            "%BID_DEADLINE%" => $this->biddingDeadline,
            "%PUBLIC%"=>$public,
            "%PRIVATE%"=>$private,
            "%BID_FRM_LOCATION%" => $this->bidsFromLocation,
            "%FEATURED_Y%" => $featured_y,
            "%FEATURED_N%" => $featured_n,
            "%HIDE_FRM_SEARCH_Y%" => $hideFrmSearch_y,
            "%HIDE_FRM_SEARCH_N%" => $hideFrmSearch_n,
            "%QUESTIONS%" => $this->questions_list(),
            "%FRESHER%" => $fresher_lvl,
            "%EXP%" => $exp_lvl,
            "%TYPE%" => filtering($this->type),
            "%ID%" => filtering($this->id, 'input', 'int')
        );

        $content = str_replace(array_keys($fields), array_values($fields), $main_content);
        return sanitize_output($content);
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
            $questions .= "<div class='row'><input type='checkbox' name='question[]' value='".$value['id']."' ".$checked." > ".$value['question']."</div>";
        }
        return $questions;
    }

    public function dataGrid() 
    {
        $content = $operation = $whereCond = $totalRow = NULL;
        $result = $tmp_rows = $row_data = array();
        extract($this->searchArray);
        $chr = str_replace(array('_', '%'), array('\_', '\%'), $chr);
       $whereCond = "(isApproved = ? OR isApproved = ?)";
       $wArray = array('p','r');
        
        if (!empty($chr)) 
        {
                $whereCond .= " AND (j.jobTitle LIKE ? OR jc.category_name LIKE ? OR u.username LIKE ? OR j.budget LIKE ?) ";
                $wArray[] = "%$chr%";
                $wArray[] = "%$chr%";
                $wArray[] = "%$chr%";
                $wArray[] = "%$chr%";
        }

        if(isset($category) && $category!='')
        {
            $whereCond.= ' and j.jobCategory ="'.$category.'"';
        }
        if(isset($subcategory) && $subcategory!='')
        {
            $whereCond.= ' and j.jobSubCategory ="'.$subcategory.'"';
        }
        if(isset($skills) && $skills!='')
        {
            $whereCond.= ' and FIND_IN_SET( "'.$skills.'" , j.skills) ';
        }

        if(isset($filtering_type) && $filtering_type!='')
        {
            if($filtering_type=='f')
            {
                $whereCond .= ' and j.featured="y" ';
            }
            else
            {
                $whereCond.= ' and j.isApproved ="'.$filtering_type.'"';
            }
        }

        if (isset($sort))
            $sorting = $sort . ' ' . $order;
        else
            $sorting = 'j.id DESC';

        $qrySel = $this->db->pdoQuery("SELECT j.isApproved,j.isActive,j.jobTitle,jc.category_name,j.budget,j.jobType,CONCAT(u.firstName,' ',u.lastName) As username,j.id As jobID FROM tbl_jobs As j
            LEFT JOIN tbl_category As jc ON jc.id = j.jobCategory
            LEFT JOIN tbl_users As u ON u.id = j.posterId
            WHERE $whereCond ORDER BY $sorting LIMIT $offset , $rows",$wArray)->results();

         $totalRow = $this->db->pdoQuery("SELECT j.isApproved,j.isActive,j.jobTitle,jc.category_name,j.budget,j.jobType,CONCAT(u.firstName,' ',u.lastName) As username,j.id As jobID FROM tbl_jobs As j
            LEFT JOIN tbl_category As jc ON jc.id = j.jobCategory
            LEFT JOIN tbl_users As u ON u.id = j.posterId
            WHERE $whereCond ORDER BY $sorting ",$wArray)->affectedRows();
         
        foreach ($qrySel as $fetchRes) 
        {
            
            if($fetchRes['isApproved'] == 'p')
            {
                $operation = (in_array('status', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=updateStatus&id=" . $fetchRes['jobID'] . "",  "extraAtt" => "title = 'Approve'","class" => "btn default black btn-approve", "value" => '<i class="fa fa-check"></i>', "title"=>"Approve")) : '' ;
                $operation .=(in_array('delete', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=delete&id=" . $fetchRes['jobID'] . "", "extraAtt" => "title = 'Reject'", "class" => "btn default red btn-delete", "value" => '<i class="fa fa-times" aria-hidden="true"></i>', "title"=>"Reject")) : '';
            }
            else
            {
                $operation = (in_array('status', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=updateStatus&id=" . $fetchRes['jobID'] . "", "class" => "btn default black btn-approve", "value" => '<i class="fa fa-check"></i>', "title"=>"Approve")) : '' ;
            }

            $operation .=(in_array('view', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=view&id=" . $fetchRes['jobID'] . "", "extraAtt" => "title = 'View'", "class" => "btn default blue btn-viewbtn", "value" => '<i class="fa fa-laptop"></i>')) : '';

                              
            $final_array = array(
                $fetchRes['jobTitle'],
                filtering($fetchRes["category_name"]),
                ucfirst($fetchRes['username']),              
                (CURRENCY_SYMBOL.$fetchRes["budget"]),
                ($fetchRes["jobType"]=='pu') ? 'Public':'Private',
                ($fetchRes['isApproved']=='p') ? "<label class='label label-info'>Pending</label>" : "<label class='label label-danger'>Rejected</label>"

            );
            /*if (in_array('status', $this->Permission)) 
            {
                $final_array = array_merge($final_array, array($switch));
            }*/
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

    public function getPageContent() 
    {
        $final_result = NULL;
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/" . $this->module . ".skd");
        $main_content->breadcrumb = $this->getBreadcrumb();
        $final_result = $main_content->compile();

        $category = 
        $fields = array("%CAT%",  "%SKILL%","%SUB_CAT%");
        $fields_replace = array($this->get_category(),  $this->get_skills(),$this->get_subcategory());
        return str_replace($fields, $fields_replace, $final_result);
    }
    public function get_category()
    {
        $query = $this->db->pdoQuery("select * from tbl_category where isActive='y' and isDelete='n' ")->results();
        $category = "";
        foreach ($query as $value) 
        {
            $category.="<option value='".$value['id']."'>".$value['category_name']."</option>";
        }
        return $category;
    }

    public function get_subcategory()
    {
        $query = $this->db->pdoQuery("select * from tbl_subcategory where isActive='y' and isDelete='n'")->results();
        $subcategory = "";
        foreach ($query as $value) 
        {
               $subcategory.="<option value='".$value['id']."'>".$value['subcategory_name']."</option>";
        }   
        return $subcategory;
    }

    public function get_skills()
    {
        $query = $this->db->pdoQuery("select * from tbl_skills where isActive='y' and isDelete='n' ")->results();
        $skills = "";
        foreach ($query as $value) {
            $skills.="<option value='".$value['id']."'>".$value['skill_name']."</option>";
        }
        return $skills;
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
        $objPost->skills = isset($skills) ? implode(",",$skills) : '';
        $objPost->description = isset($description) ? $description : '';
        $objPost->budget = isset($budget) ? $budget : 'j';
        $objPost->expLevel = isset($expLevel) ? $expLevel : 'j';
        $objPost->estimatedDuration = isset($estimatedDuration) ? $estimatedDuration : 'j';
        $objPost->biddingDeadline = isset($biddingDeadline) ? $biddingDeadline : 'j';
        $objPost->jobType = isset($jobType) ? $jobType : 'j';
        $objPost->bidsFromLocation = isset($bidsFromLocation) ? $bidsFromLocation : 'j';
        $objPost->addedQuestion = isset($addedQuestion) ? $addedQuestion : 'j';
        $objPost->featured = isset($featured) ? $featured : 'j';
        $objPost->hideFrmSearch = isset($hideFrmSearch) ? $hideFrmSearch : 'j';
        $objPost->jobPostDate = date('Y-m-d H:i:s');


        if(!empty($_FILES['job_file']['name']))
        {
            $file_name = uploadFile($_FILES['job_file'], DIR_CATEGORY_FILE, SITE_CATEGORY_FILE);
            $file = $file_name['file_name'];
        }
        else
        {
            $file = ($type == 'edit') ? $old_file : '';
        }


        $objPost->file = $file;  


        if ($type == 'edit' && $id > 0) 
        {

            if (in_array('edit', $Permission))
            {
               
                    $objPostArray = (array) $objPost;
                    $this->db->update($this->table, $objPostArray, array("id" => $id));

                    $response['status'] = true;
                    $response['success'] = "Ad Category has been updated successfully";
                    $activity_array = array("id"=>$id, "module"=>$this->module, "activity"=>'edit');
                    add_admin_activity($activity_array);
                    echo json_encode($response);
                    exit;
               
            } 
            else 
            {
                $response['error'] = "You don't have permission";
                echo json_encode($response);
                exit;
            }
        } 
        else 
        {
            $objPost->isActive = isset($isActive) ? $isActive : 'n';
            $objPost->isApproved = 'p';
            if (in_array('add', $Permission)) 
            {
                
                    $objPostArray = (array) $objPost;
                    $id = $this->db->insert($this->table, $objPostArray)->getLastInsertId();        
                    $response['status'] = true;
                    $response['success'] = "Job has been added successfully";
                    $activity_array = array("id"=>$id, "module"=>$this->module, "activity"=>'add');
                        add_admin_activity($activity_array);
                    echo json_encode($response);
                    exit;
                  
            }
            else
            {
                $response['error'] = "You don't have permission";
                echo json_encode($response);
                exit;
            }
        }
    }
}




        
            
            

               
          
            
