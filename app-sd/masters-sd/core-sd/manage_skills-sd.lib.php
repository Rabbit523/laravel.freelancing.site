<?php

class Skills extends Home
{

    public $isActive;
    public $data = array();

    public function __construct($module, $id = 0, $objPost = NULL, $searchArray = array(), $type = '')
    {
        global $db, $fields, $sessCataId;
        $this->db = $db;
        $this->data['id'] = $this->id = $id;
        $this->fields = $fields;
        $this->module = $module;
        $this->table = 'tbl_skills';

        $this->type = ($this->id > 0 ? 'edit' : 'add');
        $this->searchArray = $searchArray;
        parent::__construct();
        if ($this->id > 0)
        {
            $qrySel = $this->db->select($this->table, "*", array("id" => $id))->result();
            $fetchRes = $qrySel;

            $this->data['skill_name'] = $this->skill_name = $fetchRes['skill_name'];
            $this->data['category_ids'] = $this->category_ids = $fetchRes['category_ids'];
            $this->data['isActive'] = $this->isActive = $fetchRes['isActive'];

            $this->category_ids = !empty($this->category_ids) ? json_decode($this->category_ids,true) : [];
        }
        else
        {
            $this->data['skill_name'] = $this->skill_name = '';
            $this->data['category_ids'] = $this->category_ids = '';
            $this->data['isActive'] = $this->isActive = 'y';
            $this->category_ids = !empty($this->category_ids) ? json_decode($this->category_ids,true) : [];
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

        $content = "";
        /*$this->displayBox(array("label" => "Skill Name&nbsp;:", "value"=>filtering($this->skill_name))).*/

        foreach (getLangValues($this->table,$this->id,'skill_name','Skill Name') as $key => $value) {
            $content .= $this->displayBox(array("label" => $value['f_title']."&nbsp;:", "value" => filtering($value['f_value'])));
        }
        $content .= $this->displayBox(array("label" => "Status&nbsp;:", "value" => $this->isActive == 'y' ? 'Active' : 'Deactive'));

        return $content;


    }

    public function getForm()
    {
        $content = '';
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/form-sd.skd");
        $main_content = $main_content->compile();
        $static_a = ($this->isActive == 'y' ? 'checked' : '');
        $static_d = ($this->isActive != 'y' ? 'checked' : '');


        $temp = (new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/lang_skill_name-sd.skd"))->compile();
        $lang_skill_name = getLangForm($this->table,$this->id,'skill_name','Skill Name',$temp);


        $option_list = $this->db->pdoQuery('SELECT * FROM `tbl_category` WHERE (category_type = "j" or category_type = "b")  AND isActive="y" AND isDelete="n" ')->results();

        $categories = '';

        $this->category_ids = is_array($this->category_ids) ? $this->category_ids : [];

        foreach ($option_list as $key => $value) {
                $categories .= '<option value="'.$value['id'].'" '.(in_array($value['id'],$this->category_ids) ? 'selected="selected"' : '').' > '.$value['category_name'].' </option>';            
        } 

        //echo ;

        $fields = array(
            "%SKILL_NAME%" =>  filtering($this->data['skill_name']),
            "%LANG_SKILL_NAME%" => $lang_skill_name,
            "%STATIC_A%" => filtering($static_a),
            '%CATEGORIES%' => $categories,
            "%STATIC_D%" => filtering($static_d),
            "%TYPE%" => filtering($this->type),
            "%ID%" =>  filtering($this->id, 'input', 'int')
            );

        $content = str_replace(array_keys($fields), array_values($fields), $main_content);
        return sanitize_output($content);
    }

    public function dataGrid()
    {
        $content = $operation = $whereCond = $totalRow = NULL;
        $result = $tmp_rows = $row_data = array();
        extract($this->searchArray);
        $chr = str_replace(array('_', '%'), array('\_', '\%'), $chr);
        $whereCond = array();
        if (isset($chr) && $chr != '')
        {
            $whereCond = array("skill_name LIKE" => "%$chr%");
        }

        if (isset($sort))
            $sorting = $sort . ' ' . $order;
        else
            $sorting = 'id DESC';

        $totalRow = $this->db->count($this->table, $whereCond);

        $qrySel = $this->db->select("tbl_skills", "*", $whereCond, " ORDER BY $sorting limit $offset , $rows")->results();
        foreach ($qrySel as $fetchRes) {
            $status = ($fetchRes['isActive'] == "y") ? "checked" : "";

            $switch = (in_array('status', $this->Permission)) ? $this->toggel_switch(array("action" => "ajax." . $this->module . ".php?id=" . $fetchRes['id'] . "", "check" => $status)) : '';



            $isDeleted = ($fetchRes['isDelete'] == "y") ? "isDeleted" : "";

            if($fetchRes['isDelete'] == 'y')
            {
                $status = ($fetchRes['isDelete'] == "y") ? "" : "checked";
                $switch = (in_array('status', $this->Permission)) ? $this->toggel_switch(array("action" => "ajax." . $this->module . ".php?id=" . $fetchRes['id'] . "","class" => $isDeleted, "check" => $status,"deleteskill"=>"y")) : '';
                $operation =(in_array('undo', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=undo&id=" . $fetchRes['id'] . "","extraAtt" => "title = 'Undo'", "class" => "btn default btn-info btn-undo", "value" => '<i class="fa fa-reply"></i>')) : '';
            }
            else
            {
                $switch = (in_array('status', $this->Permission)) ? $this->toggel_switch(array("action" => "ajax." . $this->module . ".php?id=" . $fetchRes['id'] . "","class" => $isDeleted, "check" => $status)) : '';
                $operation = '';
                $operation .= (in_array('edit', $this->Permission)) ? $this->operation(array("href" => "ajax." . $this->module.".php?action=edit&id=" . $fetchRes['id'] . "", "extraAtt" => "title = 'Edit'", "class" => "btn default black btnEdit", "value" => '<i class="fa fa-edit"></i>')) : '';
                $operation .=(in_array('delete', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=delete&id=" . $fetchRes['id'] . "", "extraAtt" => "title = 'Delete'", "class" => "btn default  red btn-delete", "value" => '<i class="fa fa-trash-o"></i>')) : '';
                $operation .=(in_array('view', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=view&id=" . $fetchRes['id'] . "", "extraAtt" => "title = 'View'", "class" => "btn default blue btn-viewbtn", "value" => '<i class="fa fa-laptop"></i>')) : '';
                if($fetchRes['isApproved']=='n')
                {
                    $operation .= (in_array('status', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=approveStatus&id=" . $fetchRes['id'] . "", "class" => "btn default black btn-approve", "value" => '<i class="fa fa-check"></i>', "title"=>"Approve")) : '' ;
                }
            }

            $final_array = array(
                filtering($fetchRes["id"]),
                filtering($fetchRes["skill_name"])
                );
            if (in_array('status', $this->Permission))
            {
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

    public function toggel_switch($text)
    {
        $text['action'] = isset($text['action']) ? $text['action'] : 'Enter Action Here: ';
        $text['check'] = isset($text['check']) ? $text['check'] : '';
        $text['name'] = isset($text['name']) ? $text['name'] : '';
        $text['class'] = isset($text['class']) ? '' . trim($text['class']) : '';
        $text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';
        if(!empty($text['deleteskill'])){
            if(empty($text['check']) && $text['deleteskill']=='y'){
                                    $disabledSwitch='disabled';
                                }
        }

        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/switch-sd.skd');
        $main_content = $main_content->compile();
        $fields = array("%NAME%", "%CLASS%", "%ACTION%", "%EXTRA%", "%CHECK%");
        $fields_replace = array($text['name'], $text['class'], $text['action'], $text['extraAtt'], $text['check']);
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
        return $final_result;
    }
    public function contentSubmit($data,$Permission)
    {

        $response = array();
        $response['status'] = false;
        extract($data);

        $objPost = new stdClass();
        $objPost->skill_name = isset($skill_name) ? filtering(ucfirst($skill_name)) : '';
        $objPost->isActive = isset($isActive) ? $isActive : 'n';
        $objPost->category_ids = !empty($category_ids) ? json_encode($category_ids): '';

        if ($objPost->skill_name == "")
        {
            $response['error'] = "Please enter your skill name";
            echo json_encode($response);
            exit;
        }
        $objPost = setfeilds($objPost,'skill_name');

        if ($type == 'edit' && $id > 0)
        {
            if (in_array('edit', $Permission))
            {
                $qry =$this->db->pdoQuery("SELECT skill_name FROM tbl_skills where skill_name = '".$objPost->skill_name."' AND id!=$id")->affectedRows();

                if($qry <= 0)
                {
                    $objPostArray = (array) $objPost;
                    $this->db->update($this->table, $objPostArray, array("id" => $id));

                    $response['status'] = true;
                    $response['success'] = "Skill has been updated successfully.";
                    $activity_array = array("id" => $id, "module" => $this->module, "activity" => 'edit', "action" => "edit");
                     add_admin_activity($activity_array);
                     echo json_encode($response);
                    exit;
                }
                else
                {
                    $response['status'] = false;
                    $response['error'] = "This record already exists.";
                    echo json_encode($response);
                    exit;
                }
            }
            else
            {
                $response['error'] = "You don't have permission.";
                echo json_encode($response);
                exit;
            }
        }
        else
        {
            if (in_array('add', $Permission))
            {
                $qry =$this->db->pdoQuery("SELECT skill_name FROM tbl_skills where skill_name='".$objPost->skill_name."'")->affectedRows();

                if($qry > 0)
                {
                    $response['status'] = false;
                    $response['error'] = "This record already exists.";
                    echo json_encode($response);
                    exit;
                }
                else
                {
                    $objPostArray = (array) $objPost;
                    $id = $this->db->insert($this->table, $objPostArray)->getLastInsertId();
                    $response['status'] = true;
                    $response['success'] = "Skill has been added successfully.";
                    $activity_array = array("id" => $id, "module" => $this->module, "activity" => 'add', "action" => "add");
                     add_admin_activity($activity_array);
                    echo json_encode($response);
                    exit;
                }
            }
            else
            {
                $response['error'] = "You don't have permission.";
                echo json_encode($response);
                exit;
            }
        }
    }
}