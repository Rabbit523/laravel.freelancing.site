<?php
class servicesRequest extends Home 
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
        $this->table = 'tbl_services';

        $this->type = ($this->id > 0 ? 'edit' : 'add');
        $this->searchArray = $searchArray;
        parent::__construct();
        if ($this->id > 0) 
        {
            //$qrySel = $this->db->select($this->table, "*", array("id" => $id))->result();
            $qrySel = $this->db->pdoQuery("Select s.*,jc.category_name,jsc.subcategory_name,CONCAT(u.firstName,' ',u.lastName) As username from tbl_services As s LEFT JOIN tbl_category As jc ON s.servicesCategory = jc.id
                LEFT JOIN tbl_subcategory As jsc ON s.servicesSubCategory = jsc.id
                LEFT JOIN tbl_users As u ON s.freelanserId = u.id
                where s.id='".$id."'
                ")->result();

            $fetchRes = $qrySel;
            $this->data['serviceTitle'] = $this->serviceTitle = $fetchRes['serviceTitle'];
            $this->data['servicesCategory'] = $this->servicesCategory = $fetchRes['servicesCategory'];
            $this->data['servicesSubCategory'] = $this->servicesSubCategory = $fetchRes['servicesSubCategory'];
            $this->data['description'] = $this->description = $fetchRes['description'];
            $this->data['servicesPostDate'] = $this->servicesPostDate = $fetchRes['servicesPostDate'];
            $this->data['services_image'] = $this->services_image = $fetchRes['services_image'];
            $this->data['noDayDelivery'] = $this->noDayDelivery = $fetchRes['noDayDelivery'];
            $this->data['servicesPrice'] = $this->servicesPrice = $fetchRes['servicesPrice'];            
            $this->data['requiredDetails'] = $this->requiredDetails = $fetchRes['requiredDetails'];
            $this->data['featured'] = $this->featured = $fetchRes['featured'];
            
            $this->data['freelanserId'] = $this->freelanserId = $fetchRes['freelanserId'];
            $this->data['isApproved'] = $this->isApproved = $fetchRes['isApproved'];
            $this->data['reportStatus'] = $this->reportStatus = $fetchRes['reportStatus'];
            $this->data['category_name'] = $this->category_name = $fetchRes['category_name'];
            $this->data['subcategory_name'] = $this->subcategory_name = $fetchRes['subcategory_name'];
            $this->data['username'] = $this->username = $fetchRes['username'];
            $this->data['isActive'] = $this->isActive = $fetchRes['isActive'];
        } 
        else 
        {
            $this->data['serviceTitle'] = $this->serviceTitle = '';
            $this->data['servicesCategory'] = $this->servicesCategory = '';
            $this->data['servicesSubCategory'] = $this->servicesSubCategory = '';
            $this->data['description'] = $this->description = '';
            $this->data['servicesPostDate'] = $this->servicesPostDate = '';
            $this->data['services_image'] = $this->services_image = '';
            $this->data['noDayDelivery'] = $this->noDayDelivery = '';
            $this->data['servicesPrice'] = $this->servicesPrice = '';
            $this->data['requiredDetails'] = $this->requiredDetails = '';
            $this->data['featured'] = $this->featured = '';
            
            $this->data['freelanserId'] = $this->freelanserId = '';
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
        if($this->freelanserId != 0)
        {
            $user_detail = $this->db->pdoQuery("Select * from tbl_users where id='".$this->freelanserId."' ")->result();
            $freelanser_img = "<img src='".SITE_USER_PROFILE.$user_detail['profileImg']."' width='50' height='50'>";
            $user_data = 
                        $this->displayBox(array("label" => "Freelancer Name &nbsp;:", "value" => $user_detail['userName'] )).
                        $this->displayBox(array("label" => "Freelancer Profile&nbsp;:", "value" => $freelanser_img )).
                        $this->displayBox(array("label" => "Star Rating &nbsp;:", "value" => '' )).
                        $this->displayBox(array("label" => "Freelancer Location&nbsp;:", "value" => $user_detail['location'] )).
                         $this->displayBox(array("label" => "Status of approvel&nbsp;:", "value" => $user_detail['status'] == 'a'?'Active':'Deactive' ));

        }
        else
        {
            $user_data = $this->displayBox(array("label" => "Freelancer Name &nbsp;:", "value" => 'Admin' ));
        }

        $addon_query = $this->db->pdoQuery("select * from tbl_services_addon where services_id='".$this->id."' ");
        $addon_detail = $addon_query->results();
        if($addon_query->affectedRows()>0)
        {
            $addon_data = "";
            $addon_data .= "<center>Services Addon Detail</center>";
            foreach ($addon_detail as $value) 
            {
                $addon_data .=                     
                    $this->displayBox(array("label" => "Addon Title &nbsp;:", "value" => $value['addonTitle'] )).
                    $this->displayBox(array("label" => "Addon Day Required &nbsp;:", "value" => $value['addonDayRequired'] )).
                    $this->displayBox(array("label" => "Addon Price &nbsp;:", "value" => CURRENCY_SYMBOL.$value['addonPrice'] )).
                    $this->displayBox(array("label" => "Addon Description &nbsp;:", "value" => $value['addonDesc']  ))."<hr>";
            }
        }
        $content = 
            $this->displayBox(array("label" => "Services Title &nbsp;:", "value" => filtering($this->serviceTitle))).
            $this->displayBox(array("label" => "Services Category &nbsp;:", "value" => filtering($this->category_name))).
            $this->displayBox(array("label" => "Services Subcategory &nbsp;:", "value" => filtering($this->subcategory_name))).
          
            $this->displayBox(array("label" => "Services Description &nbsp;:", "value" => filtering($this->description))).
            $this->displayBox(array("label" => "Services Posted date &nbsp;:", "value" => filtering(date('d-m-Y H:i:s',strtotime($this->servicesPostDate))))).
            $this->displayBox(array("label" => "No of Day of Delivery &nbsp;:", "value" => filtering($this->noDayDelivery))).
            $this->displayBox(array("label" => "Services Price &nbsp;:", "value" => CURRENCY_SYMBOL.$this->servicesPrice)).
            "<center><strong>Freelancer Detail</strong></center>".
            "<hr>".$user_data.            
            "<hr>".$addon_data.                      
            $this->displayBox(array("label" => "Featured &nbsp;:", "value" => ($this->featured == 'y') ? 'Yes':'No'))
           
        ;                   
        return $content;
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
                $whereCond .= " AND (s.serviceTitle LIKE ? OR jc.category_name LIKE ? OR u.username LIKE ?) ";
                $wArray[] = "%$chr%";
                $wArray[] = "%$chr%";
                $wArray[] = "%$chr%";
        }

        if(isset($category) && $category!='')
        {
            $whereCond.= ' and s.servicesCategory ="'.$category.'"';
        }
        if(isset($subcategory) && $subcategory!='')
        {
            $whereCond.= ' and s.servicesSubCategory ="'.$subcategory.'"';
        }
        if(isset($filtering_type) && $filtering_type!='')
        {
            if($filtering_type=='f')
            {
                $whereCond .= ' and s.featured="y" ';
            }
            else
            {
                $whereCond.= ' and s.isApproved ="'.$filtering_type.'"';
            }
        }

        if (isset($sort))
            $sorting = $sort . ' ' . $order;
        else
            $sorting = 's.id DESC';

        $qrySel = $this->db->pdoQuery("SELECT s.servicesPrice,s.isApproved,s.isActive,s.serviceTitle,jc.category_name,CONCAT(u.firstName,' ',u.lastName) As username,s.id As jobID FROM tbl_services As s
            LEFT JOIN tbl_category As jc ON jc.id = s.servicesCategory
            LEFT JOIN tbl_users As u ON u.id = s.freelanserId
            WHERE $whereCond ORDER BY $sorting LIMIT $offset , $rows",$wArray)->results();

         $totalRow = $this->db->pdoQuery("SELECT s.servicesPrice,s.isApproved,s.isActive,s.serviceTitle,jc.category_name,CONCAT(u.firstName,' ',u.lastName) As username,s.id As jobID FROM tbl_services As s
            LEFT JOIN tbl_category As jc ON jc.id = s.servicesCategory
            LEFT JOIN tbl_users As u ON u.id = s.freelanserId
            WHERE $whereCond ORDER BY $sorting ",$wArray)->affectedRows();
         
        foreach ($qrySel as $fetchRes) 
        {
            
            if($fetchRes['isApproved'] == 'p')
            {
                $operation = (in_array('status', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=updateStatus&id=" . $fetchRes['jobID'] . "", "class" => "btn default black btn-approve", "value" => '<i class="fa fa-check"></i>', "extraAtt" => "title = 'Approve'")) : '' ;
                $operation .=(in_array('status', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=reject&id=" . $fetchRes['jobID'] . "", "extraAtt" => "title = 'Reject'", "class" => "btn default red btn-reject", "value" => '<i class="fa fa-times" aria-hidden="true"></i>')) : '';
            }
            else
            {
                $operation = (in_array('status', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=updateStatus&id=" . $fetchRes['jobID'] . "", "class" => "btn default black btn-approve", "value" => '<i class="fa fa-check"></i>', "title"=>"Approve")) : '' ;
            }

            $operation .=(in_array('view', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=view&id=" . $fetchRes['jobID'] . "", "extraAtt" => "title = 'View'", "class" => "btn default blue btn-viewbtn", "value" => '<i class="fa fa-laptop"></i>')) : '';

                              
            $final_array = array(
                $fetchRes['serviceTitle'],
                filtering($fetchRes["category_name"]),
                ucfirst($fetchRes['username']),              
                CURRENCY_SYMBOL.($fetchRes["servicesPrice"]),
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
        $fields = array("%CAT%");
        $fields_replace = array($this->get_category());
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

    public function get_subcategory($cat)
    {
        $query = $this->db->pdoQuery("select * from tbl_subcategory where isActive='y' and isDelete='n' and maincat_id='".$cat."' ")->results();
        $subcategory = "<option value=''>All Subcategory</option>";
        foreach ($query as $value) 
        {
               $subcategory.="<option value='".$value['id']."'>".$value['subcategory_name']."</option>";
        }   
        return $subcategory;
    }

    
   
}




        
            
            

               
          
            
