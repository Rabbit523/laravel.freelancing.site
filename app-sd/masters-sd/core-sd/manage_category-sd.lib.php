<?php

class Category extends Home 
{

    public $page_name;
    public $page_title;
    public $meta_keyword;
    public $meta_desc;
    public $page_desc;
    public $isActive;
    public $data = array();

    public function __construct($module, $id = 0, $objPost = NULL, $searchArray = array(), $type = '') 
    {
        global $db, $fields, $sessCataId;
        $this->db = $db;
        $this->data['id'] = $this->id = $id;
        $this->fields = $fields;
        $this->module = $module;
        $this->table = 'tbl_category';

        $this->type = ($this->id > 0 ? 'edit' : 'add');
        $this->searchArray = $searchArray;
        parent::__construct();
        if ($this->id > 0) 
        {
            $qrySel = $this->db->select($this->table, "*", array("id" => $id))->result();
            $fetchRes = $qrySel;
            $this->data['category_name'] = $this->category_name = $fetchRes['category_name'];
            $this->data['category_type'] = $this->category_type = $fetchRes['category_type'];
            $this->data['category_image'] = $this->category_image = $fetchRes['category_image'];
            $this->data['isActive'] = $this->isActive = $fetchRes['isActive'];

        } 
        else 
        {
            $this->data['category_name'] = $this->category_name = '';
            $this->data['category_type'] = $this->category_type = '';
            $this->data['category_image'] = $this->category_image = '';
            $this->data['isActive'] = $this->isActive = 'y';
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
            case 'undo' : {
                $this->data['content'] = json_encode($this->dataGrid());
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
        $content = '';

        foreach (getLangValues($this->table,$this->id,'category_name','Category Name') as $key => $value) {
            $content .= $this->displayBox(array("label" => $value['f_title']."&nbsp;:", "value" => filtering($value['f_value'])));
        }

        $content .= $this->displayBox(array("label" => "Status&nbsp;:", "value" => $this->isActive == 'y' ? 'Active' : 'Deactive'));           
        return $content;
    }

    public function getForm() 
    {

        $content = '';
        // $pop = '';
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/form-sd.skd");
        $main_content = $main_content->compile();
        $static_a = ($this->isActive == 'y' ? 'checked' : '');
        $static_d = ($this->isActive != 'y' ? 'checked' : '');

        $type_j = ($this->category_type == 'j' ? 'checked' : '');
        $type_s = ($this->category_type == 's' ? 'checked' : '');
        $type_b = ($this->category_type == 'b' ? 'checked' : '');
        
        $temp = (new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/lang_category_name-sd.skd"))->compile();
        $temp_category_name = getLangForm($this->table,$this->id,'category_name','Category Name',$temp);
        
        $image = $this->category_image;
        $img = ($image != '') ? SITE_CATEGORY_IMAGE.$image : '';
        $old_img_class = ($image == '') ? "hide" : '';
        $fields = array(
            "%CATEGORY_NAME%",
            '%TEMP_CATEGORY_NAME%',
            "%CATEGORY_IMAGE%",
            "%CATEGORY_TYPE_J%",
            "%CATEGORY_TYPE_S%",
            "%CATEGORY_TYPE_B%",
            "%OLD_IMAGE_SRC%",
            "%IMAGE_SHOW_CLASS%",
            "%STATIC_A%",
            "%STATIC_D%",
            "%TYPE%",
            "%ID%"
        );

        $fields_replace = array(
            $this->data['category_name'],
            $temp_category_name,
            filtering($this->data['category_image']),
            $type_j ,
            $type_s,
            $type_b,
            $img,
            $old_img_class,
            filtering($static_a),
            filtering($static_d),
            filtering($this->type),
            filtering($this->id, 'input', 'int')
        );

        $content = str_replace($fields, $fields_replace, $main_content);
        return sanitize_output($content);
    }

    public function dataGrid() 
    {
        $content = $operation = $whereCond = $totalRow = NULL;
        $result = $tmp_rows = $row_data = array();
        extract($this->searchArray);
        $chr = str_replace(array('_', '%'), array('\_', '\%'), $chr);
        $whereCond = "1 = ?";
        $wArray = array(1);
        
        if (!empty($chr)) 
        {
            $whereCond .= " AND (category_name LIKE ?)";
            $wArray[] = "%$chr%";
        }

        if(isset($filtering_type) && $filtering_type!=''){
            $whereCond.=' and category_type="'.$filtering_type.'"';
        }

        if (isset($sort))
            $sorting = $sort . ' ' . $order;
        else
            $sorting = 'id DESC';

        $qrySel = $this->db->pdoQuery("SELECT * FROM tbl_category WHERE $whereCond ORDER BY $sorting LIMIT $offset , $rows",$wArray)->results();

        $totalRow = $this->db->pdoQuery("SELECT * FROM tbl_category WHERE $whereCond ORDER BY $sorting",$wArray)->affectedRows();

        foreach ($qrySel as $fetchRes) 
        {
            $status = ($fetchRes['isActive'] == "y") ? "checked" : "";
            $status1 = ($fetchRes['isHomepage'] == "y") ? "checked" : "";

            $operation = '';
            $switch = '';

            if($fetchRes['isDelete']=='y'){

             $status = ($fetchRes['isDelete'] == "y") ? "" : "checked";
             $status1 = ($fetchRes['isHomepage'] == "y") ? "checked" : "";
             $switch = (in_array('status', $this->Permission)) ? $this->toggel_switch(array("action" => "ajax." . $this->module . ".php?id=" . $fetchRes['id'] . "", "check" =>$status,"deletecat"=>"y")) : '';

             
             $operation .=(in_array('undo', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=undo&id=" . $fetchRes['id'] . "","extraAtt" => "title = 'Undo'", "class" => "btn default btn-info btn-undo", "value" => '<i class="fa fa-undo"></i>')) : '';

             $homepage_avail = $this->toggel_switch(array("action" => "ajax." . $this->module . ".php?cat_id=" . $fetchRes['id'] . "", "check" => $status1,"homecat"=>"y"));
         }
         else
         {
            $status1 = ($fetchRes['isHomepage'] == "y") ? "checked" : "";
            $switch = (in_array('status', $this->Permission)) ? $this->toggel_switch(array("action" => "ajax." . $this->module . ".php?id=" . $fetchRes['id'] . "", "check" => $status)) : '';
            $operation .=(in_array('delete', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=delete&id=" . $fetchRes['id'] . "", "extraAtt" => "title = 'Delete'", "class" => "btn default red btn-delete", "value" => '<i class="fa fa-trash-o"></i>')) : '';
            $operation .=(in_array('view', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=view&id=" . $fetchRes['id'] . "", "extraAtt" => "title = 'View'", "class" => "btn default blue btn-viewbtn", "value" => '<i class="fa fa-laptop"></i>')) : '';
            $operation .= (in_array('edit', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=edit&id=" . $fetchRes['id'] . "", "extraAtt" => "title = 'Edit'", "class" => "btn default black btnEdit", "value" => '<i class="fa fa-edit"></i>')) : '';
            $homepage_avail = $this->toggel_switch(array("action" => "ajax." . $this->module . ".php?cat_id=" . $fetchRes['id'] . "", "check" => $status1));
        }


        $final_array = array(
            '<img src="'.SITE_CATEGORY_IMAGE.$fetchRes['category_image'].'" width="100">',
            filtering($fetchRes["category_name"]),
            ($fetchRes["category_type"] == 'j') ? 'Job' :(($fetchRes["category_type"] == 's') ? 'Services' : 'Both'),
            $homepage_avail 

        );
        if (in_array('status', $this->Permission)) 
        {
            $final_array = array_merge($final_array, array($switch));
        }
        if (in_array('edit', $this->Permission) || in_array('delete', $this->Permission) || in_array('view', $this->Permission)) 
        {
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
    $disabledSwitch=NULL;
    $text['action'] = isset($text['action']) ? $text['action'] : 'Enter Action Here: ';
    $text['check'] = isset($text['check']) ? $text['check'] : '';
    $text['name'] = isset($text['name']) ? $text['name'] : '';
    $text['class'] = isset($text['class']) ? '' . trim($text['class']) : '';
    $text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';
    if(!empty($text['deletecat'])){
     if(empty($text['check']) && $text['deletecat']=='y'){
        $disabledSwitch='disabled';
    }
}
if(!empty($text['homecat'])){
    if(empty($text['check']) && $text['homecat']=='y')
    {
        $disabledSwitch='disabled';
    }
}

$main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/switch-sd.skd');
$main_content = $main_content->compile();
$fields = array("%NAME%", "%CLASS%", "%ACTION%", "%EXTRA%", "%CHECK%","%DISABLECAT%");
$fields_replace = array($text['name'], $text['class'], $text['action'], $text['extraAtt'], $text['check'],$disabledSwitch);
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
public function contentSubmit($data,$Permission){

    $response = array();
    $response['status'] = false;
    $isExist="";
    extract($data);



    $objPost = new stdClass();
    $objPost->id = isset($id) ? $id : '';
    $objPost->category_name = isset($category_name) ? $category_name : '';  
    $objPost->category_type = isset($category_type) ? $category_type : 'j';
    $objPost->isActive = isset($isActive) ? $isActive : 'n';
    $objPost->created_date = date('Y-m-d H:i:s');

    $objPost = setfeilds($objPost,'category_name');

    if ($objPost->category_name == "") 
    {
        $response['error'] = "Please enter Category Name";
        echo json_encode($response);
        exit;
    }

    if(!empty($_FILES['category_image']['name']))
    {
        $file_name = uploadFile($_FILES['category_image'], DIR_CATEGORY_IMAGE, SITE_CATEGORY_IMAGE);
        $image = $file_name['file_name'];
    }
    else
    {
        $image = ($type == 'edit') ? $old_image : '';
    }


    $objPost->category_image = $image;

    if ($type == 'edit' && $id > 0) 
    {
        $isExist=NULL;
        if (in_array('edit', $Permission))
        {

            $isExist =$this->db->pdoQuery("SELECT * FROM tbl_category where id !='".$objPost->id."'")->results();


            foreach ($isExist as $value) 
            {
               if($value['category_name'] == $objPost->category_name)
               {
                $isExist='1';
                break;
            }
        }

        if($isExist!='1')
        {
            $objPostArray = (array) $objPost;
            $this->db->update($this->table, $objPostArray, array("id" => $id));

            $response['status'] = true;
            $response['success'] = "Ad Category has been updated successfully";
            $activity_array = array("id"=>$id, "module"=>$this->module, "activity"=>'edit');
            add_admin_activity($activity_array);
            echo json_encode($response);
            exit;
        }
        else 
        {
            $response['status'] = false;
            $response['error'] = "This Category already exists";
            echo json_encode($response);
            exit;    
        }
    } 
    else 
    {
        $response['error'] = "You don't have permission";
        echo json_encode($response);
        exit;
    }
} 
else 
{
    if (in_array('add', $Permission)) 
    {
        $qry =$this->db->pdoQuery("SELECT category_name FROM tbl_category where category_name='".$objPost->category_name."'")->affectedRows();

        if($qry > 0)
        {
            $response['status'] = false;
            $response['error'] = "This Category already exists";
            echo json_encode($response);
            exit;
        }
        else
        {
            $objPostArray = (array) $objPost;
            $id = $this->db->insert($this->table, $objPostArray)->getLastInsertId();        
            $response['status'] = true;
            $response['success'] = "Category has been added successfully";
            $activity_array = array("id"=>$id, "module"=>$this->module, "activity"=>'add');
            add_admin_activity($activity_array);
            echo json_encode($response);
            exit;
        }   
    }
    else
    {
        $response['error'] = "You don't have permission";
        echo json_encode($response);
        exit;
    }
}
}
}











