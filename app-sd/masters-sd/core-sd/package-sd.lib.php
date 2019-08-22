<?php

class Package extends Home {

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
        $this->table = 'tbl_credit_package';

        $this->type = ($this->id > 0 ? 'edit' : 'add');
        $this->searchArray = $searchArray;
        parent::__construct();
        if ($this->id > 0) {
            $qrySel = $this->db->select($this->table, "*", array("id" => $id))->result();
            $fetchRes = $qrySel;
            $this->data['id'] = $this->title = $fetchRes['id'];
            $this->data['title'] = $this->title = $fetchRes['title'];
            $this->data['price'] = $this->price = $fetchRes['price'];
            $this->data['noCredits'] = $this->noCredits = $fetchRes['noCredits'];
            $this->data['isActive'] = $this->isActive = $fetchRes['isActive'];
            $this->data['CreatedDate'] = $this->CreatedDate = $fetchRes['CreatedDate'];
        } else {
            $this->data['title'] = $this->title = '';
            $this->data['title'] = $this->title = '';
            $this->data['price'] = $this->price = '';
            $this->data['noCredits'] = $this->noCredits = '';
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
        
        $content = '';

        foreach (getLangValues($this->table,$this->id,'title','Plan Name') as $key => $value) {
            $content .= $this->displayBox(array("label" => $value['f_title']."&nbsp;:", "value" => filtering($value['f_value'])));
        }

        $content .= $this->displayBox(array("label" => "Plan Price/month&nbsp;:", "value" => (filtering(CURRENCY_SYMBOL.$this->price)))).
                $this->displayBox(array("label" => "No. of Credits user will get&nbsp;:", "value" => (filtering($this->noCredits)))).
                $this->displayBox(array("label" => "Inserted Date &nbsp;:", "value" => date(DATE_FORMAT_ADMIN,strtotime($this->CreatedDate))));
        return $content;
    }

    public function getForm() {
        
        $content = '';
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/form-sd.skd");
        $main_content = $main_content->compile();
        $static_a = ($this->isActive == 'y' ? 'checked' : '');
        $static_d = ($this->isActive != 'y' ? 'checked' : '');
        $title = $this->title;
        $price = $this->price;
        $noCredits = $this->noCredits;

        $temp = (new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/lang_title-sd.skd"))->compile();
        $temp_title = getLangForm($this->table,$this->id,'title','Plan Name',$temp);

        $category = "";
        
        $fields = array(
            "%TEMP_TITLE%" => $temp_title,
            "%PRICE%" => $this->price,
            "%EDIT_PRICE%" => ($this->id==1 && $this->price==0)?"readonly":"",
            "%CREDIT%" => $this->noCredits,
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
        $chr = trim($chr);
        if (isset($chr) && $chr != '') {
            $whereCond .=" WHERE (title LIKE '%".$chr."%' OR price LIKE '%".$chr."%'  OR DATE_FORMAT(CreatedDate, '" . MYSQL_DATE_FORMAT . "') LIKE '%" . $chr . "%')";
        }

        if (isset($sort))
            $sorting = $sort . ' ' . $order;
        else
            $sorting = 'id DESC';

        
        $qrySel = $this->db->pdoQuery("SELECT * from tbl_credit_package " . $whereCond . " ORDER BY " . $sorting. " LIMIT " . $offset . " ," . $rows . " ")->results();
        
        $totalRow = $this->db->pdoQuery("SELECT * from tbl_credit_package " . $whereCond )->affectedRows();
        foreach ($qrySel as $fetchRes) 
        {
            $status = ($fetchRes['isActive'] == "y") ? "checked" : "";

            $switch = (in_array('status', $this->Permission)) ? $this->toggel_switch(array("action" => "ajax." . $this->module . ".php?id=" . $fetchRes['id'] . "", "check" => $status)) : '';
            $operation = '';
            $operation .= (in_array('edit', $this->Permission)) ? $this->operation(array("href" => "ajax." . $this->module . ".php?action=edit&id=" . $fetchRes['id'] . "", "class" => "btn default  black btnEdit","extraAtt" => "title = 'Edit'", "value" => '<i class="fa fa-edit"></i>')) : '';
            $operation .=(in_array('view', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=view&id=" . $fetchRes['id'] . "", "class" => "btn default blue  btn-viewbtn","extraAtt" => "title = 'View'", "value" => '<i class="fa fa-laptop"></i>')) : '';
           
            $final_array = array(
                filtering($fetchRes["id"]),
                filtering($fetchRes["title"]),
                filtering(CURRENCY_SYMBOL.$fetchRes["price"]),
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

    public function contentSubmit($data,$Permission){

        $response = array();
        $response['status'] = false;    
        extract($data);
        $objPost = new stdClass();    
        $objPost->title = isset($title) ? $title : '';
        $objPost->price = isset($price) ? $price : '';
        $objPost->noCredits = isset($noCredits) ? $noCredits : '';
        $objPost->isActive = isset($isActive) ? $isActive : 'n';

        $objPost = setfeilds($objPost,'title');
        
        if ($type == 'edit' && $id > 0) {

            if (in_array('edit', $Permission)) {

                $objPostArray = (array) $objPost;
                $this->db->update($this->table, $objPostArray, array("id" => $id));

                $activity_array = array("id" => $id, "module" => $this->module, "activity" => 'edit');
                add_admin_activity($activity_array);

                $response['status'] = true;
                $response['success'] = "Credit Plan updated successfully";

                $_SESSION["toastr_message"] = disMessage(array('type' => 'suc', 'var' => "Credit Plan updated successfully"));
                
                echo json_encode($response);
                exit;
            } else {
                $response['error'] = "You don't have permission to edit Credit Plan";
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
                $response['success'] = "Credit Plan added successfully";
                echo json_encode($response);
                exit;
            } else {
                $response['error'] = "You don't have permission to add Credit Plan";
                echo json_encode($response);
                exit;
            }
        }
    }

}
