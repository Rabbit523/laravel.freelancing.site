<?php

class CustomerFinancialDashboard extends Home
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
    global $sessUserId;
	  $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/customer_financial_dashboard-sd.skd");
    $sub_content = $sub_content->compile();

    $realUserId = $sessUserId;   
    $oppositeUserid = $this->getOppositeUserData();
    $user_detail = $this->db->pdoQuery("select * from tbl_users where id=?",array($realUserId))->result();

    $financial_job_data = $this->get_job_data();
    $financial_service_data = $this->get_service_data();
    $totalJobIncome = $this->totalJobIncome($oppositeUserid);       
    $totalServiceIncome = $this->totalServiceIncome($oppositeUserid);   
    $totalamount = ($totalJobIncome+$totalServiceIncome);
    $totalJobExpense = $this->get_job_data('all');
    $totalServiceExpense = $this->get_service_data('all');
    $total_expenses = ($totalJobExpense+$totalServiceExpense);
    $total_pending_expenses_from_job = $this->get_job_data('all','p');
    $total_pending_expenses_from_service = $this->get_service_data('all','p');
    $onhold_amount = $this->get_service_data('all','p');
    $wallet_amount = $user_detail["walletAmount"];
    $total_wallet_amnt = $this->wallet_history("all");
    $wallet_history = $this->wallet_history();
    $total_redeem_amnt = $this->redeemRequest("all");
    $redeem_history = $this->redeemRequest();
    $job_hold_history = $this->jobOnHoldAmount();
    $services_hold_history = $this->serviceonholdAmount();
    $total_job_onhold_amnt = $this->jobOnHoldAmount('all');
    $total_services_onhold_amnt = $this->serviceonholdAmount('all');
    $featuredFees = $this->featuredFees();
    $featuredFeesHistory = $this->featuredFees('all');
    $getRefundData = $this->getRefundData();
    $getRefundHistory = $this->getRefundData('all');
    $chart_res = $this->getChartData($realUserId,$oppositeUserid);

    $array = array(
      "%SUB_HEADER_CONTENT%" => customerSubHeaderContent("financial"),
      "%TOTAL_INCOME%"=>$totalamount."<span>".CURRENCY_SYMBOL."</span>",
      "%TOTAL_EXPENSES%"=>$total_expenses."<span>".CURRENCY_SYMBOL."</span>",
      "%TOTAL_EXPENSES_FROM_JOBS%" => $totalJobExpense."<span>".CURRENCY_SYMBOL."</span>",
      "%TOTAL_EXPENSES_FROM_SERVICE%" => $totalServiceExpense."<span>".CURRENCY_SYMBOL."</span>",
      "%FINANCIAL_JOB_DATA%" => $financial_job_data,
      "%TOTAL_PENDING_EXPENSES_FROM_JOB%" => $total_pending_expenses_from_job."<span>".CURRENCY_SYMBOL."</span>",
      "%TOTAL_PENDING_EXPENSES_FROM_SERVICE%" => $total_pending_expenses_from_service."<span>".CURRENCY_SYMBOL."</span>",
      "%FINANCIAL_SERVICE_DATA%" => $financial_service_data,
      "%MILESTONE_PAYMENTS%" => $this->milestone_payments(),
      "%ONHOLD_AMOUNT%" => $onhold_amount."<span>".CURRENCY_SYMBOL."</span>",
      "%WALLET_AMOUNT%" => $wallet_amount."<span>".CURRENCY_SYMBOL."</span>",
      "%WALLET_HISTORY%" => $wallet_history,
      "%TOTAL_WALLET_AMNT%" => $total_wallet_amnt."<span>".CURRENCY_SYMBOL."</span>",
      "%TOTAL_REDEEM_AMOUNT%" => $total_redeem_amnt."<span>".CURRENCY_SYMBOL."</span>",
      "%REDEEM_HISTORY%" => $redeem_history,
      "%JOB_HOLD_HISTORY%" => $job_hold_history,
      "%SERVICES_HOLD_HISTORY%" => $services_hold_history,
      "%TOTAL_JOB_ONHOLD_AMNT%" => $total_job_onhold_amnt."<span>".CURRENCY_SYMBOL."</span>",
      "%TOTAL_SERVICES_ONHOLD_AMNT%" => $total_services_onhold_amnt."<span>".CURRENCY_SYMBOL."</span>",
      '%FEATUREDFEES_TOTAL%' => $featuredFees."<span>".CURRENCY_SYMBOL."</span>",
      '%FEATUREDFEES_HISTORY%' => $featuredFeesHistory,
      '%DISPUTE_REFUND_TOTAL%' => $getRefundData."<span>".CURRENCY_SYMBOL."</span>",
      '%DISPUTE_REFUND_HISTORY%' => $getRefundHistory,
      "%CHART_HEADING%" => $chart_res["heading"],
      "%CHART_INCOME%" => $chart_res["income"],
      "%CHART_ENPENSE%" => $chart_res["expense"],
      "%SCALES_NAME%" => $chart_res["scales_name"],
      "%REAL_USER_ID%" => $realUserId,
      "%OPPOSITE_USER_ID%" => $oppositeUserid,
    );
	  return str_replace(array_keys($array),array_values($array),$sub_content);
  }
  public function getChartData($realUserId,$oppositeUserid,$type="month",$is_json="n"){
    // pre_print($realUserId);
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
  public function getChartExpenseData($realUserId,$oppositeUserid,$list,$type){

    $jobExpense = $serviceExpense = array();

    // Expense on Job
    $user_jobs = $this->db->pdoQuery("select group_concat(id) As jobList from tbl_jobs where posterId=?",array($realUserId))->result();
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
    $user_service = $this->db->pdoQuery("select group_concat(id) As orderId from tbl_services_order where customerId=? and orderStatus=? ",array($realUserId,'c'))->result();
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
        WHERE  w.entity_type='s' and o.orderStatus='c' and w.paymentStatus='c' and w.status='completed' AND w.entity_id IN(".$user_service['orderId'].") AND o.serviceStatus!='cl' AND o.orderStatus='c' and (w.transactionType='escrow' OR w.transactionType='refund' OR w.transactionType IS NULL ) AND w.userId = ".$realUserId)->result();
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
    $user_jobs = $this->db->pdoQuery("select group_concat(jobid) As jobList from tbl_job_bids where userId=? and isHired=?",array($oppositeUserid,'y'))->result();
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
    $user_service = $this->db->pdoQuery("select group_concat(id) As orderId from tbl_services_order where freelanserId=? and orderStatus=? ",array($oppositeUserid,'c'))->result();
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
          WHERE w.entity_id IN(".$user_service['orderId'].") AND w.entity_type='s' and w.paymentStatus='c' and w.status='completed' AND o.orderStatus='c' and (w.transactionType='escrow' OR w.transactionType='refund' OR w.transactionType IS NULL ) AND w.userId = ".$oppositeUserid)->result();
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
  public function totalJobIncome($userId){
    $user_jobs = $this->db->pdoQuery("select group_concat(jobid) As jobList from tbl_job_bids
    where userId=? and isHired=?",array($userId,'y'))->result();
    $data = '';
    $totalIncome = $totalIncome = 0;
    if($user_jobs['jobList']!='')
    {
      $wallet_detail = $this->db->pdoQuery("SELECT SUM(CASE WHEN m.paymentstatus='c' THEN w.amount ELSE 0 END) AS totalamount,SUM(CASE WHEN (m.paymentstatus='p' OR m.paymentstatus='ap') THEN m.amount ELSE 0 END) AS totalamountpending,jobid,u.firstName,u.lastName,j.id,j.jobTitle,w.createdDate,j.jobStatus,j.jobSlug,j.id As jobId FROM `tbl_milestones` AS m LEFT JOIN tbl_wallet AS w ON w.entity_id = m.id LEFT JOIN tbl_users AS u ON u.id = m.ownerid LEFT JOIN tbl_jobs AS j ON j.id = m.jobid WHERE m.jobid IN(".$user_jobs['jobList'].") AND (CASE WHEN (m.paymentstatus='c') THEN w.entity_type='ml' ELSE 1 END) GROUP BY m.jobid ORDER BY w.createdDate DESC")->results();
      foreach ($wallet_detail as $value)
      {
        $totalIncome+=$value['totalamount'];
      }
      $data = $totalIncome;
    }
    return $data;
  } 
  public function totalServiceIncome($userId){
    $user_service = $this->db->pdoQuery("select group_concat(id) As orderId from tbl_services_order where freelanserId=? and orderStatus=? ",array($userId,'c'))->result();
    $data = '';
    if($user_service['orderId']!=''){ 
      $query = $this->db->pdoQuery("SELECT o.id As oID,w.id,w.entity_id,s.id as ser_id,s.serviceTitle,u.firstName,u.lastName,o.serviceStatus,s.serviceSslug,s.id AS sId,
            (CASE WHEN o.orderStatus='c' and w.transactionType='escrow' and w.paymentStatus='p' and w.status='onhold' and w.entity_type='s' THEN w.amount ELSE 0 END) AS holdAmnt,
            (CASE WHEN w.entity_type='s' and o.orderStatus='c' and w.paymentStatus='c' and w.status='completed' THEN w.amount ELSE 0 END) AS finalAmnt,w.createdDate 
            FROM tbl_wallet AS w
            JOIN tbl_services_order AS o ON o.id = w.entity_id
            JOIN tbl_services AS s ON s.id = o.servicesId
            JOIN tbl_users AS u ON u.id = o.customerId
            WHERE w.entity_id IN(".$user_service['orderId'].") AND o.serviceStatus!='cl' AND o.orderStatus='c' and (w.transactionType='escrow' OR w.transactionType='refund' OR w.transactionType IS NULL ) AND w.userId = ".$userId." ORDER BY w.createdDate DESC
              ")->results();
      
      $totalIncome = 0;
      if(count($query)>0){
        foreach ($query as $value){ 
          $totalIncome+=$value['finalAmnt'];
        }
      }
      $data = $totalIncome;
    }
    return $data;
  }
  public function milestone_payments($type= ''){
    $user_jobs = $this->db->pdoQuery("select group_concat(j.id) As jobList from tbl_jobs As j
    LEFT JOIN tbl_job_bids As b ON b.jobid = j.id
    where j.posterId=? and b.isHired=?",array($this->sessUserId,'y'))->result();
    $jobList = !empty($user_jobs['jobList']) ? $user_jobs['jobList'] : 0;
    if(empty($type)){
      $pdata = $this->db->pdoQuery("SELECT SUM('amount') as totalamount FROM `tbl_wallet` WHERE entity_id in (SELECT id FROM `tbl_milestones` WHERE jobId in (".$jobList.")) AND entity_type = 'ml'")->result();
      return !empty($pdata['totalamount'])  ?$pdata['totalamount'] : 0; 
    }else{
        $pdata = $this->db->pdoQuery("SELECT * FROM `tbl_wallet` WHERE entity_id in (SELECT id FROM `tbl_milestones` WHERE jobId in (".$jobList.")) AND entity_type = 'ml'")->results();          
    }
  }
  public function get_service_data($dataVar='',$varType='')
  {
      $user_service = $this->db->pdoQuery("select group_concat(id) As orderId from tbl_services_order where customerId=? and orderStatus=? ",array($this->sessUserId,'c'))->result();

      if($user_service['orderId']!='')
      {
          $query = $this->db->pdoQuery("SELECT o.id As oID,w.id,w.entity_id,s.serviceTitle,u.firstName,u.lastName,o.serviceStatus,s.servicesslug,s.id AS sId,w.transactionType,w.amount,
          (CASE WHEN o.orderStatus='c' and w.transactionType='escrow' and w.paymentStatus='p' and w.status='onhold' and w.entity_type='s' THEN w.amount ELSE 0 END) AS holdAmnt,
          (CASE WHEN w.entity_type='s' and o.orderStatus='c' and w.paymentStatus='c' and w.Status='completed' THEN w.amount ELSE 0 END) AS finalAmnt,w.createdDate FROM tbl_wallet AS w
          INNER JOIN tbl_services_order AS o ON o.id = w.entity_id
          INNER JOIN tbl_services AS s ON s.id = o.servicesId
          INNER JOIN tbl_users AS u ON u.id = o.freelanserId
          WHERE w.entity_id IN(".$user_service['orderId'].") AND o.serviceStatus!='cl' AND o.orderStatus='c' and (w.transactionType='escrow' OR w.transactionType='refund' ) AND w.userType = 'c' AND w.userId = ".$this->sessUserId." ORDER BY w.createdDate DESC
            ")->results();

          $data = '';
          $totalEarnings = $totalEarningsPending = 0;
          if(count($query)>0)
          {
            foreach ($query as $value)
            {
              $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/all_service_data-sd.skd");
              $sub_content = $sub_content->compile();

              $link = SITE_URL."service/workroom/".base64_encode($value['oID'])."/".$value['servicesslug'];
              $sLink = "<a href='".$link."'>".VIEW_DETAILS."</a>";

              // $refund_amount = $value['transactionType'] == 'refund' && $value['status'] == 'completed' ? $va
              $status = service_status($value['serviceStatus']);
              $array = array(
                "%TITLE%"=> filtering(ucfirst($value['serviceTitle'])),
                "%FREELANCER_NM%"=> filtering(ucfirst($value['firstName']))." ".filtering(ucfirst($value['lastName'])),
                "%PENDING_AMOUNT%"=> "<div class='amount-wrap'>".$value['holdAmnt']."<span>".CURRENCY_SYMBOL."</span></div>",
                "%RECEIVED_AMOUNT%"=> "<div class='amount-wrap'>".$value['finalAmnt']."<span>".CURRENCY_SYMBOL."</span></div>",
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
    $user_jobs = $this->db->pdoQuery("select group_concat(j.id) As jobList from tbl_jobs As j
    LEFT JOIN tbl_job_bids As b ON b.jobid = j.id
    where j.posterId=? and b.isHired=?",array($this->sessUserId,'y'))->result();
    $data = '';
    $totalEarnings = $totalEarningsPending = 0;
    if($user_jobs['jobList']!='')
    {
      /* $wallet_detail = $this->db->pdoQuery("

        SELECT SUM(CASE WHEN m.paymentstatus='c' THEN w.amount ELSE 0 END) AS totalamount,SUM(CASE WHEN (m.paymentstatus='p' OR m.paymentstatus='ap') THEN m.amount ELSE 0 END) AS totalamountpending,u.firstName,u.lastName,j.jobTitle,w.createdDate,j.jobStatus,j.jobSlug,j.id As jobId FROM `tbl_milestones` AS m LEFT JOIN tbl_wallet AS w ON w.entity_id = m.id LEFT JOIN tbl_users AS u ON u.id = m.ownerid LEFT JOIN tbl_jobs AS j ON j.id = m.jobid WHERE m.jobid IN(".$user_jobs['jobList'].") AND (CASE WHEN (m.paymentstatus='c') THEN w.entity_type='ml' ELSE 1 END) GROUP BY m.jobid ORDER BY w.createdDate DESC  ")->results();*/

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

            $admin_commission = $this->db->pdoQuery("select amount from tbl_admin_commision where entityId=? and entityType=? ORDER BY id DESC LIMIT 1",array($value['jobId'],'j'))->result();
            $freelanser_res = $this->db->pdoQuery(" SELECT CONCAT(u.firstName,' ',u.lastName) as freelanser_name 
                              FROM tbl_job_bids AS b
                              JOIN tbl_jobs AS j ON b.jobId  = j.id
                              JOIN tbl_users AS u ON u.id = b.userId
                              where j.id ='".$value["jobId"]."' and (b.isHired ='a' || b.isHired ='y')")->result();

            $milestones = $this->db->pdoQuery("SELECT GROUP_CONCAT(id) as ids FROM `tbl_milestones`  where jobId=".$value["jobId"])->result();
            $admin_commission = !empty($admin_commission["amount"])?$admin_commission["amount"]:0;
            if(!empty($milestones) && ($value["jobStatus"]=="dsc" || $value["jobStatus"]=="dsCo")){
              $disputeRes = $this->db->pdoQuery("SELECT * FROM tbl_dispute WHERE entityId IN(".$milestones["ids"].")")->result();
              if(!empty($disputeRes)){
                $admin_commission = ($disputeRes["payToEntityOwner"]!=0 || $disputeRes["payToDisputer"]!=0)?($admin_commission/2):$admin_commission;
              }
            }
            $total_job_amount = ($value['totalamountpending']+$value['totalamount']+$admin_commission); 
            $array = array(
              "%TITLE%" => filtering(ucfirst($value['jobTitle'])),
              "%CUSTOMER_NM%" => !empty($freelanser_res["freelanser_name"])?filtering(ucfirst($freelanser_res["freelanser_name"])):"-",
              "%MILESTONE%" => $value["jobStatus"]!='dsc'?noMilestone($value['jobid']):"",
              "%PENDING_AMOUNT%" => "<div class='amount-wrap'>".$value['totalamountpending']."<span>".CURRENCY_SYMBOL."</span></div>",
              "%RECEIVED_AMOUNT%" => "<div class='amount-wrap'>".$value['totalamount']."<span>".CURRENCY_SYMBOL."</span></div>",
              "%ADMIN_COMMISSION%" => "<div class='amount-wrap'>".(!empty($admin_commission)?$admin_commission:0)."<span>".CURRENCY_SYMBOL."</span></div>",
              "%TOTAL_AMOUNT%" => "<div class='amount-wrap'>".$total_job_amount."<span>".CURRENCY_SYMBOL."</span></div>",
              "%DATE%" => date('dS F,Y',strtotime($value['createdDate'])),
              "%STATUS%" => getJobStatus($value['jobStatus']),
              "%LINK%" => "<a href='".$workroom_link."' >".VIEW_DETAILS."</a>"
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
      $query = $this->db->pdoQuery("select * from tbl_redeem_request where userId=? order by id DESC",array($this->sessUserId))->results();
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
            "%REQUEST_AMNT%" => CURRENCY_SYMBOL.$value['amount'],
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
       $data .= "<tr><td colspan=5>".NO_CREDIT_AMOUNT_FOUND."</td></tr>";
      }
      else
      {
        foreach ($query as $value) {
          $array = array(
            "%TRANSACTOIN_ID%" => $value['transactionId'],
            "%PAYMENT_STATUS%" => ($value['paymentStatus']=='p') ? 'Pending' : 'Completed',
            "%DATE%" =>  date('dS F,Y',strtotime($value['createdDate'])),
            "%AMNT%" => CURRENCY_SYMBOL.$value['amount'],
          );
          $data .= str_replace(array_keys($array), array_replace($array), $sub_content);
        }
      }
    }
    return $data;
  }
  public function jobOnHoldAmount($dataVar='')
  {
    if($dataVar=='all')
    {
      $query = $this->db->pdoQuery("select SUM(w.amount) As totalAmount,GROUP_CONCAT(w.entity_id) AS jobs from tbl_wallet As w
        INNER JOIN tbl_jobs As j ON j.id = w.entity_id
        where w.userId=? and w.status=? and w.transactionType=? and w.userType=? and w.entity_type=?",array($this->sessUserId,'onhold','escrow','c','j'))->result();
      if($query['jobs']!='')
      {
        $milestoneId = $this->db->pdoQuery("select GROUP_CONCAT(id) As milestones from tbl_milestones where jobId IN(".$query['jobs'].") ")->result();
        if($milestoneId['milestones']!=""){
          $query1 = $this->db->pdoQuery("select SUM(amount) As totalAmountRelease from tbl_wallet where entity_type=? and entity_id IN(".$milestoneId['milestones'].") and userId=? and transactionType=? and status=?",array('ml',$this->sessUserId,'payToFreelancer','completed'))->result();
          $data = $query['totalAmount']-$query1['totalAmountRelease'];
        }
        else{
          $data = 0;
        }
      }
      else
      {
        $data = 0;
      }
    }
    else
    {
      $data = '';
      $query = $this->db->pdoQuery("select j.jobTitle,w.amount,w.entity_id from tbl_wallet As w
        INNER JOIN tbl_jobs As j ON j.id = w.entity_id
        where w.userId=? and w.status=? and w.transactionType=? and w.userType=? and w.entity_type=?",array($this->sessUserId,'onhold','escrow','c','j'))->results();
      if(!empty($query)){
        foreach ($query as $value)
        {
          if($value['entity_id']!='')
          {
            $milestoneId = $this->db->pdoQuery("select GROUP_CONCAT(id) As milestones from tbl_milestones where jobId IN(".$value['entity_id'].") ")->result();
            if($milestoneId['milestones']!=""){
              $cQuery = $this->db->pdoQuery("select SUM(amount) As totalAmountRelease from tbl_wallet where entity_type=? and entity_id=? and userId=? and transactionType=? and status=?",array('ml',$milestoneId['milestones'],$this->sessUserId,'payToFreelancer','completed'))->result();

              $final = $value['amount'] - $cQuery['totalAmountRelease'];

              $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/onhold_amount-sd.skd");
              $sub_content = $sub_content->compile();
              $array = array(
                "%TITLE%" => filtering(ucfirst($value['jobTitle'])),
                "%AMNT%" => CURRENCY_SYMBOL.$final
              );
              $data .= str_replace(array_keys($array), array_replace($array), $sub_content);
            }
            else{
              $data = '<tr><td colspan=2>'.NO_HOLD_AMOUNT.'</td></tr>';
            }
          }
          else
          {
            $data = '<tr><td colspan=2>'.NO_HOLD_AMOUNT.'</td></tr>';
          }
        }
      }else{
        $data = '<tr><td colspan=2>'.NO_HOLD_AMOUNT.'</td></tr>';
      }
    }
    return $data;
  }
  public function serviceonholdAmount($dataVar="")
  {
    if($dataVar== 'all')
    {
      $query = $this->db->pdoQuery("select SUM(w.amount) As totalAmount from tbl_wallet As w
        INNER JOIN tbl_services_order As so ON so.id = w.entity_id
        INNER JOIN tbl_services As s ON s.id = so.servicesId
        where w.userId=? and w.status=? and w.transactionType=? and w.userType=? and entity_type=? and w.paymentStatus = ?",array($this->sessUserId,'onhold','escrow','c','s','p'))->result();
      $data = ($query['totalAmount']=='') ? '0' : $query['totalAmount'];
    }
    else
    {
      $data = '';
      $query = $this->db->pdoQuery("select s.serviceTitle,w.amount from tbl_wallet As w
        INNER JOIN tbl_services_order As so ON so.id = w.entity_id
        INNER JOIN tbl_services As s ON s.id = so.servicesId
        where w.userId=? and w.status=? and w.transactionType=? and w.userType=? and entity_type=? and w.paymentStatus = ?",array($this->sessUserId,'onhold','escrow','c','s','p'))->results();
      if(count($query)>0)
      {
        foreach ($query as $value)
        {
          $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/onhold_amount-sd.skd");
          $sub_content = $sub_content->compile();
          $array = array(
            "%TITLE%" => filtering(ucfirst($value['serviceTitle'])),
            "%AMNT%" => CURRENCY_SYMBOL.$value['amount']
          );
          $data .= str_replace(array_keys($array), array_replace($array), $sub_content);
        }
      }
      else
      {
       $data = '<tr><td colspan=2>'.NO_HOLD_AMOUNT.'</td></tr>';
     }
   }
   return $data;
  }
  public function featuredFees($type=''){
    if($type == ''){
      $query = $this->db->pdoQuery("SELECT SUM(amount) as total_amount FROM `tbl_wallet` WHERE userType = 'c' AND userId = ? AND transactionType='featuredFees' ",array($this->sessUserId))->result();
      return !empty($query['total_amount']) ? $query['total_amount'] : 0;
    }else{
      $content = '';
      $query = $this->db->pdoQuery("SELECT  w.*,j.jobTitle FROM `tbl_wallet` as w LEFT JOIN tbl_jobs as j on w.entity_id = j.id
        WHERE w.userType = 'c' AND w.userId = ? AND w.transactionType='featuredFees' ",array($this->sessUserId))->results();
      if(!empty($query)){
        foreach ($query as $key => $value) {
          $content .= '<tr><td>'.ucfirst($value['jobTitle']).'</td><td>'.CURRENCY_SYMBOL.$value['amount'].'</td></tr>';
        }
      }else{
        $content = "<tr><td colspan=2>".LBL_NO_FEATURED_JOB_PAYMENTS."</td></tr>";
      }
      return $content;

    }
  }
  public function getRefundData($type= ''){
    if($type == ''){
        $query = "SELECT SUM(w.amount) as total_amount FROM `tbl_wallet` as w WHERE w.entity_type = 's' AND w.userType = 'c' AND w.transactionType = 'refund' and w.userId = ? ";
        $data = $this->db->pdoQuery($query,[$this->sessUserId])->result();
        return !empty($data['total_amount']) ? $data['total_amount'] : 0;
    }else{
      $query = "SELECT w.*,s.serviceTitle FROM `tbl_wallet` as w 
      LEFT JOIN tbl_services_order  as so ON w.entity_id = so.id
      LEFT JOIN tbl_services  as s ON so.servicesId = s.id
      WHERE entity_type = 's' AND userType = 'c' AND transactionType = 'refund' and w.userId = ? 
      ORDER BY w.createdDate DESC";
      $data = $this->db->pdoQuery($query,[$this->sessUserId])->results();
      $content = '';
      if(!empty($data) && !empty($data[0]['serviceTitle'])){
        foreach ($data as $key => $value) {
            $value['serviceTitle'] = !empty($value['serviceTitle']) ? $value['serviceTitle'] : 'N/A';
            $content .= '<tr>';
            $content .= '<td> '.$value['serviceTitle'].' </td>';
            $content .= '<td> '.CURRENCY_SYMBOL.$value['amount'].'</td>';
            $content .= '<td> '.date('dS M,Y H:i:s',strtotime($value['createdDate'])).'</td>';
            $content .= '</tr>';
        }
      }else{
        $content = "<tr><td colspan=3>".LBL_NO_REFUND_HISTORY."</td></tr>";
      }
      return $content;
    }
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
  public function sendRedeemRequestData($data)
  {
    extract($data);
    $amount = $this->db->insert("tbl_redeem_request",array("userId"=>$this->sessUserId,"amount"=>$amountRedeem,"createdDate"=>date('Y-m-d H:i:s'),"paymentStatus"=>'pending'));
    $user_detail = getUser($this->sessUserId);
    if(empty($user_detail['paypal_email']) || $user_detail['isPaypalVarified'] == 'n'){
      $msgType = $_SESSION["msgType"] = disMessage(array('type'=>'err','var'=>PAYPAL_EMAIL_IS_NOT_VERIFIED)); 
      redirectPage(SITE_URL."c/account-setting");
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
    $msgType = $_SESSION["msgType"] = disMessage(array('type'=>'suc','var'=>YOUR_REDEEM_REQUEST_FOR.' '.CURRENCY_SYMBOL.$amountRedeem.' '.AMOUNT_HAS_BEEN_SENT_SUCCESSFULLY));
    redirectPage(SITE_URL."c/financial-dashboard");
  }
}
?>


