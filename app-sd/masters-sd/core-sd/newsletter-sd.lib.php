<?php

class NewsLetter extends Home {

    public $data = array();

    public function __construct($module, $id = 0, $objPost = NULL, $searchArray = array(), $type = '') {
        global $db, $fields, $sessCataId;
        $this->db = $db;
        $this->data['id'] = $this->id = $id;
        $this->fields = $fields;
        $this->module = $module;
        $this->table = 'tbl_newsletters';

        $this->type = ($this->id > 0 ? 'edit' : 'add');
        $this->searchArray = $searchArray;

        parent::__construct();
        if ($this->id > 0) 
        {
            $qrySel = $this->db->select($this->table, "*", array("id" => $id))->result();
            $fetchRes = $qrySel;
            $this->data['newsletter_name'] = $this->newsletter_name = filtering($fetchRes['newsletter_name']);
            $this->data['newsletter_subject'] = $this->newsletter_subject = $fetchRes['newsletter_subject'];
            $this->data['newsletter_content'] = $this->newsletter_content = $fetchRes['newsletter_content'];
            $this->data['added_on'] = $this->added_on = filtering($fetchRes['added_on']);
            $this->data['updated_on'] = $this->updated_on = filtering($fetchRes['updated_on']);
            $this->data['status'] = $this->status = filtering($fetchRes['status']);
        } 
        else 
        {
            $this->data['newsletter_name'] = $this->newsletter_name = '';
            $this->data['newsletter_subject'] = $this->newsletter_subject = '';
            $this->data['newsletter_content'] = $this->newsletter_content = '';
            $this->data['subscriber_id'] = $this->subscriber_id = '';
            $this->data['added_on'] = $this->added_on = '';
            $this->data['updated_on'] = $this->updated_on = '';
            $this->data['status'] = $this->status = 'a';
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
            case 'send_mail' : {
                $this->data['content'] = (in_array('send_newsletter', $this->Permission)) ? $this->sendemail() : '';
                break;
            }
            case 'datagrid' : {
                    $this->data['content'] = (in_array('module', $this->Permission)) ? json_encode($this->dataGrid()) : '';
                }
        }
    }

