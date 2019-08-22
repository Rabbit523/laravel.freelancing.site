<?php

class ConfirmServiceOrder {
	function __construct($module = "", $id = 0, $token = "",$search_array= array()) {
		foreach ($GLOBALS as $key => $values) {
			$this->$key = $values;
		}
		$this->module = $module;
		$this->id = $id;
    $this->search_array = $search_array;
  }

  public function getPageContent()
  {
    $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/".$this->module.".skd");
    $sub_content = $sub_content->compile();
    $odata = $this->getOrderDetails();

    $oid = !empty($odata['id']) ? $odata['id'] : '0';

    $final_price_class = ($this->finalPrice()=='') ? 'hide' : '';
    return str_replace(array('%OID%',"%ORDER_LOOP%","%FINAL_PRICE%","%FINAL_PRICE_CLASS%"), array($oid,$this->order_loop(),CURRENCY_SYMBOL.$this->finalPrice(),$final_price_class), $sub_content);
  }

  public function finalPrice()
  {
   $data = $this->db->pdoQuery("select o.*,s.servicesPrice,o.quantity from tbl_services_order_temp As o
    LEFT JOIN tbl_services As s ON s.id = o.servicesId where o.orderStatus=? and o.customerId=?",array('p',$this->sessUserId))->result();
   $amount = $data['servicesPrice'] * $data['quantity'];

   if(!empty($data['addOn'])){

     $addons = $this->db->pdoQuery("select * from tbl_services_addon where id in (".$data['addOn'].")")->results();
     $addons = !empty($addons) ? $addons : [];
     foreach ($addons as $key => $value) {
       $amount =  $amount + $value['addonPrice'];
     }
   }
   return $amount;
 }


 public function finaldays()
 {
   $data = $this->db->pdoQuery("select o.*,s.noDayDelivery,o.quantity from tbl_services_order_temp As o
    LEFT JOIN tbl_services As s ON s.id = o.servicesId where o.orderStatus=? and o.customerId=?",array('p',$this->sessUserId))->result();
   $days = $data['noDayDelivery'] * $data['quantity'];
   if(!empty($data['addOn'])){
     $addons = $this->db->pdoQuery("select * from tbl_services_addon where id in (?)",[$data['addOn']])->results();
     $addons = !empty($addons) ? $addons : [];
     foreach ($addons as $key => $value) {
      $days =  $days + $value['addonDayRequired'];
    }
  }

  return $days;

}

public function getOrderDetails(){
  $query = $this->db->pdoQuery("select o.*,s.id as service_id from tbl_services_order_temp As o
    LEFT JOIN tbl_services As s ON s.id = o.servicesId
    where o.orderStatus=? and o.customerId=?",array('p',$this->sessUserId))->result();
  return $query;
}



public function order_loop()
{
 $query = $this->db->pdoQuery("select s.serviceTitle,s.id As sId,s.noDayDelivery,o.totalPayment,o.totalDuration,o.addOn,o.id As oId,o.quantity,s.servicesPrice from tbl_services_order_temp As o
  LEFT JOIN tbl_services As s ON s.id = o.servicesId
  where o.orderStatus=? and o.customerId=? ",array('p',$this->sessUserId))->results();
 $data = '';$i=1;
 if(count($query)>0)
 {

  foreach($query As $value)
  {
    $this->db->pdoQuery("update tbl_services_order_temp set totalPayment = ? , totalDuration = ? where id=?",array($this->finalPrice(),$this->finalDays(),$value['oId']));
    $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/order_loop-sd.skd");
    $sub_content = $sub_content->compile();

    $service_img = getserviceImages($value['sId'],1);
    $array = array(
     "%SERVICE_IMG%" => $service_img[0],
     "%SERVICE_TITLE%" => filtering(ucfirst($value['serviceTitle'])),
     "%ADDON_LOOP%" => ($value['addOn']!='') ? $this->addon_loop($value['addOn'],'',$value['oId']) : '',
     "%ADDON_PRICE%" => ($value['addOn']!='') ? $this->addon_loop($value['addOn'],'allPrice') : '',
     "%PRICE%" => $value['totalPayment']."<span>".CURRENCY_SYMBOL."</span>",
     "%DELIVERED_DAY%" => $this->finaldays(),
     "%TOTAL_DAYS%" => $value['totalDuration'],
     "%DATA_ID%" => $i,
     "%ORDER_ID%" => $value['oId'],
     "%QTY%"=> $value['quantity'],
     "%ONE_QTY_PRICE%"=> $value['servicesPrice']."<span>".CURRENCY_SYMBOL."</span>",
     "%ONE_QTY_DAY%"=> $value['noDayDelivery'],
     "%ADDON_DAY%"=> ($value['addOn']!='') ? $this->addon_loop($value['addOn'],'allDays') : ''

   );
    $data .= str_replace(array_keys($array), array_values($array), $sub_content);
    $i++;
  }
}
else
{
  $data .= "<tr><td colspan='7'><center>".NO_SERVICE_ORDER."</center></td></tr>";
}
return $data;
}

public function addon_loop($id,$type='',$orderId='')
{
 $data = '';
 if($type=='allPrice')
 {
  $query = $this->db->pdoQuery("select SUM(addonPrice) As addOnPrice from tbl_services_addon where id IN(".$id.") ")->result();
  $data .= $query['addOnPrice'];
}
else if($type=='allDays')
{
  $query = $this->db->pdoQuery("select SUM(addonDayRequired) As addonDayRequired from tbl_services_addon where id IN(".$id.") ")->result();
  $data .= $query['addonDayRequired'];
}
else
{
  $query = $this->db->pdoQuery("select * from tbl_services_addon where id IN(".$id.") ")->results();
  foreach ($query as $value)
  {
   $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/addOn_loop-sd.skd");
   $sub_content = $sub_content->compile();

   $array = array(
    "%TITLE%" => filtering(ucfirst($value['addonTitle'])),
    "%DAYS%" => $value['addonDayRequired'],
    "%PRICE%" => $value['addonPrice']."<span>".CURRENCY_SYMBOL."</span>",
    "%ROW_PRICE%" => $value['addonPrice'],
    "%DESC%" => filtering($value['addonDesc']),
    "%ADDON_ID%" => $value['id'],
    "%ORDER_ID%" => $orderId
  );

   $data .= str_replace(array_keys($array), array_values($array), $sub_content);
 }
}
return $data;
}

public function payForOrder($data)
{
  extract($data);
  $j=1;$total = 0;
  $userWalletAmount = finalWalletAmount($this->sessUserId);


  for($i=0;$i<count($quantity);$i++)
  {
    $addOn_detail = $this->db->pdoQuery("select * from tbl_services_order_temp where id=?",array($id[$i]))->result();




    if($addOn_detail['addOn']!='')
    {
      $addOn_query = $this->db->pdoQuery("select SUM(addonPrice) As TotalAddonPrice from tbl_services_addon where id IN(".$addOn_detail['addOn'].")")->result();
      $addonPrice = $addOn_query['TotalAddonPrice'];
    }
    else
    {
      $addonPrice = 0;
    }
    $orderId = $id[$i];
    $qty = $quantity[$i];
    $price = $qty*$_REQUEST['price_'.$j];
    $finalPrice = $this->finalPrice();
    $deliveryDays = $_REQUEST['deliveryDays_'.$j];
    $finalDays = $this->finalDays();

    if($userWalletAmount<$finalPrice)
    {
      $needAmount = $finalPrice - $userWalletAmount;
      $msgType = $_SESSION["msgType"] = disMessage(array('type'=>'err','var'=> 'You have insufficient wallet balance. Please credit your wallet to complete your payment. Your current wallet balance - '.CURRENCY_SYMBOL.$userWalletAmount.'.You need '.CURRENCY_SYMBOL.$needAmount.' amount to complete the payment'));
      redirectPage(SITE_URL."c/financial-dashboard");
    }
    else
    {
      /*order entry*/



      $last_id = $this->db->insert("tbl_services_order",array("servicesId"=>$addOn_detail['servicesId'],"freelanserId"=>$addOn_detail['freelanserId'],"customerId"=>$this->sessUserId,"orderDate"=>date('Y-m-d H:i:s'),"addOn"=>$addOn_detail['addOn'],"accept_status"=>'p',"quantity"=>$qty,"totalPayment"=>$finalPrice,"totalDuration"=>$finalDays,"serviceStatus"=>'no',"orderStatus"=>'c',"paymentStatus"=>'p'))->getLastInsertId();


      $sdata = $this->db->pdoQuery("select * from tbl_services_order where id=?",array($last_id))->result();
      $sdata['service_data'] = $this->db->pdoQuery("select * from tbl_services where id=?",array($sdata['servicesId']))->result();
      $adata = !empty($sdata['addOn']) ? $sdata['addOn'] : 0;
      $sdata['service_addons_data'] = $this->db->pdoQuery("select * from tbl_services_addon where id in (".$adata.") ")->results();


      $this->db->pdoQuery("update tbl_services_order set service_order_data = ? where id= ? ",array(json_encode($sdata),$last_id));

      /*wallet entry*/
      $this->db->insert("tbl_wallet",array("userType"=>'c',"entity_id"=>$last_id,"entity_type"=>'s',"userId"=>$this->sessUserId,"amount"=>$finalPrice,"transactionType"=>'escrow',"status"=>'onhold',"createdDate"=>date('Y-m-d H:i:s'),"ipAddress"=>get_ip_address()));

      /*delete temp entry*/
      $this->db->delete("tbl_services_order_temp",array("id"=>$id[$i]));

      $this->db->pdoQuery("update tbl_users set walletAmount=walletAmount-'".$finalPrice."' where email=?",array($_SESSION['pickgeeks_email']));

      $freelancerEmail = getUserDetails('email',$addOn_detail['freelanserId']);
      $customerEmail = getUserDetails('email',$this->sessUserId);
      $customerName = getUserDetails('CONCAT(firstName," ",lastName)',$this->sessUserId);
      $freelancerName = getUserDetails('CONCAT(firstName," ",lastName)',$addOn_detail['freelanserId']);
      $serviceDetail = $this->db->pdoQuery("select * from tbl_services where id=?",array($addOn_detail['servicesId']))->result();
      $link = SITE_URL."service/".$serviceDetail['servicesSlug'];
      $service_link = "<a href='".$link."'>".filtering(ucfirst($serviceDetail['serviceTitle']))."</a>";

      /*send mail to customer start*/
      $arrayCont = array('greetings'=>"There!",'SERVICE_TITLE'=>$service_link);
      $array = generateEmailTemplate('Customer_confirmed_order',$arrayCont);
      sendEmailAddress($customerEmail,$array['subject'],$array['message']);
      /*send mail to customer end*/

      /*Admin notification start*/

      $msg = $customerName." has purchased service - ".$serviceDetail['serviceTitle']." of ".$freelancerName.".";
      $this->db->insert("tbl_notification",array("userId"=>'0',"message"=>$msg,"isRead"=>'n',"notificationType"=>'a',"createdDate"=>date('Y-m-d H:i:s')));

      /*Admin notification end*/

      /*Freelancer notification start*/

      $msg=$customerName." has purchased your service - ".$serviceDetail['serviceTitle'];
      $link=SITE_URL."f/services-order";
      notify('f',$addOn_detail['freelanserId'],$msg,$link);

      /*Freelancer notification end*/

      /*send mail to freelancer start*/

      $addOnList = '';
      foreach ($addOn_detail['addOn'] as $value) {
        $addOnQuery = $this->db->pdoQuery("select * from tbl_services_addon where id ='".$value."' ")->result();
        $addOnList = "<p>".filtering(ucfirst($addOnQuery['addonTitle']))."</p>"."<br>";
      }




      $arrayCont1 = array('greetings'=>"There!",'SERVICE_TITLE'=>$service_link,"CUSTOMER_NM"=>$customerName,"QTY"=>$qty,"PRICE"=>CURRENCY_SYMBOL.$finalPrice,"ADDON"=>$addOnList);
      $array1 = generateEmailTemplate('Customer_confirm_order_intimate_to_freelancer',$arrayCont1);
      sendEmailAddress($freelancerEmail,$array1['subject'],$array1['message']);
      /*send mail to freelancer end*/

      $msgType = $_SESSION["msgType"] = disMessage(array('type'=>'suc','var'=>'Your order for '.$serviceDetail['serviceTitle'].' has been confirmed successfully.'));
      //redirectPage(SITE_URL."c/service-order");
      redirectPage(SITE_URL."service/workroom/".base64_encode($last_id)."/".$serviceDetail['servicesSlug']);
    }
    $j++;
  }

}

}
?>


