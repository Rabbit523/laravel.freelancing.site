<?php

class HeaderConstant extends Home {
    
    public $typeName;
    public $isActive;
    public $data = array();

    public function __construct($module, $id = 0, $tab_name= "",$objPost = NULL, $searchArray = array(), $type = '') {
        global $db, $fields, $sessCataId;
        $this->db = $db;
        $this->data['id'] = $this->id = $id;
        $this->fields = $fields;
        $this->module = $module;
        $this->table = 'tbl_language_constant';
        $this->tab_name = $tab_name;

        $this->type = ($this->id > 0 ? 'edit' : 'add');
        $this->searchArray = $searchArray;
        parent::__construct();
        if ($this->id > 0) {
            $qrySel = $this->db->select($this->table, "*", array("id" => $id))->result();
            
            $fetchRes = $qrySel;
            $this->data['tab_constant'] = $this->tab_constant = $fetchRes['tab_constant'];
            $this->data['tab_name'] = $this->tab_name = $fetchRes['tab_name'];
            $this->data['constant'] = $this->constant = $fetchRes['constant'];
            $this->data['value'] = $this->value = $fetchRes['value'];
        } else {
            $this->data['tab_constant'] = $this->tab_constant = '';
            $this->data['tab_name'] = $this->tab_name = '';
            $this->data['constant'] = $this->constant = '';
            $this->data['value'] = $this->value = '';
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
                    $this->data['content'] = json_encode($this->dataGrid($tab_name));
                    break;
                }
        }
    }

    public function viewForm() {
        $content = 
                $this->displayBox(array("label" => "Sub AdminType&nbsp;:", "value" => filtering($this->typeName)));
                
        return $content;
    }

    public function getForm() {
        $content = '';
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/form-sd.skd");
        $main_content = $main_content->compile();

        $fields = array(
            "%TAB_NAME%",
            "%VALUE%",
            "%TYPE%",
            "%ID%"
        );

        $fields_replace = array(
            $this->tab_constant,
            filtering($this->data['value']),
            filtering($this->type),
            filtering($this->id, 'input', 'int')
        );

        $content = str_replace($fields, $fields_replace, $main_content);
        return sanitize_output($content);
    }

    public function dataGrid($tabName) 
    {

        $content = $operation = $whereCond = $totalRow = NULL;
        $result = $tmp_rows = $row_data = array();
        extract($this->searchArray);
        $chr = str_replace(array('_', '%'), array('\_', '\%'), $chr);

        $whereCond = "where tab_constant = '".$tabName."' ";
        if (isset($chr) && $chr != '') {
            $whereCond .= "and value LIKE '%".$chr."%' ";
        }
        
        if (isset($sort))
            $sorting = $sort . ' ' . $order;
        else
            $sorting = 'id DESC';

        $totalRow = $this->db->pdoQuery("select * from tbl_language_constant ".$whereCond." ORDER BY $sorting ")->affectedRows();
       
        $qrySel = $this->db->pdoQuery("select * from tbl_language_constant ".$whereCond." ORDER BY $sorting limit $offset , $rows")->results();
        
        foreach ($qrySel as $fetchRes) 
        {
           
            $operation = '';
            $operation .= (in_array('edit', $this->Permission)) ? $this->operation(array("href" => "ajax." . $this->module . ".php?action=edit&id=" . $fetchRes['id'] . "&tab_name=" .$tabName, "class" => "btn default black btnEdit add-back chng-btn", "value" => '<i class="fa fa-edit"></i>',"title"=>"Edit",  "extraAtt"=>"data-tab-id='".$tabName."'")) : '';
            
            $final_array = array(
                filtering($fetchRes["id"]),
                filtering($fetchRes["value"])
            );
            
            if (in_array('edit', $this->Permission) ||  in_array('view', $this->Permission)) {
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

    public function toggel_switch($text) {
        $text['action'] = isset($text['action']) ? $text['action'] : 'Enter Action Here: ';
        $text['check'] = isset($text['check']) ? $text['check'] : '';
        $text['name'] = isset($text['name']) ? $text['name'] : '';
        $text['class'] = isset($text['class']) ? '' . trim($text['class']) : '';
        $text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';

        $main_content = new MainTemplater(DIR_ADMIN_TMPL . 'switch-sd.skd');
        $main_content = $main_content->compile();
        $fields = array("%NAME%", "%CLASS%", "%ACTION%", "%EXTRA%", "%CHECK%");
        $fields_replace = array($text['name'], $text['class'], $text['action'], $text['extraAtt'], $text['check']);
        return str_replace($fields, $fields_replace, $main_content);
    }

    public function operation($text) {

        $text['title'] = isset($text['title']) ? $text['title'] : '';
        $text['href'] = isset($text['href']) ? $text['href'] : 'Enter Link Here: ';
        $text['value'] = isset($text['value']) ? $text['value'] : '';
        $text['name'] = isset($text['name']) ? $text['name'] : '';
        $text['class'] = isset($text['class']) ? '' . trim($text['class']) : '';
        $text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/operation-sd.skd');
        $main_content = $main_content->compile();
        $fields = array("%HREF%", "%CLASS%", "%VALUE%", "%EXTRA%","%TITLE%");
        $fields_replace = array($text['href'], $text['class'], $text['value'], $text['extraAtt'],$text['title']);
        return str_replace($fields, $fields_replace, $main_content);
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

    public function getPageContent() 
    {
        $final_result = NULL;
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/" . $this->module . ".skd");
        $main_content->breadcrumb = $this->getBreadcrumb();
        $final_result = $main_content->compile();

        $data = $this->db->pdoQuery("select * from tbl_language_constant where page_id='42' group by tab_constant ")->results();
        $tab_name = $tab_div_name = '';
        $i=1;
        foreach ($data as $value) 
        {
            $class = ($i==1) ? 'active' : '';
            $tab_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/tab_li-sd.skd");
            $final_result1 = $tab_content->compile();
            $fields_replace = array(
                "%TAB_CONSTANT%" => $value['tab_constant'],
                "%TAB_NAME%" => $value['tab_name'],
                "%ACTIVE_CLASS%" => $class
            );
            $tab_name .= str_replace(array_keys($fields_replace), array_values($fields_replace), $final_result1);
            $i++;
        }
        $j=1;
        foreach ($data as $value) 
        {
            $class = ($j==1) ? 'active' : '';
            $tab_status_class = ($j==1) ? 'true' : 'false';
            $tab_content_div = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/tab_div-sd.skd");
            $final_result2 = $tab_content_div->compile();
            $fields_replace = array(
                "%TAB_CONSTANT%" => $value['tab_constant'],
                "%TAB_NAME%" => $value['tab_name'],
                "%ACTIVE_CLASS%" => $class,
                "%FALSE_STATUS%" => $tab_status_class
            );
            $tab_div_name .= str_replace(array_keys($fields_replace), array_values($fields_replace), $final_result2);
            $j++;
        }


        $tab_loop = str_replace(array('%tab_loop%','%tab_div_loop%','%load_tab%'), array($tab_name,$tab_div_name,$data[0]['tab_constant']), $final_result);
        return $tab_loop;
    }

}
