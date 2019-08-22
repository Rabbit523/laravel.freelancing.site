<?php

class FreelancerFinancialDashboard extends Home
{
  	function __construct($module = "", $id = 0, $token = "") {
  		foreach ($GLOBALS as $key => $values) {
  			$this->$key = $values;
  		}
  		$this->module = $module;
  		$this->id = $id;
  	}

  	public function getPageContent()
  	{
      global $sessUserId,$sessUserType;
  		  $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/freelancer_financial_dashboard-sd.skd");
        $sub_content = $sub_content->compile();

        $realUserId = $sessUserId;          
        $oppositeUserid = $this->getOppositeUserData();

        $financial_job = $this->get_job_data();
        $totalJobIncome = $this->get_job_data('all');
        $totalJobExpense = $this->get_job_expense_data($oppositeUserid);
        $totalJobPendingAmount = $this->get_job_data('all','p');
        $financial_service = $this->get_service_data();
        $totalServiceIncome = $this->get_service_data('all');
        $totalServiceExpense = $this->get_service_expense_data($oppositeUserid);
        $totalServicePendingAmount = $this->get_service_data('all','p');
        $totalamount = ($totalJobIncome+$totalServiceIncome);
        $total_expenses = ($totalJobExpense+$totalServiceExpense);
        $job_onhold_amnt = $this->onholdAmount('all');
        $services_onhold_amnt = $this->onholdServiceAmount('all');
        $totalOnholdAmount = ($job_onhold_amnt+$services_onhold_amnt);
        $user_detail = $this->db->pdoQuery("select * from tbl_users where id=?",array($this->sessUserId))->result();        
        $chart_res = $this->getChartData($realUserId,$oppositeUserid);

        $array = array( 
            "%SUB_HEADER_CONTENT%"=>subHeaderContent("financial"),
            "%TOTAL_INCOME%"=>$totalamount."<span>".CURRENCY_SYMBOL."</span>",
            "%TOTAL_EXPENSES%"=>$total_expenses."<span>".CURRENCY_SYMBOL."</span>",
            "%ONHOLD_AMOUNT%"=>$totalOnholdAmount."<span>".CURRENCY_SYMBOL."</span>",
            "%TOTAL_EARNINGS_FROM_JOBS%" => $totalJobIncome."<span>".CURRENCY_SYMBOL."</span>",
            "%TOTAL_PENDING_EARNINGS_FROM_JOBS%" =>  $totalJobPendingAmount ."<span>".CURRENCY_SYMBOL."</span>",
            "%FINANCIAL_JOB_DATA%" => $financial_job,
            "%TOTAL_EARNINGS_FROM_SERVICE%" => $totalServiceIncome."<span>".CURRENCY_SYMBOL."</span>",
            "%TOTAL_PENDING_EARNINGS_FROM_SERVICE%" => $totalServicePendingAmount."<span>".CURRENCY_SYMBOL."</span>",
            "%FINANCIAL_SERVICE_DATA%" => $financial_service,
            "%WALLET_AMOUNT%" => $user_detail["walletAmount"]."<span>".CURRENCY_SYMBOL."</span>",
            "%REAL_USER_ID%" => $realUserId,
            "%OPPOSITE_USER_ID%" => $oppositeUserid,
            "%CHART_HEADING%" => $chart_res["heading"],
            "%CHART_INCOME%" => $chart_res["income"],
            "%CHART_ENPENSE%" => $chart_res["expense"],
            "%SCALES_NAME%" => $chart_res["scales_name"],
            "%WALLET_HISTORY%" => $this->wallet_history(),
            "%TOTAL_WALLET_AMNT%" => ($this->wallet_history("all")=='') ? '0'.CURRENCY_SYMBOL : $this->wallet_history("all").CURRENCY_SYMBOL,
            "%TOTAL_REDEEM_AMOUNT%" => ($this->redeemRequest("all")=='') ? '0'.CURRENCY_SYMBOL : $this->redeemRequest("all").CURRENCY_SYMBOL,
            "%REDEEM_HISTORY%" => $this->redeemRequest(),
            "%JOB_HOLD_AMNT%" => $this->onholdAmount(),
            "%SERVICES_HOLD_AMNT%" => $this->onholdServiceAmount(),
            "%TOTAL_JOB_ONHOLD_AMNT%" => $this->onholdAmount('all').CURRENCY_SYMBOL,
            "%TOTAL_SERVICES_ONHOLD_AMNT%" => $this->onholdServiceAmount('all').CURRENCY_SYMBOL,
            "%PAYMENT_HISTORY_DATA%" => $this->payment_history_data(),
            '%CREDIT_PACKAGE_DATA%'=>$this->credit_package_data(),
            '%PAYMENT_SERVICE_DATA%'=>$this->payment_service_data(),
        );
  		  return str_replace(array_keys($array),array_values($array),$sub_content);
    }
    public function wallet_history($type='')
    {
        if($type=="all")
        {
            $query = $this->db->pdoQuery("select SUM(amount) As total_wallet_amnt from tbl_wallet where userId=? and transactionType=? ORDER BY id DESC",array($this->sessUserId,'paypal'))->result();
            $data = $query['total_wallet_amnt'];
        }
        else
        {
            $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/wallet_history-sd.skd");
            $sub_content = $sub_content->compile();

            $query = $this->db->pdoQuery("select * from tbl_wallet where userId=? and transactionType=? ORDER BY id DESC",array($this->sessUserId,'paypal'))->results();

            $data = '';
            if(count($query)==0)
            {
               $data .= "<tr><td colspan=5>No credit amount found</td></tr>";
            }
            else
            {
              foreach ($query as $value) {
                  $array = array(
                      "%TRANSACTOIN_ID%" => $value['transactionId'],
                      "%PAYMENT_STATUS%" => ($value['paymentStatus']=='p') ? 'Pending' : 'Completed',
                      "%DATE%" =>  date('dS F,Y',strtotime($value['createdDate'])),
                      "%AMNT%" => $value['amount'].CURRENCY_SYMBOL
                  );
                  $data .= str_replace(array_keys($array), array_replace($array), $sub_content);
              }
            }
          
        }
        return $data;
    }
    public function redeemRequest($type='')
    {
      $data = "";
      if($type=="all")
      {
        $query = $this->db->pdoQuery("select SUM(amount) AS totalRedeemAmnt from tbl_redeem_request where userId=? ",array($this->sessUserId))->result();
        $data .= $query['totalRedeemAmnt'];
      }
      else
      {
        $query = $this->db->pdoQuery("select * from tbl_redeem_request where userId=? ORDER BY id DESC",array($this->sessUserId))->results();
        if(count($query)>0)
        {
          foreach ($query as $value) {
            $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/redeem_request-sd.skd");
            $sub_content = $sub_content->compile();

            $transaction_query = $this->db->pdoQuery("select * from tbl_wallet where entity_id=? and entity_type='r'",array($value['id']));
            $transaction_detail = $transaction_query->result();
            $transaction_rows = $transaction_query->affectedRows();

            $date1 = date_create($value['createdDate']);
            $date2 = date_create(date('Y-m-d H:i:s'));

            $interval = $date1->diff($date2);
            $remind_class = ($value['paymentStatus'] == 'pending' && ($interval->format('%R%a days'))>1) ? '' : 'hide';
            $array = array(
                "%TRANSACTOIN_ID%" => ($transaction_rows>0) ? $transaction_detail['transactionId'] : 'N/A',
                "%TRANSACTION_DATE%" => ($transaction_rows>0) ? date('dS F,Y',strtotime($transaction_detail['createdDate'])) : 'N/A',
                "%REQUEST_SEND_DATE%" => date('dS M,Y H:i:s',strtotime($value['createdDate'])),
                "%REQUEST_AMNT%" => $value['amount'].CURRENCY_SYMBOL,
                "%REQUEST_STATUS%" => $value['paymentStatus'],
                "%REMIND_CLASS%" => $remind_class,
                "%REDEEM_ID%" => $value["id"]
              );
            $data .= str_replace(array_keys($array), array_replace($array), $sub_content);
          }
        }
        else
        {
          $data .= "<tr><td colspan=5>".NO_REDEEM_REQUEST_FOUND."</td></tr>";
        }
      }
      return $data;
    } 
    public function onholdAmount($dataVar="")
    {      
        if($dataVar=='all')
        {
            $query = $this->db->pdoQuery("select SUM(jb.budget) As holdAmount from tbl_job_bids As jb
              LEFT JOIN tbl_jobs As j ON j.id = jb.jobid
              where NOT EXISTS (SELECT * FROM tbl_milestones WHERE tbl_milestones.jobId = j.id) and
              jb.userId=? and jb.isHired=? and (j.jobStatus=? OR j.jobStatus=? OR j.jobStatus=?) ",array($this->sessUserId,'y','ip','ud','dsp'))->result();
            $query1 = $this->db->pdoQuery("select SUM(m.amount) As holdAmount from tbl_job_bids As jb
              LEFT JOIN tbl_jobs As j ON j.id = jb.jobid
              LEFT JOIN tbl_milestones As m ON m.jobid = jb.jobid where 
              jb.userId=? and jb.isHired=? and m.paymentStatus=? ",array($this->sessUserId,'y','p'))->result();

            $data = $query['holdAmount'] + $query1['holdAmount'];
        }
        else
        {
            $query = $this->db->pdoQuery("SELECT j.jobTitle,jb.budget As holdAmount 
              FROM tbl_job_bids As jb
              LEFT JOIN tbl_jobs As j ON j.id = jb.jobid
              where NOT EXISTS (SELECT * FROM tbl_milestones WHERE tbl_milestones.jobId = j.id) and
              jb.userId=? and jb.isHired=? and (j.jobStatus=? OR j.jobStatus=? OR j.jobStatus=?) GROUP BY j.id ORDER BY jb.id DESC",array($this->sessUserId,'y','ip','ud','dsp'))->results();
            $query1 = $this->db->pdoQuery("SELECT j.jobTitle,SUM(m.amount) As holdAmount 
              FROM tbl_job_bids As jb
              LEFT JOIN tbl_jobs As j ON j.id = jb.jobid
              LEFT JOIN tbl_milestones As m ON m.jobid = jb.jobid where 
              jb.userId=? and jb.isHired=? and m.paymentStatus=? ORDER BY jb.id DESC",array($this->sessUserId,'y','p'))->results();

            $final_array = array_merge($query,$query1);
            
            $data = '';
            if(count($query)==0 && count($query1)==0)
            {
                  $data .= "<tr><td colspan=2>".NO_HOLD_DATA_FOUND."</td></tr>";
            }
            else
            {
              foreach ($final_array as $value) {
                  $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/onhold_amount-sd.skd");
                  $sub_content = $sub_content->compile();
                  $array = array(
                      "%TITLE%" => filtering(ucfirst($value['jobTitle'])),
                      "%AMNT%" => $value['holdAmount'].CURRENCY_SYMBOL
                    );
                  $data .= str_replace(array_keys($array), array_replace($array), $sub_content); 
              }
            }
        }
      return $data;
    }

    public function onholdServiceAmount($dataVar="")
    {
        if($dataVar=='all')
        {
            $query = $this->db->pdoQuery("select SUM(totalPayment) As totalHoldAmount from tbl_services_order where freelanserId=? and paymentStatus=?",array($this->sessUserId,'p'))->result();
            $data = ($query['totalHoldAmount']=='') ? 0 : $query['totalHoldAmount'];
        }
        else
        {
            $query = $this->db->pdoQuery("select s.serviceTitle,so.totalPayment from tbl_services_order As so
              LEFT JOIN tbl_services As s ON s.id = so.servicesId
              where so.freelanserId=? and so.paymentStatus=? AND so.serviceStatus NOT IN('ud','ds','dsc','cl') ORDER BY so.id DESC",array($this->sessUserId,'p'))->results();
            
            $data = '';
            if(count($query)>0)
            {
              foreach ($query as $value) 
              {
                  $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/onhold_amount-sd.skd");
                  $sub_content = $sub_content->compile();

                  $array = array(
                        "%TITLE%" => filtering(ucfirst($value['serviceTitle'])),
                        "%AMNT%" => $value['totalPayment'].CURRENCY_SYMBOL
                      );
                  $data .= str_replace(array_keys($array), array_values($array), $sub_content);
              }
            }
            else
            {
              $data .= "<tr><td colspan=2>".NO_HOLD_DATA_FOUND."</td></tr>";
            }

        }
        return $data;
    } 
    public function payment_history_data()
    {
        $query = $this->db->pdoQuery("select s.serviceTitle,w.* from tbl_wallet As w
            LEFT JOIN tbl_services AS s ON s.id = w.entity_id
            where userId=? and transactionType=? and entity_type=?",array($this->sessUserId,'featuredFees',"s"))->results();
        $data = '';
        foreach ($query as $value) 
        {
            $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/payment_history_data-sd.skd");
            $sub_content = $sub_content->compile();

            $array = array(
                "%PAYMENT_DATE%" => date('dS F,Y',strtotime($value['createdDate'])),
                "%PAID_AMOUNT%" => CURRENCY_SYMBOL.$value['amount'],
                "%SERVICE_TITLE%" => filtering(ucfirst($value['serviceTitle'])),
                "%PAYMENT_STATUS%" => ($value['paymentStatus']=='p') ? PENDING_LABEL : C_SO_COMPLETED_LBL
                );

            $data .= str_replace(array_keys($array), array_replace($array), $sub_content);
        }

        if(empty($data)){
            $data = '<tr> <td colspan="4" style="text-align:center" > '.NO_RECORDS_FOUND.'</td> </tr>';
        }

        return $data;
    }

    public function credit_package_data()
    {
        $query = $this->db->pdoQuery("SELECT w.*,c.title FROM `tbl_wallet` as w LEFT JOIN tbl_credit_package as c on w.entity_id = c.id WHERE transactionType = 'creaditPurchase' AND w.userId = ? order by createdDate desc  ",array($this->sessUserId))->results();
        $data = '';

        foreach ($query as $value) 
        {
            $value['title'] = !empty($value['title']) ? $value['title'] : 'N/A';
            $data .= '<tr>';
            $data .= '<td>'.filtering(ucfirst($value['title'])).' </td>';
            $data .= '<td>'.CURRENCY_SYMBOL.$value['amount'].' </td>';
            $data .= '<td>'.date('dS F,Y',strtotime($value['createdDate'])).' </td>';
            $data .= '<td>'.(($value['paymentStatus']=='p') ? PENDING_LABEL : C_SO_COMPLETED_LBL).' </td>';
            $data .= '</tr>';
        }

        if(empty($data)){
            $data = '<tr> <td colspan="4" style="text-align:center" > '.NO_RECORDS_FOUND.'</td> </tr>';
        }

        return $data;
    }

    public function payment_service_data()
    {
        $query = $this->db->pdoQuery("SELECT w.*,s.serviceTitle FROM `tbl_wallet` as w LEFT JOIN tbl_services as s on w.entity_id = s.id WHERE transactionType = 'featuredFees' AND w.userId = ? and w.entity_type = 's' order by createdDate desc  ",array($this->sessUserId))->results();
        $data = '';

        foreach ($query as $value) 
        {
            $value['serviceTitle'] = !empty($value['serviceTitle']) ? $value['serviceTitle'] : 'N/A';
            $data .= '<tr>';
            $data .= '<td>'.filtering(ucfirst($value['serviceTitle'])).' </td>';
            $data .= '<td>'.CURRENCY_SYMBOL.$value['amount'].' </td>';
            $data .= '<td>'.date('dS F,Y',strtotime($value['createdDate'])).' </td>';
            $data .= '<td>'.(($value['paymentStatus']=='p') ? PENDING_LABEL : C_SO_COMPLETED_LBL).' </td>';
            $data .= '</tr>';
        }

        if(empty($data)){
            $data = '<tr> <td colspan="4" style="text-align:center" > '.NO_RECORDS_FOUND.'</td> </tr>';
        }

        return $data;
    }

    public function getOppositeUserData(){
      global $sessUserId,$sessUserType;  
      $real_user_res = $this->db->select("tbl_users",array("email","userType"),array("id"=>$sessUserId))->result();    
      $oppositeUserType = $real_user_res["userType"]=="F"?"C":"F";      
      $oppositeUserId = getTableValue("tbl_users","id",array("email"=>$real_user_res["email"],"userType"=>$oppositeUserType));
      if(!empty($oppositeUserId)){
        return $oppositeUserId;
      }else{
        return 0;
      }
    }

    public function get_service_expense_data($oppositeUserid){
      $user_service = $this->db->pdoQuery("select group_concat(id) As orderId from tbl_services_order where customerId=? and orderStatus=? ",array($oppositeUserid,'c'))->result();
      $data = '';
      if($user_service['orderId']!=''){ 
        $query = $this->db->pdoQuery("SELECT (CASE WHEN w.entity_type='s' and o.orderStatus='c' and w.paymentStatus='c' and w.status='completed' THEN w.amount ELSE 0 END) AS finalAmnt,w.createdDate 
        FROM tbl_wallet AS w
        JOIN tbl_services_order AS o ON o.id = w.entity_id
        JOIN tbl_services AS s ON s.id = o.servicesId
        JOIN tbl_users AS u ON u.id = o.customerId
        WHERE w.entity_id IN(".$user_service['orderId'].") AND o.serviceStatus!='cl' AND o.orderStatus='c' and (w.transactionType='escrow' OR w.transactionType='refund' OR w.transactionType IS NULL ) AND w.userId = ".$oppositeUserid." ORDER BY w.createdDate DESC
          ")->results();
        
        $totalExpense = 0;
        if(count($query)>0){
          foreach ($query as $value){ 
            $totalExpense+=$value['finalAmnt'];
          }
        }
        $data = $totalExpense;
      }
      return $data;
    }
    public function get_job_expense_data($oppositeUserid){
      $user_jobs = $this->db->pdoQuery("select group_concat(id) As jobList from tbl_jobs where posterId=?",array($oppositeUserid))->result();
      $data = '';
      $totalExpense = $totalExpense = 0;
      if($user_jobs['jobList']!='')
      {
        $wallet_detail = $this->db->pdoQuery("SELECT 
          SUM(CASE WHEN m.paymentstatus='c' THEN w.amount ELSE 0 END) AS totalamount 
          FROM tbl_milestones AS m 
          JOIN tbl_wallet AS w ON w.entity_id = m.id 
          JOIN tbl_users AS u ON u.id = m.ownerid 
          JOIN tbl_jobs AS j ON j.id = m.jobid
          WHERE m.jobid IN(".$user_jobs['jobList'].") AND (CASE WHEN (m.paymentstatus='c') THEN w.entity_type='ml' ELSE 1 END) GROUP BY m.jobid ORDER BY w.createdDate DESC")->results();
        foreach ($wallet_detail as $value)
        {
          $totalExpense+=$value['totalamount'];
        }
        $data = $totalExpense;
      }
      return $data;
    }    
    public function getChartExpenseData($realUserId,$oppositeUserid,$list,$type){

      $jobExpense = $serviceExpense = array();

      // Expense on Job
      $user_jobs = $this->db->pdoQuery("select group_concat(id) As jobList from tbl_jobs where posterId=?",array($oppositeUserid))->result();
      $data = '';
      if($user_jobs['jobList']!='')
      {
        $select = "";
        if($type=="month"){
          $current_year = DATE("Y");
          foreach ($list as $key => $val) {
            $name = addOrdinalNumberSuffix(($key+1));            
            $select .= "SUM(CASE WHEN m.paymentstatus='c' AND (YEAR(w.createdDate)=".$current_year." AND MONTH(w.createdDate)=".$val.") THEN w.amount ELSE 0 END) AS ".$name.",";               
          }
        }else if($type=="year"){
          foreach ($list as $key => $val) {
            $name = addOrdinalNumberSuffix(($key+1));
            $select .= "SUM(CASE WHEN m.paymentstatus='c' AND (YEAR(w.createdDate)=".$val.") THEN w.amount ELSE 0 END) AS ".$name.",";               
          }
        }
        $select_data = rtrim($select,",");
        $job_detail = $this->db->pdoQuery("SELECT 
          $select_data
          FROM tbl_milestones AS m 
          JOIN tbl_wallet AS w ON w.entity_id = m.id 
          JOIN tbl_users AS u ON u.id = m.ownerid 
          JOIN tbl_jobs AS j ON j.id = m.jobid
          WHERE m.jobid IN(".$user_jobs['jobList'].") AND (CASE WHEN (m.paymentstatus='c') THEN w.entity_type='ml' ELSE 1 END) GROUP BY u.id")->result();        
        foreach ($job_detail as $key => $value)
        {
          $jobExpense[$key]=$value;
        }
      }

      // Expense on Service
      $user_service = $this->db->pdoQuery("select group_concat(id) As orderId from tbl_services_order where customerId=? and orderStatus=? ",array($oppositeUserid,'c'))->result();
      $data = '';
      if($user_service['orderId']!=''){ 
        $select = "";
        if($type=="month"){
          $current_year = DATE("Y");
          foreach ($list as $key => $val) {
            $name = addOrdinalNumberSuffix(($key+1));
            // $select .= "SUM(CASE WHEN (YEAR(w.createdDate)=".$current_year." AND MONTH(w.createdDate)=".$val.") THEN w.amount ELSE 0 END) AS ".$name.",";
            $select .= "SUM(CASE WHEN(YEAR(w.createdDate)=".$current_year." AND MONTH(w.createdDate)=".$val.") THEN w.amount ELSE 0 END) AS ".$name.",";
            
          }
        }else if($type=="year"){
          foreach ($list as $key => $val) {
            $name = addOrdinalNumberSuffix(($key+1));
            $select .= "SUM(CASE WHEN(YEAR(w.createdDate)=".$val.") THEN w.amount ELSE 0 END) AS ".$name.",";
          }
        }
        $select_data = rtrim($select,",");
        $service_detail = $this->db->pdoQuery("SELECT $select_data
          FROM tbl_wallet AS w
          JOIN tbl_services_order AS o ON o.id = w.entity_id
          JOIN tbl_services AS s ON s.id = o.servicesId
          JOIN tbl_users AS u ON u.id = o.customerId
          WHERE  w.entity_type='s' and o.orderStatus='c' and w.paymentStatus='c' and w.status='completed' AND w.entity_id IN(".$user_service['orderId'].") AND o.serviceStatus!='cl' AND o.orderStatus='c' and (w.transactionType='escrow' OR w.transactionType='refund' OR w.transactionType IS NULL ) AND w.userId = ".$oppositeUserid)->result();
        if(count($service_detail)>0){
          foreach ($service_detail as $key => $value)
          {
            $serviceExpense[$key]=$value;
          }
        }
      }

      $data = array();
      $amount_array = !empty($jobExpense)?$jobExpense:$serviceExpense;
      if(!empty($amount_array)){
        foreach ($amount_array as $key => $value) {
          $job_amount = !empty($jobExpense)?$jobExpense[$key]:0;
          $service_amount = !empty($serviceExpense)?$serviceExpense[$key]:0;
          $data[] = ($value + $serviceExpense[$key]);
        }
      }
      return $data;
    }
    public function getChartIncomeData($realUserId,$oppositeUserid,$list,$type){
      $jobIncome = $serviceIncome = array();

      // Income From Job
      $user_jobs = $this->db->pdoQuery("select group_concat(jobid) As jobList from tbl_job_bids where userId=? and isHired=?",array($realUserId,'y'))->result();
      $data = '';
      if($user_jobs['jobList']!='')
      {
        $select = "";
        if($type=="month"){
          $current_year = DATE("Y");
          foreach ($list as $key => $val) {
            $name = addOrdinalNumberSuffix(($key+1));
            $select .= "SUM(CASE WHEN m.paymentstatus='c' AND (YEAR(w.createdDate)=".$current_year." AND MONTH(w.createdDate)=".$val.") THEN w.amount ELSE 0 END) AS ".$name.",";               
          }
        }else if($type=="year"){
          foreach ($list as $key => $val) {
            $name = addOrdinalNumberSuffix(($key+1));
            $select .= "SUM(CASE WHEN m.paymentstatus='c' AND (YEAR(w.createdDate)=".$val.") THEN w.amount ELSE 0 END) AS ".$name.",";               
          }
        }
        $select_data = rtrim($select,",");
        $job_detail = $this->db->pdoQuery("SELECT 
          $select_data
          FROM tbl_milestones AS m 
          JOIN tbl_wallet AS w ON w.entity_id = m.id 
          JOIN tbl_users AS u ON u.id = m.ownerid 
          JOIN tbl_jobs AS j ON j.id = m.jobid 
          WHERE m.jobid IN(".$user_jobs['jobList'].") AND (CASE WHEN (m.paymentstatus='c') THEN w.entity_type='ml' ELSE 1 END)")->result();
        if(count($job_detail)>0){
          foreach ($job_detail as $key => $value)
          {
            $jobIncome[$key]=$value;
          }
        }
      }

      // Income From service
      $user_service = $this->db->pdoQuery("select group_concat(id) As orderId from tbl_services_order where freelanserId=? and orderStatus=? ",array($this->sessUserId,'c'))->result();
      if($user_service['orderId']!=''){ 
        $select = "";
        if($type=="month"){
          $current_year = DATE("Y");
          foreach ($list as $key => $val) {
            $name = addOrdinalNumberSuffix(($key+1));
            $select .= "SUM(CASE WHEN (YEAR(w.createdDate)=".$current_year." AND MONTH(w.createdDate)=".$val.") THEN w.amount ELSE 0 END) AS ".$name.",";   
          }
        }else if($type=="year"){
          foreach ($list as $key => $val) {
            $name = addOrdinalNumberSuffix(($key+1));
            $select .= "SUM(CASE WHEN(YEAR(w.createdDate)=".$val.") THEN w.amount ELSE 0 END) AS ".$name.",";             
          }
        }
        $select_data = rtrim($select,",");
        $service_detail = $this->db->pdoQuery("SELECT $select_data
            FROM tbl_wallet AS w
            JOIN tbl_services_order AS o ON o.id = w.entity_id
            JOIN tbl_services AS s ON s.id = o.servicesId
            JOIN tbl_users AS u ON u.id = o.customerId
            WHERE w.entity_id IN(".$user_service['orderId'].") AND w.entity_type='s' and w.paymentStatus='c' and w.status='completed' AND o.orderStatus='c' and (w.transactionType='escrow' OR w.transactionType='refund' OR w.transactionType IS NULL ) AND w.userId = ".$realUserId)->result();
        if(count($service_detail)>0){
          foreach ($service_detail as $key => $value)
          {
            $serviceIncome[$key]=$value;
          }
        }
      }
      $data = array();
      $amount_array = !empty($jobIncome)?$jobIncome:$serviceIncome;
      if(!empty($amount_array)){
        foreach ($amount_array as $key => $value) {
          $job_amount = !empty($jobIncome)?$jobIncome[$key]:0;
          $service_amount = !empty($serviceIncome)?$serviceIncome[$key]:0;
          $data[] = ($value + $serviceIncome[$key]);
        }
      }
      return $data;
    }

    public function get_service_data($dataVar='',$varType='')
    {
        $user_service = $this->db->pdoQuery("select group_concat(id) As orderId from tbl_services_order where freelanserId=? and orderStatus=? ",array($this->sessUserId,'c'))->result();
        if($user_service['orderId']!='')
          { 
              $query = $this->db->pdoQuery("SELECT o.id As oID,w.id,w.entity_id,s.id as ser_id,s.serviceTitle,u.firstName,u.lastName,o.serviceStatus,s.serviceSslug,s.id AS sId,
              (CASE WHEN o.orderStatus='c' and w.transactionType='escrow' and w.paymentStatus='p' and w.status='onhold' and w.entity_type='s' THEN w.amount ELSE 0 END) AS holdAmnt,
              (CASE WHEN w.entity_type='s' and o.orderStatus='c' and w.paymentStatus='c' and w.status='completed' THEN w.amount ELSE 0 END) AS finalAmnt,w.createdDate 
              FROM tbl_wallet AS w
              JOIN tbl_services_order AS o ON o.id = w.entity_id
              JOIN tbl_services AS s ON s.id = o.servicesId
              JOIN tbl_users AS u ON u.id = o.customerId
              WHERE w.entity_id IN(".$user_service['orderId'].") AND o.serviceStatus!='cl' AND o.orderStatus='c' and (w.transactionType='escrow' OR w.transactionType='refund' OR w.transactionType IS NULL ) AND w.userId = ".$this->sessUserId." ORDER BY w.createdDate DESC
                ")->results();
            $data = '';
            $totalEarnings = $totalEarningsPending = 0;
                
            if(count($query)>0)
            {
              foreach ($query as $value)
              { 
                  // if($value['holdAmnt']==0 && $value['finalAmnt']==0)
                  // {
                  //   continue;
                  // }
                  $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/all_service_data-sd.skd");
                  $sub_content = $sub_content->compile();
                  $admin_commission = $this->db->pdoQuery("select amount from tbl_admin_commision where entityId=? and entityType=?",array($value['ser_id'],'s'))->result();
                  $total_job_amount = ($value['holdAmnt']+$value['finalAmnt']+$admin_commission["amount"]);

                  $link = SITE_URL."service/workroom/".base64_encode($value['oID'])."/".$value['serviceSslug'];
                  $sLink = "<a href='".$link."'>View Detail</a>";
                  $status = service_status($value['serviceStatus']);
                  $array = array(
                    "%TITLE%"=> filtering(ucfirst($value['serviceTitle'])),
                    "%CUSTOMER_NM%"=> filtering(ucfirst($value['firstName']))." ".filtering(ucfirst($value['lastName'])),
                    "%PENDING_AMOUNT%"=> "<div class='amount-wrap'>".$value['holdAmnt']."<span>".CURRENCY_SYMBOL."</span></div>",
                    "%RECEIVED_AMOUNT%"=> "<div class='amount-wrap'>".$value['finalAmnt']."<span>".CURRENCY_SYMBOL."</span></div>",
                    "%ADMIN_COMMISSION%" => "<div class='amount-wrap'>".$admin_commission["amount"]."<span>".CURRENCY_SYMBOL."</span></div>",
                    "%TOTAL_AMOUNT%" => "<div class='amount-wrap'>".$total_job_amount."<span>".CURRENCY_SYMBOL."</span></div>",
                    "%DATE%"=> date('dS F,Y',strtotime($value['createdDate'])),
                    "%STATUS%"=> $status["status"],
                    "%LINK%"=> $sLink
                    );
                  $data .= str_replace(array_keys($array), array_values($array), $sub_content);
                  $totalEarnings+=$value['finalAmnt'];
                  $totalEarningsPending+= $value['holdAmnt'];
              }
            }
            else
            {
              $data .= '<tr><td colspan=7>'.NO_RECORDS_FOUND.'</td></tr>';
            }
          }
          else
          {
              $data .= '<tr><td colspan=7>'.NO_RECORDS_FOUND.'</td></tr>';
          }

         if($dataVar=='all' && $varType=='p')
         {
            return ($totalEarningsPending==0) ? 0 : $totalEarningsPending;
         }
         else if($dataVar=='all')
         {
            return ($totalEarnings==0) ? 0 : $totalEarnings;
         }
         else
         {
            return $data;
         }
    }
    public function get_job_data($dataVar='',$varType='')
    {
      global $sessUserId;
      $user_jobs = $this->db->pdoQuery("select group_concat(jobid) As jobList from tbl_job_bids
      where userId=? and isHired=?",array($this->sessUserId,'y'))->result();

      $data = '';
      $totalEarnings = $totalEarningsPending = 0;
      if($user_jobs['jobList']!='')
      {
        // pre_print($user_jobs['jobList']);
        $wallet_detail = $this->db->pdoQuery("
          SELECT res.totalamount,res.totalamountpending,res.jobid,res.firstName,res.lastName,res.jobTitle,res.createdDate,res.jobStatus,res.jobSlug,res.jobId
            FROM (
                SELECT SUM(CASE WHEN m.paymentstatus='c' THEN w.amount ELSE 0 END) AS totalamount,
                      SUM(CASE WHEN (m.paymentstatus='p' OR m.paymentstatus='ap') THEN m.amount ELSE 0 END) AS totalamountpending,
                      u.firstName,u.lastName,j.jobTitle,w.createdDate,j.jobStatus,j.jobSlug,j.id As jobId 
                FROM tbl_milestones AS m 
                JOIN tbl_wallet AS w ON w.entity_id = m.id 
                JOIN tbl_users AS u ON u.id = m.ownerid 
                JOIN tbl_jobs AS j ON j.id = m.jobid 
                WHERE m.jobid IN(".$user_jobs['jobList'].") AND (j.jobStatus!='dsc' OR j.jobStatus!='dsCo') AND (CASE WHEN (m.paymentstatus='c') THEN w.entity_type='ml' ELSE 1 END) GROUP BY j.id
              UNION ALL
                SELECT w.amount AS totalamount,
                  0 as totalamountpending,u.firstName,u.lastName,j.jobTitle,w.createdDate,j.jobStatus,j.jobSlug,j.id As jobId 
                FROM tbl_wallet AS w 
                JOIN tbl_jobs AS j ON j.id = w.entity_id 
                JOIN tbl_users AS u ON u.id = w.userId 
                WHERE w.entity_id IN(".$user_jobs['jobList'].") AND w.userId=".$sessUserId." AND transactionType='disputeSolved' AND (j.jobStatus='dsc' OR j.jobStatus='dsCo') GROUP BY j.id
            ) as res ORDER BY res.createdDate DESC
        ")->results();

          /*SELECT SUM(CASE WHEN m.paymentstatus='c' THEN w.amount ELSE 0 END) AS totalamount,
                SUM(CASE WHEN (m.paymentstatus='p' OR m.paymentstatus='ap') THEN m.amount ELSE 0 END) AS totalamountpending,jobid,u.firstName,u.lastName,j.jobTitle,w.createdDate,j.jobStatus,j.jobSlug,j.id As jobId 
          FROM tbl_milestones AS m 
          JOIN tbl_wallet AS w ON w.entity_id = m.id 
          JOIN tbl_users AS u ON u.id = m.ownerid 
          JOIN tbl_jobs AS j ON j.id = m.jobid 
          WHERE m.jobid IN(".$user_jobs['jobList'].") AND jobStatus!='dsc' AND (CASE WHEN (m.paymentstatus='c') THEN w.entity_type='ml' ELSE 1 END) GROUP BY m.jobid ORDER BY w.createdDate DESC
*/
          
        if(!empty($wallet_detail)){
          foreach ($wallet_detail as $value)
          {
            if($value['totalamountpending']==0 && $value['totalamount']==0)
            {
              continue;
            }
            $workroom_link = SITE_URL.'job/workroom/'.$value['jobSlug'];
            $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/all_data-sd.skd");
            $sub_content = $sub_content->compile();

            $commissionRes = $this->db->pdoQuery("select amount from tbl_admin_commision where entityId=? and entityType=?",array($value['jobId'],'j'))->result();
            $milestones = $this->db->pdoQuery("SELECT GROUP_CONCAT(id) as ids FROM `tbl_milestones`  where jobId=".$value["jobId"])->result();
            $admin_commission = !empty($commissionRes["amount"])?$commissionRes["amount"]:0;
            if(!empty($milestones) && ($value["jobStatus"]=="dsc" || $value["jobStatus"]=="dsCo")){
              $disputeRes = $this->db->pdoQuery("SELECT * FROM tbl_dispute WHERE entityId IN(".$milestones["ids"].")")->result();
              if(!empty($disputeRes)){
                $admin_commission = ($disputeRes["payToEntityOwner"]!=0 || $disputeRes["payToDisputer"]!=0)?($admin_commission/2):$admin_commission;
              }
            }
            $total_job_amount = ($value['totalamountpending']+$value['totalamount']+$admin_commission);
            
            $array = array(
              "%TITLE%" => filtering(ucfirst($value['jobTitle'])),
              "%CUSTOMER_NM%" => filtering(ucfirst($value['firstName']))." ".filtering(ucfirst($value['lastName'])),
              "%MILESTONE%" => $value["jobStatus"]!='dsc'?noMilestone($value['jobid']):"",
              "%PENDING_AMOUNT%" => "<div class='amount-wrap'>".$value['totalamountpending']."<span>".CURRENCY_SYMBOL."</span></div>",
              "%RECEIVED_AMOUNT%" => "<div class='amount-wrap'>".$value['totalamount']."<span>".CURRENCY_SYMBOL."</span></div>",
              "%ADMIN_COMMISSION%" => "<div class='amount-wrap'>".$admin_commission."<span>".CURRENCY_SYMBOL."</span></div>",
              "%TOTAL_AMOUNT%" => "<div class='amount-wrap'>".$total_job_amount."<span>".CURRENCY_SYMBOL."</span></div>",
              "%DATE%" => date('dS F,Y',strtotime($value['createdDate'])),
              "%STATUS%" => getJobStatus($value['jobStatus']),
              "%LINK%" => "<a href='".$workroom_link."' >Details</a>"
            );
            $data .= str_replace(array_keys($array), array_values($array), $sub_content);
            $totalEarnings+=$value['totalamount'];
            $totalEarningsPending+= $value['totalamountpending'];
          }
        }
      }
      else
      {
         $data .= '<tr><td colspan=8>'.NO_RECORDS_FOUND.'</td></tr>';
      }
      if($dataVar=='all' && $varType=='p')
      {
          return ($totalEarningsPending==0) ? 0 : $totalEarningsPending;
      }
      else if($dataVar=='all')
      {
          return ($totalEarnings==0) ? 0 : $totalEarnings;
      }
      else
      {
          return $data;
      }
    }
    public function getChartData($realUserId,$oppositeUserid,$type="month",$is_json="n"){
      $data = $month_ids = $years = $list = $expenseData=$incodeData = array();      
      if($type=="month"){
        $list = array("January","February","March","April","May","June","July","August","September","October","November","December");
        $month_ids = array("1","2","3","4","5","6","7","8","9","10","11","12");
        $expenseData = $this->getChartExpenseData($realUserId,$oppositeUserid,$month_ids,$type);
        $incodeData = $this->getChartIncomeData($realUserId,$oppositeUserid,$month_ids,$type);
      }else{
        $current_year = DATE("Y");
        $last_year = date('Y', strtotime('+12 years'));
        for($i=$current_year;$i<$last_year;$i++){
          $years[] = $i;
          $list[] = $i;
        }  
        $expenseData = $this->getChartExpenseData($realUserId,$oppositeUserid,$years,$type);
        $incodeData = $this->getChartIncomeData($realUserId,$oppositeUserid,$years,$type);
      }
      $heading = array();
      if(!empty($list)){
        foreach ($list as  $value) {          
          $heading[] = (string) $value;;
        }
      }
      $data["heading"]=$is_json=="n"?json_encode($heading):$heading;
      $data["income"]=$is_json=="n"?json_encode($incodeData):$incodeData;
      $data["expense"]=$is_json=="n"?json_encode($expenseData):$expenseData;
      $data["scales_name"]=($type=="month"?"Monthly":"Yearly");
      return $data;
    }
    public function sendRedeemRequestData($data)
    {
      extract($data);
      $amount = $this->db->insert("tbl_redeem_request",array("userId"=>$this->sessUserId,"amount"=>$amountRedeem,"createdDate"=>date('Y-m-d H:i:s'),"paymentStatus"=>'pending'));

      $user_detail = getUser($this->sessUserId);

      if(empty($user_detail['paypal_email']) || $user_detail['isPaypalVarified'] == 'n'){
        $msgType = $_SESSION["msgType"] = disMessage(array('type'=>'err','var'=>PAYPAL_EMAIL_IS_NOT_VERIFIED)); 
        redirectPage(SITE_URL."f/account-setting");
      }

      /*mail to admin start*/
      $arrayCont = array('USER'=> filtering(ucfirst($user_detail['firstName'])),
                      'AMOUNT' => CURRENCY_SYMBOL.$amountRedeem,
                      'USERNAME' => filtering(ucfirst($user_detail['firstName']))." ".filtering(ucfirst($user_detail['lastName'])),
                      'EMAIL' => $user_detail['email'],
                      'WALLET_BAL' => CURRENCY_SYMBOL.$user_detail['walletAmount']
                      );
      $array = generateEmailTemplate('Redeem_request_intimate_to_admin',$arrayCont);   
      sendEmailAddress(ADMIN_EMAIL,$array['subject'],$array['message']);
      /*mail to admin end*/
      
      $user_detail['walletAmount'] = $user_detail['walletAmount'] - $amountRedeem; 
      $cnt = $this->db->update("tbl_users",array('walletAmount'=>$user_detail['walletAmount']),array("email"=>$user_detail['email'] ))->affectedRows();

      $msgType = $_SESSION["msgType"] = disMessage(array('type'=>'suc','var'=>'Your Redeem Request for '.CURRENCY_SYMBOL.$amountRedeem.' amount has been sent successfully. Admin will respond you shortly.')); 
      redirectPage(SITE_URL."financial-dashboard");
    }

    
    /*public function onholdAmount($dataVar="")
    {      
        if($dataVar=='all')
        {
            $query = $this->db->pdoQuery("select SUM(jb.budget) As holdAmount from tbl_job_bids As jb
              LEFT JOIN tbl_jobs As j ON j.id = jb.jobid
              where NOT EXISTS (SELECT * FROM tbl_milestones WHERE tbl_milestones.jobId = j.id) and
              jb.userId=? and jb.isHired=? and (j.jobStatus=? OR j.jobStatus=? OR j.jobStatus=?) ",array($this->sessUserId,'y','ip','ud','dsp'))->result();
            $query1 = $this->db->pdoQuery("select SUM(m.amount) As holdAmount from tbl_job_bids As jb
              LEFT JOIN tbl_jobs As j ON j.id = jb.jobid
              LEFT JOIN tbl_milestones As m ON m.jobid = jb.jobid where 
              jb.userId=? and jb.isHired=? and m.paymentStatus=? ",array($this->sessUserId,'y','p'))->result();

            $data = $query['holdAmount'] + $query1['holdAmount'];
        }
        else
        {
            $query = $this->db->pdoQuery("select j.jobTitle,jb.budget As holdAmount from tbl_job_bids As jb
              LEFT JOIN tbl_jobs As j ON j.id = jb.jobid
              where NOT EXISTS (SELECT * FROM tbl_milestones WHERE tbl_milestones.jobId = j.id) and
              jb.userId=? and jb.isHired=? and (j.jobStatus=? OR j.jobStatus=? OR j.jobStatus=?) ORDER BY jb.id DESC",array($this->sessUserId,'y','ip','ud','dsp'))->results();
            $query1 = $this->db->pdoQuery("select j.jobTitle,SUM(m.amount) As holdAmount from tbl_job_bids As jb
              LEFT JOIN tbl_jobs As j ON j.id = jb.jobid
              LEFT JOIN tbl_milestones As m ON m.jobid = jb.jobid where 
              jb.userId=? and jb.isHired=? and m.paymentStatus=? ORDER BY jb.id DESC",array($this->sessUserId,'y','p'))->results();

            $final_array = array_merge($query,$query1);
            
            $data = '';
            if(count($query)==0 && count($query1)==0)
            {
                  $data .= "<tr><td colspan=2>".NO_HOLD_DATA_FOUND."</td></tr>";
            }
            else
            {
              foreach ($final_array as $value) {
                  $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/onhold_amount-sd.skd");
                  $sub_content = $sub_content->compile();
                  $array = array(
                      "%TITLE%" => filtering(ucfirst($value['jobTitle'])),
                      "%AMNT%" => CURRENCY_SYMBOL.$value['holdAmount']
                    );
                  $data .= str_replace(array_keys($array), array_replace($array), $sub_content); 
              }
            }
        }
      return $data;
    }

    public function onholdServiceAmount($dataVar="")
    {
        if($dataVar=='all')
        {
            $query = $this->db->pdoQuery("select SUM(totalPayment) As totalHoldAmount from tbl_services_order where freelanserId=? and paymentStatus=?",array($this->sessUserId,'p'))->result();
            $data = ($query['totalHoldAmount']=='') ? 0 : $query['totalHoldAmount'];
        }
        else
        {
            $query = $this->db->pdoQuery("select s.serviceTitle,so.totalPayment from tbl_services_order As so
              LEFT JOIN tbl_services As s ON s.id = so.servicesId
              where so.freelanserId=? and so.paymentStatus=? AND so.serviceStatus NOT IN('ud','ds','dsc','cl') ORDER BY so.id DESC",array($this->sessUserId,'p'))->results();
            
            $data = '';
            if(count($query)>0)
            {
              foreach ($query as $value) 
              {
                  $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/onhold_amount-sd.skd");
                  $sub_content = $sub_content->compile();

                  $array = array(
                        "%TITLE%" => filtering(ucfirst($value['serviceTitle'])),
                        "%AMNT%" => CURRENCY_SYMBOL.$value['totalPayment']
                      );
                  $data .= str_replace(array_keys($array), array_values($array), $sub_content);
              }
            }
            else
            {
              $data .= "<tr><td colspan=2>".NO_HOLD_DATA_FOUND."</td></tr>";
            }

        }
        return $data;
    } */

}


 ?>


