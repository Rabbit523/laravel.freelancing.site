<?php

class CreditPlan {
	function __construct($module = "", $id = 0, $token = "") {
		foreach ($GLOBALS as $key => $values) {
			$this->$key = $values;
		}
		$this->module = $module;
		$this->id = $id;
	}	
  public function getPageContent() {
  	$sub_content = new MainTemplater(DIR_TMPL . $this->module . "/credit_plan-sd.skd");
    $sub_content = $sub_content->compile();
    $current_plan_detail = $this->db->pdoQuery("select * from tbl_user_plan where userId=? and isCurrent=?",array($this->sessUserId,'y'))->result();
    $planRes = $this->db->pdoQuery("select * from tbl_credit_package where id=? AND isActive=?",array($current_plan_detail["planId"],'y'))->result();
    $usable_credit = ($current_plan_detail['used_credit']." /".$current_plan_detail['no_credit']);
		return str_replace(array("%PLAN_DETAIL%","%SUB_HEADER_CONTENT%","%TOTAL_CREDIT%"), array($this->plan_loop(),subHeaderContent("credit"),$usable_credit), $sub_content);
  }  
  public function plan_loop(){
  	$data = $this->db->pdoQuery("select * from tbl_credit_package where isActive=?",array('y'))->results();
  	$loop_data = '';
  	foreach ($data as $key => $value) 
  	{
      $current_plan_detail = $this->db->pdoQuery("select * from tbl_user_plan where userId=? and isCurrent=?",array($this->sessUserId,'y'))->result();
      $is_free_plan = $value["price"]==0?"y":"n";
      $upgration_detail_query = $this->db->pdoQuery("select * from tbl_user_plan where userId=? and isCurrent=? and planId=? and subscribedDate LIKE '%".date('Y-m',strtotime($current_plan_detail['subscribedDate']))."%' ORDER BY id DESC",array($this->sessUserId,'n',$value['id']));
      $upgration_detail = $upgration_detail_query->affectedRows();
      if($upgration_detail!=0)
      {
        $upgration_detail_row = $upgration_detail_query->result();
        $subscribe_date = date('dS F, Y',strtotime($upgration_detail_row['subscribedDate']));
        $upgration_date = date('dS F, Y',strtotime($current_plan_detail['subscribedDate']));
        $upgrade_date_class = '';
      }
      else
      {
        $subscribe_date = ($current_plan_detail['isCurrent']=='y' && $current_plan_detail['subscribedDate']!='') ? date('dS F, Y',strtotime($current_plan_detail['subscribedDate'])) : '';
         $upgration_date = date('dS F, Y',strtotime($current_plan_detail['subscribedDate'] .'+1 month'));
        $upgrade_date_class = 'hide';
      }
		  $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/loop_data-sd.skd");
    	$sub_content = $sub_content->compile();
      $current_class = ($current_plan_detail['planId'] == $value['id']) ? 'free' : '';  
    	$array = array(
          "%PLAN_ID%" => $value['id'],
      		"%TITLE%"=> filtering($value['title']),
      		"%PRICE%"=> filtering($value['price'])."<sup>".CURRENCY_SYMBOL."</sup>",
      		/*"%CREDIT%"=> filtering($value['noCredits']),*/
      		"%USABLE_CREDIT%"=> ($current_plan_detail['planId'] == $value['id']) ? ($current_plan_detail['used_credit']." /".$current_plan_detail['no_credit']) : $value['noCredits'],
          "%USED_CREDIT_CLASS%" => ($current_plan_detail['planId'] != $value['id']) ? 'hide' : '',
      		"%SUBSCRIBED_DATE%"=>  $subscribe_date,
          "%NEXT_SUBSCRIBED_DATE%" =>$upgration_date,
          "%CURRENT_CLASS%" => ($current_class=='free') ? 'Upgrade' : 'Subscribe',
          "%CURRENT_PLAN_DIV_COLOR%" => $current_class,
          "%SUBSCRIBE_DETAIL%" => ($current_class=='free') ? '' : 'hide',
          "%UPGRADE_DATE%" => $upgration_date,
          "%UPGRADE_DATE_CLASS%" => $upgrade_date_class,
          "%IS_FREE_HIDE%" => $is_free_hide,
          "%ACTIVE_CLASS%" => ($current_plan_detail['planId'] == $value['id']) ? 'active_plan' : '',
          "%ACTIVE_ICON%" => ($current_plan_detail['planId'] == $value['id']) ? '<span class="icon"><i class="fa fa-check-circle" aria-hidden="true"></i></span>' : '',
          "%TOTAL_CREDIT_LBL%" => ($current_plan_detail['planId'] == $value['id']) ? 'Available Credits' : 'Total Credits'
    	);
  			$loop_data .= str_replace(array_keys($array),array_replace($array),$sub_content);
		}
  		return $loop_data;
	}
}
?>


