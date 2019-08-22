<?php

class SubCategory extends Home
{

    public $data = array();

    public function __construct($module, $id = 0, $objPost = NULL, $searchArray = array(), $type = '')
    {
        global $db, $fields, $sessCataId;
        $this->db = $db;
        $this->data['id'] = $this->id = $id;
        $this->fields = $fields;
        $this->module = $module;
        $this->table = ' tbl_subcategory';

        $this->type = ($this->id > 0 ? 'edit' : 'add');
        $this->searchArray = $searchArray;
        parent::__construct();
        if ($this->id > 0)
        {
            $qrySel = $this->db->select($this->table, "*", array("id" => $id))->result();
            $fetchRes = $qrySel;
            $this->data['maincat_id'] = $this->maincat_id  = $fetchRes['maincat_id'];
            $this->data['subcategory_name'] = $this->subcategory_name  = $fetchRes['subcategory_name'];
            $this->data['sub_cat_image'] = $this->sub_cat_image = $fetchRes['sub_cat_image'];
            $this->data['created_date']  = $this->created_date   = $fetchRes['created_date'];
            $this->data['isActive'] = $this->isActive = $fetchRes['isActive'];
        }
        else
        {
            $this->data['maincat_id'] = $this->maincat_id = '';
            $this->data['subcategory_name'] = $this->subcategory_name = '';
            $this->data['sub_cat_image'] = $this->sub_cat_image = '';
            $this->data['created_date'] = $this->created_date = '';
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
        // $categoryName = getTableValue("tbl_faq_category","categoryName",array("id"=>$this->id));
        $categoryName = $this->db->pdoQuery("SELECT category_name FROM tbl_category WHERE id = $this->maincat_id")->result();

        $content='';

        $content .= $this->displayBox(array("label"=>"Category&nbsp;:","value"=>$categoryName['category_name']));

        foreach (getLangValues($this->table,$this->id,'subcategory_name','Subcategory Name') as $key => $value) {
            $content .= $this->displayBox(array("label" => $value['f_title']."&nbsp;:", "value" => filtering($value['f_value'])));
        }

        $content .= $this->displayBox(array("label" => "Created On &nbsp;:", "value" => date(DATE_FORMAT,strtotime(filtering($this->created_date))))).
        $this->displayBox(array("label" => "Status &nbsp;:", "value" => filtering($this->isActive=='y'?"Active":"Deactive")));

        return $content;
    }

    public function getForm()
    {

        $content = '';
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/form-sd.skd");
        $main_content = $main_content->compile();
        $static_a = ($this->isActive == 'y' ? 'checked' : '');
        $static_d = ($this->isActive != 'y' ? 'checked' : '');


        $all_cat = $this->db->select("tbl_category", "*",array("isActive"=>'y'))->results();
        $all_category = "";
        foreach ($all_cat as $value)
        {
            if(($this->maincat_id) == ($value['id']))
            {
                $condition = "selected";
            }
            else
            {
                $condition = "";
            }
            $all_category .= "<option value='".$value['id']."' ".$condition.">".$value['category_name']."</option>";
        }


        $temp = (new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/lang_subcategory-sd.skd"))->compile();
        $lang_subcategory_name = getLangForm($this->table,$this->id,'subcategory_name','Subcategory Name',$temp);

        $image = $this->sub_cat_image;
        $img = ($image != '') ? SITE_SUB_CATEGORY_IMAGE.$image : '';
        $old_img_class = ($image == '') ? "hide" : '';

        $fields = array(
            "%ALL_CATEGORY%",
            "%FAQ_QUESTION%",
            "%LANG_SUBCATEGORY_NAME%",
            "%STATIC_A%",
            "%STATIC_D%",
            "%TYPE%",
            "%ID%",
            "%SUB_CAT_IMAGE%",
            "%OLD_IMAGE_SRC%",
            "%IMAGE_SHOW_CLASS%",
        );

        $fields_replace = array(
            $all_category,
            filtering($this->subcategory_name),
            $lang_subcategory_name,
            filtering($static_a),
            filtering($static_d),
            filtering($this->type),
            filtering($this->id, 'input', 'int'),
            filtering($this->data['sub_cat_image']),
            $img,
            $old_img_class,
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
            $whereCond .= " AND (c. category_name LIKE ? OR sc.subcategory_name LIKE ?)";
            $wArray[] = "%$chr%"; $wArray[] = "%$chr%";
        }
        if (isset($filterCategory) && $filterCategory != ''){
            if ($whereCond) {
                $whereCond .= " AND ";
            } else {
                $whereCond .= " WHERE ";
            }
            $whereCond .= " c.id = '$filterCategory'";
        }
        if (isset($sort))
            $sorting = $sort . ' ' . $order;
        else
            $sorting = 'sc.id DESC';

        $qrySel = $this->db->pdoQuery("SELECT sc.*,c.category_name FROM  tbl_subcategory  AS sc INNER JOIN tbl_category AS c ON c.id = sc.maincat_id WHERE $whereCond ORDER BY $sorting LIMIT $offset,$rows",$wArray)->results();

        $totalRow = $this->db->pdoQuery("SELECT sc.*,c.category_name FROM tbl_subcategory AS sc INNER JOIN tbl_category AS c ON c.id = sc.maincat_id WHERE $whereCond ORDER BY $sorting",$wArray)->affectedRows();

        foreach ($qrySel as $fetchRes)
        {

            $status = ($fetchRes['isActive'] == "y") ? "checked" : "";

            $operation = '';
            $switch = '';

            if($fetchRes['isDelete']=='y'){
                     // $switch = (in_array('status', $this->Permission)) ? '&nbsp;&nbsp;' . $this->toggel_switch(array("action" => "ajax." . $this->module . ".php?id=" . $fetchRes['id'] . "", "check" => $status)) : '';
                 $status = ($fetchRes['isDelete'] == "y") ? "" : "checked";
                 $switch = (in_array('status', $this->Permission)) ? $this->toggel_switch(array("action" => "ajax." . $this->module . ".php?id=" . $fetchRes['id'] . "", "check" =>$status,"deletesubcat"=>"y")) : '';
                 $operation .=(in_array('undo', $this->Permission)) ? '&nbsp;&nbsp;' . $this->operation(array("href" => "ajax." . $this->module . ".php?action=undo&id=" . $fetchRes['id'] . "","extraAtt" => "title = 'Undo'", "class" => "btn default btn-info btn-undo", "value" => '<i class="fa fa-undo"></i>')) : '';
                /*$operation .=(in_array('delete', $this->Permission)) ? '&nbsp;&nbsp;' . $this->operation(array("href" => "ajax." . $this->module . ".php?action=undo&id=" . $fetchRes['id'] . "", "extraAtt" => "title = 'Undo'", "class" => "btn btn-info btn-approve", "value" => '<i class="fa fa-undo"></i>')) : '';*/

             }else{
                $switch = (in_array('status', $this->Permission)) ?$this->toggel_switch(array("action" => "ajax." . $this->module . ".php?id=" . $fetchRes['id'] . "", "check" => $status)) : '';
                $operation .=(in_array('delete', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=delete&id=" . $fetchRes['id'] . "", "extraAtt" => "title = 'Delete'", "class" => "btn default red btn-delete", "value" => '<i class="fa fa-trash-o"></i>')) : '';
                $operation .= (in_array('edit', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=edit&id=" . $fetchRes['id'] . "", "extraAtt" => "title = 'Edit'", "class" => "btn default  black btnEdit", "value" => '<i class="fa fa-edit"></i>')) : '';
                $operation .=(in_array('view', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=view&id=" . $fetchRes['id'] . "", "extraAtt" => "title = 'View'", "class" => "btn default blue btn-viewbtn", "value" => '<i class="fa fa-laptop"></i>')) : '';
            }
            $final_array = array(
                '<img src="'.SITE_SUB_CATEGORY_IMAGE.$fetchRes['sub_cat_image'].'" width="100">',
                filtering($fetchRes['category_name']),
                filtering($fetchRes['subcategory_name']),
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
    if(!empty($text['deletesubcat'])){
        if(empty($text['check']) && $text['deletesubcat']=='y'){
            $disabledSwitch='disabled';
        }
    }

    $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/switch-sd.skd');
    $main_content = $main_content->compile();
    $fields = array("%NAME%", "%CLASS%", "%ACTION%", "%EXTRA%", "%CHECK%","%SUBCATSWITCH%");
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
    $fields = array(
        "%CATEGORY_OPTION%" => $this->getCategory()
    );
    $final_result = str_replace(array_keys($fields), array_values($fields), $final_result);
    return $final_result;
}
 public function getCategory(){
        $content='';
        $company_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/category_option-sd.skd");
        $company_content = $company_content->compile();
        $qryCompany = $this->db->pdoQuery("SELECT * from tbl_category WHERE isActive='y'")->results();
              foreach ($qryCompany as $key => $value) {
                    $fields = array(
                        "%CATEGORY%" => filtering(ucfirst($value['category_name'])),
                        "%CAT_ID%" => $value['id'],
                     );
            $content .= str_replace(array_keys($fields), array_values($fields), $company_content);
          }
        return $content;
    }
public function contentSubmit($data,$Permission)
{

    $response = array();
    $response['status'] = false;
    extract($data);

    $objPost = new stdClass();
    $objPost->id = isset($id) ? $id : '';
    $objPost->maincat_id = isset($category_id) ? $category_id : '';
    $objPost->subcategory_name = isset($subcategory_name) ? $subcategory_name : '';
    $objPost->created_date = date('Y-m-d H:i:s');
    $objPost->isActive = isset($isActive) ? $isActive : 'y';

    if ($objPost->subcategory_name == "")
    {
        $response['error'] = "Please fill all the data";
        echo json_encode($response);
        exit;
    }

    $objPost = setfeilds($objPost,'subcategory_name');

    if(!empty($_FILES['sub_cat_image']['name']))
    {
        $file_name = uploadFile($_FILES['sub_cat_image'], DIR_SUB_CATEGORY_IMAGE, SITE_SUB_CATEGORY_IMAGE);
        $image = $file_name['file_name'];
    }
    else
    {
        $image = ($type == 'edit') ? $old_image : '';
    }


    $objPost->sub_cat_image = $image;
    if ($type == 'edit' && $id > 0)
    {
        if (in_array('edit', $Permission))
        {
            $isExist=NULL;
            $isExist =$this->db->pdoQuery("
                SELECT *  
                FROM tbl_subcategory 
                WHERE id != ".$objPost->id."
                ")->results();


            foreach ($isExist as $value) {
               if($objPost->subcategory_name == $value['subcategory_name'] && $objPost->maincat_id == $value['maincat_id'] )
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
            $response['success'] = "Subcategory has been updated successfully";

            $activity_array = array("id"=>$id,"module"=>$this->module,"activity"=>"edit");
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
        $objPost->isDelete = 'n';
        $qry =$this->db->pdoQuery("
            SELECT subcategory_name,maincat_id  
            FROM tbl_subcategory 
            WHERE maincat_id=".$objPost->maincat_id ."
            ")->result();


        if($objPost->subcategory_name == $qry['subcategory_name'] && $objPost->maincat_id == $qry['maincat_id'] )
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
            $response['success'] = "Subcategory has been added successfully";
            $activity_array = array("id"=>$id, "module"=>$this->module, "activity"=>"add");
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
