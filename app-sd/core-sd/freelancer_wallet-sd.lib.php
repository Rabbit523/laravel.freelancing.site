<?php

class FreelancerWallet extends Home{
	function __construct($module = "", $id = 0, $token = "") {
		foreach ($GLOBALS as $key => $values) {
			$this->$key = $values;
		}
		$this->module = $module;
		$this->id = $id;
	}
	
  	public function getPageContent() 
  	{
  		  $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/freelancer_wallet-sd.skd");
        $sub_content = $sub_content->compile();

        $user_detail = $this->db->pdoQuery("select * from tbl_users where id=?",array($this->sessUserId))->result(); 

        $array = array(
        	"%TOTAL_WALLET_AMOUNT%" => ($user_detail['walletAmount']=='') ? CURRENCY_SYMBOL.'0' : CURRENCY_SYMBOL.$user_detail['walletAmount'],
          "%TOTAL_REDEEM_AMOUNT%" => ($this->redeemRequest("all")=='') ? CURRENCY_SYMBOL.'0' : CURRENCY_SYMBOL.$this->redeemRequest("all"),
          "%REDEEM_HISTORY%" => $this->redeemRequest(),
          "%WALLET_HISTORY%" => $this->wallet_history(),
          "%TOTAL_WALLET_AMNT%" => ($this->wallet_history("all")=='') ? CURRENCY_SYMBOL.'0' : CURRENCY_SYMBOL.$this->wallet_history("all"),
          "%JOB_HOLD_AMNT%" => $this->onholdAmount(),
          "%SERVICES_HOLD_AMNT%" => $this->onholdServiceAmount(),
          "%TOTAL_JOB_ONHOLD_AMNT%" => CURRENCY_SYMBOL.$this->onholdAmount('all'),
          "%TOTAL_SERVICES_ONHOLD_AMNT%" => CURRENCY_SYMBOL.$this->onholdServiceAmount('all')
        	);

  		return str_replace(array_keys($array), array_replace($array), $sub_content);
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
                      "%AMNT%" => CURRENCY_SYMBOL.$value['amount']
                  );
                  $data .= str_replace(array_keys($array), array_replace($array), $sub_content);
              }
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
    } 
    public function sendRedeemRequestData($data)
    {
      extract($data);
      $amount = $this->db->insert("tbl_redeem_request",array("userId"=>$this->sessUserId,"amount"=>$amountRedeem,"createdDate"=>date('Y-m-d H:i:s'),"paymentStatus"=>'pending'));

      $user_detail = getUser($this->sessUserId);

      if(empty($user_detail['paypal_email']) || $user_detail['isPaypalVarified'] == 'n'){
        $msgType = $_SESSION["msgType"] = disMessage(array('type'=>'err','var'=>' You Paypal email is not verified. Please enter correct Paypal email.')); 
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
}
?>


