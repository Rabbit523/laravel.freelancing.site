<?php

class Constant extends Home {

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
        $this->table = 'tbl_language_constant';

        $this->type = ($this->id > 0 ? 'edit' : 'add');
        $this->searchArray = $searchArray;
        parent::__construct();
        if ($this->id > 0) {
            $qrySel = $this->db->select($this->table, "*", array("id" => $id))->result();
            $fetchRes = $qrySel;

            $this->data['constant'] = $this->constant = $fetchRes['constant'];
            
            $this->data['createdDate'] = $this->createdDate = $fetchRes['createdDate'];
            
        } else {
            $this->data['constant'] = $this->constant = '';

            $this->data['createdDate'] = $this->createdDate = '';

        }
        switch ($type) {
            case 'add' : {
                $this->data['content'] = $this->getForm();
                break;
            }
            case 'import_data' : {
                $this->data['content'] = $this->import_data();
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

    public function import_data(){
        $content = '';
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/import_form-sd.skd");
        $main_content = $main_content->compile();
        $fields = array(
            
        );

        $content = str_replace(array_keys($fields), array_values($fields), $main_content);
        return sanitize_output($content);

    }

    /*public function viewForm() {

        //$parent_cat = $this->db->select("tbl_language_constant", "*", array("id"=>$this->parent_id))->result();

        //$content = 
               // $this->displayBox(array("label" => "Category Name &nbsp;:", "value" => filtering($this->constant))) .
               // $this->displayBox(array("label" => "parent Category &nbsp;:", "value" => $parent_cat['constant']));
                
        //return $content;
    }*/

    public function getForm() {

        $content = '';
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/form-sd.skd");
        $main_content = $main_content->compile();
        $select_class = "selected";
        $lang_value = '';

        $value_temp = (new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/lang_value-sd.skd"))->compile();
        $value_data = getLangForm('tbl_language_constant',$this->id,'value','Value',$value_temp);

        $fields = array(
            "%CONSTANT%" => filtering($this->data['constant']),
            '%LANG_VALUE%' => $value_data,
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
        
        if (isset($chr) && $chr != '') {

            $whereCond = "WHERE (constant LIKE  '%".$chr."%' OR `value` LIKE  '%".$chr."%' OR DATE_FORMAT(createdDate, '" . MYSQL_DATE_FORMAT . "') LIKE '%" . $chr . "%') ";
        }

        if (isset($sort))
            $sorting = $sort . ' ' . $order;
        else
            $sorting = 'id DESC';
        $qrySel = $this->db->pdoQuery("SELECT * FROM tbl_language_constant " . $whereCond . " ORDER BY " . $sorting . " LIMIT " . $offset . " ," . $rows . " ")->results();
        $totalRow = $this->db->pdoQuery("SELECT * FROM tbl_language_constant " . $whereCond)->affectedRows();
        
        foreach ($qrySel as $fetchRes) {
            $status =  "";
            $switch =  '';
            $operation = '';
            $operation .= (in_array('edit', $this->Permission)) ? $this->operation(array("href" => "ajax." . $this->module . ".php?action=edit&id=" . $fetchRes['id'] . "", "class" => "btn default  black btnEdit","extraAtt" => "title = 'Edit'", "value" => '<i class="fa fa-edit"></i>')) : '';
            $operation .=(in_array('delete', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=delete&id=" . $fetchRes['id'] . "","extraAtt" => "title = 'Delete'", "class" => "btn default  red btn-delete", "value" => '<i class="fa fa-trash-o"></i>')) : '';
            $final_array = array(
                filtering($fetchRes["id"]),
                filtering($fetchRes["constant"]),
                filtering($fetchRes["value"]),
                filtering(date(DATE_FORMAT_ADMIN,strtotime($fetchRes["createdDate"])))
            );
            
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

    public function import_constant($data,$Permission){
        //print_r($_FILES);
        die;
    }

    public function contentSubmit($data,$Permission){

        $response = array();
        $response['status'] = false;
        extract($data);
        
        $objPost = new stdClass();
        $objPost->constant = isset($constant) ? filtering($constant) : '';

        if ($objPost->constant == "") {
            $response['error'] = "Please enter Category Name";
            echo json_encode($response);
            exit;
        }
        
        $objPost = setfeilds($objPost,'value');

        if ($type == 'edit' && $id > 0) {

            if (in_array('edit', $Permission)) {

                $objPostArray = (array) $objPost;
                $this->db->update($this->table, $objPostArray, array("id" => $id));

                $activity_array = array("id" => $id, "module" => $this->module, "activity" => $type);
                add_admin_activity($activity_array);

                $response['status'] = true;
                $response['success'] = "Constant updated successfully";
                echo json_encode($response);
                exit;
            } else {
                $response['error'] = "You don't have permission to edit Constant";
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
                $response['success'] = "Constant added successfully";
                echo json_encode($response);
                exit;
            } else {
                $response['error'] = "You don't have permission to add Constant";
                echo json_encode($response);
                exit;
            }
        }
    }

}
