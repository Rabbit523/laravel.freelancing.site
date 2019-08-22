<?php

class Comments extends Home {

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
        $this->data['commentId'] = $this->id = $id;
        $this->fields = $fields;
        $this->module = $module;
        $this->table = 'tbl_comments';

        $this->type = ($this->id > 0 ? 'edit' : 'add');
        $this->searchArray = $searchArray;
        parent::__construct();
        if ($this->id > 0) {
            $qrySel=$this->db->pdoQuery("select tbl_comments.*,tbl_listing.listingUrl from tbl_comments  join tbl_listing on (tbl_listing.listingId=tbl_comments.listingId ) where tbl_comments.commentId = $id");
            $qrySel=$qrySel->result();
            //$qrySel = $this->db->select($this->table, "*", array("commentId" => $id))->result();
            $fetchRes = $qrySel;
            $this->data['userId'] = $this->userId = $fetchRes['userId'];
            $this->data['listingUrl'] = $this->listingUrl = $fetchRes['listingUrl'];
            $this->data['comments'] = $this->comments = $fetchRes['comments'];
            $this->data['isActive'] = $this->isActive = $fetchRes['isActive'];
            $this->data['createdDate'] = $this->createdDate = $fetchRes['createdDate'];
        } else {
            $this->data['userId'] = $this->userId = '';
            $this->data['listingUrl'] = $this->listingUrl = '';
            $this->data['comments'] = $this->comments = '';
            $this->data['isActive'] = $this->isActive = 'y';
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
        $content = 
                $this->displayBox(array("label" => "User Name&nbsp;:", "value" => filtering(getUserName($this->userId)))).
                $this->displayBox(array("label" => "Listing URL&nbsp;:", "value" => filtering($this->listingUrl))) .
                $this->displayBox(array("label" => "Comments&nbsp;:", "value" => filtering($this->comments, 'output', 'text'))).
                $this->displayBox(array("label" => "Inserted Date&nbsp;:", "value" => filtering(date(DATE_FORMAT_ADMIN,strtotime($this->createdDate)))));
        return $content;
    }

    public function getForm() {
        $content = $content_email='';
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/form-sd.skd");
        $main_content = $main_content->compile();
        $static_a = ($this->isActive == 'y' ? 'checked' : '');
        $static_d = ($this->isActive != 'y' ? 'checked' : '');
        $fields = array(
            "%USER_EMAIL%",
            "%TAGLINE%",
            "%COMMENTS%",
            "%STATIC_A%",
            "%STATIC_D%",
            "%TYPE%",
            "%ID%"
        );

        $fields_replace = array(
            filtering(getUserName($this->userId)),
            filtering($this->data['listingUrl']),
            filtering($this->data['comments'], 'output', 'text'),
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
        if (isset($chr) && $chr != '') {
            $whereCond= "where (comments LIKE '%".$chr."%' or tl.listingUrl LIKE '%".$chr."%' tl.appName LIKE '%".$chr."%' or tbl_users.userName LIKE '%" . $chr . "%')";
        }

        if (isset($sort))
            $sorting = $sort . ' ' . $order;
        else
            $sorting = 'commentId DESC';
        $qrySel=$this->db->pdoQuery("SELECT tbl_comments.*,TRIM(TRAILING ',' FROM SUBSTRING_INDEX(tbl_comments.comments, ' ', 15)) as comments,tl.listingUrl,tl.isDeleted,tl.appName,tl.listingTypeId,tbl_users.firstName,tbl_users.lastName,tbl_users.userName,tbl_users.isDeleted As userDeleted from tbl_comments  join tbl_listing  as tl on (tl.listingId=tbl_comments.listingId ) join tbl_users on (tbl_users.id=tbl_comments.userId)   $whereCond  ORDER BY $sorting limit $offset , $rows")->results();
            $totalRow=$this->db->pdoQuery("SELECT tbl_comments.*,TRIM(TRAILING ',' FROM SUBSTRING_INDEX(tbl_comments.comments, ' ', 15)) as comments,tl.listingUrl,tl.isDeleted,tl.appName,tl.listingTypeId,tbl_users.firstName,tbl_users.lastName,tbl_users.userName,tbl_users.isDeleted As userDeleted from tbl_comments  join tbl_listing as tl on (tl.listingId=tbl_comments.listingId ) join tbl_users on (tbl_users.id=tbl_comments.userId)   $whereCond")->affectedRows();
        foreach ($qrySel as $fetchRes) {
            $status = ($fetchRes['isActive'] == "y") ? "checked" : "";

            $switch = (in_array('status', $this->Permission)) ? $this->toggel_switch(array("action" => "ajax." . $this->module . ".php?id=" . $fetchRes['commentId'] . "", "check" => $status)) : '';
            $operation = '';
            $operation .= (in_array('edit', $this->Permission)) ? $this->operation(array("href" => "ajax." . $this->module . ".php?action=edit&id=" . $fetchRes['commentId'] . "", "class" => "btn default  black btnEdit","extraAtt" => "title = 'Edit'", "value" => '<i class="fa fa-edit"></i>')) : '';
            $operation .=(in_array('view', $this->Permission)) ? '&nbsp;&nbsp;' . $this->operation(array("href" => "ajax." . $this->module . ".php?action=view&id=" . $fetchRes['commentId'] . "", "class" => "btn default blue  btn-viewbtn","extraAtt" => "title = 'View'", "value" => '<i class="fa fa-laptop"></i>')) : '';
            $operation .=(in_array('delete', $this->Permission)) ? '&nbsp;&nbsp;' . $this->operation(array("href" => "ajax." . $this->module . ".php?action=delete&id=" . $fetchRes['commentId'] . "", "extraAtt" => "title = 'Delete'","class" => "btn default  red btn-delete", "value" => '<i class="fa fa-trash-o"></i>')) : '';
            $delete_status = ($fetchRes['isDeleted']=='y')?"<span class='label label-warning'>&nbsp;Listing deleted</span>":'';
            $user_status = ($fetchRes['userDeleted']=='y')?"<span class='label label-warning'>&nbsp;User deleted</span>":'';
            $final_array = array(
                filtering($fetchRes["commentId"]),
                filtering(ucfirst($fetchRes["userName"])).'<br>'.$user_status,
                filtering(($fetchRes['listingTypeId']=='4')?$fetchRes['appName']:displaySiteUrl($fetchRes["listingUrl"])).'<br>'.$delete_status,
                filtering($fetchRes["comments"]."...")
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
        $id = isset($id) ? $id : '';
        $objPost->comments = isset($comments) ? $comments : '';
        $objPost->isActive = isset($isActive) ? $isActive : 'n';
        if ($type == 'edit' && $id > 0) {

            if (in_array('edit', $Permission)) {

                $objPostArray = (array) $objPost;
                $this->db->update($this->table, $objPostArray, array("commentId" => $id));

                $activity_array = array("id" => $id, "module" => $this->module, "activity" => $type);
                add_admin_activity($activity_array);

                $response['status'] = true;
                $response['success'] = "Comment updated successfully";
                echo json_encode($response);
                exit;
            } else {
                $response['error'] = "You don't have permission to edit Comment";
                echo json_encode($response);
                exit;
            }
        } 
    }

}
