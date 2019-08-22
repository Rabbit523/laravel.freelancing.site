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
            $this->data['page_content_type'] = $this->page_content_type = $fetchRes['page_content_type'];
            $this->data['pageTitle'] = $this->pageTitle = $fetchRes['pageTitle'];
            $this->data['metaTitle'] = $this->metaTitle = $fetchRes['metaTitle'];
            $this->data['metaKeyword'] = $this->metaKeyword = $fetchRes['metaKeyword'];
            $this->data['metaDesc'] = $this->metaDesc = $fetchRes['metaDesc'];
            $this->data['pageDesc'] = $this->pageDesc = $fetchRes['pageDesc'];
            $this->data['page_slug'] = $this->page_slug = $fetchRes['page_slug'];
            $this->data['isActive'] = $this->isActive = $fetchRes['isActive'];
            $this->data['createdDate'] = $this->createdDate = $fetchRes['createdDate'];
            $this->data['displayInFooter'] = $this->displayInFooter = $fetchRes['displayInFooter'];
            $this->data['displayInHeader'] = $this->displayInHeader = $fetchRes['displayInHeader'];
        } else {
            $this->data['page_content_type'] = $this->page_content_type = '';
            $this->data['pageTitle'] = $this->pageTitle = '';
            $this->data['metaTitle'] = $this->metaTitle = '';
            $this->data['metaKeyword'] = $this->metaKeyword = '';
            $this->data['metaDesc'] = $this->metaDesc = '';
            $this->data['pageDesc'] = $this->pageDesc = '';
            $this->data['page_slug'] = $this->page_slug = '';
            $this->data['isActive'] = $this->isActive = 'y';
            $this->data['createdDate'] = $this->createdDate = '';
            $this->data['displayInFooter'] = $this->displayInFooter = '';
            $this->data['displayInHeader'] = $this->displayInHeader = '';
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
          $page_content = $this->db->pdoQuery("select * from tbl_content where pId = ?",array($this->id))->results();

          // $page_content = $this->db->select($this->table, "*" )->result();
          foreach ($page_content as $key => $value) {
                $this->$key = $value;
            }

        // foreach (getLangValues($this->table,$this->id,'page_title','page_title') as $key => $value) {
        //     $content .= $this->displayBox(array("label" => $value['f_title']."&nbsp;:", "value" => filtering($value['f_value'])));
        // }

        
                
        // $content = "";
        //     $this->displayBox(array("label" => "Page Title&nbsp;:", "value" => filtering($this->pageTitle))) .

       
        $content .= 

            $this->displayBox(array("label" => "Meta Title&nbsp;:", "value" => filtering($this->metaTitle) == '' ? '--': filtering($this->metaTitle))) .
            $this->displayBox(array("label" => "Meta Keyword&nbsp;:", "value" => filtering($this->metaKeyword) == '' ? '--': filtering($this->metaKeyword))) .
            $this->displayBox(array("label" => "Meta Description&nbsp;:", "value" => filtering($this->metaDesc)== '' ? '--': filtering($this->metaDesc))) .
            $this->displayBox(array("label" => "Page Description&nbsp;:", "value" => $this->pageDesc)).
            $this->displayBox(array("label" => "Page Slug&nbsp;:", "value" => filtering($this->page_slug))).
            $this->displayBox(array("label" => "View In Footer&nbsp;:", "value" => filtering($this->displayInFooter=='y'?'Yes':'No'))).
            $this->displayBox(array("label" => "View In Header&nbsp;:", "value" => filtering($this->displayInHeader=='y'?'Yes':'No'))).
            $this->displayBox(array("label" => "title&nbsp;:", "value" => filtering($this->pageTitle, 'output', 'text'))).
            $this->displayBox(array("label" => "Inserted Date&nbsp;:", "value" => filtering(date(DATE_FORMAT_ADMIN,strtotime($this->createdDate)))));
             return $content;
        
       
    }
    public function getForm() {
        $content = '';
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/form-sd.skd");
        $main_content = $main_content->compile();
        $static_a = ($this->isActive == 'y' ? 'checked' : '');
        $static_d = ($this->isActive != 'y' ? 'checked' : '');
        $footer_check=($this->displayInFooter=='y')?'checked':'';
        $header_check=($this->displayInHeader=='y')?'checked':'';

        $temp = (new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/lang_pageTitle-sd.skd"))->compile();
        $lang_pageTitle = getLangForm($this->table,$this->id,'pageTitle','Page Title',$temp,'pId = '.$this->id);

        $temp = (new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/lang_pageDesc-sd.skd"))->compile();
        $lang_pageDesc = getLangForm($this->table,$this->id,'pageDesc','Page Description',$temp,'pId = '.$this->id);

        $fields = array(
            '%PAGE_SLUG%',
            "%PAGE_TITLE%",
            "%LANG_PAGETITLE%",
            "%META_TITLE%",
            "%META_KEYWORD%",
            "%META_DESCRIPTION%",
            "%PAGE_DESCRIPTION%",
            "%LANG_PAGEDESC%",
            "%TOTAL_META_KEYWORD%",
            "%TOTAL_META_DESC%",            
            "%STATIC_A%",
            "%STATIC_D%",
            "%FOOTER_CHECK%",
            "%HEADER_CHECK%",
            "%TYPE%",
            "%ID%","%CONTENT_TYPE%"
        );
        $fields_replace = array(
            filtering($this->data['page_slug']),
            filtering($this->data['pageTitle']),
            $lang_pageTitle,
            filtering($this->data['metaTitle']),
            filtering($this->data['metaKeyword']),
            filtering($this->data['metaDesc']),
            filtering($this->data['pageDesc'], 'output', 'text'),
            $lang_pageDesc,
            160-strlen($this->data['metaKeyword']),
            160-strlen($this->data['metaDesc']),
            filtering($static_a),
            filtering($static_d),
            $footer_check,
            $header_check,
            filtering($this->type),
            filtering($this->id, 'input', 'int'),
            filtering($this->data['page_content_type']),
        );
        $content = str_replace($fields, $fields_replace, $main_content);
        return sanitize_output($content);
    }
    public function dataGrid() {
        $content = $operation = $whereCond = $totalRow = NULL;
        $result = $tmp_rows = $row_data = array();
        extract($this->searchArray);
        $chr = str_replace(array('_', '%',"'",'"'), array('\_', '\%',"\'",'\"'), $chr);
        $whereArray = array();
        if (isset($chr) && $chr != '') {
            $whereCond = 'where (pageTitle LIKE "%'.$chr.'%" or page_slug LIKE "%'.$chr.'%")';
        }
        if (isset($sort))
            $sorting = $sort . ' ' . $order;
        else
            $sorting = 'pId DESC';
        $totalRow = $this->db->pdoQuery('select * from tbl_content '.$whereCond)->affectedRows();
        
        $qrySel = $this->db->pdoQuery("select * from tbl_content $whereCond order by $sorting limit $offset , $rows")->results();
        foreach ($qrySel as $fetchRes) {
            $status = ($fetchRes['isActive'] == "y") ? "checked" : "";
            $switch = (in_array('status', $this->Permission)) ? $this->toggel_switch(array("action" => "ajax." . $this->module . ".php?id=" . $fetchRes['pId'] . "", "check" => $status)) : '';
            $operation = '';
            $operation .= (in_array('edit', $this->Permission)) ? $this->operation(array("href" => "ajax." . $this->module . ".php?action=edit&id=" . $fetchRes['pId'] . "", "class" => "btn default  black btnEdit","extraAtt" => "title = 'Edit'", "value" => '<i class="fa fa-edit"></i>')) : '';
 $operation .=(in_array('view', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=view&id=" . $fetchRes['pId'] . "", "class" => "btn default blue  btn-viewbtn","extraAtt" => "title = 'View'", "value" => '<i class="fa fa-laptop"></i>')) : '';
            $operation .=(in_array('delete', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=delete&id=" . $fetchRes['pId'] . "", "extraAtt" => "title = 'Delete'","class" => "btn default  red btn-delete", "value" => '<i class="fa fa-trash-o"></i>')) : '';
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
        $objPost->page_slug = isset($page_slug) ? $page_slug : '';
        $objPost->metaTitle = isset($metaTitle) ? $metaTitle : '';
        $objPost->metaKeyword = isset($metaKeyword) ? $metaKeyword : '';
        $objPost->metaDesc = isset($metaDesc) ? $metaDesc : '';
        $objPost->pageDesc = isset($pageDesc) ? $pageDesc : '';        
        $objPost->isActive = isset($isActive) ? $isActive : 'n';
        $objPost->displayInFooter = isset($displayInFooter) ? $displayInFooter : 'n';        
        $objPost->displayInHeader = isset($displayInHeader) ? $displayInHeader : 'n';        
        if ($objPost->pageTitle == "") {
            $response['error'] = "Please enter Page Title";
            echo json_encode($response);
            exit;
        }

        $objPost = setfeilds($objPost,'pageTitle');
        $objPost = setfeilds($objPost,'pageDesc');

        if ($type == 'edit' && $id > 0) {
            if (in_array('edit', $Permission)) {
                $objPostArray = (array) $objPost;
                $this->db->update($this->table, $objPostArray, array("pId" => $id));
                $activity_array = array("id" => $id, "module" => $this->module, "activity" => $type,"action"=>"e");
                add_admin_activity($activity_array);
                $response['status'] = true;
                $response['success'] = "Content Page updated successfully";
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
                 $activity_array = array("id" => $id, "module" => $this->module, "activity" => 'add',"action"=>"a");
                add_admin_activity($activity_array);
                $response['status'] = true;
                $response['success'] = "Content Page added successfully";
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
