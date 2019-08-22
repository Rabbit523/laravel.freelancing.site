<?php

class FAQ extends Home {

    public $question;
    public $answer;
    public $priority;
    public $isActive;
    public $data = array();

    public function __construct($module, $id = 0, $objPost = NULL, $searchArray = array(), $type = '') {
        global $db, $fields, $sessCataId;
        $this->db = $db;
        $this->data['id'] = $this->id = $id;
        $this->fields = $fields;
        $this->module = $module;
        $this->table = 'tbl_faq';

        $this->type = ($this->id > 0 ? 'edit' : 'add');
        $this->searchArray = $searchArray;
        parent::__construct();
        if ($this->id > 0) {
            $qrySel = $this->db->select($this->table, "*", array("id" => $id))->result();
            $fetchRes = $qrySel;

            $this->data['faqCategoryId'] = $this->faqCategoryId = $fetchRes['faqCategoryId'];
            $this->data['question'] = $this->question = $fetchRes['question'];
            //$this->data['ansImage'] = $this->ansImage = $fetchRes['ansImage'];
            $this->data['ansDesc'] = $this->ansDesc = $fetchRes['ansDesc'];
            $this->data['isActive'] = $this->isActive = $fetchRes['isActive'];
            $this->data['CreatedDate'] = $this->CreatedDate = $fetchRes['CreatedDate'];
        } else {
            $this->data['faqCategoryId'] = $this->faqCategoryId = '';
            $this->data['question'] = $this->question = '';
           // $this->data['ansImage'] = $this->ansImage = '';
            $this->data['ansDesc'] = $this->ansDesc = '';            
            $this->data['isActive'] = $this->isActive = 'y';
            $this->data['CreatedDate'] = $this->CreatedDate = '';
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

    public function viewForm() {
        //$img = "<img src='".SITE_UPD."FAQ/".$this->ansImage."' height='100' width='100'>";
        $content = "";
                //$this->displayBox(array("label" => "Question&nbsp;:", "value" => filtering($this->question))) .
        foreach (getLangValues($this->table,$this->id,'question','Question') as $key => $value) {
            $content .= $this->displayBox(array("label" => $value['f_title']."&nbsp;:", "value" => filtering($value['f_value'])));
        }
        $content .=  $this->displayBox(array("label" => "Answer Desc&nbsp;:", "value" => (trim($this->ansDesc) == '')?'--':trim($this->ansDesc))).
        $this->displayBox(array("label" => "Inserted Date &nbsp;:", "value" => date(DATE_FORMAT_ADMIN,strtotime($this->CreatedDate))));
        return $content;
    }

    public function getForm() {
        $content = '';
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/form-sd.skd");
        $main_content = $main_content->compile();
        $static_a = ($this->isActive == 'y' ? 'checked' : '');
        $static_d = ($this->isActive != 'y' ? 'checked' : '');


        $temp = (new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/lang_question-sd.skd"))->compile();
        $lang_question = getLangForm($this->table,$this->id,'question','Question',$temp);

        $temp = (new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/lang_ansDesc-sd.skd"))->compile();
        $lang_ansDesc = getLangForm($this->table,$this->id,'ansDesc','Answer',$temp);

      // if($this->data['ansImage']==""){$class = "hide";}else{$class = "";}
        $category = "";
        $cat_list = $this->db->select("tbl_faq_category","*",array("isActive"=>"y"))->results();
        $category .= "<option selected disabled='disabled'>--select category--</option>";
        foreach ($cat_list as $cat) {
            if($cat['id'] == $this->faqCategoryId){$class="selected";}else{$class="";}
            $category .= "<option value=".$cat['id']." ".$class.">".$cat['categoryName']."</option>";
        }
        
        $fields = array(
            "%CATEGORY%" => $category,
            "%QUESTION%" => filtering($this->data['question']),
            "%LANG_QUESTION%" => $lang_question,
            "%LANG_ANSDESC%" => $lang_ansDesc,
           /* "%IMG%" => SITE_UPD."FAQ/".filtering($this->data['ansImage']),
            "%CLASS%" => $class,
            "%OLD_IMG%" => filtering($this->data['ansImage']),*/
            "%ANS_DESC%" =>  htmlentities($this->data['ansDesc']),
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
        $content = $operation = $whereCond = $totalRow = NULL;
        $result = $tmp_rows = $row_data = array();
        extract($this->searchArray);
        $chr = str_replace(array('_', '%',"'",'"'), array('\_', '\%',"\'",'\"'), $chr);
        // $whereCond = array();
        if (isset($chr) && $chr != '') {
            $whereCond .=" WHERE (question LIKE '%".$chr."%' OR DATE_FORMAT(f.CreatedDate, '" . MYSQL_DATE_FORMAT . "') LIKE '%" . $chr . "%' OR categoryName LIKE '%".$chr."%')";
        }

        if (isset($sort))
            $sorting = $sort . ' ' . $order;
        else
            $sorting = 'f.id DESC';

        // $totalRow = $this->db->count($this->table, $whereCond);
        
        // $qrySel = $this->db->select("tbl_faq", "*", $whereCond, " ORDER BY $sorting limit $offset , $rows")->results();
        $qrySel = $this->db->pdoQuery("SELECT f.*,fc.categoryName FROM tbl_faq f LEFT JOIN tbl_faq_category as fc ON f.faqCategoryId = fc.id " . $whereCond . " ORDER BY " . $sorting. " LIMIT " . $offset . " ," . $rows . " ")->results();
        // exit;
        $totalRow = $this->db->pdoQuery("SELECT f.*,fc.categoryName FROM tbl_faq  f LEFT JOIN tbl_faq_category as fc ON f.faqCategoryId = fc.id" . $whereCond )->affectedRows();
        foreach ($qrySel as $fetchRes) 
        {
            $status = ($fetchRes['isActive'] == "y") ? "checked" : "";

            $switch = (in_array('status', $this->Permission)) ? $this->toggel_switch(array("action" => "ajax." . $this->module . ".php?id=" . $fetchRes['id'] . "", "check" => $status)) : '';
            $operation = '';
            $operation .= (in_array('edit', $this->Permission)) ? $this->operation(array("href" => "ajax." . $this->module . ".php?action=edit&id=" . $fetchRes['id'] . "", "class" => "btn default  black btnEdit","extraAtt" => "title = 'Edit'", "value" => '<i class="fa fa-edit"></i>')) : '';
            $operation .=(in_array('view', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=view&id=" . $fetchRes['id'] . "", "class" => "btn default blue  btn-viewbtn","extraAtt" => "title = 'View'", "value" => '<i class="fa fa-laptop"></i>')) : '';
            $operation .=(in_array('delete', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=delete&id=" . $fetchRes['id'] . "","extraAtt" => "title = 'Delete'", "class" => "btn default  red btn-delete", "value" => '<i class="fa fa-trash-o"></i>')) : '';

            $final_array = array(
                filtering($fetchRes["id"]),
                filtering($fetchRes["question"]),
                filtering($fetchRes["categoryName"]),
                filtering(date(DATE_FORMAT_ADMIN,strtotime($fetchRes["CreatedDate"])))
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

}
