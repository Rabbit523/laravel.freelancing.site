<?php

class Plateform extends Home {

    public $page_name;
    public $page_title;
    public $meta_keyword;
    public $meta_desc;
    public $page_desc;
    public $isActive;
    public $data = array();

    public function __construct($module, $id = 0, $objPost = NULL, $searchArray = array(), $type = '') {
        global $db, $fields, $sessCataId;
        $this->db = $db;
        $this->data['id'] = $this->id = $id;
        $this->fields = $fields;
        $this->module = $module;
        $this->table = 'tbl_listing_platform';

        $this->type = ($this->id > 0 ? 'edit' : 'add');
        $this->searchArray = $searchArray;
        parent::__construct();
        if ($this->id > 0) {
            $qrySel = $this->db->select($this->table, "*", array("id" => $id),' ORDER BY platformName asc')->result();
            $fetchRes = $qrySel;

            
            $this->data['platformName'] = $this->platformName = $fetchRes['platformName'];
            $this->data['createdDate'] = $this->createdDate = $fetchRes['createdDate'];
            $this->data['isActive'] = $this->isActive = $fetchRes['isActive'];
        } else {
            
            $this->data['platformName'] = $this->platformName = '';
            $this->data['createdDate'] = $this->createdDate = '';
            $this->data['isActive'] = $this->isActive = 'y';
        }
        switch ($type) {
            case 'add' : {
                    $this->data['content'] = $this->getForm();
                    break;
                }
            case 'edit' : {
                    $this->data['content'] = $this->getForm();
                    break;
                }
            /*case 'view' : {
                    $this->data['content'] = $this->viewForm();
                    break;
                }*/
            case 'delete' : {
                    $this->data['content'] = json_encode($this->dataGrid());
                    break;
                }
            case 'undo' : {
                    $this->data['content'] = json_encode($this->dataGrid());
                    break;
                }
            case 'datagrid' : {
                    $this->data['content'] = json_encode($this->dataGrid());
                    break;
                }
        }
    }

    /*public function viewForm() {
       

        $content = 
                $this->displayBox(array("label" => "Platform Name &nbsp;:", "value" => filtering($this->platformName))) ;
               
               
        return $content;
    }*/

    public function getForm() {

        $content = '';
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/form-sd.skd");
        $main_content = $main_content->compile();
        $static_a = ($this->isActive == 'y' ? 'checked' : '');
        $static_d = ($this->isActive != 'y' ? 'checked' : '');

       
        

        $fields = array(
            "%NICHE_NAME%",
            /*"%ALL_NICHE%",*/
            
            "%STATIC_A%",
            "%STATIC_D%",
            "%TYPE%",
            "%ID%"
        );

        $fields_replace = array(
            filtering($this->data['platformName']),
            /*$all_niche,*/
           
            filtering($static_a),
            filtering($static_d),
            filtering($this->type),
            filtering($this->id, 'input', 'int')
        );

        $content = str_replace($fields, $fields_replace, $main_content);
        return sanitize_output($content);
    }

