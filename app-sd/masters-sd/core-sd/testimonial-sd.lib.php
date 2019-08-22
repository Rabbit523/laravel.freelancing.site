<?php

class Content extends Home {

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
        $this->table = 'tbl_content';

        $this->type = ($this->id > 0 ? 'edit' : 'add');
        $this->searchArray = $searchArray;
        parent::__construct();
        if ($this->id > 0) {
            $qrySel = $this->db->select($this->table, "*", array("pId" => $id))->result();
            $fetchRes = $qrySel;

            $this->data['pageTitle'] = $this->pageTitle = $fetchRes['pageTitle'];
            $this->data['metaKeyword'] = $this->metaKeyword = $fetchRes['metaKeyword'];
            $this->data['metaDesc'] = $this->metaDesc = $fetchRes['metaDesc'];
            $this->data['pageDesc'] = $this->pageDesc = $fetchRes['pageDesc'];
            $this->data['page_slug'] = $this->page_slug = $fetchRes['page_slug'];
            $this->data['isActive'] = $this->isActive = $fetchRes['isActive'];
        } else {
            $this->data['pageTitle'] = $this->pageTitle = '';
            $this->data['metaKeyword'] = $this->metaKeyword = '';
            $this->data['metaDesc'] = $this->metaDesc = '';
            $this->data['pageDesc'] = $this->pageDesc = '';
            $this->data['page_slug'] = $this->page_slug = '';
            $this->data['isActive'] = $this->isActive = 'y';
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
        $content = 
                $this->displayBox(array("label" => "Page Title&nbsp;:", "value" => filtering($this->pageTitle))) .
                $this->displayBox(array("label" => "Met Keyword&nbsp;:", "value" => filtering($this->metaKeyword))) .
                $this->displayBox(array("label" => "Meta Description&nbsp;:", "value" => filtering($this->metaDesc))) .
                $this->displayBox(array("label" => "Page Description&nbsp;:", "value" => filtering($this->pageDesc, 'output', 'text'))).
                $this->displayBox(array("label" => "Page Slug&nbsp;:", "value" => filtering($this->page_slug)));
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
        $result = $tmp_rows = $row_data = array();
        extract($this->searchArray);
        $chr = str_replace(array('_', '%',"'",'"'), array('\_', '\%',"\'",'\"'), $chr);
        $whereCond = array();
        if (isset($chr) && $chr != '') {
            $whereCond = array("pageTitle LIKE" => "%$chr%");
        }

        if (isset($sort))
            $sorting = $sort . ' ' . $order;
        else
            $sorting = 'pId DESC';

        $totalRow = $this->db->count($this->table, $whereCond);
        
        $qrySel = $this->db->select("tbl_content", "*", $whereCond, " ORDER BY $sorting limit $offset , $rows")->results();
        foreach ($qrySel as $fetchRes) {
            $status = ($fetchRes['isActive'] == "y") ? "checked" : "";

            $switch = (in_array('status', $this->Permission)) ? $this->toggel_switch(array("action" => "ajax." . $this->module . ".php?id=" . $fetchRes['pId'] . "", "check" => $status)) : '';
            $operation = '';
            $operation .= (in_array('edit', $this->Permission)) ? $this->operation(array("href" => "ajax." . $this->module . ".php?action=edit&id=" . $fetchRes['pId'] . "", "class" => "btn default  black btnEdit","extraAtt" => "title = 'Edit'","value" => '<i class="fa fa-edit"></i>')) : '';
            $operation .=(in_array('delete', $this->Permission)) ? '&nbsp;&nbsp;' . $this->operation(array("href" => "ajax." . $this->module . ".php?action=delete&id=" . $fetchRes['pId'] . "", "class" => "btn default  red btn-delete","extraAtt" => "title = 'Delete'", "value" => '<i class="fa fa-trash-o"></i>')) : '';
            $operation .=(in_array('view', $this->Permission)) ? '&nbsp;&nbsp;' . $this->operation(array("href" => "ajax." . $this->module . ".php?action=view&id=" . $fetchRes['pId'] . "", "class" => "btn default blue  btn-viewbtn","extraAtt" => "title = 'View'", "value" => '<i class="fa fa-laptop"></i>')) : '';

            $final_array = array(
                filtering($fetchRes["pId"]),
                filtering($fetchRes["pageTitle"]),
                filtering($fetchRes["page_slug"])
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
        $objPost->pageTitle = isset($pageTitle) ? $pageTitle : '';
        
        $objPost->metaKeyword = isset($metaKeyword) ? $metaKeyword : '';
        $objPost->metaDesc = isset($metaDesc) ? $metaDesc : '';
        $objPost->pageDesc = isset($pageDesc) ? $pageDesc : '';
        
        $objPost->isActive = isset($isActive) ? $isActive : 'n';
        
        if ($objPost->pageTitle == "") {
            $response['error'] = "Please enter page title.";
            echo json_encode($response);
            exit;
        }
        
        $objPost->page_slug = Slug($objPost->pageTitle);
        
        if ($type == 'edit' && $id > 0) {

            if (in_array('edit', $Permission)) {

                $objPostArray = (array) $objPost;
                $this->db->update($this->table, $objPostArray, array("pId" => $id));

                $response['status'] = true;
                $response['success'] = "Content Page updated successfully.";
                echo json_encode($response);
                exit;
            } else {
                $response['error'] = "You don't have permission to edit Content Page";
                echo json_encode($response);
                exit;
            }
        } else {
            if (in_array('add', $Permission)) {
                $objPost->createdDate = date("Y-m-d H:i:s");

                $objPostArray = (array) $objPost;
                $id = $this->db->insert($this->table, $objPostArray)->getLastInsertId();

                $response['status'] = true;
                $response['success'] = "Content Page has been added successfully";
                echo json_encode($response);
                exit;
            } else {
                $response['error'] = "You don't have permission to add Content Page";
                echo json_encode($response);
                exit;
            }
        }
    }

}
