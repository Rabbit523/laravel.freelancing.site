<?php

class Language extends Home {

    public $language;
    public $answer;
    public $priority;
    public $isActive;
    public $data = array();

    public function __construct($module, $id = 0, $objPost = NULL, $searchArray = array(), $type = '') {
        global $db, $fields, $sessCataid;
        $this->db = $db;
        $this->data['id'] = $this->id = $id;
        $this->fields = $fields;
        $this->module = $module;
        $this->table = 'tbl_language';

        $this->type = ($this->id > 0 ? 'edit' : 'add');
        $this->searchArray = $searchArray;
        parent::__construct();
        if ($this->id > 0) {
            $qrySel = $this->db->select($this->table, "*", array("id" => $id))->result();
            $fetchRes = $qrySel;
            $this->data['id'] = $this->id = $fetchRes['id'];
            $this->data['language'] = $this->language = $fetchRes['language'];
            $this->data['url_constant'] = $this->url_constant = $fetchRes['url_constant'];
            $this->data['isActive'] = $this->isActive = $fetchRes['isActive'];
            $this->data['createdDate'] = $this->createdDate = $fetchRes['createdDate'];
        } else {
            $this->data['id'] = $this->id = '';
            $this->data['language'] = $this->language = '';            
            $this->data['url_constant'] = $this->url_constant = '';            
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

    public function getForm() {
        $content = '';
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/form-sd.skd");
        $main_content = $main_content->compile();
        
        $static_a = ($this->isActive == 'y' ? 'checked' : '');
        $static_d = ($this->isActive != 'y' ? 'checked' : '');
        $fields = array(
            "%LANGUAGE%" =>filtering($this->data['language']),
            "%URL_CONSTANT%" =>filtering($this->data['url_constant']),
            "%STATIC_A%" => filtering($static_a),
            "%STATIC_D%" => filtering($static_d),
            "%TYPE%" => filtering($this->type),
            "%ID%" => filtering($this->id, 'input', 'int')
        );

        $content = str_replace(array_keys($fields), array_values($fields), $main_content);
        return sanitize_output($content);
    }

    public function dataGrid() 
    {
        $content = $operation = $whereCond = $totalRow =NULL;
        $result = $tmp_rows = $row_data = array();
        extract($this->searchArray);
        $chr = str_replace(array('_', '%',"'",'"'), array('\_', '\%',"\'",'\"'), $chr);
        
        if (isset($chr) && $chr != '') {
            $whereCond .=  "  WHERE (language LIKE '%".$chr."%' OR DATE_FORMAT(createdDate, '" . MYSQL_DATE_FORMAT . "') LIKE '%" . $chr . "%')"; 
        }
        
        if (isset($sort))
            $sorting = $sort . ' ' . $order;
        else
            $sorting = 'id DESC';
        $qrySel = $this->db->pdoQuery("SELECT * FROM $this->table" . $whereCond . " ORDER BY " . $sorting. " LIMIT " . $offset . " ," . $rows ." ")->results();
        $totalRow = $this->db->pdoQuery("SELECT * FROM $this->table" . $whereCond)->affectedRows();
        foreach ($qrySel as $fetchRes) 
        {
            $status = ($fetchRes['isActive'] == "y") ? "checked" : "";
            $switch = (in_array('status', $this->Permission)) ? $this->toggel_switch(array("action" => "ajax." . $this->module . ".php?id=" . $fetchRes['id'] . "", "check" => $status)) : 'Active';
            $operation = '';
            $operation .= (in_array('edit', $this->Permission)) ? $this->operation(array("href" => "ajax." . $this->module . ".php?action=edit&id=" . $fetchRes['id'] . "", "class" => "btn default  black btnEdit","extraAtt" => "title = 'Edit'", "value" => '<i class="fa fa-edit"></i>')) : '';
            
            $operation .=(in_array('delete', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=delete&id=" . $fetchRes['id'] . "","extraAtt" => "title = 'Delete'","class" => "btn default  red btn-delete", "value" => '<i class="fa fa-trash-o"></i>')) : '';
            
            $final_array = array(
                filtering($fetchRes["id"]),
                filtering($fetchRes["language"]),
                filtering(date(DATE_FORMAT_ADMIN,strtotime($fetchRes["createdDate"])))
            );
            if (in_array('status', $this->Permission)) {
                $final_array = array_merge($final_array, array($switch));
            }
            if (in_array('edit', $this->Permission) || in_array('delete', $this->Permission)) {
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
        $objPost = new stdClass();
        extract($data);
        $id = (isset($id) && $id!="") ? $id : 0;
        $objPost->language = isset($language) ? $language : ''; 
        $objPost->url_constant = isset($url_constant) ? $url_constant : ''; 
        $objPost->isActive = isset($isActive) ? $isActive : 'n';
        
        if ($type == 'edit' && $id > 0) {
            if (in_array('edit', $Permission)) {
                $objPostArray = (array) $objPost;
                $this->db->update($this->table, $objPostArray, array("id" => $id));
                $activity_array = array("id" => $id, "module" => $this->module, "activity" => 'edit');
                add_admin_activity($activity_array);
                $response['status'] = true;
                $response['success'] = "Language updated successfully";
                $_SESSION["toastr_message"] = disMessage(array('type' => 'suc', 'var' => 'Language updated successfully'));                
                echo json_encode($response);
                exit;
            } else {
                $response['error'] = "You don't have permission to edit Language";
                echo json_encode($response);
                exit;
            }
        } else {

            if (in_array('add', $Permission)) {             

             $objPost->createdDate = date("Y-m-d H:i:s");
             $objPostArray = (array) $objPost;
             $insertId = $this->db->insert($this->table, $objPostArray)->getLastInsertId();

             if(!empty($insertId)){
                $column_name = "value_".$insertId;
                // slider
                $this->db->query("ALTER TABLE `tbl_slider` 
                    ADD `title_".$insertId."` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
                    ADD `content_".$insertId."` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL");
                $this->db->query("update tbl_slider set title_".$insertId." = Title, content_".$insertId." = Content ");
                // how its work
                $this->db->query("ALTER TABLE `tbl_how_it_work` 
                    ADD `title_".$insertId."` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
                    ADD `content_".$insertId."` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL");
                $this->db->query("update tbl_how_it_work set title_".$insertId." = Title, content_".$insertId." = Content ");

                // way to work
                $this->db->query("ALTER TABLE `tbl_way_to_work` 
                    ADD `content_".$insertId."` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL");
                $this->db->query("update tbl_way_to_work set content_".$insertId." = content ");

                // way to work service
                $this->db->query("ALTER TABLE `tbl_way_to_work_service` 
                    ADD `content_".$insertId."` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL");
                $this->db->query("update tbl_way_to_work_service set content_".$insertId." = content ");

                //download app
                $this->db->query("ALTER TABLE `tbl_download_app` 
                    ADD `tag_line_".$insertId."` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL NULL AFTER tag_line,
                    ADD `title_".$insertId."` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL NULL AFTER title,
                    ADD `content_".$insertId."` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL NULL AFTER content");
                $this->db->query("update tbl_download_app set tag_line_".$insertId." = tag_line,title_".$insertId." = title,content_".$insertId." = content");

                //Search Section
                $this->db->query("ALTER TABLE `tbl_search_section` 
                    ADD `before_login_title_".$insertId."` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL NULL AFTER before_login_title,
                    ADD `before_login_content_".$insertId."` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL NULL AFTER before_login_content
                    ADD `customer_login_title_".$insertId."` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL NULL AFTER customer_login_title,
                    ADD `customer_login_content_".$insertId."` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL NULL AFTER customer_login_content
                    ADD `freelancer_login_title_".$insertId."` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL NULL AFTER freelancer_login_title,
                    ADD `freelancer_login_content_".$insertId."` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL NULL AFTER freelancer_login_content");
                $this->db->query("update tbl_search_section set title_".$insertId." = title,content_".$insertId." = content");

                //Hire Section
                $this->db->query("ALTER TABLE `tbl_hire_section` 
                    ADD `title_".$insertId."` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL NULL AFTER title,
                    ADD `content_".$insertId."` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL NULL AFTER content");
                $this->db->query("update tbl_hire_section set title_".$insertId." = title,content_".$insertId." = content");

               
                // tbl_language_constant
                $this->db->query("ALTER TABLE `tbl_language_constant` ADD `".$column_name."` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `value` ");
                $this->db->query("UPDATE tbl_language_constant SET $column_name = value");

                // tbl_blog
                $this->db->query("ALTER TABLE `tbl_blog` 
                    ADD `blogTitle_".$insertId."` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `blogTitle`,
                    ADD `blogDesc_".$insertId."` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `blogDesc` ");
                $this->db->query("update tbl_blog set blogTitle_".$insertId." = blogTitle, blogDesc_".$insertId." = blogDesc ");

                // tbl_blog_category
                $this->db->query("ALTER TABLE `tbl_blog_category` 
                    ADD `categoryName_".$insertId."` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `categoryName` ");
                $this->db->query("update tbl_blog_category set categoryName_".$insertId." = categoryName " );

                // tbl_category
                $this->db->query("ALTER TABLE `tbl_category` 
                    ADD `category_name_".$insertId."` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `category_name` ");
                $this->db->query("update tbl_category set category_name_".$insertId." = category_name" );

                //tbl_content
                $this->db->query("ALTER TABLE `tbl_content` 
                    ADD `pageTitle_".$insertId."` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `pageTitle`,
                    ADD `pageDesc_".$insertId."` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `pageDesc` ");
                $this->db->query("update tbl_content set pageTitle_".$insertId." = pageTitle, pageDesc_".$insertId." = pageDesc " );

                // tbl_credit_package
                $this->db->query("ALTER TABLE `tbl_credit_package` 
                    ADD `title_".$insertId."` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `title` ");
                $this->db->query("update tbl_credit_package set title_".$insertId." = title" );

                // tbl_faq
                $this->db->query("ALTER TABLE `tbl_faq` 
                    ADD `question_".$insertId."` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `question`,
                    ADD `ansDesc_".$insertId."` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `ansDesc` ");
                $this->db->query("update tbl_faq set question_".$insertId." = question, ansDesc_".$insertId." = ansDesc " );

                // tbl_faq_category
                $this->db->query("ALTER TABLE `tbl_faq_category` 
                    ADD `categoryName_".$insertId."` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `categoryName` ");
                $this->db->query("update tbl_faq_category set categoryName_".$insertId." = categoryName" );

                // tbl_question
                $this->db->query("ALTER TABLE `tbl_question` 
                    ADD `question_".$insertId."` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `question` ");
                $this->db->query("update tbl_question set question_".$insertId." = question" );

                // tbl_skills
                $this->db->query("ALTER TABLE `tbl_skills` 
                    ADD `skill_name_".$insertId."` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `skill_name` ");
                $this->db->query("update tbl_skills set skill_name_".$insertId." = skill_name" );

                // tbl_subcategory
                $this->db->query("ALTER TABLE `tbl_subcategory` 
                    ADD `subcategory_name_".$insertId."` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `subcategory_name` ");
                $this->db->query("update tbl_subcategory set subcategory_name_".$insertId." = subcategory_name" );

            }


            $activity_array = array("id" => $insertId, "module" => $this->module, "activity" => 'add');
            add_admin_activity($activity_array);
            $response['status'] = true;
            $response['success'] = "Language added successfully";
            echo json_encode($response);
            exit;
        } else {
            $response['error'] = "You don't have permission to add Language";
            echo json_encode($response);
            exit;
        }
    }
}

}