    public function dataGrid() {

        $content = $operation = $whereCond = $totalRow = NULL;
        $result = $tmp_rows = $row_data = array();
        extract($this->searchArray);
        $chr = str_replace(array('_', '%',"'",'"'), array('\_', '\%',"\'",'\"'), $chr);
        
        $whereCond = "where 1=1";
        

        if (isset($chr) && $chr != '') {
            $whereCond.=' and platformName LIKE  "%'.$chr.'%" or DATE_FORMAT(tbl_listing_platform.createdDate, "' . MYSQL_DATE_FORMAT . '") LIKE "%' . $chr . '%"';
        }
        if (isset($sort))
            $sorting = $sort . ' ' . $order;
        else
            $sorting = 'createdDate DESC';
        
        $qrySel = $this->db->pdoQuery("SELECT * from tbl_listing_platform $whereCond ORDER BY $sorting limit $offset , $rows")->results();
        // $qrySel=$qrySel->results();
        $totalRow=$this->db->pdoQuery("SELECT * from tbl_listing_platform $whereCond")->affectedRows();
        foreach ($qrySel as $fetchRes) {
            $status = ($fetchRes['isActive'] == "y") ? "checked" : "";

            $switch = (in_array('status', $this->Permission)) ? $this->toggel_switch(array("action" => "ajax." . $this->module . ".php?id=" . $fetchRes['id'] . "", "check" => $status)) : '';
            $operation = '';
            $operation .= (in_array('edit', $this->Permission)) ? $this->operation(array("href" => "ajax." . $this->module . ".php?action=edit&id=" . $fetchRes['id'] . "", "class" => "btn default  black btnEdit","extraAtt" => "title = 'Edit'", "value" => '<i class="fa fa-edit"></i>')) : '';
            //$operation .=(in_array('view', $this->Permission)) ? '&nbsp;&nbsp;' . $this->operation(array("href" => "ajax." . $this->module . ".php?action=view&id=" . $fetchRes['id'] . "", "class" => "btn default blue  btn-viewbtn", "value" => '<i class="fa fa-laptop"></i>')) : '';
            if($fetchRes['isDeleted'] == 'n')
            {
                $operation .=(in_array('delete', $this->Permission)) ? '&nbsp;&nbsp;' . $this->operation(array("href" => "ajax." . $this->module . ".php?action=delete&id=" . $fetchRes['id'] . "", "class" => "btn default  red btn-delete","extraAtt" => "title = 'Delete'","value" => '<i class="fa fa-trash-o"></i>')) : '';
            }
            else
            {
                $operation .=(in_array('undo', $this->Permission)) ? '&nbsp;&nbsp;' . $this->operation(array("href" => "ajax." . $this->module . ".php?action=undo&id=" . $fetchRes['id'] . "","extraAtt" => "title = 'Undo'", "class" => "btn default btn-info btn-undo", "value" => '<i class="fa fa-reply"></i>')) : '';
            }

            $delete_status = ($fetchRes['isDeleted']=='y')?"<span class='label label-warning'>&nbsp;Listing plateform deleted</span>":'';


            $final_array = array(
                filtering($fetchRes["id"]),
                filtering($fetchRes["platformName"]).'<br>'.$delete_status,
                filtering(date(DATE_FORMAT_ADMIN,strtotime($fetchRes["createdDate"])))
                
            );
            if (in_array('status', $this->Permission)) {
                $final_array = array_merge($final_array, array($switch));
            }
            if (in_array('edit', $this->Permission) || in_array('delete', $this->Permission) || in_array('view', $this->Permission)) {
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
    public function getPageContent() {
        $final_result = NULL;
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/" . $this->module . ".skd");
        $main_content->breadcrumb = $this->getBreadcrumb();
        $final_result = $main_content->compile();
        return $final_result;
    }
    public function contentSubmit($data,$Permission){
        
        $response = array();
        $response['status'] = false;
        extract($data);
        
        $objPost = new stdClass();
        $objPost->platformName = isset($platformName) ? $platformName : '';        
       /* $objPost->parent_id = isset($parentId) ? $parentId : '';*/
       
        
        $objPost->isActive = isset($isActive) ? $isActive : 'n';
        
        if ($objPost->platformName == "") {
            $response['error'] = "Please enter Platform Name";
            echo json_encode($response);
            exit;
        }
       
        if ($type == 'edit' && $id > 0) {

            if (in_array('edit', $Permission)) {

                $objPostArray = (array) $objPost;
                $this->db->update($this->table, $objPostArray, array("id" => $id));

                $activity_array = array("id" => $id, "module" => $this->module, "activity" => $type);
                add_admin_activity($activity_array);

                $response['status'] = true;
                $response['success'] = "Listing Platform updated successfully";
                echo json_encode($response);
                exit;
            } else {
                $response['error'] = "You don't have permission to edit Listing Platform";
                echo json_encode($response);
                exit;
            }
        } else {
            if (in_array('add', $Permission)) {
                
                $objPost->createdDate=date('Y-m-d H:i:s');
                $objPostArray = (array) $objPost;
                $id = $this->db->insert($this->table, $objPostArray)->getLastInsertId();

                $activity_array = array("id" => $id, "module" => $this->module, "activity" => 'add');
                add_admin_activity($activity_array);

                $response['status'] = true;
                $response['success'] = "Listing Platform added successfully";
                echo json_encode($response);
                exit;
            } else {
                $response['error'] = "You don't have permission to add Listing Platform";
                echo json_encode($response);
                exit;
            }
        }
    }

}
