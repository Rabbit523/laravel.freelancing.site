<?php

class Question extends Home {

    public $question;
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
        $this->table = 'tbl_question';

        $this->type = ($this->id > 0 ? 'edit' : 'add');
        $this->searchArray = $searchArray;
        parent::__construct();
        if ($this->id > 0) {
            $qrySel = $this->db->select($this->table, "*", array("id" => $id))->result();
            $fetchRes = $qrySel;
            $this->data['id'] = $this->id = $fetchRes['id'];
            $this->data['question'] = $this->question = $fetchRes['question'];
            $this->data['isActive'] = $this->isActive = $fetchRes['isActive'];
            //$this->data['createdDate'] = $this->createdDate = $fetchRes['createdDate'];
        } else {
            $this->data['id'] = $this->id = '';
            $this->data['question'] = $this->question = '';            
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
        $listing='';
        // $default=($this->defaultAmount=='y' ?'disabled' : '');

        $temp = (new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/lang_question-sd.skd"))->compile();
        $lang_question = getLangForm($this->table,$this->id,'question','Question',$temp);
        
        $static_a = ($this->isActive == 'y' ? 'checked' : '');
        $static_d = ($this->isActive != 'y' ? 'checked' : '');
        $fields = array(
            "%QUESTION%" =>filtering($this->data['question']),
            "%LANG_QUESTION%" => $lang_question,
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
        // $whereCond = "";
        
        if (isset($chr) && $chr != '') {
            $whereCond .=  "  WHERE (question LIKE '%".$chr."%')"; 
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
            $switch = (in_array('status', $this->Permission)) ? $this->toggel_switch(array("action" => "ajax." . $this->module . ".php?id=" . $fetchRes['id'] . "", "check" => $status)) : '';
            $operation = '';
            $operation .= (in_array('edit', $this->Permission)) ? $this->operation(array("href" => "ajax." . $this->module . ".php?action=edit&id=" . $fetchRes['id'] . "", "class" => "btn default  black btnEdit","extraAtt" => "title = 'Edit'", "value" => '<i class="fa fa-edit"></i>')) : '';
            
            $operation .=(in_array('delete', $this->Permission)) ? '&nbsp;&nbsp;' . $this->operation(array("href" => "ajax." . $this->module . ".php?action=delete&id=" . $fetchRes['id'] . "","extraAtt" => "title = 'Delete'","class" => "btn default  red btn-delete", "value" => '<i class="fa fa-trash-o"></i>')) : '';
            
            $final_array = array(
                filtering($fetchRes["id"]),
                filtering($fetchRes["question"])
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
        $objPost->question = isset($question) ? filtering($question) : ''; 
        $objPost->isActive = isset($isActive) ? $isActive : 'n';

        $objPost = setfeilds($objPost,'question');
        
        if ($type == 'edit' && $id > 0) {
            if (in_array('edit', $Permission)) {
                    $objPostArray = (array) $objPost;
                    $this->db->update($this->table, $objPostArray, array("id" => $id));
                    $activity_array = array("id" => $id, "module" => $this->module, "activity" => 'edit');
                    add_admin_activity($activity_array);
                    $response['status'] = true;
                    $response['success'] = "Question updated successfully";
                    $_SESSION["toastr_message"] = disMessage(array('type' => 'suc', 'var' => 'Question updated successfully'));                
                    echo json_encode($response);
                    exit;
                } else {
                    $response['error'] = "You don't have permission to edit Question";
                    echo json_encode($response);
                    exit;
                    }
        } else {

                if (in_array('add', $Permission)) {             

                   $objPost->createdDate = date("Y-m-d H:i:s");
                   $objPostArray = (array) $objPost;
                   $id = $this->db->insert($this->table, $objPostArray)->getLastInsertId();

                   $activity_array = array("id" => $id, "module" => $this->module, "activity" => 'add');
                   add_admin_activity($activity_array);
                   $response['status'] = true;
                   $response['success'] = "Question added successfully";
                   echo json_encode($response);
                   exit;
                } else {
                    $response['error'] = "You don't have permission to add Question";
                    echo json_encode($response);
                    exit;
                }
            }
        }

}
