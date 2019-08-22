<?php

class Home {

    public function __construct() {
        foreach ($GLOBALS as $key => $values) {
            $this->$key = $values;
        }
    }

    public function index() {
        $content = NULL;
        return $content;
    }
    
    public function getViewAllBtn() {
        $content = '';

        $view_all_btn = new MainTemplater(DIR_ADMIN_TMPL . "/view-all-btn-sd.skd");
        $view_all_btn->put('module_url', SITE_ADM_MOD . $this->module);

        $content = $view_all_btn->compile();

        return $content;
    }

    public function getLeftMenu() {
        $admSl = $this->db->select("tbl_admin", array("adminType"), array("id =" => (int) $this->adminUserId))->result();

        $final_result = NULL;
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . "left_panel-sd.skd");

        $sub_content_menu = new MainTemplater(DIR_ADMIN_TMPL . "left_panel_menu-sd.skd");
        $sub_content_menu = $sub_content_menu->compile();

        $sub_content_submenu = new MainTemplater(DIR_ADMIN_TMPL . "left_panel_submenu-sd.skd");
        $sub_content_submenu = $sub_content_submenu->compile();

        $sub_content_submenu_item = new MainTemplater(DIR_ADMIN_TMPL . "left_panel_submenu_item-sd.skd");
        $sub_content_submenu_item = $sub_content_submenu_item->compile();

        $fields = array("%IMAGE%", "%SECTION_NAME%", "%SUBMENU_LIST%");
        $fields_submenu = array("%SUBMENU_ITEMS%");
        $fields_submenu_item = array("%PAGE_NAME%", "%PAGE_URL%", "%TITLE%");

        $qrySel = $this->db->select("tbl_adminsection", array("id", "section_name", "image"), array("id >" => 0), "ORDER BY `order` ASC")->results();
        if (!empty($qrySel[0]) > 0) {

            $sub_final_result = '';

            foreach ($qrySel as $fetchRes) {
                $sub_final_result_submenu_item = $sub_final_result_submenu = NULL;
                $id = $fetchRes['id'];
                $qSelMenu = $this->db->select("tbl_adminrole", array('id,title,pagenm'), array("sectionid" => $id, "and status !=" => "d"), "ORDER BY seq ASC")->results();
                $qSelMenu_sub = $this->db->select("tbl_adminrole", array('GROUP_CONCAT(id) as id'), array("sectionid" => $id, "and status !=" => "d"))->result();

                if ($qSelMenu_sub['id'] != '') {
                    //echo $qSelMenu_sub['id'];
                    //exit;
                    $qSelMenu_total = $this->db->pdoQuery("select count(id) as total_records from tbl_admin_permission where admin_id = '" . (int) $this->adminUserId . "' and page_id in (" . $qSelMenu_sub['id'] . ") and permission!=''")->result();
                    $totalRow = $qSelMenu_total['total_records'];
                }

                if (!empty($qSelMenu[0]) > 0) {
                    foreach ($qSelMenu as $fetchMenu) {
                        $chkPermssion = $this->db->select("tbl_admin_permission", array("permission"), array("admin_id" => (int) $this->adminUserId, "page_id" => $fetchMenu['id']))->result();
                        if ((!empty($chkPermssion['permission'])) || $admSl['adminType'] != 'g') {
                            $title = $fetchMenu['title'];
                            $pagenm = $fetchMenu['pagenm'];
                            $fields_replace_submenu_item = array($pagenm, SITE_ADM_MOD . $pagenm, $title);
                            $sub_final_result_submenu_item .=str_replace($fields_submenu_item, $fields_replace_submenu_item, $sub_content_submenu_item);
                        }
                    }
                    $fields_replace_submenu = array($sub_final_result_submenu_item);
                    $sub_final_result_submenu .= str_replace($fields_submenu, $fields_replace_submenu, $sub_content_submenu);
                }

                //(!empty($chkPermssion['permission']) || 

                if ($totalRow > 0 || $admSl['adminType'] != 'g') {
                    $fields_replace = array($fetchRes['image'], $fetchRes['section_name'], $sub_final_result_submenu);
                    $sub_final_result .= str_replace($fields, $fields_replace, $sub_content_menu);
                }
            }
        }

