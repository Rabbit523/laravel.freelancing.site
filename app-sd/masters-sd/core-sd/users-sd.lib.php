<?php

class Users extends Home {

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
        $this->table = 'tbl_users';

        $this->type = ($this->id > 0 ? 'edit' : 'add');
        $this->searchArray = $searchArray;
        parent::__construct();
        if ($this->id > 0) {
            $qrySel = $this->db->select($this->table, "*", array("id" => $id))->result();
            $fetchRes = $qrySel;
            $this->data['userType'] = $this->userType = $fetchRes['userType'];
            $this->data['firstName'] = $this->firstName = filtering($fetchRes['firstName']);
            $this->data['lastName'] = $this->lastName = filtering($fetchRes['lastName']);
            $this->data['userName'] = $this->userName = filtering($fetchRes['userName']);
            $this->data['email'] = $this->email = filtering($fetchRes['email']);
            $this->data['contactNo'] = $this->contactNo = filtering($fetchRes['contactNo']);
            $this->data['location'] = $this->location = filtering($fetchRes['location']);
            $this->data['birthDate'] = $this->birthDate = filtering($fetchRes['birthDate']);
            //$this->data['HomePageUrl'] = $this->HomePageUrl = filtering($fetchRes['HomePageUrl']);
            $this->data['profileImg'] = $this->profileImg = filtering($fetchRes['profileImg']);
            //$this->data['gender'] = $this->gender = $fetchRes['gender'];
            $this->data['status'] = $this->status = $fetchRes['status'];
            $this->data['createdDate'] = $this->createdDate = $fetchRes['createdDate'];
            $this->data['lastLogin'] = $this->lastLogin = $fetchRes['lastLogin'];
            $this->data['loginWith'] = $this->loginWith = $fetchRes['loginWith'];
            $this->data['ipAddress'] = $this->ipAddress = $fetchRes['ipAddress'];
            $this->data['aboutme'] = $this->aboutme = $fetchRes['aboutme'];
            $this->data['userType'] = $this->userType = $fetchRes['userType'];
            //$this->data['description'] = $this->description = $fetchRes['description'];
            $this->data['emailVeriStatus'] = $this->emailVeriStatus = ($fetchRes['emailVeriStatus']=='y')?'Yes':'No';
            //$this->data['fbVeriStatus'] = $this->fbVeriStatus = ($fetchRes['fbVeriStatus']=='y')?'Yes':'No';
            //$this->data['fbUserProfile'] = $this->fbUserProfile = $fetchRes['fbUserProfile'];
            
            $this->data['isDeleted'] = $this->admin_delete = $fetchRes['isDeleted'];
        } else {
            $this->data['firstName'] = $this->firstName = '';
            $this->data['lastName'] = $this->lastName = '';
            $this->data['userName'] = $this->userName = '';
            $this->data['email'] = $this->email = '';
            $this->data['contactNo'] = $this->contactNo = '';
            $this->data['publicProfileUrl'] = $this->publicProfileUrl = '';
            $this->data['location'] = $this->location = '';
            $this->data['paypalAccount'] = $this->paypalAccount = '';
            $this->data['birthDate'] = $this->birthDate = '';
            $this->data['HomePageUrl'] = $this->HomePageUrl = '';
            $this->data['profileImg'] = $this->profileImg = '';
            $this->data['status'] = $this->status = 'a';
            $this->data['gender'] = $this->gender = 'n';
            $this->data['createdDate'] = $this->createdDate = '';
            $this->data['lastLogin'] = $this->lastLogin = '';
            $this->data['loginWith'] = $this->loginWith = '';
            $this->data['ipAddress'] = $this->ipAddress = '';
            $this->data['aboutme'] = $this->aboutme = '';
            $this->data['description'] = $this->description = '';
            $this->data['phoneVeriTimes'] = $this->phoneVeriTimes = '';
            $this->data['PhoneVeriStatus'] = $this->PhoneVeriStatus = 'n';
            $this->data['emailVeriStatus'] = $this->emailVeriStatus = 'n';
            $this->data['fbVeriStatus'] = $this->fbVeriStatus = 'n';
            $this->data['fbUserProfile'] = $this->fbUserProfile = '';
            $this->data['isFbDisplay'] = $this->isFbDisplay = 'n';
            $this->data['twitterVeriStatus'] = $this->twitterVeriStatus = 'n';
            $this->data['twitterUserProfile'] = $this->twitterUserProfile = '';
            $this->data['isTwitterDisplay'] = $this->isTwitterDisplay = 'n';
            $this->data['linkedInVeriStatus'] = $this->linkedInVeriStatus = 'n';
            $this->data['linkedInUserProfile'] = $this->linkedInUserProfile = '';
            $this->data['isLinkedInDisplay'] = $this->isLinkedInDisplay = 'n';
            $this->data['isDeleted'] = $this->admin_delete = '';
            $this->data['userType'] = $this->userType = '';
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
            case 'view_counter' : {     
                    $this->data['content'] = $this->viewCounterForm();      
                     break;      
            }
            case 'delete' : {
                    $this->data['content'] = json_encode($this->dataGrid());
                    break;
                }
            case 'undo' : {
                    $this->data['content'] = json_encode($this->dataGrid());
                    break;
                }
            case 'login_history' : {
                    $this->data['content'] = (in_array('view', $this->Permission)) ? $this->viewLoginHistory() : '';
                    break;
                }

            case 'login_history_datagrid' : {
                $this->data['content'] =  (in_array('view',$this->Permission))?json_encode($this->login_history_datagrid()):'';
                break;
            }

            case 'userlist' : {
                    $this->data['content'] = (in_array('view', $this->Permission)) ? $this->ipUserList() : '';
                    break;
            }

            case 'ipbase_user' : {
                $this->data['content'] =  (in_array('view',$this->Permission))?json_encode($this->userlistIpbase()):'';
                break;
            }
            case 'datagrid' : {
                    $this->data['content'] = json_encode($this->dataGrid());
                    break;
                }

        }
    }
    public function viewLoginHistory() {
        return $main_content = (new MainTemplater(DIR_ADMIN_TMPL.$this->module."/view_login_history_datatable-sd.skd"))->compile();
    }

    public function login_history_datagrid() 
    {
        $content = $operation = $whereCond = $totalRow = NULL;
        $result = $tmp_rows = $row_data = array();
        extract($this->searchArray);
        if (isset($sort)) {
            $sorting = $sort . ' ' . $order;
        } else {
            $sorting = 'u.createdDate DESC';
        }

        $totalRow = $this->db->pdoQuery("select * from tbl_login_history where userId='".$this->id."' ORDER BY " . $sorting)->affectedRows();
        $qryRes = $this->db->pdoQuery("select * from tbl_login_history where userId='".$this->id."'  ORDER BY $sorting LIMIT $offset, $rows")->results();
        foreach($qryRes as $key => $fetchRes) 
        {
           $id = $fetchRes['id'];
           $total_user_fetch = $this->db->pdoQuery("select l.id,u.id As uId from tbl_login_history As l 
            LEFT JOIN tbl_users As u On u.id = l.userId where l.ip='".$fetchRes['ip']."' group by l.userId")->results();
           //print_r(count($total_user));
           //exit;
           //print_r("select count(id) As totalUser from tbl_login_history where ip='".$fetchRes['ip']."' and createdDate='".$fetchRes['createdDate']."' ");
           //exit;
           $date = date(DATE_FORMAT_ADMIN,strtotime($fetchRes['createdDate']));
           $time = date('H:i:s',strtotime($fetchRes['createdDate']));
           $ip = $fetchRes['ip'];
           $total_user = count($total_user_fetch)."&nbsp;&nbsp;<a href='ajax." . $this->module . ".php?action=userlist&ip=".$fetchRes['ip']."' type='button' class='user_popup_show btn-viewuserhistory btn-sm btn-primary' data-page_title='".$fetchRes['ip']."'>Show</a>";
           $row_data[] = array($id,$date, $time, $ip ,$total_user);
        }
        $result["sEcho"] = $sEcho;
        $result["iTotalRecords"] = (int)$totalRow;
        $result["iTotalDisplayRecords"] = (int)$totalRow;
        $result["aaData"] = $row_data;
        return $result;

    }

    public function ipUserList()
    {
        return $main_content = (new MainTemplater(DIR_ADMIN_TMPL.$this->module."/view_ipbase_user_datatable-sd.skd"))->compile();
    }

    public function userlistIpbase()
    {
        $content = $operation = $whereCond = $totalRow = NULL;
        $result = $tmp_rows = $row_data = array();
        extract($this->searchArray);

        //$totalRow = $this->db->pdoQuery("select count(id) As totalrecord from tbl_login_history where ip='".$_REQUEST['ip']."' ")->result();       

        if (isset($sort)) {
            $sorting = $sort . ' ' . $order;
        } else {
            $sorting = 'u.userName DESC';
        }

        $totalRow = $this->db->pdoQuery("select l.*,u.*,u.id As uId from tbl_login_history As l 
        LEFT JOIN tbl_users As u On u.id = l.userId where l.ip='".$_REQUEST['ip']."'  AND is_default_usertype='y' ORDER BY " . $sorting )->affectedRows();
        $qryRes = $this->db->pdoQuery("select l.*,u.*,u.id As uId from tbl_login_history As l 
        LEFT JOIN tbl_users As u On u.id = l.userId where l.ip='".$_REQUEST['ip']."'  AND is_default_usertype='y' ORDER BY  $sorting LIMIT $offset,$rows" )->results();
        foreach($qryRes as $key => $fetchRes) 
        {     
           $id = $fetchRes['uId'];
           $username = ucfirst($fetchRes['userName']);
           $email = $fetchRes['email'];
           $row_data[] = array($id,$username,$email);
        }
        $result["sEcho"] = $sEcho;
        $result["iTotalRecords"] = (int)$totalRow;
        $result["iTotalDisplayRecords"] = (int)$totalRow;
        $result["aaData"] = $row_data;
        return $result;
    }


    public function viewForm() {
        $img1=($this->profileImg!='')? $this->profileImg : "no_user_image.png";
        $img="<img src='".SITE_USER_PROFILE.$img1."' alt='".$this->userName." profile' height='100px' width='100px'></img>";
        $userType = ($this->userType == 'C') ?'Customer' : 'Freelancer';
        $content =$this->displayBox(array("label" => "User Type&nbsp;:", "value" => $userType));
        $content.=$this->displayBox(array("label" => "First Name&nbsp;:", "value" => $this->firstName));
        $content.=$this->displayBox(array("label" => "Last Name&nbsp;:", "value" => $this->lastName));
        $content.=$this->displayBox(array("label" => "Profile Image&nbsp;:", "value" => $img));
        $content.=$this->displayBox(array("label" => "Registered On&nbsp;:", "value" => date(DATE_FORMAT_ADMIN,strtotime($this->createdDate))));       
        $content.=$this->displayBox(array("label" => "Location&nbsp;:", "value" => $this->location));                         
        $content.=$this->displayBox(array("label" => "Status&nbsp;:", "value" => $this->status == 'a' ? 'Active' : 'Deactivate'));
        return $content;
    }
    public function viewCounterForm() {
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/view_counters-sd.skd");
        $main_content = $main_content->compile();
        $jobs_res =  $this->db->pdoQuery("SELECT COUNT(id) as posted,SUM(jobStatus='p') as pending,SUM(jobStatus='co') as completed, SUM(jobStatus NOT IN('c','dsc','dsCo','co')) as live
            FROM tbl_jobs WHERE posterId=".$this->id)->result();
        $invited_jobs =  $this->db->pdoQuery("SELECT SUM(status='a') as accepted,SUM(status='r') as rejected FROM `tbl_job_invitation` WHERE status='a' AND freelancerId=".$this->id)->result(); 
        $private_jobs =  $this->db->pdoQuery("SELECT SUM(ji.customerid=?)AS sent_req,SUM(ji.freelancerid=?) AS rec_req 
                                            FROM tbl_jobs AS j 
                                            LEFT JOIN tbl_job_invitation AS ji ON(ji.jobid=j.id) WHERE ji.id>0 AND jobtype='pr'",array($this->id,$this->id))->result(); 
        $service_res =  $this->db->pdoQuery("SELECT COUNT(DISTINCT(s.id)) as posted,
                                            COUNT(DISTINCT(so.id)) as orders,
                                            SUM(so.serviceStatus NOT IN ('no','in','p')) as pending, 
                                            SUM(so.serviceStatus='c') as completed,
                                            SUM(so.orderStatus='c') as sold,
                                            COUNT(DISTINCT (case when isActive='y' then s.id end)) as live
                                            FROM tbl_services as s 
                                            LEFT JOIN tbl_services_order as so ON(so.servicesId=s.id) 
                                            WHERE s.freelanserId=".$this->id)->result();
        $revenue_res = $this->db->pdoQuery("SELECT SUM(amount) as revenue_earned
            FROM tbl_wallet 
            WHERE userType='f' AND status='completed' AND userId=".$this->id)->result();
        $job_commission_res = $this->db->pdoQuery("SELECT SUM(amount) as commission FROM `tbl_admin_commision` as c 
                                            JOIN tbl_jobs as j ON (c.entityId=j.id)
                                            WHERE j.posterId=".$this->id)->result();
        $service_commission_res = $this->db->pdoQuery("SELECT SUM(amount) as commission FROM `tbl_admin_commision` as c 
                                            JOIN tbl_jobs as j ON (c.entityId=j.id)
                                            WHERE j.posterId=".$this->id)->result();

        $redeem_res = $this->db->pdoQuery("SELECT COUNT(id) as redeem_request, SUM(amount) as redeem_amount 
                                        FROM tbl_redeem_request 
                                        WHERE userId=".$this->id)->result();
        $fields = array(
            "%JOBS_POSTED%",
            "%JOBS_ACCEPTED%",
            "%JOBS_REJECTED%",
            "%PENDING_JOBS%",
            "%COMPLETED_JOBS%",
            "%PRIVATE_JOB_REQUESTS_SENT%",
            "%PRIVATE_JOB_REQUESTS_RECEIVED%",
            "%LIVE_JOBS%",
            "%SERVICES_POSTED%",
            "%SERVICE_ORDERS%",
            "%COMPLETED_SERVICES%",
            "%PENDING_SERVICES%",
            "%SOLD_SERVICES%",
            "%LIVE_SERVICES%",
            "%REVENUE_EARNED%",
            "%COMMISSION_PAID%",
            "%REDEEM_REQUEST%",
            "%REDEEM_AMOUNT%",
        );

        $fields_replace = array(
            !empty($jobs_res['posted'])?$jobs_res['posted']:0,
            !empty($invited_jobs['accepted'])?$invited_jobs['accepted']:0,
            !empty($invited_jobs['rejected'])?$invited_jobs['rejected']:0,
            !empty($jobs_res['pending'])?$jobs_res['pending']:0,
            !empty($jobs_res['completed'])?$jobs_res['completed']:0,
            !empty($private_jobs['sent_req'])?$private_jobs['sent_req']:0,
            !empty($private_jobs['rec_req'])?$private_jobs['rec_req']:0,
            !empty($jobs_res['live'])?$jobs_res['live']:0,
            !empty($service_res['posted'])?$service_res['posted']:0,
            !empty($service_res['orders'])?$service_res['orders']:0,
            !empty($service_res['completed'])?$service_res['completed']:0,
            !empty($service_res['pending'])?$service_res['pending']:0,
            !empty($service_res['sold'])?$service_res['sold']:0,
            !empty($service_res['live'])?$service_res['live']:0,
            !empty($revenue_res['revenue_earned'])?$revenue_res['revenue_earned']:0,
            ($job_commission_res["commission"]+$service_commission_res['commission']),
            !empty($redeem_res['redeem_request'])?$redeem_res['redeem_request']:0,
            !empty($redeem_res['redeem_amount'])?$redeem_res['redeem_amount']:0,

        );

        $content = str_replace($fields, $fields_replace, $main_content);
        return sanitize_output($content);
    }
    public function getForm() {
        $img=$content="";
        if($this->profileImg!=""){
            $img=SITE_UPD."profile/".$this->data['profileImg'];
        }else{
            $img=SITE_UPD."th2_no_user_image.png";
        }
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/form-sd.skd");
        $main_content = $main_content->compile();
        $static_a = ($this->status == 'a' ? 'checked' : '');
        $static_d = ($this->status != 'a' ? 'checked' : '');
        $gender_m = ($this->gender == 'm' ? 'checked' : '');
        $gender_f = ($this->gender == 'f' ? 'checked' : '');
        $gender_n = ($this->gender == 'n' ? 'checked' : '');
        $fields = array(
            "%FIRST_NAME%",
            "%LAST_NAME%",
            "%ADDRESS%",
            "%IMAGE%",
            "%PROFILE_IMG%",
            "%BIRTH_DATE%",
            "%GENDER_M%",
            "%GENDER_F%",
            "%GENDER_N%",
            "%STATUS_A%",
            "%STATUS_D%",
            "%TYPE%",
            "%ID%"
        );

        $fields_replace = array(
            $this->data['firstName'],
            $this->data['lastName'],
            $this->data['location'],
            $img,
            $this->data['profile_img'],
            ($this->data['birthDate']=='0000-00-00' || $this->data['birthDate']=='' )?'':date('Y-m-d',strtotime($this->data['birthDate'])),
            $gender_m,
            $gender_f,
            $gender_n,
            $static_a,
            $static_d,
            $this->type,
            $this->id
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
            $whereCond .= "  WHERE (userName LIKE '%" . $chr . "%' OR email LIKE '%" . $chr . "%' OR location LIKE '%" . $chr . "%' OR DATE_FORMAT(createdDate, '" . MYSQL_DATE_FORMAT . "') LIKE '%" . $chr . "%')";
        }
        if (isset($day) && $day != '') {
            if ($whereCond) {
                $whereCond .= " AND ";
            } else {
                $whereCond .= " WHERE ";
            }
            $whereCond .= " DAY(u.createdDate) = '" . $day . "' ";
        }
        if (isset($month) && $month != '') {
            if ($whereCond) {
                $whereCond .= " AND ";
            } else {
                $whereCond .= " WHERE ";
            }
            $whereCond .= " MONTH(u.createdDate) = '" . $month . "' ";
        }
        if (isset($year) && $year != '') {
            if ($whereCond) {
                $whereCond .= " AND ";
            } else {
                $whereCond .= " WHERE ";
            }
            $whereCond .= " YEAR(u.createdDate) = '" . $year . "' ";
        }
        if (isset($filterLocation) && $filterLocation != ''){
             if ($whereCond) {
                $whereCond .= " AND ";
            } else {
                $whereCond .= " WHERE ";
            }
            $whereCond .= " u.location = '$filterLocation'";
        }
         if (isset($filterCompany) && $filterCompany != ''){
             if ($whereCond) {
                $whereCond .= " AND ";
            } else {
                $whereCond .= " WHERE ";
            }
            $whereCond .= " u.userType = '$filterCompany'";
        }

        if (isset($sort)) {
            $sorting = $sort . ' ' . $order;
        } else {
            $sorting = 'id DESC';
        }
        $query = "SELECT u.* FROM tbl_users u ".$whereCond." GROUP BY u.email ORDER BY ".$sorting;
        $query_with_limit = $query." LIMIT ".$offset. " ," . $rows . " ";

        $totalUsers = $this->db->pdoQuery($query)->results();

        $qrySel = $this->db->pdoQuery($query_with_limit)->results();
        $totalRow = count($totalUsers);


        foreach ($qrySel as $fetchRes){
            $cnt = 0;
            if($fetchRes['userType'] == 'C'){
                $jdata = $this->db->pdoQuery("select * from tbl_jobs where posterId =".$fetchRes['id']." and jobStatus != 'c' || jobStatus != 'co' || jobStatus != 'dsCo' || jobStatus != 'dsc' ")->results();
                $cnt = count($jdata);
            } else if($fetchRes['userType'] == 'F'){
                $sdata = $this->db->pdoQuery("select * from tbl_services_order where (freelanserId = ".$fetchRes['id']." and serviceStatus != 'c') or (freelanserId =".$fetchRes['id']." and serviceStatus != 'cl') ")->results();
                $cnt = count($sdata);
                // echo  "<br>".$fetchRes['userType']."   User Id ".$fetchRes['id']."  Couter ".$cnt;
            }
           
            $status = ($fetchRes['isActive'] == "y") ? "checked" : "";
            $switch = (in_array('status', $this->Permission)) ? $this->toggel_switch(array("action" => "ajax." . $this->module . ".php?id=" . $fetchRes['id'] . "", "check" => $status)) : '';
            $operation = $type ='';
            /* If User is freelancer then redirect to front profile page */
            /*if($fetchRes['userType']=='F')
            {
                $type='&type=F';
                $class = '';
            } 
            else 
            {*/
                $class='btn-viewbtn';
            /*}
*/
            $operation .=(in_array('view', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=view&id=" . $fetchRes['id'],"class" => "btn default blue $class","extraAtt" => "title = 'View' target='_blank'","value" => '<i class="fa fa-laptop"></i>')) : '';

            $operation .=(in_array('view', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=login_history&id=" . $fetchRes['id'] . "", "class" => "btn btn-warning btn-viewbtn","extraAtt" => "title = 'View Login History'", "value" => '<i class="fa fa-history"></i>',"title"=>"Login history")) : '';

             $operation .=(in_array('view', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=view_counter&id=" . $fetchRes['id'] ."$type","class" => "btn default black btn-viewbtn","extraAtt" => "title = 'View Counters' target='_blank'","value" => '<i class="fa fa-eye"></i>')) : '';
            
            if($fetchRes['isDeleted'] == 'n' && $cnt == 0)
            {
                $operation .=(in_array('delete', $this->Permission)) ?$this->operation(array("href" => "javascript:void(0);","extraAtt" => "title = 'Delete' data-id='".$fetchRes['id']."' ", "class" => "btn default red user_delete", "value" => '<i class="fa fa-trash-o"></i>')) : '';
            } else if($fetchRes['isDeleted'] == 'y' && $cnt == 0){
                $operation .=(in_array('undo', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=undo&id=" . $fetchRes['id'] . "","extraAtt" => "title = 'Undo'", "class" => "btn default btn-info btn-undo", "value" => '<i class="fa fa-reply"></i>')) : '';
                $operation .=(in_array('delete', $this->Permission)) ?$this->operation(array("href" =>  "ajax." . $this->module . ".php?action=perDelete&id=" . $fetchRes['id'] . "", "extraAtt" => "title = 'Permanent Delete' data-id='".$fetchRes['id']."'", "class" => "btn default red btn-perdelete", "value" => '<i class="fa fa-trash-o"></i>')) : '';
                // echo "<br>".$cnt;
            } else {
                echo ""; 
                // $operation .=(in_array('undo', $this->Permission)) ? '&nbsp;&nbsp;' . $this->operation(array("href" => "ajax." . $this->module . ".php?action=undo&id=" . $fetchRes['id'] . "","extraAtt" => "title = 'Undo'", "class" => "btn default btn-info btn-undo", "value" => '<i class="fa fa-reply"></i>')) : '';
            }

            $userName = (isset($fetchRes["userName"]) && $fetchRes["userName"] != '') ? $fetchRes["userName"] : 'N/A';
            $email = (isset($fetchRes["email"]) && $fetchRes["email"] != '') ? $fetchRes["email"] : 'N/A';
            $createdDate = (isset($fetchRes["createdDate"]) && $fetchRes["createdDate"] != '') ? $fetchRes["createdDate"] : 'N/A';

            $delete_status = ($fetchRes['isDeleted']=='y') ? "<span class='label label-warning'>&nbsp;User deleted</span>" : '';
            $userType = ($fetchRes['userType'] == 'C') ?'Customer' : 'Freelancer';
            $final_array = array(
                filtering($fetchRes['id'], 'output', 'int'),
                filtering($fetchRes['firstName']),
                filtering($fetchRes['lastName']),
                filtering($email).'<br>'.$delete_status,
                filtering($fetchRes['location']),
                $userType,
                filtering(date(DATE_FORMAT_ADMIN,strtotime($createdDate)))
            );
            $final_array = array_merge($final_array, array($switch));
            $final_array = array_merge($final_array, array($operation));
            $row_data[] = $final_array;
        }
        $result["sEcho"] = $sEcho;
        $result["iTotalRecords"] = (int) $totalRow;
        $result["iTotalDisplayRecords"] = (int) $totalRow;
        $result["aaData"] = $row_data;
        return $result;
    }
    public function img($text) {
        $text['src'] = isset($text['src']) ? $text['src'] : 'Enter Image Path Here: ';
        $text['height'] = isset($text['height']) ? '' . trim($text['height']) : '';
        $text['width'] = isset($text['width']) ? '' . trim($text['width']) : '';
        $text['alt'] = isset($text['alt']) ? '' . trim($text['alt']) : '';
            $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/img.skd");
            $main_content = $main_content->compile();
        $fields = array("%SRC%", "%ALT%", "%WIDTH%", "%HEIGHT%");
        $fields_replace = array($text['src'], $text['alt'], $text['width'], $text['height']);
        return str_replace($fields,$fields_replace,$main_content);
    }
    public function getPageContent() {
        $final_result = NULL;
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/" . $this->module . ".skd");
        $main_content->breadcrumb = $this->getBreadcrumb();
        $final_result = $main_content->compile();
        $qryCompany = $this->db->pdoQuery("SELECT * from tbl_users WHERE isActive='y'")->results();
            
                    $fields = array(
                    "%USER_LOCATION_OPTIONS%" => $this->getLocation(),
                    // "%USER_TYPE_OPTIONS%" => $this->getUserType()
                );
            $final_result = str_replace(array_keys($fields), array_values($fields), $final_result);
           

       
        return $final_result;
    }
    public function getLocation(){
        $content='';

        $company_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/location_option-sd.skd");

        $company_content = $company_content->compile();
        $qryCompany = $this->db->pdoQuery("SELECT * from tbl_users WHERE isActive='y'")->results();
              foreach ($qryCompany as $key => $value) {
                    $fields = array(
                        "%LOCATION%" => filtering(ucfirst($value['location'])),
                        "%USER_ID%" => $value['id'],
                     );
            $content .= str_replace(array_keys($fields), array_values($fields), $company_content);
          }
        return $content;
    }
  
    public function contentSubmit($data,$Permission){

        $response = array();
        $response['status'] = false;
        extract($data);

        if($reason != ''){
            $objPost = new stdClass();
            $objPost->deleteDesc = isset($reason) ? $reason : ''; 
            $objPost->isDeleted = 'y'; 
            // $objPost->isActive = 'n';    
            $objPostArray = (array)$objPost;
            $this->db->update($this->table, $objPostArray, array("id" => $userId));

            $user = $this->db->select("tbl_users","*",array("id"=>$userId))->result();
            $arrayCont = array("USERNAME"=>$user['userName'],"REASON"=>$reason);
            $to = $user['email'];
            $array = generateEmailTemplate('delete_user_alert',$arrayCont);
            sendEmailAddress($to,$array['subject'],$array['message']);
        }

        $_SESSION["toastr_message"] = disMessage(array('type' => 'suc', 'var' => 'User deleted successfully'));
    }

    public function fileSubmit($data){
        $response = array();
        $response['status'] = false;
        $type = $_FILES["profile_img"]["type"];
        $fileName1 = $_FILES["profile_img"]["name"];
        $TmpName = $_FILES["profile_img"]["tmp_name"];
        $file_path=DIR_UPD."profile/";
        $type_array=array("image/jpeg","image/png","image/gif","image/x-png","image/jpg","image/x-png","image/x-jpeg","image/pjpeg","image/x-icon");
        if (in_array($type, $type_array)) {
            $height_width_array = array('height' => 130, 'width' => 110);
            $fileName = GenerateThumbnail($fileName1, $file_path, $TmpName, array($height_width_array));
            return $fileName;
        }
        else{
            $response['error'] = "Only image file is allowed";
            echo json_encode($response);
            exit;
        }
    }
}
