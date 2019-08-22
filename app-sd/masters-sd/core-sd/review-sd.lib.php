<?php

class Review extends Home {

    public $data = array();

    public function __construct($module, $id = 0, $objPost = NULL, $searchArray = array(), $type = '') {
        global $db, $fields, $sessCataId;
        $this->db = $db;
        $this->data['id'] = $this->id = $id;
        $this->fields = $fields;
        $this->module = $module;
        $this->table = 'tbl_listing_rating';

        $this->type = ($this->id > 0 ? 'edit' : 'add');
        $this->searchArray = $searchArray;
        parent::__construct();
        if ($this->id > 0) {
            $qrySel = $this->db->select($this->table, "*", array("ratingId" => $id))->result();
            $fetchRes = $qrySel;

            $this->data['ratingId'] = $this->ratingId = $fetchRes['ratingId'];
            $this->data['userId'] = $this->userId = $fetchRes['userId'];
            $this->data['listingId'] = $this->listingId = $fetchRes['listingId'];
            $this->data['listingRating'] = $this->listingRating = $fetchRes['listingRating'];
            $this->data['listingRatingDesc'] = $this->listingRatingDesc = $fetchRes['listingRatingDesc'];
            $this->data['createdDate'] = $this->createdDate = $fetchRes['createdDate'];
            $this->data['isActive'] = $this->isActive = $fetchRes['isActive'];
        } else {
            $this->data['ratingId'] = $this->ratingId = '';
            $this->data['userId'] = $this->userId = '';
            $this->data['listingId'] = $this->listingId = '';
            $this->data['listingRating'] = $this->listingRating = '';
            $this->data['listingRatingDesc'] = $this->listingRatingDesc = '';
            $this->data['createdDate'] = $this->createdDate = '';
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
        $project = $this->db->select("tbl_listing","listingUrl",array("listingid"=>$this->listingId))->result();
        $content = 
                $this->displayBox(array("label" => "Listing URL&nbsp;:", "value" => $project['listingUrl'])) .
                $this->displayBox(array("label" => "User Name&nbsp;:", "value" => getUserName($this->userId))) .
                $this->displayBox(array("label" => "Review&nbsp;:", "value" => $this->listingRatingDesc)) .
                $this->displayBox(array("label" => "Inserted Date&nbsp;:", "value" => date(DATE_FORMAT_ADMIN,strtotime($this->createdDate))));
        return $content;
    }

    public function getForm() {
        $content = '';
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/form-sd.skd");
        $main_content = $main_content->compile();   

        $static_a = ($this->isActive == 'y' ? 'checked' : '');
        $static_d = ($this->isActive != 'y' ? 'checked' : '');

        $fields = array(
            "%PROJECT_CAT%",
            "%USERS%",
            "%RATING%",
            "%RATING_DESC%",
            "%STATIC_A%",
            "%STATIC_D%",
            "%TYPE%",
            "%ID%"
        );

        $project_category = "";
        $project_category .= "<option disabled>--select project--</option>";
        $cat_list = $this->db->select("tbl_listing","listingId,listingUrl")->results();
        foreach ($cat_list as $value) 
        {
            if($value['listingId']==$this->listingId){$class = "selected";}
            else{$class = "";}            
            $project_category .= "<option value=".$value['listingId']." ".$class.">".$value['listingUrl']."</option>";
        }

        $user_list = "";
        $user_list .= "<option disabled>--select users--</option>";
        $users = $this->db->select("tbl_users","id,email")->results();
        foreach ($users as $value) 
        {
            if($value['id']==$this->userId){$class = "selected";}
            else{$class = "";}            
            $user_list .= "<option value=".$value['id']." ".$class.">".$value['email']."</option>";
        }

        $fields_replace = array(
            $project_category,
            $user_list,                 
            filtering($this->data['listingRating']),
            filtering($this->data['listingRatingDesc']),
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
        $whereCond="where 1=1";
        extract($this->searchArray);
        $chr = str_replace(array('_', '%',"'",'"'), array('\_', '\%',"\'",'\"'), $chr);
        
        $whereCond = '';
        if (isset($chr) && $chr != '') {
            $whereCond .= "  and (p.listingUrl LIKE '%".$chr."%' or p.appName LIKE '%".$chr."%' or tbl_users.userName LIKE '%".$chr."%' or tbl_listing_type.listingTypeName LIKE '%".$chr."%' or c.listingRating LIKE '%".$chr."%')";
        }
        if(!empty($filtering_type) && $filtering_type!=0) {
                $whereCond.=" and tbl_listing_type.listingTypeId=".$filtering_type;
            }
        if (isset($sort))
            $sorting = $sort . ' ' . $order;
        else
            $sorting = 'c.ratingId DESC';
        
        $qrySel= $this->db->pdoQuery("SELECT c.*,p.listingUrl,tbl_listing_type.listingTypeName,p.appName,p.listingTypeId,p.isDeleted,tbl_users.firstName,tbl_users.lastName,tbl_users.userName,tbl_users.isDeleted as userDeleted FROM tbl_listing_rating As c JOIN tbl_listing As p ON p.listingId = c.listingId join tbl_listing_type on (p.listingTypeId=tbl_listing_type.listingTypeId) join tbl_users on (tbl_users.id=c.userId) $whereCond  ORDER BY $sorting LIMIT $offset , $rows")->results();
        $totalRow = $this->db->pdoQuery("SELECT c.*,p.listingUrl,tbl_listing_type.listingTypeName,p.appName,p.listingTypeId,p.isDeleted,tbl_users.firstName,tbl_users.lastName,tbl_users.userName,tbl_users.isDeleted as userDeleted FROM tbl_listing_rating As c JOIN tbl_listing As p ON p.listingId = c.listingId join tbl_listing_type on (p.listingTypeId=tbl_listing_type.listingTypeId) join tbl_users on (tbl_users.id=c.userId) $whereCond")->affectedRows();

        foreach ($qrySel as $fetchRes) 
        {
            
            $status = ($fetchRes['isActive'] == "y") ? "checked" : "";
            $switch = (in_array('status', $this->Permission)) ? $this->toggel_switch(array("action" => "ajax." . $this->module . ".php?id=" . $fetchRes['ratingId'] . "", "check" => $status)) : '';
            $operation = '';
            $operation .= (in_array('edit', $this->Permission)) ? $this->operation(array("href" => "ajax." . $this->module . ".php?action=edit&id=" . $fetchRes['ratingId'] . "", "class" => "btn default  black btnEdit","extraAtt" => "title = 'Edit'", "value" => '<i class="fa fa-edit"></i>')) : '';
            $operation .=(in_array('view', $this->Permission)) ? '&nbsp;&nbsp;' . $this->operation(array("href" => "ajax." . $this->module . ".php?action=view&id=" . $fetchRes['ratingId'] . "", "class" => "btn default blue  btn-viewbtn","extraAtt" => "title = 'View'", "value" => '<i class="fa fa-laptop"></i>')) : '';
            $operation .=(in_array('delete', $this->Permission)) ? '&nbsp;&nbsp;' . $this->operation(array("href" => "ajax." . $this->module . ".php?action=delete&id=" . $fetchRes['ratingId'] . "", "class" => "btn default  red btn-delete","extraAtt" => "title = 'Delete'", "value" => '<i class="fa fa-trash-o"></i>')) : '';
            
            if($fetchRes["listingRating"]=="0"){$rate="0%";}
            elseif($fetchRes["listingRating"]=="1"){$rate = "20%";}
            elseif($fetchRes["listingRating"] == "2"){$rate = "40%";}
            elseif($fetchRes["listingRating"]=="3"){$rate = "60%";}
            elseif($fetchRes["listingRating"]=="4"){$rate = "80%";}
            else{$rate = "100%";}
            $ratings = "<div class='star-ratings-sprite'><span style='width:".$rate."' class='star-ratings-sprite-rating'></span></div>";
            $delete_status = ($fetchRes['isDeleted']=='y')?"<span class='label label-warning'>&nbsp;Listing deleted</span>":'';
            $user_status = ($fetchRes['userDeleted']=='y')?"<span class='label label-warning'>&nbsp;User deleted</span>":'';
            $final_array = array(
                filtering($fetchRes["ratingId"]),
                filtering(($fetchRes['listingTypeId']=='4')?$fetchRes['appName']:displaySiteUrl($fetchRes["listingUrl"])).'<br>'.$delete_status,
                filtering(ucfirst($fetchRes["userName"])).'<br>'.$user_status,
                filtering($fetchRes["listingTypeName"]),
                $ratings
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
    public function getPageContent() 
    {
        $final_result = NULL;
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/" . $this->module . ".skd");
        $main_content->breadcrumb = $this->getBreadcrumb();
        $final_result = $main_content->compile();
        return $final_result;
    }
    public function contentSubmit($data,$Permission)
    {
        $response = array();
        $response['status'] = false;
        extract($data);
        
        $objPost = new stdClass();
        $id = isset($id) ? $id : '';
        $objPost->userId = isset($userId) ? $userId : '';
        $objPost->listingId = isset($listingId) ? $listingId : '';
        $objPost->listingRating = isset($score) ? $score : '';
        $objPost->listingRatingDesc = isset($listingRatingDesc) ? $listingRatingDesc : '';
        $objPost->isActive = isset($isActive) ? $isActive : 'n';
        
        if ($type == 'edit' && $id > 0) {

            if (in_array('edit', $Permission)) {

                $objPostArray = (array) $objPost;
                $this->db->update($this->table, $objPostArray, array("ratingId" => $id));

                $activity_array = array("id" => $id, "module" => $this->module, "activity" => $type);
                add_admin_activity($activity_array);

                $response['status'] = true;
                $response['success'] = "Rating & Review updated successfully";
                echo json_encode($response);
                exit;
            } else {
                $response['error'] = "You don't have permission to edit Rating & Review";
                echo json_encode($response);
                exit;
            }
        } 
    }

}