    public function sendemail() {
        $subscribers = $content_type = NULL;
        $main_content = (new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/email-sd.skd"))->compile();

        $subscribers = $this->db->pdoQuery("SELECT email FROM tbl_newsletter_subscriber WHERE isActive = ?", array('y'))->results();
        if(!empty($subscribers)) {
            foreach($subscribers as $key => $value) {
                $content_type .= '<option value="'.$value['email'].'">'.($value['email']).'</option>';
            }
        }

        $fields_repalce = array(
            "%CONTENT%" => $content_type,
            "%ID%" => $this->id
        );

        $content = str_replace(array_keys($fields_repalce), array_values($fields_repalce), $main_content);
        return sanitize_output($content);
    }

    public function viewForm()
    {
         $content = $this->displayBox(array("label" => "NewsLetter Name&nbsp;:", "value" => filtering($this->newsletter_name))).
         $this->displayBox(array("label" => "NewsLetter Subject &nbsp;:", "value" => $this->newsletter_subject)).
         $this->displayBox(array("label" => "NewsLetter Content &nbsp;:", "value" =>trim($this->newsletter_content))).
         $this->displayBox(array("label" => "Updated Date &nbsp;:", "value" => filtering(date(DATE_FORMAT_ADMIN,strtotime($this->updated_on)))));
        return $content;
    }
    public function getForm() {
        $content = '';

        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/form-sd.skd");
        $main_content = $main_content->compile();

        $static_a = ($this->status == 'a' ? 'checked' : '');
        $static_d = ($this->status != 'a' ? 'checked' : '');

        $subscriber_detail = $this->db->select("tbl_newsletter_subscriber", "*", "ORDER BY id DESC")->results();
        $subscribers = '';
        $i=1;

        $fields = array(
            "%NEWS_NAME%",
            "%NEWS_SUBJECT%",
            "%CONTENT%",
            "%STATIC_A%",
            "%STATIC_D%",
            "%SUBSCRIBERS%",
            "%TYPE%",
            "%ID%"
            );

        $fields_replace = array(
            filtering($this->data['newsletter_name']),
            filtering($this->data['newsletter_subject']),
            trim($this->data['newsletter_content']),
            filtering($static_a),
            filtering($static_d),
            $subscribers,
            $this->type,
            $this->id
            );

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
            $whereCond .= " WHERE newsletter_name LIKE '%" . $chr . "%' OR newsletter_subject LIKE '%" . $chr . "%' ";
        }

        if (isset($sort))
            $sorting = $sort . ' ' . $order;
        else
            $sorting = 'id DESC';

        //$totalRow = $this->db->count($this->table, $whereCond);
        $qrySel = $this->db->pdoQuery("SELECT  * FROM tbl_newsletters
                                       " . $whereCond . " order by " . $sorting . " limit " . $offset . " ," . $rows . " ")->results();
        $totalRow = count($qrySel);
        foreach ($qrySel as $fetchRes) {
            $id = $fetchRes['id'];
            $status = ($fetchRes['status'] == "a") ? "checked" : "";
            $switch = (in_array('status', $this->Permission)) ? $this->toggel_switch(array("action" => "ajax." . $this->module . ".php?id=" . $fetchRes['id'] . "", "check" => $status)) : '';
            $operation = '';

            $operation .= (in_array('edit', $this->Permission)) ? $this->operation(array("href" => "ajax." . $this->module . ".php?action=edit&id=" . $fetchRes['id'] . "", "class" => "btn default black btnEdit","extraAtt" => "title = 'Edit'","value" => '<i class="fa fa-edit"></i>', "title"=>"Edit")) : '';
            $operation .=(in_array('view', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=view&id=" . $fetchRes['id'] . "", "class" => "btn default blue btn-viewbtn","extraAtt" => "title = 'View'", "value" => '<i class="fa fa-laptop"></i>', "title"=>"View")) : '';

            $operation .=(in_array('delete', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=delete&id=" . $fetchRes['id'] . "", "class" => "btn default red btn-delete","extraAtt" => "title = 'Delete'", "value" => '<i class="fa fa-trash-o"></i>')) : '';

            $operation .= (in_array('send_newsletter', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=send_mail&id=" . $fetchRes['id'] . "", "class" => "btn default black btnEdit", "value" => '<i class="fa fa-paper-plane"></i>',"extraAtt" => "title = 'Send Mail'")): '';

            $final_array = array(
                filtering($fetchRes["id"]),
                filtering($fetchRes["newsletter_name"]),
                filtering($fetchRes["newsletter_subject"])
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

    public function displayBox($text) {

        $text['label'] = isset($text['label']) ? $text['label'] : 'Enter Text Here: ';
        $text['value'] = isset($text['value']) ? $text['value'] : '';
        $text['name'] = isset($text['name']) ? $text['name'] : '';
        $text['class'] = isset($text['class']) ? 'form-control-static ' . trim($text['class']) : 'form-control-static';
        $text['onlyField'] = isset($text['onlyField']) ? $text['onlyField'] : false;
        $text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';

        $main_content = new MainTemplater(DIR_ADMIN_TMPL . '/displaybox.skd');
        $main_content = $main_content->compile();
        $fields = array("%LABEL%", "%CLASS%", "%VALUE%");
        $fields_replace = array($text['label'], $text['class'], $text['value']);
        return str_replace($fields, $fields_replace, $main_content);
    }

    public function getPageContent() {
        $final_result = NULL;
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/" . $this->module . ".skd");
        $main_content->breadcrumb = $this->getBreadcrumb();
        $final_result = $main_content->compile();
        return $final_result;
    }
    
}
  