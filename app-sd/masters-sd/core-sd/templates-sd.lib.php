<?php

class Templates extends Home {

    public $category;
    public $status;
    public $data = array();

    public function __construct($module, $id = 0, $objPost = NULL, $searchArray = array(), $type = '') {
        global $db, $fields, $sessCataId;
        $this->db = $db;
        $this->data['id'] = $this->id = $id;
        $this->fields = $fields;
        $this->module = $module;
        $this->table = 'tbl_email_templates';

        $this->type = ($this->id > 0 ? 'edit' : 'add');
        $this->searchArray = $searchArray;
        parent::__construct();
        if ($this->id > 0) {
            $qrySel = $this->db->select($this->table, "*", array("id" => $id))->result();
            $fetchRes = $qrySel;
            $this->data['subject'] = $this->subject = filtering($fetchRes['subject']);
            $this->data['templates'] = $this->templates = filtering($fetchRes['templates'], 'output', 'text');
            $this->data['description'] = $this->description = filtering($fetchRes['description']);
            $this->data['status'] = $this->status = filtering($fetchRes['status']);
        } else {
            $this->data['subject'] = $this->subject = '';
            $this->data['templates'] = $this->templates = '';
            $this->data['description'] = $this->description = '';
            $this->data['status'] = $this->status = 'y';
        }
        switch ($type) {
            case 'add' : {
                    $this->data['content'] = (in_array('add', $this->Permission)) ? $this->getForm() : '';
                    break;
                }
            case 'edit' : {
                    $this->data['content'] = (in_array('edit', $this->Permission)) ? $this->getForm() : '';
                    break;
                }
            case 'view' : {
                    $this->data['content'] = (in_array('view', $this->Permission)) ? $this->viewForm() : '';
                    break;
                }
            case 'delete' : {
                    $this->data['content'] = (in_array('delete', $this->Permission)) ? json_encode($this->dataGrid()) : '';
                    break;
                }
            case 'datagrid' : {
                    $this->data['content'] = (in_array('module', $this->Permission)) ? json_encode($this->dataGrid()) : '';
                }
        }
    }

    public function viewForm() {
        $content = $this->fields->displayBox(array("label" => "Subject&nbsp;:", "value" => $this->subject)) .
                $content = $this->fields->displayBox(array("label" => "Templates&nbsp;:", "value" => $this->templates));
        return $content;
    }

    public function getForm() {
        $content = '';

        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/form-sd.skd");
        $main_content = $main_content->compile();

        $fields = array("%MEND_SIGN%", "%SUBJECT%", "%DESCRIPTION%", "%TEMPLATES%", "%TYPE%", "%ID%");

        $fields_replace = array(MEND_SIGN, $this->subject, $this->description, htmlentities($this->templates), $this->type, $this->id);

        $content = str_replace($fields, $fields_replace, $main_content);
        return sanitize_output($content);
    }

    public function dataGrid() {

        $content = $operation = $whereCond = $totalRow = NULL;
        $result = $tmp_rows = $row_data = array();
        extract($this->searchArray);
        $chr = str_replace(array('_', '%',"'",'"'), array('\_', '\%',"\'",'\"'), $chr);
        $whereCond = '';
        if (isset($chr) && $chr != '') {
            $whereCond .= " WHERE (constant LIKE '%$chr%' OR types LIKE '%$chr%' OR subject LIKE '%$chr%' OR description LIKE '%$chr%')";
        }

        if (isset($sort))
            $sorting = $sort . ' ' . $order;
        else
            $sorting = 'id DESC';

        //$totalRow = $this->db->count($this->table, $whereCond);
        $qrySel = $this->db->pdoQuery("SELECT  id, types, constant, subject, templates, description, updateDate,status FROM tbl_email_templates " . $whereCond . " order by " . $sorting . " limit " . $offset . " ," . $rows . " ")->results();
        // $totalRow = count($qrySel);
        $totalRow = $this->db->pdoQuery("SELECT  id, types, constant, subject, templates, description, updateDate,status FROM tbl_email_templates  " . $whereCond)->affectedRows();
        foreach ($qrySel as $fetchRes) {
            $id = $fetchRes['id'];
            $status = ($fetchRes['status'] == "y") ? "checked" : "";
            $switch = (in_array('status', $this->Permission)) ? $this->toggel_switch(array("action" => "ajax." . $this->module . ".php?id=" . $fetchRes['id'] . "", "check" => $status)) : '';
            $operation = '';
            $operation .= (in_array('edit', $this->Permission)) ? $this->operation(array("href" => "ajax." . $this->module . ".php?action=edit&id=" . $fetchRes['id'] . "", "class" => "btn default  black btnEdit","extraAtt" => "title = 'Edit'", "value" => '<i class="fa fa-edit"></i>')) : '';

            $final_array = array(
                filtering($fetchRes["id"]),
                filtering($fetchRes["constant"]),
                filtering($fetchRes["types"]),
                filtering($fetchRes["description"])
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
    public function templateSubmit($data,$Permission){
        extract($data);
        $objPost = new stdClass();
        $objPost->subject = isset($subject) ? filtering($subject, 'input') : '';
        $objPost->templates = isset($templates) ? filtering($templates, 'input', 'text') : '';
        $objPost->description = isset($description) ? filtering($description, 'input') : '';

        if ($type == 'edit' && $id > 0) {
            if ($type == 'edit' && $id > 0) {
                $this->db->update("tbl_email_templates", array("subject" => $objPost->subject, "description" => $objPost->description, "templates" => $objPost->templates), array("id" => $id));

                $activity_array = array("id" => $id, "module" => $this->module, "activity" => $type);
                add_admin_activity($activity_array);

                $_SESSION["toastr_message"] = disMessage(array('type' => 'suc', 'var' => 'Email Template updated successfully'));
            } else {
                $toastr_message = $_SESSION["toastr_message"] = disMessage(array('type' => 'err', 'var' => "You don't have permission to edit Email Template"));
            }
        } else {
            if (in_array('add', $Permission)) {
                $objPost->updateDate = date('Y-m-d H:i:s');
                
                $valArray = array(
                    "subject" => $objPost->subject,
                    "description" => $objPost->description,
                    "templates" => $objPost->templates,
                    "updateDate" => $objPost->updateDate
                );
                
                $activity_array = array("id" => $id, "module" => $this->module, "activity" => 'add');
                add_admin_activity($activity_array);

                $this->db->insert("tbl_email_templates", $valArray);
                $_SESSION["toastr_message"] = disMessage(array('type' => 'suc', 'var' => 'Email Template added successfully'));
            } else {
                $toastr_message = $_SESSION["toastr_message"] = disMessage(array('type' => 'err', 'var' => "You don't have permission to add Email Template"));
            }
        }
      redirectPage(SITE_ADM_MOD . $this->module);
    }

}
