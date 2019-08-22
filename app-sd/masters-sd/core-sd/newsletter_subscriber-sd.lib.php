<?php

class NewsletterSubscriber extends Home {

    public $email;
    public $isActive;
    public $data = array();

    public function __construct($module, $id = 0, $objPost = NULL, $searchArray = array(), $type = '') {
        global $db, $fields, $sessCataId;
        $this->db = $db;
        $this->data['id'] = $this->id = $id;
        $this->fields = $fields;
        $this->module = $module;
        $this->table = 'tbl_newsletter_subscriber';

        $this->type = ($this->id > 0 ? 'edit' : 'add');
        $this->searchArray = $searchArray;
        parent::__construct();
        if ($this->id > 0) {

            $qrySel = $this->db->select($this->table, "*", array("id" => $id))->result();
            foreach ($qrySel as $key => $value) {
                $this->$key = $value;
            }
        } else {
            $this->isActive = 'y';
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
            case 'send_mail' : {
                $this->data['content'] = (in_array('send_newsletter', $this->Permission)) ? $this->sendemail() : '';
                break;
            }	
            case 'datagrid' : {
                    $this->data['content'] = json_encode($this->dataGrid());
                    break;
                }
        }
    }

    public function sendemail() {
        $subscribers = $content_type = NULL;
        $main_content = (new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/email-sd.skd"))->compile();

        $subscribers = $this->db->pdoQuery("SELECT newsletter_name FROM tbl_newsletters WHERE status = ?", array('a'))->results();
        if(!empty($subscribers)) {
            foreach($subscribers as $key => $value) {
                $content_type .= '<option value="'.$value['newsletter_name'].'">'.($value['newsletter_name']).'</option>';
            }
        }

        $fields_repalce = array(
            "%CONTENT%" => $content_type,
            "%ID%" => $this->id
        );

        $content = str_replace(array_keys($fields_repalce), array_values($fields_repalce), $main_content);
        return sanitize_output($content);
    }

    public function dataGrid() {



        $content = $operation = $whereCond = $totalRow = NULL;
        $result = $tmp_rows = $row_data = array();
        extract($this->searchArray);
        $chr = str_replace(array('_', '%',"'",'"'), array('\_', '\%',"\'",'\"'), $chr);
        
        if (isset($chr) && $chr != '') {
            $whereCond .=  " WHERE (email LIKE '%".$chr."%' OR ipAddress LIKE '%".$chr."%' OR DATE_FORMAT(subscribed_on, '" . MYSQL_DATE_FORMAT . "') LIKE '%".$chr."%')";
        }

        if (isset($sort))
            $sorting = $sort . ' ' . $order;
        else
            $sorting = 'id';

        // $totalRow = $this->db->count($this->table, $whereCond);

        // $qrySel = $this->db->select("tbl_newsletter_subscriber", "*", $whereCond, " ORDER BY $sorting limit $offset , $rows")->results();
        $qrySel =$this->db->pdoQuery("SELECT * FROM tbl_newsletter_subscriber $whereCond ORDER BY $sorting LIMIT $offset , $rows ")->results();
         $totalRow =$this->db->pdoQuery("SELECT * FROM tbl_newsletter_subscriber  $whereCond")->affectedRows();
        foreach ($qrySel as $fetchRes) {
            $status = ($fetchRes['isActive'] == "y") ? "checked" : "";

            $switch = (in_array('status', $this->Permission)) ? $this->toggel_switch(array("action" => "ajax." . $this->module . ".php?id=" . $fetchRes['id'] . "", "check" => $status)) : '';
            $operation = '';


            $operation .=(in_array('delete', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=delete&id=" . $fetchRes['id'] . "","extraAtt" => "title = 'Delete'", "class" => "btn default red btn-delete", "value" => '<i class="fa fa-trash-o"></i>', "title"=>"Delete")) : '';

            $operation .= (in_array('send_newsletter', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=send_mail&id=" . $fetchRes['id'] . "", "class" => "btn default black btnEdit", "value" => '<i class="fa fa-paper-plane"></i>',"extraAtt" => "title = 'Send Mail'")) : '';

            $final_array = array(
                filtering($fetchRes["id"]),
                filtering($fetchRes["email"]),
                filtering($fetchRes["ipAddress"]),
                filtering(date(DATE_FORMAT_ADMIN,strtotime($fetchRes["subscribed_on"])))
            );
            //print_r(filtering($fetchRes["ipAddress"]));
            
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

    public function displaybox($text) {

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

    public function getPageContent() {
        $final_result = NULL;
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/" . $this->module . ".skd");
        $main_content->breadcrumb = $this->getBreadcrumb();
        $final_result = $main_content->compile();
        return $final_result;
    }

    public function getForm() {
        $content = '';
        $content_type = '';
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/form-sd.skd");
        $main_content = $main_content->compile();

        $fields = '';
        $qrysel = $this->db->select($this->table,"*")->results();
        foreach($qrysel as $sel){
            $main_content1 = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/selectbox-sd.skd");
            $html= $main_content1->compile();

            $fields = array("%VALUE%","%DISPLAY_VALUE%");
            $value = array($sel['email'],$sel['email']);
            $content_type .= str_replace($fields, $value, $html);
        }

        $fields_replace = '';
        $fields = array("%CONTENT%","%ID%");

        $fields_replace = array($content_type,$this->id);

        $content = str_replace($fields, $fields_replace, $main_content);
        return sanitize_output($content);
    }

    public function contentSubmit($data,$Permission){

        $response = array();
        $response['status'] = false;

        /*extract($data);

        if (in_array('add', $Permission)) {

            if(isset($emails))
            {
                foreach ($emails as $key => $email) {
                    $arrayCont = array('greetings'=>$email,'message'=>'Simple message','newsletter_content'=>'You Have been Subscribed');
                    sendEmailAddress($email, 'newsletter', $arrayCont);
                }
            }

            $response['status'] = true;
            $response['success'] = "Email sent successfully.";
            echo json_encode($response);
            exit;
        } else {
                $response['error'] = "You don't have permission to send mail";
                echo json_encode($response);
                exit;
        }*/

        extract($_POST);
        $newsletter = $this->db->pdoQuery("SELECT email FROM tbl_newsletter_subscriber WHERE id= $id")->result();
        if(count($newsletter['email'])>0)
        {
            foreach ($newsletter_name as $value) {
              
                $newsletterTemplate = $this->db->select('tbl_newsletters', array('newsletter_content', 'newsletter_subject'), array('newsletter_name'=>$value))->result();
                $name = getTablevalue('tbl_users', 'firstName', array('email'=>$newsletter['email']));
                $name = (!empty($name) ? $name : 'There');
                $arrayCont = array('greetings' => $name,'newsletter_content'=>$newsletterTemplate['newsletter_content'],'newsletter_subject'=>filtering($newsletterTemplate['newsletter_subject'],"output"));
                $array = generateEmailTemplate('newsletter',$arrayCont);
                
                sendEmailAddress($newsletter['email'],filtering($array['subject'],"output"),$array['message']);

            }
                $activity_array = array("id" => $id, "module" => $this->module, "activity" => 'send');
                add_admin_activity($activity_array);
                $responce['status'] = true;
            $responce['success'] = 'Newsletter sent successfully'; 
        }else{
            $responce['status'] = false;
                    $responce['success'] = 'There seems to be an issue to sending NewsLetter, Please try again';
        }
        echo json_encode($responce);
        exit;

    }
}