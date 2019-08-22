<?php

class ServiceOrders {
	function __construct($module = "", $id = 0, $token = "") {
		foreach ($GLOBALS as $key => $values) {
			$this->$key = $values;
		}
		$this->module = $module;
		$this->id = $id;
	}	
  public function getPageContent() {
  	$sub_content = new MainTemplater(DIR_TMPL . $this->module . "/service_orders-sd.skd");
    $sub_content = $sub_content->compile();
    return $sub_content;
  }  
}
?>


