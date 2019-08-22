<?php

class ContactUs extends Home {

    public $category;
    public $status;
    public $data = array();

    public function __construct($module, $id = 0, $objPost = NULL, $searchArray = array(), $type = '') {
        global $db, $fields, $sessCataId;
        $this->db = $db;
        $this->data['id'] = $this->id = $id;
        $this->fields = $fields;
        $this->module = $module;
        $this->table = 'tbl_contact_us';

        $this->type = ($this->id > 0 ? 'edit' : 'add');
        $this->searchArray = $searchArray;
        parent::__construct();
        if ($this->id > 0) {
            $qrySel = $this->db->select('tbl_contact_us', "*", array("id" => $id))->result();
            $fetchRes = $qrySel;
            $this->data['firstName'] = $this->firstName = filtering($fetchRes['firstName']);
            $this->data['lastName'] = $this->lastName = filtering($fetchRes['lastName'], 'output', 'text');
            $this->data['message'] = $this->message = filtering($fetchRes['message']);
            $this->data['replayMessage'] = $this->replayMessage =$fetchRes['replayMessage'];
            $this->data['location'] = $this->location =$fetchRes['location'];
            $this->data['email'] = $this->email = filtering($fetchRes['email']);
            
        } else {
            $this->data['firstName'] = $this->firstName = '';
            $this->data['lastName'] = $this->lastName = '';
            $this->data['message'] = $this->message = '';
            $this->data['replayMessage'] = $this->replayMessage = '';
            $this->data['location'] = $this->location = '';
            $this->data['email'] = $this->email = '';
            
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
                    break;
                }
                
        }
    }

    public function viewForm() {
        /*$content = $this->fields->displayBox(array("label" => "Subject&nbsp;:", "value" => $this->subject)) .
                $content = $this->fields->displayBox(array("label" => "Templates&nbsp;:", "value" => $this->templates));
        return $content;*/
    }

    public function getForm() {
        $content = '';

        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/form-sd.skd");
        $main_content = $main_content->compile();

        $fields = array("%MEND_SIGN%", "%FIRSTNAME%", "%MESSAGE%", "%REPLAYMESSAGE%", "%LASTNAME%", "%EMAIL%", "%ID%",  "%TYPE%" );

        $fields_replace = array(MEND_SIGN, $this->firstName, $this->message,$this->replayMessage,$this->lastName, $this->email, $this->id,$this->type);

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
            $whereCond .= " WHERE email LIKE '%" . $chr . "%' OR (CONCAT_WS(' ', firstName, lastName)) LIKE '%" . $chr . "%' OR message LIKE '%" . $chr . "%' OR replayMessage LIKE '%" . $chr . "%' OR location LIKE '%" . $chr . "%' OR DATE_FORMAT(createdDate,'".MYSQL_DATE_FORMAT."' ) LIKE '%".$chr."%' ";
        }

        if (isset($sort))
            $sorting = $sort . ' ' . $order;
        else
            $sorting = 'id DESC';

        //$totalRow = $this->db->count($this->table, $whereCond);
        $qrySel = $this->db->pdoQuery("SELECT  *
									   FROM tbl_contact_us
									   " . $whereCond . " order by " . $sorting . " limit " . $offset . " ," . $rows . " ")->results();

        $totalRow = count($qrySel);
        foreach ($qrySel as $fetchRes) {
            $id = $fetchRes['id'];
            $operation = '';
            $operation .= (in_array('edit', $this->Permission)) ? $this->operation(array("href" => "ajax." . $this->module . ".php?action=edit&id=" . $fetchRes['id'] . "", "class" => "btn default  black btnEdit", "extraAtt" => "title = 'Reply'","value" => '<i class="fa fa-reply"></i>')) : '';
            
            $operation .=(in_array('delete', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=delete&id=" . $fetchRes['id'] . "","extraAtt" => "title = 'Delete'", "class" => "btn default  red btn-delete", "value" => '<i class="fa fa-trash-o"></i>')) : '';

            $final_array = array(
                filtering($fetchRes["id"]),
                filtering($fetchRes["firstName"]).' '.filtering($fetchRes["lastName"]),
                filtering($fetchRes['email']),
                filtering($fetchRes["message"]),
                filtering($fetchRes["location"] == ''? "--" : $fetchRes["location"]),                
                $fetchRes['replayMessage']!=""?$fetchRes["replayMessage"]:"Not given yet!",
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
    public function contactUsSubmit($data){
        extract($data);
        $objPost = new stdClass();
        $objPost->firstName = isset($firstName) ? filtering($firstName, 'input') : '';
        $objPost->lastName = isset($lastName) ? filtering($lastName, 'input', 'text') : '';
        $objPost->message = isset($message) ? $message: '';
        $objPost->replayMessage = isset($replayMessage) ? nl2br(filtering($replayMessage)): '';
        $objPost->email = isset($email) ? filtering($email, 'input') : '';
       
        if ($type == 'edit' && $id > 0) {
            if ($type == 'edit' && $id > 0) {
                $this->db->update($this->table, array("firstName" => $objPost->firstName, "lastName" => $objPost->lastName, "message" => $objPost->message, "replayMessage" => $objPost->replayMessage, "email" => $objPost->email), array("id" => $id));

                
                $arrayCont = array('greetings'=>$objPost->firstName.' '.$objPost->lastName,'message'=>$objPost->message,'replay'=>$objPost->replayMessage);
               
               $array = generateEmailTemplate('contactus_replay',$arrayCont);
               sendEmailAddress($objPost->email,$array['subject'],$array['message']);  
               
               $activity_array = array("id" => $id, "module" => $this->module, "activity" => $type);
                add_admin_activity($activity_array);  

                $_SESSION["toastr_message"] = disMessage(array('type' => 'suc', 'var' => 'Reply of message sent successfully'));
            } else {
                $toastr_message = $_SESSION["toastr_message"] = disMessage(array('type' => 'err', 'var' => "You don't have permission to sent mail"));
            }
        }
        redirectPage(SITE_ADM_MOD . $this->module);
    }

}
