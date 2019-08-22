<?php

class CustomerWallet extends Home{
	function __construct($module = "", $id = 0, $token = "") {
		foreach ($GLOBALS as $key => $values) {
			$this->$key = $values;
		}
		$this->module = $module;
		$this->id = $id;
	}

 public function getPageContent()
 {
  $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/customer_wallet-sd.skd");
  $sub_content = $sub_content->compile();

  $user_detail = $this->db->pdoQuery("select * from tbl_users where id=?",array($this->sessUserId))->result();

  $array = array(
   "%TOTAL_WALLET_AMOUNT%" => ($user_detail['walletAmount']=='') ? CURRENCY_SYMBOL.'0' : CURRENCY_SYMBOL.$user_detail['walletAmount'],
   "%TOTAL_REDEEM_AMOUNT%" => ($this->redeemRequest("all")=='') ? CURRENCY_SYMBOL.'0' : CURRENCY_SYMBOL.$this->redeemRequest("all"),
   "%REDEEM_HISTORY%" => $this->redeemRequest(),
   "%WALLET_HISTORY%" => $this->wallet_history(),
   "%TOTAL_WALLET_AMNT%" => ($this->wallet_history("all")=='') ? CURRENCY_SYMBOL.'0' : CURRENCY_SYMBOL.$this->wallet_history("all"),
   "%JOB_HOLD_AMNT%" => $this->jobOnHoldAmount(),
   "%SERVICES_HOLD_AMNT%" => $this->serviceonholdAmount(),
   "%TOTAL_JOB_ONHOLD_AMNT%" => CURRENCY_SYMBOL.$this->jobOnHoldAmount('all'),
   "%TOTAL_SERVICES_ONHOLD_AMNT%" => CURRENCY_SYMBOL.$this->serviceonholdAmount('all'),
   '%FEATUREDFEES_TOTAL%' => $this->featuredFees(),
   '%FEATUREDFEES_CONTENT%' => $this->featuredFees('all'),
   '%DISPUTE_REFUND_TOTAL%' => CURRENCY_SYMBOL.$this->getRefundData(),
   '%DISPUTE_REFUND_DATA%' => $this->getRefundData('all')
 );

  return str_replace(array_keys($array), array_replace($array), $sub_content);
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




}


?>


