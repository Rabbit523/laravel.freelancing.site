<?php

class Monetization extends Home {

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
        $this->data['monTypeId'] = $this->id = $id;
        $this->fields = $fields;
        $this->module = $module;
        $this->table = 'tbl_monetize_type';

        $this->type = ($this->id > 0 ? 'edit' : 'add');
        $this->searchArray = $searchArray;
        parent::__construct();
        if ($this->id > 0) {
            $qrySel = $this->db->select($this->table, "*", array("monTypeId" => $id))->result();
            $fetchRes = $qrySel;

            $this->data['monTypeName'] = $this->monTypeName = $fetchRes['monTypeName'];
            $this->data['isActive'] = $this->isActive = $fetchRes['isActive'];
            
        } else {
            $this->data['monTypeName'] = $this->monTypeName = '';
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
    
    public function getForm() {

        $content = '';
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/form-sd.skd");
        $main_content = $main_content->compile();
        $static_a = ($this->isActive == 'y' ? 'checked' : '');
        $static_d = ($this->isActive != 'y' ? 'checked' : '');        
        $select_class = "selected";
        

        $fields = array(
            "%CATEGORY_NAME%" => filtering($this->data['monTypeName']),
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
            //$whereCond = array("monTypeName LIKE" => "%$chr%","parentCategory LIKE" => "%$chr%");
            $whereCond = "WHERE (monTypeName LIKE  '%".$chr."%') ";
        }
        if (isset($sort))
            $sorting = $sort . ' ' . $order;
        else
            $sorting = 'monTypeId DESC';

        $query = "SELECT * FROM tbl_monetize_type " . $whereCond . " ORDER BY " . $sorting;
        $query_with_limit = $query . " LIMIT " . $offset . " ," . $rows . " ";

        $totalFaq= $this->db->pdoQuery($query)->results();
        $qrySel = $this->db->pdoQuery($query_with_limit)->results();
        $totalRow = count($totalFaq);
        
        foreach ($qrySel as $fetchRes) {
            $status = ($fetchRes['isActive'] == "y") ? "checked" : "";

            $switch = (in_array('status', $this->Permission)) ? $this->toggel_switch(array("action" => "ajax." . $this->module . ".php?id=" . $fetchRes['monTypeId'] . "", "check" => $status)) : '';
            $operation = '';
            $operation .= (in_array('edit', $this->Permission)) ? $this->operation(array("href" => "ajax." . $this->module . ".php?action=edit&id=" . $fetchRes['monTypeId'] . "", "class" => "btn default  black btnEdit","extraAtt" => "title = 'Edit'", "value" => '<i class="fa fa-edit"></i>')) : '';
            if($fetchRes['isDeleted'] == 'n')
            {
                $operation .=(in_array('delete', $this->Permission)) ? '&nbsp;&nbsp;' . $this->operation(array("href" => "ajax." . $this->module . ".php?action=delete&id=" . $fetchRes['monTypeId'] . "", "class" => "btn default  red btn-delete","extraAtt" => "title = 'Delete'", "value" => '<i class="fa fa-trash-o"></i>')) : '';
            }
            else
            {
                $operation .=(in_array('undo', $this->Permission)) ? '&nbsp;&nbsp;' . $this->operation(array("href" => "ajax." . $this->module . ".php?action=undo&id=" . $fetchRes['monTypeId'] . "","extraAtt" => "title = 'Undo'", "class" => "btn default btn-info btn-undo", "value" => '<i class="fa fa-reply"></i>')) : '';
            }
            $delete_status = ($fetchRes['isDeleted']=='y')?"<span class='label label-warning'>&nbsp;Monetization method deleted</span>":'';


            $final_array = array(
                filtering($fetchRes["monTypeId"]),
                filtering($fetchRes["monTypeName"]).'<br>'.$delete_status,
        
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
        $objPost->monTypeName = isset($monTypeName) ? $monTypeName : '';
        
        
        $objPost->isActive = isset($isActive) ? $isActive : 'n';
        
        if ($objPost->monTypeName == "") {
            $response['error'] = "Please Enter Monetization Type Name.";
            echo json_encode($response);
            exit;
        }
        
        $id=isset($id)? $id : 0;
        if ($type == 'edit' && $id > 0) {

            if (in_array('edit', $Permission)) {

                $objPostArray = (array) $objPost;
                $this->db->update($this->table, $objPostArray, array("monTypeId" => $id));

                $activity_array = array("id" => $id, "module" => $this->module, "activity" => $type);
                add_admin_activity($activity_array);

                $response['status'] = true;
                $response['success'] = "Monetization Method updated successfully";
                echo json_encode($response);
                exit;
            } else {
                $response['error'] = "You don't have permission to edit Monetization Method";
                echo json_encode($response);
                exit;
            }
        } else {
            if (in_array('add', $Permission)) {
                

                $objPostArray = (array) $objPost;
                $id = $this->db->insert($this->table, $objPostArray)->getLastInsertId();

                $activity_array = array("id" => $id, "module" => $this->module, "activity" => 'add');
                add_admin_activity($activity_array);

                $response['status'] = true;
                $response['success'] = "Monetization Method added successfully";
                echo json_encode($response);
                exit;
            } else {
                $response['error'] = "You don't have permission to add Monetization Method";
                echo json_encode($response);
                exit;
            }
        }
    }
}
