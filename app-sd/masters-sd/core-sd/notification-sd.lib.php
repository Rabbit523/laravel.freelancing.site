<?php

class Notification extends Home {

    public $page_name;
    public $page_title;
    public $meta_keyword;
    public $meta_desc;
    public $page_desc;
    public $data = array();

    public function __construct($module, $id = 0, $objPost = NULL, $searchArray = array(), $type = '') {
        global $db, $fields, $sessCataId;
        $this->db = $db;
        $this->data['id'] = $this->id = $id;
        $this->fields = $fields;
        $this->module = $module;
        $this->table = 'tbl_notification';

        $this->type = ($this->id > 0 ? 'edit' : 'add');
        $this->searchArray = $searchArray;
        parent::__construct();
        if ($this->id > 0) {
            $qrySel = $this->db->select($this->table, "*", array("id" => $id))->result();
            $fetchRes = $qrySel;

            $this->data['userId'] = $this->userId = $fetchRes['userId'];
            $this->data['message'] = $this->message = $fetchRes['message'];
            $this->data['isRead'] = $this->isRead = $fetchRes['isRead'];
            $this->data['createdDate'] = $this->createdDate = $fetchRes['createdDate'];
        } else {
            $this->data['userId'] = $this->userId = '';
            $this->data['message'] = $this->message = '';
            $this->data['isRead'] = $this->isRead = '';
            $this->data['createdDate'] = $this->createdDate = '';
        }
        switch ($type) 
        {
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
         

        $content = 
       
                $this->displayBox(array("label" => "Message&nbsp;:", "value" => filtering($this->message, 'output', 'text'))).
                $this->displayBox(array("label" => "Inserted Date&nbsp;:", "value" => filtering(date(DATE_FORMAT_ADMIN,strtotime($this->createdDate)))));
        return $content;
    }

    public function getForm() {
        $content = '';
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/form-sd.skd");
        $main_content = $main_content->compile();
        $static_a = ($this->isActive == 'y' ? 'checked' : '');
        $static_d = ($this->isActive != 'y' ? 'checked' : '');

        $fields = array(
            "%PAGE_TITLE%",
            "%META_KEYWORD%",
            "%META_DESCRIPTION%",
            "%PAGE_DESCRIPTION%",
            "%STATIC_A%",
            "%STATIC_D%",
            "%TYPE%",
            "%ID%"
        );

        $fields_replace = array(
            filtering($this->data['pageTitle']),
            filtering($this->data['metaKeyword']),
            filtering($this->data['metaDesc']),
            filtering($this->data['pageDesc'], 'output', 'text'),
            filtering($static_a),
            filtering($static_d),
            filtering($this->type),
            filtering($this->id, 'input', 'int')
        );

        $content = str_replace($fields, $fields_replace, $main_content);
        return sanitize_output($content);
    }

    public function dataGrid() {
        $content = $operation = $whereCond = $totalRow = NULL;
        $result = $tmp_rows = $row_data = $wArray=array();
        extract($this->searchArray);
        $chr = str_replace(array('_', '%',"'",'"'), array('\_', '\%',"\'",'\"'), $chr);
        $wArray[] = 'a';
        $whereCond .= " notificationType = ? AND userId='0'";
       
        if (!empty($chr)) 
        {
            //$strto=strtotime($chr);
            $whereCond .= " AND (message LIKE ? OR DATE_FORMAT(createdDate, '" . MYSQL_DATE_FORMAT . "') LIKE ? )";
            $wArray[] = "%".$chr."%";
            $wArray[] = "%".$chr."%";
        }

        if (isset($sort))
            $sorting = $sort . ' ' . $order;
        else
            $sorting = 'id DESC';

        // $getUsername = getTableValue('tbl_users', 'userName', array('id'=>$this->userId));

        $qrySel = $this->db->pdoQuery("SELECT *from tbl_notification WHERE $whereCond  ORDER BY $sorting LIMIT $offset, $rows",$wArray)->results();

        $totalRow = $this->db->pdoQuery("SELECT *from tbl_notification WHERE $whereCond  ORDER BY $sorting ",$wArray)->affectedRows();


        foreach ($qrySel as $fetchRes) {

            $operation = '';
            $operation .=(in_array('view', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=view&id=" . $fetchRes['id'] . "", "class" => "btn default blue  btn-viewbtn","extraAtt" => "title = 'View'", "value" => '<i class="fa fa-laptop"></i>')) : '';
            $operation .=(in_array('delete', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=delete&id=" . $fetchRes['id'] . "", "class" => "btn default  red btn-delete","extraAtt" => "title = 'Delete'", "value" => '<i class="fa fa-trash-o"></i>')) : '';

            if ($fetchRes['notificationType'] == 'a') {
               
            
            $final_array = array(
                filtering($fetchRes["id"]),
                filtering($fetchRes["message"]),
                filtering(date(DATE_FORMAT_ADMIN ." h:i A",strtotime($fetchRes["createdDate"]))),
            );
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