        $main_content->put('getMenuList', $sub_final_result);
        $final_result = $main_content->compile();

        return $final_result;
    }

    public function SubadminAction() {
        $final_result = array();
        $qryRes = $this->db->pdoQuery("SELECT id,constant,title FROM tbl_subadmin_action")->results();
        foreach ($qryRes as $fetchRes) {
            $id = (isset($fetchRes['id'])) ? $fetchRes['id'] : 0;
            //$constant = (isset($fetchRes['constant']))?$fetchRes['constant']:'';
            $title = (isset($fetchRes['title'])) ? $fetchRes['title'] : '';

            $final_result = $final_result + array($id => $title);
        }
        return $final_result;
    }

    public function SubadminActionDetails() {
        $final_result = array();
        $qryRes = $this->db->pdoQuery("SELECT id,constant,title FROM tbl_subadmin_action")->results();
        foreach ($qryRes as $fetchRes) {
            $id = (isset($fetchRes['id'])) ? $fetchRes['id'] : 0;
            $constant = (isset($fetchRes['constant'])) ? $fetchRes['constant'] : '';
            $title = (isset($fetchRes['title'])) ? $fetchRes['title'] : '';

            $final_result[] = array("id" => $id, "constant" => $constant, "title" => $title);
        }
        return $final_result;
    }

    public function adminPageList() {
        $final_result = array();
        $qryRes = $this->db->pdoQuery("SELECT id,title,pagenm,page_action FROM tbl_adminrole WHERE status='a'")->results();
        foreach ($qryRes as $fetchRes) {
            $id = (isset($fetchRes['id'])) ? $fetchRes['id'] : 0;
            $title = (isset($fetchRes['title'])) ? $fetchRes['title'] : '';
            $pagenm = (isset($fetchRes['pagenm'])) ? $fetchRes['pagenm'] : '';
            $page_action = (isset($fetchRes['page_action'])) ? $fetchRes['page_action'] : 0;
            $page_action_id = array();
            if ($page_action != '') {
                $qryRes_sub = $this->db->pdoQuery("SELECT id,title FROM tbl_subadmin_action WHERE id in (" . $page_action . ")")->results();
                foreach ($qryRes_sub as $fetchRes_sub) {
                    $page_action_id[] = (isset($fetchRes_sub['title'])) ? $fetchRes_sub['title'] : '';
                }
            }
            $final_result[] = array("id" => $id, "title" => $title, "pagenm" => $pagenm, "pagenm" => $pagenm, "page_action" => $page_action, "page_action_id" => $page_action_id);
        }
        return $final_result;
    }

    public function getBreadcrumb() {

        $final_result = $sub_final_result = NULL;
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . "breadcrumb-sd.skd");
        $content_list = new MainTemplater(DIR_ADMIN_TMPL . "breadcrumb_item-sd.skd");
        $content_list = $content_list->compile();
        $field_array = array("%TITLE%");
        $data = $this->breadcrumb;

        for ($i = 0; $i < count($data); $i++) {
            $replace = array($data[$i]);
            $sub_final_result .= str_replace($field_array, $replace, $content_list);
        }
        $main_content->put("breadcrumb_list", $sub_final_result);
        $final_result = $main_content->compile();
        return $final_result;
    }

    public function getSelectBoxOption() {
        $content = '';
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . "select_option-sd.skd");
        $content = $main_content->compile();

        return sanitize_output($content);
    }
   
    public function getReportsArray($report_type, $month = '', $year = '', $report_tenure = 'monthly') {
        $final_result = NULL;
        
        $response_array = $result_array = $categories = array();

        if (!$month || !$year) {
            $month = date('m');
            $year = date('Y');
        }

        $sql_query = '';
        if ($report_type == 'users') {
            if ($report_tenure == 'monthly') {
                $sql_query = "SELECT DAY(createdDate) as day, MONTH(createdDate) as month, YEAR(createdDate) as year, COUNT(id) no_of_statistics 
                    FROM tbl_users 
                    WHERE YEAR(createdDate) = '" . $year . "' AND MONTH(createdDate) = '" . $month . "' 
                    GROUP BY YEAR(createdDate), MONTH(createdDate), DAY(createdDate)";
            } else {
                $sql_query = "SELECT DAY(createdDate) as day, MONTH(createdDate) as month, YEAR(createdDate) as year, COUNT(id) no_of_statistics 
                    FROM tbl_users 
                    WHERE YEAR(createdDate) = '" . $year . "'  
                    GROUP BY YEAR(createdDate), MONTH(createdDate) ";
            }
        } else if ($report_type == 'jobs') {
            if ($report_tenure == 'monthly') {
                $sql_query = "SELECT DAY(added_on) as day, MONTH(added_on) as month, YEAR(added_on) as year, COUNT(id) no_of_statistics 
                    FROM tbl_jobs 
                    WHERE YEAR(added_on) = '" . $year . "' AND MONTH(added_on) = '" . $month . "' 
                    GROUP BY YEAR(added_on), MONTH(added_on), DAY(added_on)";
            } else {
                $sql_query = "SELECT DAY(added_on) as day, MONTH(added_on) as month, YEAR(added_on) as year, COUNT(id) no_of_statistics 
                    FROM tbl_jobs 
                    WHERE YEAR(added_on) = '" . $year . "'  
                    GROUP BY YEAR(added_on), MONTH(added_on) ";
            }
        } else if ($report_type == 'companies') {
            if ($report_tenure == 'monthly') {
                $sql_query = "SELECT DAY(added_on) as day, MONTH(added_on) as month, YEAR(added_on) as year, COUNT(id) no_of_statistics 
                    FROM tbl_companies 
                    WHERE YEAR(added_on) = '" . $year . "' AND MONTH(added_on) = '" . $month . "' 
                    GROUP BY YEAR(added_on), MONTH(added_on), DAY(added_on)";
            } else {
                $sql_query = "SELECT DAY(added_on) as day, MONTH(added_on) as month, YEAR(added_on) as year, COUNT(id) no_of_statistics 
                    FROM tbl_companies 
                    WHERE YEAR(added_on) = '" . $year . "'  
                    GROUP BY YEAR(added_on), MONTH(added_on) ";
            }
        } else if ($report_type == 'groups') {
            if ($report_tenure == 'monthly') {
                $sql_query = "SELECT DAY(added_on) as day, MONTH(added_on) as month, YEAR(added_on) as year, COUNT(id) no_of_statistics 
                    FROM tbl_groups 
                    WHERE YEAR(added_on) = '" . $year . "' AND MONTH(added_on) = '" . $month . "' 
                    GROUP BY YEAR(added_on), MONTH(added_on), DAY(added_on)";
            } else {
                $sql_query = "SELECT DAY(added_on) as day, MONTH(added_on) as month, YEAR(added_on) as year, COUNT(id) no_of_statistics 
                    FROM tbl_groups 
                    WHERE YEAR(added_on) = '" . $year . "'  
                    GROUP BY YEAR(added_on), MONTH(added_on) ";
            }
        }

        $report_data = $this->db->pdoQuery($sql_query)->results();

        if ($report_tenure == 'monthly') {
            $number_of_days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            for ($i = 1; $i <= $number_of_days; $i++) {
                $response = searchInMultidimensionalArray($report_data, 'day', $i);
                if ($response['status']) {
                    $key = $response['key'];
                    $no_of_statistics = (int) $report_data[$key]['no_of_statistics'];
                } else {
                    $no_of_statistics = 0;
                }
                $date = convertDate('onlyDate', $i . "-" . $month . "-" . $year);                
                //$date = strtotime($i . "-" . $month . "-" . $year);
                $result_array[] = array( (string)$i, $no_of_statistics);                
                $categories[] = $i;
            }
        } else {
            for ($i = 1; $i <= 12; $i++) {
                $response = searchInMultidimensionalArray($report_data, 'month', $i);
                if ($response['status']) {
                    $key = $response['key'];
                    $no_of_statistics = (int) $report_data[$key]['no_of_statistics'];
                } else {
                    $no_of_statistics = 0;
                }
                $date = convertDate('onlyMonth', "01-" . $i . "-" . $year);                
                $result_array[] = array($date, $no_of_statistics);
                $categories[] = $i;
            }
        }
        
        $response_array['data'] = json_encode($result_array);
        $response_array['categories'] = json_encode($categories);
        return json_encode($result_array);
    }

    public function getMonthsDD($report_type, $selected_month_no) {
        $final_result = NULL;
        $month_options = '';

        $getSelectBoxOption = $this->getSelectBoxOption();
        $fields = array("%VALUE%", "%SELECTED%", "%DISPLAY_VALUE%");

        $formattedMonthArray = array(
            "1" => "January",
            "2" => "February",
            "3" => "March",
            "4" => "April",
            "5" => "May",
            "6" => "June",
            "7" => "July",
            "8" => "August",
            "9" => "September",
            "10" => "October",
            "11" => "November",
            "12" => "December"
        );
        foreach ($formattedMonthArray as $month_no => $month_name) {
            $selected = ($selected_month_no == $month_no) ? "selected" : "";

            $fields_replace = array($month_no, $selected, $month_name);

            $month_options .= str_replace($fields, $fields_replace, $getSelectBoxOption);
        }

        $months_dd = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/months-dd-sd.skd");
        $months_dd_parsed = $months_dd->compile();


        $fields_month = array("%REPORT_TYPE%", "%MONTH_OPTIONS%");

        $fields_month_replace = array($report_type, $month_options);

        $final_result = str_replace($fields_month, $fields_month_replace, $months_dd_parsed);

        return $final_result;
    }

    public function getYearDD($report_type, $selected_year) {
        $final_result = NULL;
        $year_options = '';


        $getSelectBoxOption = $this->getSelectBoxOption();
        $fields = array("%VALUE%", "%SELECTED%", "%DISPLAY_VALUE%");

        for ($i = 2015; $i <= date('Y'); $i++) {
            $selected = ($selected_year == $i) ? "selected" : "";

            $fields_replace = array($i, $selected, $i);

            $year_options .= str_replace($fields, $fields_replace, $getSelectBoxOption);
        }

        $years_dd = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/years-dd-sd.skd");
        $years_dd_parsed = $years_dd->compile();

        $fields_year = array("%REPORT_TYPE%", "%YEAR_OPTIONS%");

        $fields_year_replace = array($report_type, $year_options);

        $final_result = str_replace($fields_year, $fields_year_replace, $years_dd_parsed);

        return $final_result;
    }

    /*filter based on date*/
    public function getStatisticsCount($date){
        print_r($date);exit();
        if(!empty($date)) 
        {
             $start_date = $date[0]." 00:00:00";
             $end_date = $date[1]." 23:59:00";
             $whereCond = "(createdDate BETWEEN '$start_date' AND '$end_date')";
             $whereCond2 = "(jobPostDate BETWEEN '$start_date' AND '$end_date')";
             $whereCond3 = "(orderDate BETWEEN '$start_date' AND '$end_date')";
        }
        $getUsers=$this->db->pdoQuery("SELECT count(id) as totalUser FROM tbl_users WHERE  $whereCond")->result();
        $getJobs=$this->db->pdoQuery("SELECT count(id) as totalJobs FROM tbl_jobs WHERE  $whereCond2")->result();
        $getServices=$this->db->pdoQuery("SELECT count(id) as totalServices FROM tbl_services_order WHERE  $whereCond3")->result();
        $total_revenue = $this->db->pdoQuery("SELECT sum(budget) as jobBudget FROM tbl_jobs WHERE $whereCond2")->result();
        $total_service_amount = $this->db->pdoQuery("SELECT sum(totalPayment) as serviceAmount from tbl_services_order WHERE $whereCond3")->result();
        $total_revenue = $total_revenue['jobBudget'] + $total_service_amount['serviceAmount'];
        $net_profit = $this->db->pdoQuery("SELECT sum(amount) as profit from tbl_wallet where transactionType='escrow' and status='onhold' AND $whereCond ")->result();
        $total_active_jobs = $this->db->pdoQuery("select count(id) As total from tbl_jobs where (jobStatus='ip' OR jobStatus='ud' OR jobStatus='dsp' OR jobStatus='p') and isApproved='a' and isActive='y' ANd $whereCond2  ")->result();
        $total_active_orders = $this->db->pdoQuery("select count(id) As total from tbl_services_order where (orderStatus='c') AND ( serviceStatus='ip' OR serviceStatus='ar' OR serviceStatus='ud')  AND $whereCond3 ")->result();
        $redeem_request = $this->db->pdoQuery("select count(id) As total from tbl_redeem_request where paymentStatus='pending' AND $whereCond")->result();
        $final_data['getUsers']=$getUsers['totalUser'];
        $final_data['getJobs']=$getJobs['totalJobs'];
        $final_data['getServices']=$getServices['totalServices'];
        $final_data['getRevenue']=CURRENCY_SYMBOL.$total_revenue;
        $final_data['getNetProfit']=CURRENCY_SYMBOL.(!empty($net_profit['profit']) ? $net_profit['profit'] : 0 );
        $final_data['getActiveJobs']= $total_active_jobs['total'];
        $final_data['getActiveOrders']= $total_active_orders['total'];
        $final_data['getRedeemRequ']= $redeem_request['total'];
        return $final_data;
    }

    public function getPageContent() {
       
        $admSl = $this->db->select("tbl_admin", array("adminType"), array("id =" => (int) $this->adminUserId))->result();
        $final_result = NULL;

        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/" . $this->module . ".skd");
        $main_content->breadcrumb = $this->getBreadcrumb();

        $sub_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/dashboard_list-sd.skd");
        $sub_content = $sub_content->compile();

        $fields = array(
            '%PAGE_LINK%',
            '%COLOR%',
            '%IMAGE%',
            '%PAGE_TITLE%',

        );

        $qSelMenu = $this->db->select("tbl_adminrole", array('id,title,pagenm,image'), array("status !=" => "d"), "ORDER BY seq ASC")->results();
        if (!empty($qSelMenu[0]) > 0) {
            $i = 0;
            $sub_final_result = '';
            $color_array = array("blue", "green", "red", "yellow", "dark", "purple");
            foreach ($qSelMenu as $fetchMenu) {
                $chkPermssion = $this->db->select("tbl_admin_permission", array("permission"), array("admin_id" => (int) $this->adminUserId, "page_id" => $fetchMenu['id']))->result();
                if ((!empty($chkPermssion['permission'])) || $admSl['adminType'] != 'g') {
                    $fields_replace = array(SITE_ADM_MOD . $fetchMenu['pagenm'], $color_array[$i], $fetchMenu['image'], $fetchMenu['title']);
                    $sub_final_result .=str_replace($fields, $fields_replace, $sub_content);
                    $i = ($i == 5) ? -1 : $i;
                    $i++;
                }
            }
        }
        $date = date('Y-m-d');
        // echo $date;
        // print_r(date('Y-M-d'));
        
         $start_date = $date[0]." 00:00:00";
             $end_date = $date[1]." 23:59:00";
             $whereCond = "(createdDate BETWEEN '$start_date' AND '$end_date')";
             $whereCond2 = "(jobPostDate BETWEEN '$start_date' AND '$end_date')";
             $whereCond3 = "(orderDate BETWEEN '$start_date' AND '$end_date')";
        
        $main_content->put('dashboard_list', $sub_final_result);

        $no_of_users = count($this->db->pdoQuery("SELECT id  FROM tbl_users where $whereCond")->results());
        //for bid of this week
       
        //for no of open listing
        $no_of_jobs = count($this->db->pdoQuery("SELECT count(id) as totalJobs FROM tbl_jobs WHERE  $whereCond2")->result());
        
        //for overall sale amount
        $no_of_services = count($this->db->pdoQuery("SELECT count(id) as totalServices FROM tbl_services_order WHERE  $whereCond3")->result());

        $total_revenue =$this->db->pdoQuery("SELECT sum(budget) as jobBudget FROM tbl_jobs WHERE  $whereCond2")->result();
        $total_service_amount = $this->db->pdoQuery("SELECT sum(totalPayment) as serviceAmount from tbl_services_order")->result();
        $total_revenue = count($total_revenue['jobBudget'] + $total_service_amount['serviceAmount']);
        $net_profit = $this->db->pdoQuery("SELECT sum(amount) as profit from tbl_wallet where transactionType='escrow' and status='onhold' AND $whereCond ")->result();

        $total_active_jobs = $this->db->pdoQuery("select count(id) As total from tbl_jobs where (jobStatus='ip' OR jobStatus='ud' OR jobStatus='dsp' OR jobStatus='p') and isApproved='a' and isActive='y' ANd $whereCond2  ")->result();
        $total_active_orders = $this->db->pdoQuery("select count(id) As total from tbl_services_order where (orderStatus='c') AND ( serviceStatus='ip' OR serviceStatus='ar' OR serviceStatus='ud')  AND $whereCond3 ")->result();

        $redeem_request = $this->db->pdoQuery("select count(id) As total from tbl_redeem_request where paymentStatus='pending' AND $whereCond ")->result();


        $net_profit = $net_profit['profit'];        
        $main_content->put('no_of_users', $no_of_users);
        $main_content->put('no_of_jobs', $no_of_jobs);
        $main_content->put('no_of_services', $no_of_jobs);
        $main_content->put('total_revenue', $total_revenue);

        $main_content_parsed = $main_content->compile();
        
        $fields_main_content = array(
            "%USER_REPORT_ARRAY%",
            "%MONTH_DD_USERS_REPORT%",
            "%YEAR_DD_USERS_REPORT%",
            "%TOTAL_USERS%",
            "%TOTAL_JOBS%",
            "%TOTAL_SERVICE_ORDERS%",
            "%TOTAL_REVENUE%",
            "%NET_PROFIT%",
            "%TOTAL_ACTIVE_JOBS%",
            "%TOTAL_ACTIVE_ORDERS%",
            "%TOTAL_REDEEM_REQUEST%",
            '%NOTIFIDCATION%',
            '%NOTIFIDCATION_CLASS%'
        );        
        $fields_replace_main_content = array(
            $this->getReportsArray('users'),
            $this->getMonthsDD('users', date('m')),
            $this->getYearDD('users', date("Y")),
            $no_of_users,
            $no_of_jobs,
            $no_of_services,
            CURRENCY_SYMBOL.$total_revenue,
            CURRENCY_SYMBOL.$net_profit,
            $total_active_jobs['total'],
            $total_active_orders['total'],
            $redeem_request['total'],
            $this->notification_loop(),
            $this->notification_loop('all')
        );
        $final_result = str_replace($fields_main_content, $fields_replace_main_content, $main_content_parsed);

        return $final_result;
    }
    public function notification_loop($data = '')
    {

        $query = $this->db->pdoQuery("select * from tbl_notification where notificationType=? ORDER BY id DESC LIMIT 6",array('a'))->results();
        $content = '';
        if($data=='all')
        {
            $content .= (count($query)=='0') ? 'hide' : '';
        }
        else
        {
                foreach ($query as $value) {
                    $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/notification_loop-sd.skd");
                    $main_content = $main_content->compile();

                    $data = array(
                            "%LINK%" => $value['detail_link'],
                            "%MSG%" => $value['message'],
                            "%TIME%" => getTime($value['createdDate'])
                        );
                    $content .= str_replace(array_keys($data), array_values($data), $main_content);
                }
        }
        return $content;
    }
    public function operation($text) {
        $text['href'] = !empty($text['href']) ? $text['href'] : 'Enter Link Here: ';
        $text['value'] = !empty($text['value']) ? $text['value'] : '';
        $text['name'] = !empty($text['name']) ? $text['name'] : '';
        $text['class'] = !empty($text['class']) ? '' . trim($text['class']) : '';
        $text['title'] = !empty($text['title']) ? '' . trim($text['title']) : '';
        $text['extraAtt'] = !empty($text['extraAtt']) ? $text['extraAtt'] : '';
        $main_content = (new MainTemplater(DIR_ADMIN_TMPL . '/operation-sd.skd'))->compile();
        $fields_replace = array(
            "%HREF%" => $text['href'],
            "%TITLE%" => $text['title'],
            "%CLASS%" => $text['class'],
            "%VALUE%" => $text['value'],
            "%EXTRA%" => $text['extraAtt']
        );
        return str_replace(array_keys($fields_replace), array_values($fields_replace), $main_content);
    }

    public function toggel_switch($text) {
        
        $text['action'] = isset($text['action']) ? $text['action'] : 'Enter Action Here: ';
        $text['check'] = isset($text['check']) ? $text['check'] : '';
        $text['name'] = isset($text['name']) ? $text['name'] : '';
        $text['class'] = isset($text['class']) ? '' . trim($text['class']) : '';
        $text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';
        $text['data-id'] = isset($text['data-id']) ? $text['data-id'] : '';

        $main_content = new MainTemplater(DIR_ADMIN_TMPL . '/switch-sd.skd');
        $main_content = $main_content->compile();
        $fields = array("%NAME%", "%CLASS%", "%ACTION%", "%EXTRA%", "%CHECK%","%DATAID%");
        $fields_replace = array($text['name'], $text['class'], $text['action'], $text['extraAtt'], $text['check'], $text['data-id']);
        return str_replace($fields, $fields_replace, $main_content);
    }
    public function displaybox($text) {

        $text['label'] = isset($text['label']) ? $text['label'] : 'Enter Text Here: ';
        $text['value'] = isset($text['value']) ? $text['value'] : '';
        $text['name'] = isset($text['name']) ? $text['name'] : '';
        $text['class'] = isset($text['class']) ? 'form-control-static ' . trim($text['class']) : 'form-control-static';
        $text['onlyField'] = isset($text['onlyField']) ? $text['onlyField'] : false;
        $text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';

        $main_content = new MainTemplater(DIR_ADMIN_TMPL. '/displaybox.skd');
        $main_content = $main_content->compile();
        $fields = array("%LABEL%", "%CLASS%", "%VALUE%");
        $fields_replace = array($text['label'], $text['class'], $text['value']);
        return str_replace($fields, $fields_replace, $main_content);
    }
    public function adminPagePermission($uId)
    {
        $final_result = array();
        $qryRes= $this->db->pdoQuery("
            SELECT ap.*, ar.id AS arId, ar.title, ar.pagenm, ar.page_action
            FROM tbl_admin_permission AS ap
            INNER JOIN tbl_adminrole AS ar ON (ap.page_id = ar.id)
            WHERE ap.admin_id=?
        ", array($uId))->results();
        foreach($qryRes as $keys => $fetchRes)
        {
            $page_action = ((isset($fetchRes['permission'])) ? $fetchRes['permission'] : 0);
            $page_action_id = array();

            if(!empty($page_action))
            {
                $qryRes_sub= $this->db->pdoQuery("SELECT id,title FROM tbl_subadmin_action WHERE id in (".$page_action.")")->results();
                foreach($qryRes_sub as $fetchRes_sub)
                {
                    $page_action_id[] = ((isset($fetchRes_sub['title'])) ? $fetchRes_sub['title'] : '');
                }
            }

            $final_result[] = array
            (
                "id" => $fetchRes['arId'],
                "title" => $fetchRes['title'],
                "pagenm" => $fetchRes['pagenm'],
                "page_action" => $page_action,
                "page_action_id" => $page_action_id
            );
        }
        return $final_result;
    }

}
