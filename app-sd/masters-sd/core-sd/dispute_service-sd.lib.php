<?php

class ServiceDispute extends Home {

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
        $this->table = 'tbl_dispute';

        $this->type = ($this->id > 0 ? 'edit' : 'add');
        $this->searchArray = $searchArray;
        parent::__construct();
        switch ($type) {
            case 'workroom' : {
                $this->data['content'] = $this->workroom();
                break;
            }
            case 'edit' : {
                $this->data['content'] = $this->getForm();
                break;
            }
            case 'delete' : {
                $this->data['content'] = json_encode($this->dataGrid());
                break;
            }
            case 'view' : {
                $this->data['content'] = $this->viewForm();
                break;
            }
            case 'datagrid' : {
                $this->data['content'] = json_encode($this->dataGrid());
                break;
            }
        }
    }
    public function workroom() {
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/workroom-sd.skd");
        $main_content = $main_content->compile();   

        /* Load Messages Content*/
        $messageContent = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/message-sd.skd");
        $messageContent = $messageContent->compile();

        $data = $this->db->SELECT("tbl_dispute",array('entityId'),array('disputeId'=>$this->id))->result();
        $entityId = $data['entityId'];

        $messageData = $this->db->pdoQuery("SELECT tbl_messages.*,f.id as fid,f.userType as fuserType,f.userName as fuserName,c.id as cid from tbl_messages
            left join tbl_users as f on (f.id = tbl_messages.senderId)
            left join tbl_users as c on (c.id = tbl_messages.receiverId)
            where tbl_messages.entityId = $entityId and tbl_messages.entityType = 'S'   
            ")->results();
        $message_content = '';

        foreach ($messageData as $key => $value) {
            extract($value);
            if($messageType == 'text'){
                $class = ($value['fuserType'] == 'C') ? 'customer' : 'user';
                $time = date(DATE_FORMAT_ADMIN.' h:i:s A',strtotime($createdDate));
                $fieldArr = array("%CLASS%",'%MESSAGES%','%USER_NAME%','%TIME%');
                $replaceArr = array($class,$message,$fuserName,$time);
                $message_content .= str_replace($fieldArr, $replaceArr,$messageContent); 
            }
        }
        /* End Code */

        /* Getting files of message conversation */
        $fileContent = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/files-sd.skd");
        $fileContent = $fileContent->compile();
        $fileData = '';
        foreach ($messageData as $key => $value) {

            extract($value);
            if($messageType == 'file'){
                $img_class = isset($img_class) ? $img_class : '';

                $time = date('h:i:s A',strtotime($createdDate));
                $type = explode('.',$fileName);
                $type = (string)$type[1];
                $file_class = ''; 
                
                if($type !='pdf') {
                    $file_type ="<img src='".SITE_WORKROOM.$fileName."' alt=''>";
                } else {
                    $file_type ="";
                }

                $fileNM = SITE_WORKROOM.$fileName;
                $fieldArr = array('%FILE_NAME%','%FILE_PATH%','%FILE_NM%','%TYPE%','%TIME%','%IMG_CLASS%','%FILE_CLASS%','%IS_WORK%');
                $replaceArr = array($fileName,$file_type,$fileNM,$type,$time,$img_class,$file_class,'');
                $fileData .= str_replace($fieldArr, $replaceArr,$fileContent); 
            }
        }
        /* End Code */

        $entityId= !empty($entityId) ? $entityId : 0;
        $wdata = $this->db->pdoQuery("SELECT * FROM `tbl_services_order` WHERE id = ? AND submitWork = 'y'",[$entityId])->result();


        if(!empty($wdata)){
            $img_class = isset($img_class) ? $img_class : '';
            $fileName = $wdata['submitWorkFile'];
            $time = date('h:i:s A',strtotime($wdata['submitWorkDate']));
            $type = explode('.',$wdata['submitWorkFile']);
            $type = (string)$type[1];
            $file_class = ''; 

            if($type !='pdf') {
                $file_type ="<img src='".SITE_WORK_FILES.$fileName."' alt=''>";
            } else {
                $file_type ="";
            }

            $fileNM = SITE_WORK_FILES.$fileName;
            $fieldArr = array('%FILE_NAME%','%FILE_PATH%','%FILE_NM%','%TYPE%','%TIME%','%IMG_CLASS%','%FILE_CLASS%','%IS_WORK%');
            $replaceArr = array($fileName,$file_type,$fileNM,$type,$time,$img_class,$file_class,'work_file');
            //printr($replaceArr,1);
            $fileData .= str_replace($fieldArr, $replaceArr,$fileContent);
        }


        $fieldArr = array("%MESSAGE%","%FILEDATA%");
        $replaceArr = array($message_content,$fileData);
        
        $html = str_replace($fieldArr,$replaceArr,$main_content);     
        return $html;
    }


    public function viewForm() {
        $content='';

        $fetchResPCats = $this->db->pdoQuery("
            SELECT tbl_dispute.*,u.id as uid,
            u.firstName AS ufirstName,u.lastName AS ulastName,u.profileImg as uprofileImg,u.userType AS uuserType,u.email as uemail,u.location AS ulocation,d.id AS did,d.userName as duserName,u.userName as uuserName,
            d.firstName AS dfirstName,d.lastName AS dlastName,d.userType AS duserType,d.email AS demail,d.profileImg as dprofileImg,d.location AS dlocation,
            s.id as sid,s.serviceTitle as stitle,s.servicesPrice as sPrice  
            from tbl_dispute 
            left join tbl_users as u on (tbl_dispute.disputerId=u.id)
            left join tbl_users as d on (tbl_dispute.disputedId=d.id)
            left join tbl_services_order as so on (tbl_dispute.entityId=so.id)
            left join tbl_services as s on (so.servicesId = s.id)
            where tbl_dispute.disputeId= ".$this->id)->result();
        extract($fetchResPCats);

        $img1 = ($uprofileImg!='')? $uprofileImg : "no_user_image.png";
        $uimg = "<img src='".SITE_UPD."profile/".$img1."' alt='".$uuserName." profile' height='100px' width='100px'></img>";

        $img2 = !empty($fetchResPCats->profileImg)? $fetchResPCats->profileImg : "no_user_image.png";

        $dimg = "<img src='".SITE_UPD."profile/".$img2."' alt='".$duserName." profile' height='100px' width='100px'></img>";
        $uuserType = ($uuserType =='C') ?'Customer' :'Freelancer';
        $duserType = ($duserType =='C') ?'Customer' :'Freelancer';
        $disputeStatus = ($status =='S') ? 'Solved' :'Pending';

        $content.="<center><h2><b> User Details (Dispute Raised By) </b></h2></center>";
        $content.=$this->displayBox(array("label" => "User Name &nbsp;:", "value" => filtering($ufirstName.' '.$ulastName)));
        $content.=$this->displayBox(array("label" => "User Profile &nbsp;:", "value" => $uimg));
        $content.=$this->displayBox(array("label" => "User Type &nbsp;:", "value" => $uuserType));
        $content.=$this->displayBox(array("label" => "User Location &nbsp;:", "value" => $ulocation));
        $content.=$this->displayBox(array("label" => "User Email &nbsp;:", "value" => $uemail));

        $content.="<center><h2><b> User Details (Dispute Raised Against) </b></h2></center>";
        $content.=$this->displayBox(array("label" => "User Name &nbsp;:", "value" => filtering($dfirstName.' '.$dlastName)));
        $content.=$this->displayBox(array("label" => "User Profile &nbsp;:","value" => $dimg));
        $content.=$this->displayBox(array("label" => "User Type &nbsp;:", "value" => $duserType));
        $content.=$this->displayBox(array("label" => "User Location &nbsp;:", "value" => filtering($dlocation)));
        $content.=$this->displayBox(array("label" => "User Email &nbsp;:", "value" => filtering($demail)));
        $content.="<center><h2><b> Dispute Details </b></h2></center>";

        $content.=$this->displayBox(array("label" => "Service Title&nbsp;:", "value" => filtering($stitle)));
        $content.=$this->displayBox(array("label" => "Service Budget&nbsp;:", "value" => filtering(CURRENCY_SYMBOL.$sPrice)));
        $content.=$this->displayBox(array("label" => "Dispute Reason&nbsp;:", "value" => filtering($disputeReason)));
        $content.=$this->displayBox(array("label" => "Dispute Description&nbsp;:", "value" => filtering($disputeDesc)));
        if($status=='S'){
            $content.=$this->displayBox(array("label" => "Dispute Status&nbsp;:", "value" => filtering($disputeStatus)));
            $content.=$this->displayBox(array("label" => "Paid to User(Dispute Raised By) &nbsp;:", "value" => filtering(CURRENCY_SYMBOL.$payToDisputer)));
            $content.=$this->displayBox(array("label" => "Paid to User(Dispute Against) &nbsp;:", "value" => filtering(CURRENCY_SYMBOL.$payToEntityOwner)));            
        }
        
        $content.=$this->displayBox(array("label" => "Disputed Date&nbsp;:", "value" => filtering(date(DATE_FORMAT_ADMIN.' h:i:s A',strtotime($insertedDate)))));
        return $content;
    }

    public function dataGrid() {
        $content = $operation = $whereCond = $totalRow = NULL;
        $whereCond="where 1=1 AND type ='S' ";
        $result = $tmp_rows = $row_data = array();
        extract($this->searchArray);
        $chr = str_replace(array('_', '%',"'",'"'), array('\_', '\%',"\'",'\"'), $chr);
        $chr = trim($chr);
        if (isset($chr) && $chr != '') {
            $whereCond.= " and ( u.userType LIKE '%" . $chr . "%' or  u.firstName LIKE '%" . $chr . "%' or u.lastName LIKE '%" . $chr . "%'  or DATE_FORMAT(tbl_dispute.insertedDate, '" . MYSQL_DATE_FORMAT . "') LIKE '%" . $chr . "%')";
        }

        //echo $filtering_type;
        if(!empty($filtering_type) || $filtering_type!=0) {
            $whereCond.=" and tbl_dispute.status='".$filtering_type."'";
        }

        if (isset($sort))
            $sorting = $sort.' '.$order;
        else
            $sorting = 'disputeId DESC';

        $qrySel = $this->db->pdoQuery("SELECT tbl_dispute.*,u.firstName AS ufirstName,u.lastName AS ulastName,u.userType AS uuserType from tbl_dispute 
            join tbl_users as u on (u.id=tbl_dispute.disputerId)
            $whereCond ORDER BY $sorting limit $offset,$rows")->results();

        // printr($qrySel,1);
        
        $totalRow = $this->db->pdoQuery("SELECT tbl_dispute.*,u.firstName AS ufirstName,u.lastName AS ulastName,u.userType AS uuserType 
            from tbl_dispute 
            join tbl_users as u on (u.id=tbl_dispute.disputerId)
            $whereCond ")->affectedRows();

        foreach ($qrySel as $fetchRes) {            

            $operation = '';
            // $operation .=($fetchRes['file_accept_status']=='rejected')?((in_array('edit', $this->Permission)) ? $this->operation(array("href" => "ajax." . $this->module . ".php?action=edit&id=" . $fetchRes['disputeId'] . "", "class" => "btn default black btnEdit","extraAtt" => "title = 'Edit'", "value" => '<i class="fa fa-edit"></i>')): ''):'';
            
            if($fetchRes['status'] == 'P'){
                $operation .= (in_array('edit', $this->Permission)) ? $this->operation(array("href" => "ajax." . $this->module . ".php?action=edit&id=" . $fetchRes['disputeId'] . "", "class" => "btn default  black btnEdit","extraAtt" => "title = 'Solve Dispute'", "value" => 'Solve Dispute')) : '';
            }

            $operation .= (in_array('edit', $this->Permission)) ? $this->operation(array("href" => "ajax." . $this->module . ".php?action=workroom&id=" . $fetchRes['disputeId'] . "", "class" => "btn default  black btnEdit","extraAtt" => "title = 'View Workroom'", "value" => 'View Workroom')) : '';

            $operation .=(in_array('view', $this->Permission)) ? '' . $this->operation(array("href" => "ajax." . $this->module . ".php?action=view&id=" . $fetchRes['disputeId'] . "", "class" => "btn default blue btn-viewbtn","extraAtt" => "title = 'View'", "value" => '<i class="fa fa-laptop"></i>')) : '';

            $operation .=(in_array('delete', $this->Permission)) ? '' . $this->operation(array("href" => "ajax." . $this->module . ".php?action=delete&id=" . $fetchRes['disputeId'] . "", "class" => "btn default  red btn-delete","extraAtt" => "title = 'Delete'", "value" => '<i class="fa fa-trash-o"></i>')) : '';

            $status = $fetchRes['status']=='P' ? 'Pending' : 'Solved';
            // $user_status = ($fetchRes['userDeletStatus']=='y')?"<span class='label label-warning'>&nbsp;User deleted</span>":'';

            $final_array = array(
                filtering($fetchRes["disputeId"]),
                filtering($fetchRes['ufirstName'].' '.$fetchRes['ulastName']),
                filtering($fetchRes['uuserType'] == 'C' ? 'Customer' :'Provider'),
                $status,
                filtering(date(DATE_FORMAT_ADMIN,strtotime($fetchRes["insertedDate"])))
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
    public function getForm() {

        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/form-sd.skd");
        $main_content = $main_content->compile();

        $query = $this->db->pdoQuery('
            SELECT tbl_dispute.*,u.id as uid,
            u.firstName AS ufirstName,u.lastName AS ulastName,u.profileImg as uprofileImg,u.userType AS uuserType,u.email as uemail,u.location AS ulocation,d.id AS did,
            d.firstName AS dfirstName,d.lastName AS dlastName,d.userType AS duserType,d.email AS demail,d.profileImg as dprofileImg,d.location AS dlocation,
            s.id as sid,s.serviceTitle as stitle,s.servicesPrice as sPrice, so.totalPayment as stotalPayment  
            from tbl_dispute 
            left join tbl_users as u on (tbl_dispute.disputerId=u.id)
            left join tbl_users as d on (tbl_dispute.disputedId=d.id)
            left join tbl_services_order as so on (tbl_dispute.entityId=so.id)
            left join tbl_services as s on (so.servicesId = s.id)
            where tbl_dispute.disputeId='.$this->id )->result();

        
        extract($query);
        $username = $ufirstName.' '.$ulastName;
        $userType = ($uuserType == 'C') ? 'Customer' : 'Freelancer';
        $duserName = $dfirstName.' '.$dlastName;                                 
        $duserType = ($duserType == 'C') ? 'Customer' : 'Freelancer';
        $stotalPayment = !empty($stotalPayment) ? $stotalPayment : 0; 

        $commission = getCommision($stotalPayment,'S');
        if(empty($commission)){
            $commission_per = getTableValue("tbl_site_settings","value",array("constant"=>"DEFAULT_SERVICE_COMM"));
            $commission = !empty($stotalPayment) ? (($stotalPayment*$commission_per)/100) : 0; 
        }
        $grandTotal = ($stotalPayment-$commission);
        $fieldArr = array("%DUSER_NAME%",'%DUSER_TYPE%','%USER_NAME%',"%USER_TYPE%","%BUDGET%","%PAY_TO_DISPUTER%","%PAY_TO_USER%","%TYPE%","%ID%","%UID%","%DID%");
        $replaceArr = array($username,$userType,$duserName,$duserType,$grandTotal,$payToDisputer,$payToEntityOwner,"edit",$disputeId,$did,$uid);        
        $html = str_replace($fieldArr, $replaceArr, $main_content);     
        return $html;
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

        if(!empty($data))
        {
            extract($data);
            $id = isset($id) ? $id : 0;

            if ($type == 'edit' && $id > 0) {
                if (in_array('edit', $Permission)) {

                    $array=array(
                        "payToDisputer" => $payToDisputer,
                        "payToEntityOwner" => $payToEntityOwner,
                        "status" => 'S', 
                        "paidDate" => date('Y-m-d H:i:s')
                    );
                    $whereArray = array("disputeid " => $id); 
                    $this->db->update("tbl_dispute", $array, $whereArray);

                    $ddata = $this->db->pdoQuery("SELECT tbl_dispute.*,u.id as uid,u.firstName AS ufirstName,u.lastName AS ulastName,u.profileImg as uprofileImg,u.userType AS uuserType,u.email as uemail,u.location AS ulocation,d.id AS did,d.userName as duserName,u.userName as uuserName,
                        d.firstName AS dfirstName,d.lastName AS dlastName,d.userType AS duserType,d.email AS demail,d.profileImg as dprofileImg,d.location AS dlocation,so.id as soID,
                        s.id as sid,s.servicesSlug as servicesSlug,s.serviceTitle as stitle,s.servicesPrice as sPrice  
                        from tbl_dispute 
                        left join tbl_users as u on (tbl_dispute.disputerId=u.id)
                        left join tbl_users as d on (tbl_dispute.disputedId=d.id)
                        left join tbl_services_order as so on (tbl_dispute.entityId=so.id)
                        left join tbl_services as s on (so.servicesId = s.id)
                        where tbl_dispute.disputeId= ?",[$id])->result();                     

                    $msg1 = SITE_NM.' has resolved service order dispute.';
                    $link1 = SITE_URL.'service/workroom/'.base64_encode($ddata['soID']).'/'.$ddata['servicesSlug'];
                    $tp1 = $ddata['uuserType'] == 'F' ? 'f' : 'c';
                    notify($tp1,$ddata['disputerId'],$msg1,$link1);
                    
                    $this->db->update('tbl_wallet',array('paymentStatus'=>'ds',"status"=>"completed",'transactionType' => 'disputeSolved'),array('entity_id' => $ddata['soID']));
                    $arr = [
                        'entity_type' => 's',
                        'entity_id' => $ddata['soID'],
                        'userType' => $tp1,
                        'userId' => $ddata['uid'],
                        'createdDate' => date('Y-m-d H:i:s'),
                        'amount' => $payToDisputer,
                        'paymentStatus' => 'c',
                        'transactionType' => 'refund',
                        'status' => 'completed'
                    ];
                    $this->db->insert('tbl_wallet', $arr)->getLastInsertId();

                    $msg2 = SITE_NM.' has resolved service order dispute.';
                    $link2 = SITE_URL.'service/workroom/'.base64_encode($ddata['soID']).'/'.$ddata['servicesSlug'];
                    $tp2 = $ddata['duserType'] == 'F' ? 'f' : 'c';
                    notify($tp2,$ddata['disputedId'],$msg2,$link2);

                    $arr = [
                        'entity_type' => 's',
                        'entity_id' => $ddata['soID'],
                        'userType' => $tp2,
                        'userId' => $ddata['did'],
                        'amount' => $payToEntityOwner,
                        'createdDate' => date('Y-m-d H:i:s'),
                        'paymentStatus' => 'c',
                        'transactionType' => 'refund',
                        'status' => 'completed'
                    ];
                    $this->db->insert('tbl_wallet', $arr)->getLastInsertId();


                    if($payToDisputer > 0){
                        $this->db->pdoQuery("UPDATE tbl_users SET walletAmount = walletAmount + ? WHERE email = ?", array($payToDisputer,$ddata['uemail']));


                    } 
                    if($payToEntityOwner > 0){
                        $this->db->pdoQuery("UPDATE tbl_users SET walletAmount = walletAmount + ? WHERE email = ?", array($payToEntityOwner,$ddata['demail']));
                    }

                    $this->db->pdoQuery("UPDATE `tbl_wallet` SET `status` = 'completed' WHERE `entity_type` = 's' AND `entity_id` = ? and transactionType = 'escrow'",[$ddata['soID']]);

                    $this->db->pdoQuery("UPDATE `tbl_services_order` SET `serviceStatus` = 'dsc' WHERE `tbl_services_order`.`id` = ?",[$ddata['soID']]);

                    $commission = getCommision($ddata['sPrice'],'S');  
                    $this->db->insert("tbl_admin_commision",array("entityId"=>$ddata['soID'],"entityType"=>'s',"amount"=>$commission,"createdDate"=>date('Y-m-d H:i:s')));
                    

                    $response['status'] = true;
                    $response['success'] = "Status updated successfully";
                    echo json_encode($response);
                    exit;
                } else {
                    $response['error'] = "You don't have permission to edit Status";
                    echo json_encode($response);
                    exit;
                }
            }   
        }
    }
}
