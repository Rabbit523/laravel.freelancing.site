<?php

class Conversation extends Home {

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
        $this->table = 'tbl_messages';

        $this->type = ($this->id > 0 ? 'edit' : 'add');
        $this->searchArray = $searchArray;
        parent::__construct();
        if ($this->id > 0) {
            $qrySel = $this->db->select($this->table, "*", array("id" => $id))->result();
            $fetchRes = $qrySel;

            $this->data['ownerId'] = $this->ownerId = $fetchRes['ownerId'];
            $this->data['senderId'] = $this->senderId = $fetchRes['senderId'];
            $this->data['receiverId'] = $this->receiverId = $fetchRes['receiverId'];
            $this->data['messageDesc'] = $this->messageDesc = $fetchRes['messageDesc'];
            $this->data['createdDate'] = $this->createdDate = $fetchRes['createdDate'];
         
        } else {
            $this->data['ownerId'] = $this->ownerId = '';
            $this->data['senderId'] = $this->senderId = '';
            $this->data['receiverId'] = $this->receiverId = '';
            $this->data['messageDesc'] = $this->messageDesc = '';
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
       /* $content = 
                $this->displayBox(array("label" => "Page Title&nbsp;:", "value" => filtering($this->pageTitle))) .
                $this->displayBox(array("label" => "Met Keyword&nbsp;:", "value" => filtering($this->metaKeyword))) .
                $this->displayBox(array("label" => "Meta Description&nbsp;:", "value" => filtering($this->metaDesc))) .
                $this->displayBox(array("label" => "Page Description&nbsp;:", "value" => filtering($this->pageDesc, 'output', 'text'))).
                $this->displayBox(array("label" => "Page Slug&nbsp;:", "value" => filtering($this->page_slug)));
        return $content;*/
    }

  

    public function dataGrid() {
        $content = $operation = $whereCond = $totalRow = NULL;
        $result = $tmp_rows = $row_data = array();
        extract($this->searchArray);
        $chr = str_replace(array('_', '%',"'",'"'), array('\_', '\%',"\'",'\"'), $chr);
        if (isset($chr) && $chr != '') {
            $whereCond = " where s.userName LIKE '%" . $chr . "%' or r.userName LIKE '%" . $chr . "%'";
        }

        if (isset($sort))
            $sorting = $sort . ' ' . $order;
        else
            $sorting = 'id DESC';
        $qrySel=$this->db->pdoQuery("SELECT tbl_messages.*,
            s.userName as susername,s.lastName as slastName,
            r.userName as rusername,r.lastName as rlastName 
            from tbl_messages 
            join tbl_users as s on (s.id=tbl_messages.senderId) 
            join tbl_users as r on (r.id=tbl_messages.receiverId) 
            $whereCond 
             GROUP by ownerId ORDER BY $sorting limit $offset , $rows ");
             
        $qrySel=$qrySel->results();
        $totalRow=count($qrySel);
        foreach ($qrySel as $fetchRes) {
           
            $operation = '';
            $operation .= (in_array('edit', $this->Permission)) ? $this->operation(array("href" => "ajax." . $this->module . ".php?action=view&senderId=" . $fetchRes['senderId'] . "&receiverId=" . $fetchRes['receiverId'] ."","extraAtt" => "title = 'View'", "class" => "btn default blue btnEdit", "value" => '<i class="fa fa-laptop"></i>')) : '';
            $operation .=(in_array('delete', $this->Permission)) ? '&nbsp;&nbsp;' . $this->operation(array("href" => "ajax." . $this->module . ".php?action=delete&id=" . $fetchRes['ownerId'] . "","extraAtt" => "title = 'Delete'", "class" => "btn default  red btn-delete", "value" => '<i class="fa fa-trash-o"></i>')) : '';
            $final_array = array(
                filtering($fetchRes["id"]),
                filtering(ucfirst($fetchRes['susername'])),
                filtering(ucfirst($fetchRes['rusername'])),
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

    public function conversation($senderId,$receiverId)
    {
        $content = '';
        
        
        $whereCond = "where";
        $whereCond .= " ((senderId='".$senderId."' AND receiverId='".$receiverId."') OR (senderId='".$receiverId."' AND receiverId='".$senderId."'))";

        $message = $this->db->pdoQuery("select * from tbl_messages ".$whereCond."ORDER BY createdDate DESC")->results();

        $content = "";
        foreach ($message as $value) {

            $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/form-sd.skd");
            $main_content = $main_content->compile();
            $user = $this->db->select("tbl_users","*",array("id"=>$value['senderId']))->result();
            
            if($value['senderId'] == $value["ownerId"]){
                $view_class="";                
            }else{
                $view_class="blockquote-reverse";
            }
            
            $data = array(
                "%MSG%" => nl2br(filtering($value['messageDesc'])),
                "%USER%" => $user['firstName']." ".$user['lastName'],
                "%DATE%" => getTime($value['createdDate']),
                "%CLASS%" => $view_class
            );
            $content .= str_replace(array_keys($data), array_values($data), $main_content);
        }
        
       
      
        return sanitize_output($content);
    }
    public function getPageContent() {
        $final_result = NULL;
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/" . $this->module . ".skd");
        $main_content->breadcrumb = $this->getBreadcrumb();
        $final_result = $main_content->compile();
        return $final_result;
    }
}
