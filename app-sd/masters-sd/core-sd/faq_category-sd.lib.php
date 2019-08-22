<?php

class FaqCategory extends Home {

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
        $this->table = 'tbl_faq_category';

        $this->type = ($this->id > 0 ? 'edit' : 'add');
        $this->searchArray = $searchArray;
        parent::__construct();
        if ($this->id > 0) {
            $qrySel = $this->db->select($this->table, "*", array("id" => $id))->result();
            $fetchRes = $qrySel;

            $this->data['categoryName'] = $this->categoryName = $fetchRes['categoryName'];
            $this->data['isActive'] = $this->isActive = $fetchRes['isActive'];
            $this->data['createdDate'] = $this->createdDate = $fetchRes['createdDate'];
            
        } else {
            $this->data['categoryName'] = $this->categoryName = '';
            $this->data['isActive'] = $this->isActive = 'y';
            $this->data['createdDate'] = $this->createdDate = '';
           
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
            case 'view' : {
                    $this->data['content'] = $this->viewForm();
                    break;
                }
            case 'delete' : {
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

        //$parent_cat = $this->db->select("tbl_faq_category", "*", array("id"=>$this->parent_id))->result();

        //$content = 
               // $this->displayBox(array("label" => "Category Name &nbsp;:", "value" => filtering($this->categoryName))) .
               // $this->displayBox(array("label" => "parent Category &nbsp;:", "value" => $parent_cat['categoryName']));
                
        //return $content;
    }*/

    public function getForm() {

        $content = '';
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/form-sd.skd");
        $main_content = $main_content->compile();
        $static_a = ($this->isActive == 'y' ? 'checked' : '');
        $static_d = ($this->isActive != 'y' ? 'checked' : '');        
        $select_class = "selected";

        $temp = (new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/lang_categoryName-sd.skd"))->compile();
        $lang_categoryName = getLangForm($this->table,$this->id,'categoryName','Category Name',$temp);
        

        $fields = array(
            "%CATEGORY_NAME%" => filtering($this->data['categoryName']),
            "%LANG_CATEGORYNAME%" => $lang_categoryName,
            "%STATIC_A%" => filtering($static_a),
            "%STATIC_D%" => filtering($static_d),
            "%TYPE%" => filtering($this->type),
            "%ID%" => filtering($this->id, 'input', 'int')
        );

        $content = str_replace(array_keys($fields), array_values($fields), $main_content);
        return sanitize_output($content);
    }

    public function dataGrid() {
        $content = $operation = $whereCond = $totalRow = NULL;
        $result = $tmp_rows = $row_data = array();
        extract($this->searchArray);
        $chr = str_replace(array('_', '%',"'",'"'), array('\_', '\%',"\'",'\"'), $chr);
        //$whereCond = array();
        if (isset($chr) && $chr != '') {
            //$whereCond = array("categoryName LIKE" => "%$chr%","parentCategory LIKE" => "%$chr%");
            $whereCond = "WHERE (categoryName LIKE  '%".$chr."%' OR DATE_FORMAT(createdDate, '" . MYSQL_DATE_FORMAT . "') LIKE '%" . $chr . "%') ";
        }
/*
        echo "<pre>";
        print_r($whereCond);
        exit;*/
        if (isset($sort))
            $sorting = $sort . ' ' . $order;
        else
            $sorting = 'id DESC';
        $qrySel = $this->db->pdoQuery("SELECT * FROM tbl_faq_category " . $whereCond . " ORDER BY " . $sorting . " LIMIT " . $offset . " ," . $rows . " ")->results();
        $totalRow = $this->db->pdoQuery("SELECT * FROM tbl_faq_category " . $whereCond)->affectedRows();

        // $query = "SELECT * FROM tbl_faq_category " . $whereCond . " ORDER BY " . $sorting;
        // $query_with_limit = $query . " LIMIT " . $offset . " ," . $rows . " ";

        // $totalFaq= $this->db->pdoQuery($query)->results();
        // $qrySel = $this->db->pdoQuery($query_with_limit)->results();
        // $totalRow = count($totalFaq);
        
        foreach ($qrySel as $fetchRes) {
            $status = ($fetchRes['isActive'] == "y") ? "checked" : "";

            $switch = (in_array('status', $this->Permission)) ? $this->toggel_switch(array("action" => "ajax." . $this->module . ".php?id=" . $fetchRes['id'] . "", "check" => $status)) : '';
            $operation = '';
            $operation .= (in_array('edit', $this->Permission)) ? $this->operation(array("href" => "ajax." . $this->module . ".php?action=edit&id=" . $fetchRes['id'] . "", "class" => "btn default  black btnEdit","extraAtt" => "title = 'Edit'", "value" => '<i class="fa fa-edit"></i>')) : '';
            $operation .=(in_array('delete', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=delete&id=" . $fetchRes['id'] . "","extraAtt" => "title = 'Delete'", "class" => "btn default  red btn-delete", "value" => '<i class="fa fa-trash-o"></i>')) : '';
            $final_array = array(
                filtering($fetchRes["id"]),
                filtering($fetchRes["categoryName"]),
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
        $objPost->categoryName = isset($categoryName) ? filtering($categoryName) : '';
        
        
        $objPost->isActive = isset($isActive) ? $isActive : 'n';
        
        if ($objPost->categoryName == "") {
            $response['error'] = "Please enter Category Name";
            echo json_encode($response);
            exit;
        }
        
        $objPost = setfeilds($objPost,'categoryName');

        if ($type == 'edit' && $id > 0) {

            if (in_array('edit', $Permission)) {

                $objPostArray = (array) $objPost;
                $this->db->update($this->table, $objPostArray, array("id" => $id));

                $activity_array = array("id" => $id, "module" => $this->module, "activity" => $type);
                add_admin_activity($activity_array);

                $response['status'] = true;
                $response['success'] = "FAQ Category updated successfully";
                echo json_encode($response);
                exit;
            } else {
                $response['error'] = "You don't have permission to edit FAQ Category";
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
                $response['success'] = "FAQ Category added successfully";
                echo json_encode($response);
                exit;
            } else {
                $response['error'] = "You don't have permission to add FAQ Category";
                echo json_encode($response);
                exit;
            }
        }
    }

}
